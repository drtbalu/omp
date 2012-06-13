<?php

/**
 * @file controllers/informationCenter/form/InformationCenterNotifyForm.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class InformationCenterNotifyForm
 * @ingroup informationCenter_form
 *
 * @brief Form to notify a user regarding a file
 */



import('lib.pkp.classes.form.Form');

class InformationCenterNotifyForm extends Form {
	/** @var int The file/monograph ID this form is for */
	var $itemId;

	/** @var int The type of item the form is for (used to determine which email template to use) */
	var $itemType;

	/**
	 * Constructor.
	 */
	function InformationCenterNotifyForm($itemId, $itemType) {
		parent::Form('controllers/informationCenter/notify.tpl');
		$this->itemId = $itemId;
		$this->itemType = $itemType;

		$this->addCheck(new FormValidatorListbuilder($this, 'users', 'informationCenter.notify.warning'));
		$this->addCheck(new FormValidator($this, 'message', 'required', 'informationCenter.notify.warning'));
		$this->addCheck(new FormValidatorPost($this));
	}

	/**
	 * Fetch the form.
	 * @see Form::fetch()
	 */
	function fetch(&$request) {
		$templateMgr =& TemplateManager::getManager();
		if($this->itemType == ASSOC_TYPE_MONOGRAPH) {
			$monographId = $this->itemId;
		} else {
			$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
			$monographFile =& $submissionFileDao->getLatestRevision($this->itemId);
			$monographId = $monographFile->getMonographId();
		}

		$templateMgr->assign_by_ref('monographId', $monographId);
		$templateMgr->assign_by_ref('itemId', $this->itemId);

		// All stages can choose the default template
		$templateKeys = array('NOTIFICATION_CENTER_DEFAULT');

		// template keys indexed by stageId
		$stageTemplates = array(
			WORKFLOW_STAGE_ID_SUBMISSION => array(),
			WORKFLOW_STAGE_ID_INTERNAL_REVIEW => array('EDITOR_ASSIGN'),
			WORKFLOW_STAGE_ID_EXTERNAL_REVIEW => array('EDITOR_ASSIGN'),
			WORKFLOW_STAGE_ID_EDITING => array('COPYEDIT_REQUEST'),
			WORKFLOW_STAGE_ID_PRODUCTION => array('LAYOUT_REQUEST', 'LAYOUT_COMPLETE', 'INDEX_REQUEST', 'INDEX_COMPLETE', 'EDITOR_ASSIGN')
		);

		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$monograph =& $monographDao->getById($monographId);
		$currentStageId = $monograph->getStageId();

		$templateKeys = array_merge($templateKeys, $stageTemplates[$currentStageId]);

		import('classes.mail.MonographMailTemplate');

		foreach ($templateKeys as $templateKey) {
			$template = new MonographMailTemplate($monograph, $templateKey);
			$template->assignParams(array());
			$templates[$templateKey] = $template->getSubject();
		}

		unset($templateKeys);
		$templateMgr->assign_by_ref('templates', $templates);

		// check to see if we were handed a userId from the stage participants grid.  If so,
		// pass that in so the list builder can pre-populate. The Listbuilder validates this.

		$templateMgr->assign('userId', (int) $request->getUserVar('userId'));

		return parent::fetch($request);
	}

	/**
	 * Assign form data to user-submitted data.
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('message', 'users', 'template'));
		import('lib.pkp.classes.controllers.listbuilder.ListbuilderHandler');
		$userData = $this->getData('users');
		ListbuilderHandler::unpack($request, $userData);
	}

	/**
	 * Sends a a notification.
	 * @see Form::execute()
	 */
	function execute(&$request) {
		return parent::execute($request);
	}

	/**
	 * Prepare an email for each user and send
	 * @see ListbuilderHandler::insertEntry
	 */
	function insertEntry(&$request, $newRowId) {

		$userDao =& DAORegistry::getDAO('UserDAO');
		$application =& Application::getApplication();
		$request =& $application->getRequest(); // need to do this because the method version is null.
		$fromUser =& $request->getUser();

		$monographDao =& DAORegistry::getDAO('MonographDAO');
		import('classes.mail.MonographMailTemplate');

		if($this->itemType == ASSOC_TYPE_MONOGRAPH) {
			$monographId = $this->itemId;
		} else {
			$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
			$monographFile =& $submissionFileDao->getLatestRevision($this->itemId);
			$monographId = $monographFile->getMonographId();
		}

		$monograph =& $monographDao->getById($monographId);
		$template = $this->getData('template');

		$email = new MonographMailTemplate($monograph, $template, null, null, null, false);
		$email->setFrom($fromUser->getEmail(), $fromUser->getFullName());

		import('controllers.grid.submissions.SubmissionsListGridCellProvider');
		$dispatcher =& $request->getDispatcher();

		foreach ($newRowId as $id) {
			$user = $userDao->getUser($id);
			if (isset($user)) {
				$email->addRecipient($user->getEmail(), $user->getFullName());
				$email->setBody($this->getData('message'));
				list($page, $operation) = SubmissionsListGridCellProvider::getPageAndOperationByUserRoles($request, $monograph, $user->getId());
				$submissionUrl = $dispatcher->url($request, ROUTE_PAGE, null, $page, $operation, array('monographId' => $monograph->getId()));

				// these are for *_REQUEST emails
				$email->assignParams(array(
					// COPYEDIT_REQUEST
					'copyeditorName' => $user->getFullName(),
					'copyeditorUsername' => $user->getUsername(),
					'submissionCopyeditingUrl' => $submissionUrl,
					// LAYOUT_REQUEST
					'layoutEditorName' => $user->getFullName(),
					'submissionUrl' => $submissionUrl,
					'layoutEditorUsername' => $user->getUsername(),
					// LAYOUT_COMPLETE, INDEX_COMPLETE, EDITOR_ASSIGN
					'editorialContactName' => $user->getFullname(),
					// INDEX_REQUEST
					'indexerName' => $user->getFullName(),
					'indexerUsername' => $user->getUsername(),
					// EDITOR_ASSIGN
					'editorUsername' => $user->getUsername(),
				));

				$this->_createNotifications($request, $monograph, $user, $template);
				$email->send($request);
				// remove the INDEX_ and LAYOUT_ tasks if a user has sent the appropriate _COMPLETE email
				switch ($template) {
					case 'LAYOUT_COMPLETE':
						$this->_removeUploadTaskNotification($monograph, NOTIFICATION_TYPE_LAYOUT_ASSIGNMENT, $request);
						break;
					case 'INDEX_COMPLETE':
						$this->_removeUploadTaskNotification($monograph, NOTIFICATION_TYPE_INDEX_ASSIGNMENT, $request);
						break;
				}
			}
		}
	}

