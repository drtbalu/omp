<?php

/**
 * @file controllers/grid/users/reviewer/form/ReviewerForm.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewerForm
 * @ingroup controllers_grid_users_reviewer_form
 *
 * @brief Base Form for adding a reviewer to a submission.
 * N.B. Requires a subclass to implement the "reviewerId" to be added.
 */

import('lib.pkp.classes.form.Form');

class ReviewerForm extends Form {
	/** The monograph associated with the review assignment **/
	var $_monograph;

	/** The review round associated with the review assignment **/
	var $_reviewRound;

	/** An array of actions for the other reviewer forms */
	var $_reviewerFormActions;

	/** An array with all current user roles */
	var $_userRoles;

	/**
	 * Constructor.
	 * @param $monograph Monograph
	 * @param $reviewRound ReviewRound
	 */
	function ReviewerForm(&$monograph, &$reviewRound) {
		parent::Form('controllers/grid/users/reviewer/form/defaultReviewerForm.tpl');
		$this->setMonograph($monograph);
		$this->setReviewRound($reviewRound);

		// Validation checks for this form
		$this->addCheck(new FormValidator($this, 'responseDueDate', 'required', 'editor.review.errorAddingReviewer'));
		$this->addCheck(new FormValidator($this, 'reviewDueDate', 'required', 'editor.review.errorAddingReviewer'));

		$this->addCheck(new FormValidatorPost($this));
	}

	//
	// Getters and Setters
	//
	/**
	 * Get the Monograph Id
	 * @return int monographId
	 */
	function getMonographId() {
		$monograph =& $this->getMonograph();
		return $monograph->getId();
	}

	/**
	 * Get the Monograph
	 * @return Monograph
	 */
	function &getMonograph() {
		return $this->_monograph;
	}

	/**
	 * Get the ReviewRound
	 * @return ReviewRound
	 */
	function &getReviewRound() {
		return $this->_reviewRound;
	}

	/**
	 * Set the Monograph
	 * @param $monograph Monograph
	 */
	function setMonograph(&$monograph) {
		$this->_monograph =& $monograph;
	}

	/**
	 * Set the ReviewRound
	 * @param $reviewRound ReviewRound
	 */
	function setReviewRound(&$reviewRound) {
		$this->_reviewRound =& $reviewRound;
	}

	/**
	 * Set a reviewer form action
	 * @param $action LinkAction
	 */
	function setReviewerFormAction($action) {
		$this->_reviewerFormActions[$action->getId()] =& $action;
	}

	/**
	 * Set current user roles.
	 * @param $userRoles Array
	 */
	function setUserRoles($userRoles) {
		$this->_userRoles = $userRoles;
	}

	/**
	 * Get current user roles.
	 * @return $userRoles Array
	 */
	function getUserRoles() {
		return $this->_userRoles;
	}

