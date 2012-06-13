<?php

/**
 * @file classes/submission/form/SubmissionSubmitStep3Form.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionSubmitStep3Form
 * @ingroup submission_form
 *
 * @brief Form for Step 3 of author monograph submission.
 */


import('classes.submission.form.SubmissionSubmitForm');
import('classes.submission.SubmissionMetadataFormImplementation');

class SubmissionSubmitStep3Form extends SubmissionSubmitForm {

	/** @var SubmissionMetadataFormImplementation */
	var $_metadataFormImplem;

	/**
	 * Constructor.
	 */
	function SubmissionSubmitStep3Form($press, $monograph) {
		parent::SubmissionSubmitForm($press, $monograph, 3);

		$this->_metadataFormImplem = new SubmissionMetadataFormImplementation($this);

		$this->_metadataFormImplem->addChecks($monograph);
	}

	/**
	 * Initialize form data from current monograph.
	 */
	function initData() {

		$this->_metadataFormImplem->initData($this->monograph);

		return parent::initData();
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {

		$this->_metadataFormImplem->readInputData();

		// Load the series. This is used in the step 3 form to
		// determine whether or not to display indexing options.
		$seriesDao =& DAORegistry::getDAO('SeriesDAO');
		$this->_data['series'] =& $seriesDao->getById($this->monograph->getSeriesId(), $this->monograph->getPressId());
	}

	/**
	 * Display the form
	 */
	function display($request) {
		$templateMgr =& TemplateManager::getManager();

		$templateMgr->assign('isEditedVolume', $this->monograph->getWorkType() == WORK_TYPE_EDITED_VOLUME);
		return parent::display($request);
	}

	/**
	 * Get the names of fields for which data should be localized
	 * @return array
	 */
	function getLocaleFieldNames() {
		$this->_metadataFormImplem->getLocaleFieldNames();
	}

	/**
	 * Save changes to monograph.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return int the monograph ID
	 */
	function execute($args, &$request) {

		// Execute monograph metadata related operations.
		$this->_metadataFormImplem->execute($this->monograph);

		// Get an updated version of the monograph.
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$monograph =& $monographDao->getById($this->monographId);

		// Set other monograph data.
		if ($monograph->getSubmissionProgress() <= $this->step) {
			$monograph->setDateSubmitted(Core::getCurrentDate());
			$monograph->stampStatusModified();
			$monograph->setSubmissionProgress(0);
		}

		// Save the monograph.
		$monographDao->updateMonograph($monograph);

		// Assign the default users to the submission workflow stage
		import('classes.submission.seriesEditor.SeriesEditorAction');
		$seriesEditorAction = new SeriesEditorAction();
		$seriesEditorAction->assignDefaultStageParticipants($monograph, WORKFLOW_STAGE_ID_SUBMISSION, $request);

		//
		// Send a notification to associated users
		//

		$roleDao =& DAORegistry::getDAO('RoleDAO'); /* @var $roleDao RoleDAO */

		// Get the managers.
		$pressManagers = $roleDao->getUsersByRoleId(ROLE_ID_PRESS_MANAGER);

		$pressManagersArray = $pressManagers->toAssociativeArray();

		$allUserIds = array_keys($pressManagersArray);

		$notificationManager = new NotificationManager();
		foreach ($allUserIds as $userId) {
			$notificationManager->createNotification(
				$request, $userId, NOTIFICATION_TYPE_MONOGRAPH_SUBMITTED,
				$monograph->getPressId(), ASSOC_TYPE_MONOGRAPH, $monograph->getId()
			);
		}

		// Send author notification email
		import('classes.mail.MonographMailTemplate');
		$mail = new MonographMailTemplate($monograph, 'SUBMISSION_ACK');
		$authorMail = new MonographMailTemplate($monograph, 'SUBMISSION_ACK_NOT_USER');

		$press =& $request->getPress();

		$router =& $request->getRouter();
		if ($mail->isEnabled()) {
			// submission ack emails should be from the press contact.
			$mail->setFrom($this->press->getSetting('contactEmail'), $this->press->getSetting('contactName'));
			$authorMail->setFrom($this->press->getSetting('contactEmail'), $this->press->getSetting('contactName'));

			$user = $monograph->getUser();
			$primaryAuthor = $monograph->getPrimaryAuthor();
			$mail->addRecipient($user->getEmail(), $user->getFullName());

			if ($user->getEmail() != $primaryAuthor->getEmail()) {
				$authorMail->addRecipient($primaryAuthor->getEmail(), $primaryAuthor->getFullName());
			}

			$assignedAuthors = $monograph->getAuthors();

			foreach ($assignedAuthors as $author) {
				$authorEmail = $author->getEmail();
				// only add the author email if they have not already been added as the primary author
				// or user creating the submission.
				if ($authorEmail != $primaryAuthor->getEmail() && $authorEmail != $user->getEmail()) {
					$authorMail->addRecipient($author->getEmail(), $author->getFullName());
				}
			}
			$mail->bccAssignedSeriesEditors($monograph->getId(), WORKFLOW_STAGE_ID_SUBMISSION);

			$mail->assignParams(array(
				'authorName' => $user->getFullName(),
				'authorUsername' => $user->getUsername(),
				'editorialContactSignature' => $press->getSetting('contactName') . "\n" . $press->getLocalizedName(),
				'submissionUrl' => $router->url($request, null, 'authorDashboard', 'submission', $monograph->getId()),
			));

			$authorMail->assignParams(array(
				'submitterName' => $user->getFullName(),
				'editorialContactSignature' => $press->getSetting('contactName') . "\n" . $press->getLocalizedName(),
			));

			$mail->send($request);

			$recipients = $authorMail->getRecipients();
			if (!empty($recipients)) {
				$authorMail->send($request);
			}
		}

		// Introduce the "approve submission" notification for production, if needed.
		$notificationManager->updateApproveSubmissionNotificationTypes($request, $monograph);

		return $this->monographId;
	}
}

?>