	/**
	 * Delete a signoff
	 * (It was throwing a warning when this was not specified. We just want
	 * client side delete.)
	 */
	function deleteEntry(&$request, $rowId) {
		return true;
	}

	/**
	 * Internal method to create the necessary notifications, with user validation.
	 * @param PKPRquest $request
	 * @param Monograph $monograph
	 * @param PKPUser $user
	 * @param string $template
	 */
	function _createNotifications(&$request, $monograph, $user, $template) {

		$currentStageId = $monograph->getStageId();
		$stageAssignmentDao =& DAORegistry::getDAO('StageAssignmentDAO');
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$stageAssignments =& $stageAssignmentDao->getBySubmissionAndStageId($monograph->getId(), $monograph->getStageId(), null, $user->getId());
		$notificationMgr = new NotificationManager();

		switch ($template) {
			case 'COPYEDIT_REQUEST':
				while ($stageAssignment =& $stageAssignments->next()) {
					$userGroup =& $userGroupDao->getById($stageAssignment->getUserGroupId());
					if (in_array($userGroup->getRoleId(), array(ROLE_ID_PRESS_MANAGER, ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_ASSISTANT))) {
						import('classes.monograph.MonographFile');
						$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO');
						$monographFileSignoffDao =& DAORegistry::getDAO('MonographFileSignoffDAO');
						$monographFiles =& $submissionFileDao->getLatestRevisions($monograph->getId(), MONOGRAPH_FILE_COPYEDIT);
						foreach ($monographFiles as $monographFile) {
							$signoffFactory =& $monographFileSignoffDao->getAllBySymbolic('SIGNOFF_COPYEDITING', $monographFile->getFileId());
							while ($signoff =& $signoffFactory->next()) {
								$notificationMgr->updateCopyeditRequestNotification($signoff, $user, $request);
								unset($signoff);
							}
						}
						return;
					}
				}
				// User not in valid role for this task/notification.
				break;
			case 'LAYOUT_REQUEST':
				while ($stageAssignment =& $stageAssignments->next()) {
					$userGroup =& $userGroupDao->getById($stageAssignment->getUserGroupId());
					if (in_array($userGroup->getRoleId(), array(ROLE_ID_PRESS_MANAGER, ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_ASSISTANT))) {
						$notificationMgr->updateProductionRequestNotification($monograph, $user, NOTIFICATION_TYPE_LAYOUT_ASSIGNMENT, $request);
						return;
					}
				}
				// User not in valid role for this task/notification.
				break;
			case 'INDEX_REQUEST':
				while ($stageAssignment =& $stageAssignments->next()) {
					$userGroup =& $userGroupDao->getById($stageAssignment->getUserGroupId());
					if (in_array($userGroup->getRoleId(), array(ROLE_ID_PRESS_MANAGER, ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_ASSISTANT))) {
						$notificationMgr->updateProductionRequestNotification($monograph, $user, NOTIFICATION_TYPE_INDEX_ASSIGNMENT, $request);
						return;
					}
				}
				// User not in valid role for this task/notification.
				break;
		}
	}

	/**
	 * Private method to clear potential tasks that may have been assigned to certain
	 * users on certain stages.  Right now, just LAYOUT uploads on the production stage.
	 * @param Monograph $monograph
	 * @param int $task
	 * @param PKRequest $request
	 */
	function _removeUploadTaskNotification(&$monograph, $task, &$request) {

		// if this is a submission by a LAYOUT_EDITOR for a monograph in production, check
		// to see if there is a task notification for that and if so, clear it.
		$currentStageId = $monograph->getStageId();
		$notificationMgr = new NotificationManager();

		if ($currentStageId == WORKFLOW_STAGE_ID_PRODUCTION) {

			$user =& $request->getUser();
			$stageAssignmentDao =& DAORegistry::getDAO('StageAssignmentDAO');
			$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
			$stageAssignments =& $stageAssignmentDao->getBySubmissionAndStageId($monograph->getId(), $monograph->getStageId(), null, $user->getId());

			while ($stageAssignment =& $stageAssignments->next()) {
				$userGroup =& $userGroupDao->getById($stageAssignment->getUserGroupId());
				if (in_array($userGroup->getRoleId(), array(ROLE_ID_PRESS_MANAGER, ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_ASSISTANT))) {
					$notificationMgr->deleteProductionRequestNotification($monograph, $user, $task, $request);
					return;
				}
			}
		}
	}
}

?>
