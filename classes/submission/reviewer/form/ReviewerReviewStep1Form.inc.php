<?php

/**
 * @file classes/submission/reviewer/form/ReviewerReviewStep1Form.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewerReviewStep1Form
 * @ingroup submission_reviewer_form
 *
 * @brief Form for Step 1 of a review.
 */

import('classes.submission.reviewer.form.ReviewerReviewForm');

class ReviewerReviewStep1Form extends ReviewerReviewForm {
	/**
	 * Constructor.
	 * @param $request PKPRequest
	 * @param $reviewerSubmission ReviewerSubmission
	 */
	function ReviewerReviewStep1Form($request, $reviewerSubmission, $reviewAssignment) {
		parent::ReviewerReviewForm($request, $reviewerSubmission, $reviewAssignment, 1);
	}


	//
	// Implement protected template methods from Form
	//
	/**
	 * @see Form::display()
	 */
	function display(&$request) {
		$templateMgr =& TemplateManager::getManager();

		// Add submission parameters.
		$submission =& $this->getReviewerSubmission();
		$templateMgr->assign('completedSteps', $submission->getStatus());
		// FIXME: Need press setting that denotes competing interests are required, see #6402.
		$templateMgr->assign('competingInterestsText', $submission->getCompetingInterests());

		// Add review assignment.
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$reviewAssignment = $reviewAssignmentDao->getById($submission->getReviewId());
		$templateMgr->assign_by_ref('reviewAssignment', $reviewAssignment);

		// Add press parameters.
		$press =& $this->request->getPress();
		$templateMgr->assign_by_ref('press', $press);

		// Add reviewer request text.
		$reviewerRequestParams = array(
			'reviewer' => $reviewAssignment->getReviewerFullName(),
			'personalNote' => 'EDITOR NOTE', // FIXME Bug #6531
			'editor' => $press->getSetting('contactName')
		);

		$templateMgr->assign('reviewerRequest', __('reviewer.step1.requestBoilerplate', $reviewerRequestParams));

		//
		// Assign the link actions
		//
		import('lib.pkp.classes.linkAction.request.AjaxModal');
		import('lib.pkp.classes.linkAction.request.ConfirmationModal');

		$dispatcher =& $request->getDispatcher();
		// "View metadata" action.
		import('controllers.modals.submissionMetadata.linkAction.ReviewerViewMetadataLinkAction');
		$viewMetadataLinkAction = new ReviewerViewMetadataLinkAction($request, $reviewAssignment->getSubmissionId(), $reviewAssignment->getId());
		$templateMgr->assign_by_ref('viewMetadataAction', $viewMetadataLinkAction);

		// include the confirmation modal for competing interests if the press has them.
		if ($press->getLocalizedSetting('competingInterests') != '') {
			import('controllers.confirmationModal.linkAction.ViewCompetingInterestGuidelinesLinkAction');
			$competingInterestsAction = new ViewCompetingInterestGuidelinesLinkAction($request);
			$templateMgr->assign_by_ref('competingInterestsAction', $competingInterestsAction);
		}
		// Instantiate the view review guidelines confirmation modal.
		$aboutDueDateAction = new LinkAction('viewReviewGuidelines',
			new ConfirmationModal(
				__('reviewer.aboutDueDates.text'),
				__('reviewer.aboutDueDates'),
				null, null,
				false
			),
			__('reviewer.aboutDueDates')
		);

		$templateMgr->assign_by_ref('aboutDueDatesAction', $aboutDueDateAction);

		import('lib.pkp.classes.linkAction.request.AjaxModal');
		$declineReviewLinkAction = new LinkAction('declineReview',
			new AjaxModal(
				$request->url(null, null, 'showDeclineReview', $reviewAssignment->getSubmissionId()),
				__('reviewer.monograph.declineReview')
			)
		);
		$templateMgr->assign_by_ref('declineReviewAction', $declineReviewLinkAction);

		parent::display($request);
	}

	/**
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('competingInterestOption', 'competingInterestText'));
	}

	/**
	 * @see Form::execute()
	 */
	function execute() {
		$reviewerSubmission =& $this->getReviewerSubmission();

		// Set competing interests.
		if ($this->getData('competingInterestOption') == 'hasCompetingInterests') {
			$reviewerSubmission->setCompetingInterests($this->request->getUserVar('competingInterestsText'));
		} else {
			$reviewerSubmission->setCompetingInterests(null);
		}

		// Set review to next step.
		$this->updateReviewStepAndSaveSubmission($reviewerSubmission);

		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$reviewAssignment = $reviewAssignmentDao->getById($reviewerSubmission->getReviewId());
		// if the reviewer has not previously confirmed the review, then
		// Set that the reviewer has accepted the review.
		if (!$reviewAssignment->getDateConfirmed()) {
			ReviewerAction::confirmReview($this->request, $reviewerSubmission, false, true);
		}
	}
}

?>