	/**
	 * Get all of the reviewer form actions
	 * @return array
	 */
	function getReviewerFormActions() {
		return $this->_reviewerFormActions;
	}
	//
	// Overridden template methods
	//
	/**
	 * Initialize form data from the associated author.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function initData($args, &$request) {
		$reviewerId = (int) $request->getUserVar('reviewerId');
		$press =& $request->getContext();
		$reviewRound =& $this->getReviewRound();
		$seriesEditorSubmissionDao =& DAORegistry::getDAO('SeriesEditorSubmissionDAO');
		$monograph =& $seriesEditorSubmissionDao->getById($this->getMonographId());

		// The reviewer id has been set
		if (!empty($reviewerId)) {
			if ($this->_isValidReviewer($press, $monograph, $reviewRound, $reviewerId)) {
				$userDao = & DAORegistry::getDAO('UserDAO'); /* @var $userDao UserDAO */
				$reviewer =& $userDao->getUser($reviewerId);
				$this->setData('userNameString', sprintf('%s (%s)', $reviewer->getFullname(), $reviewer->getUsername()));
			}
		}

		// Get review assignment related data;
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$reviewAssignment =& $reviewAssignmentDao->getReviewAssignment($reviewRound->getId(), $reviewerId);

		// Get the review method (open, blind, or double-blind)
		if (isset($reviewAssignment) && $reviewAssignment->getReviewMethod() != false) {
			$reviewMethod = $reviewAssignment->getReviewMethod();
		} else {
			// Set default value.
			$reviewMethod = SUBMISSION_REVIEW_METHOD_BLIND;
		}

		// Get the response/review due dates or else set defaults
		if (isset($reviewAssignment) && $reviewAssignment->getDueDate() != null) {
			$reviewDueDate = strftime(Config::getVar('general', 'date_format_short'), strtotime($reviewAssignment->getDueDate()));
		} else {
			$numWeeks = max((int) $press->getSetting('numWeeksPerReview'), 4);
			$reviewDueDate = strftime(Config::getVar('general', 'date_format_short'), strtotime('+' . $numWeeks . ' week'));
		}
		if (isset($reviewAssignment) && $reviewAssignment->getResponseDueDate() != null) {
			$responseDueDate = strftime(Config::getVar('general', 'date_format_short'), strtotime($reviewAssignment->getResponseDueDate()));
		} else {
			$numWeeks = max((int) $press->getSetting('numWeeksPerResponse'), 3);
			$responseDueDate = strftime(Config::getVar('general', 'date_format_short'), strtotime('+' . $numWeeks . ' week'));
		}

		// Get the currently selected reviewer selection type to show the correct tab if we're re-displaying the form
		$selectionType = (int) $request->getUserVar('selectionType');
		$stageId = $reviewRound->getStageId();

		$this->setData('monographId', $this->getMonographId());
		$this->setData('stageId', $stageId);
		$this->setData('reviewMethod', $reviewMethod);
		$this->setData('reviewRoundId', $reviewRound->getId());
		$this->setData('reviewerId', $reviewerId);

		import('classes.mail.MonographMailTemplate');
		$template = new MonographMailTemplate($monograph, 'REVIEW_REQUEST');
		if ($template) {
			$user =& $request->getUser();
			$dispatcher =& $request->getDispatcher();
			$press =& $request->getPress();
			$template->assignParams(array(
				'pressUrl' => $dispatcher->url($request, ROUTE_PAGE, $press->getPath()),
				'editorialContactSignature' => $user->getContactSignature(),
				'signatureFullName' => $user->getFullname(),
				'messageToReviewer' => __('reviewer.step1.requestBoilerplate'),
				'submissionReviewUrl' => $dispatcher->url($request, ROUTE_PAGE, null, 'reviewer', 'submission', null, array('monographId' => $this->getMonographId()))
			));
		}
		$this->setData('personalMessage', $template->getBody() . "\n" . $press->getSetting('emailSignature'));
		$this->setData('responseDueDate', $responseDueDate);
		$this->setData('reviewDueDate', $reviewDueDate);
		$this->setData('selectionType', $selectionType);
	}

	/**
	 * Fetch
	 * @param $request PKPRequest
	 * @see Form::fetch()
	 */
	function fetch(&$request) {

		// Get the review method options.
		$reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
		$reviewMethods = $reviewAssignmentDao->getReviewMethodsTranslationKeys();

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('reviewMethods', $reviewMethods);
		$templateMgr->assign('reviewerActions', $this->getReviewerFormActions());

		// Get the reviewer user groups for the create new reviewer/enroll existing user tabs
		$press =& $request->getPress();
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO'); /* @var $userGroupDao UserGroupDAO */
		$reviewRound =& $this->getReviewRound();
		$reviewerUserGroups =& $userGroupDao->getUserGroupsByStage($press->getId(), $reviewRound->getStageId(), false, false, ROLE_ID_REVIEWER);
		$userGroups = array();
		while($userGroup =& $reviewerUserGroups->next()) {
			$userGroups[$userGroup->getId()] = $userGroup->getLocalizedName();
			unset($userGroup);
		}

		$this->setData('userGroups', $userGroups);
		return parent::fetch($request);
	}

	/**
	 * Assign form data to user-submitted data.
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array(
			'selectionType',
			'monographId',
			'personalMessage',
			'responseDueDate',
			'reviewDueDate',
			'reviewMethod',
			'skipEmail',
			'keywords',
			'interestsTextOnly',
		));

		$keywords = $this->getData('keywords');
		if ($keywords != null && is_array($keywords['interests'])) {
			// The interests are coming in encoded -- Decode them for DB storage
			$this->setData('interestsKeywords', array_map('urldecode', $keywords['interests']));
		}
	}

	/**
	 * Save review assignment
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function execute($args, &$request) {
		$seriesEditorSubmissionDao =& DAORegistry::getDAO('SeriesEditorSubmissionDAO');
		$submission =& $seriesEditorSubmissionDao->getById($this->getMonographId());
		$press =& $request->getPress();

		$currentReviewRound =& $this->getReviewRound();
		$stageId = $currentReviewRound->getStageId();
		$reviewDueDate = $this->getData('reviewDueDate');
		$responseDueDate = $this->getData('responseDueDate');

		// Get reviewer id and validate it.
		$reviewerId = (int) $this->getData('reviewerId');

		if (!$this->_isValidReviewer($press, $submission, $currentReviewRound, $reviewerId)) {
			fatalError('Invalid reviewer id.');
		}

		$reviewMethod = (int) $this->getData('reviewMethod');

		import('classes.submission.seriesEditor.SeriesEditorAction');
		$seriesEditorAction = new SeriesEditorAction();
		$seriesEditorAction->addReviewer($request, $submission, $reviewerId, $currentReviewRound, $reviewDueDate, $responseDueDate, $reviewMethod);

		// Get the reviewAssignment object now that it has been added.
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO'); /* @var $reviewAssignmentDao ReviewAssignmentDAO */
		$reviewAssignment =& $reviewAssignmentDao->getReviewAssignment($currentReviewRound->getId(), $reviewerId);
		$reviewAssignment->setDateNotified(Core::getCurrentDate());
		$reviewAssignment->setCancelled(0);
		$reviewAssignment->stampModified();
		$reviewAssignmentDao->updateObject($reviewAssignment);

		// Notify the reviewer via email.
		import('classes.mail.MonographMailTemplate');
		$mail = new MonographMailTemplate($submission, 'REVIEW_REQUEST', null, null, null, false);

		if ($mail->isEnabled() && !$this->getData('skipEmail')) {
			$userDao = & DAORegistry::getDAO('UserDAO'); /* @var $userDao UserDAO */
			$reviewer =& $userDao->getUser($reviewerId);
			$user = $request->getUser();
			$mail->addRecipient($reviewer->getEmail(), $reviewer->getFullName());
			$mail->setBody($this->getData('personalMessage'));
			$dispatcher =& $request->getDispatcher();

			// assign the remaining parameters
			$paramArray = array(
				'reviewerName' => $reviewer->getFullName(),
				'responseDueDate' => $responseDueDate,
				'reviewDueDate' => $reviewDueDate,
				'reviewerUserName' => $reviewer->getUsername(),
			);
			$mail->assignParams($paramArray);
			$mail->send($request);
		}

		return $reviewAssignment;
	}


	//
	// Protected methods.
	//
	/**
	 * Get the link action that fetchs the search
	 * by name form content.
	 * @param $request Request
	 * @return LinkAction
	 */
	function getSearchByNameAction(&$request) {
		$reviewRound =& $this->getReviewRound();

		$actionArgs['monographId'] = $this->getMonographId();
		$actionArgs['stageId'] = $reviewRound->getStageId();
		$actionArgs['reviewRoundId'] = $reviewRound->getId();
		$actionArgs['selectionType'] = REVIEWER_SELECT_SEARCH_BY_NAME;

		return new LinkAction(
			'addReviewer',
			new AjaxAction($request->url(null, null, 'reloadReviewerForm', null, $actionArgs)),
			__('editor.monograph.returnToSimpleSearch'),
			'return'
		);
	}


	//
	// Private helper methods
	//
	/**
	 * Check if a given user id is enrolled in reviewer user group.
	 * @param $press Press
	 * @param $reviewerId int
	 * @return boolean
	 */
	function _isValidReviewer(&$press, &$monograph, &$reviewRound, $reviewerId) {
		$seriesEditorSubmissionDao =& DAORegistry::getDAO('SeriesEditorSubmissionDAO'); /* @var $seriesEditorSubmissionDao SeriesEditorSubmissionDAO */
		$reviewerFactory =& $seriesEditorSubmissionDao->getReviewersNotAssignedToMonograph($press->getId(), $monograph->getId(), $reviewRound);
		$reviewersArray = $reviewerFactory->toAssociativeArray();
		if (array_key_exists($reviewerId, $reviewersArray)) {
			return true;
		} else {
			return false;
		}
	}
}

?>
