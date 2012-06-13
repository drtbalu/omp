<?php

/**
 * @file pages/authorDashboard/AuthorDashboardHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorDashboardHandler
 * @ingroup pages_authorDashboard
 *
 * @brief Handle requests for the author dashboard.
 */

// Import base class
import('classes.handler.Handler');

class AuthorDashboardHandler extends Handler {

	/**
	 * Constructor
	 */
	function AuthorDashboardHandler() {
		parent::Handler();
		$this->addRoleAssignment($this->_getAssignmentRoles(), array('submission'));
	}


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, $args, $roleAssignments) {
		import('classes.security.authorization.OmpAuthorDashboardAccessPolicy');
		$this->addPolicy(new OmpAuthorDashboardAccessPolicy($request, $args, $roleAssignments), true);

		return parent::authorize($request, $args, $roleAssignments);
	}


	//
	// Public handler operations
	//
	/**
	 * Displays the author dashboard.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function submission($args, &$request) {
		// Pass the authorized monograph on to the template.
		$this->setupTemplate($request);
		$templateMgr =& TemplateManager::getManager();
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$templateMgr->assign_by_ref('monograph', $monograph);

		// "View metadata" action.
		import('controllers.modals.submissionMetadata.linkAction.AuthorViewMetadataLinkAction');
		$viewMetadataAction = new AuthorViewMetadataLinkAction($request, $monograph->getId());
		$templateMgr->assign('viewMetadataAction', $viewMetadataAction);

		// Import monograph file to define file stages.
		import('classes.monograph.MonographFile');

		// Workflow-stage specific "upload file" action.
		$fileStage = null;
		$currentStage = $monograph->getStageId();
		switch ($currentStage) {
			case WORKFLOW_STAGE_ID_SUBMISSION:
				$fileStage = MONOGRAPH_FILE_SUBMISSION;
				break;
			case WORKFLOW_STAGE_ID_INTERNAL_REVIEW:
			case WORKFLOW_STAGE_ID_EXTERNAL_REVIEW:
				$fileStage = MONOGRAPH_FILE_REVIEW_REVISION;
				break;

			case WORKFLOW_STAGE_ID_EDITING:
				$fileStage = MONOGRAPH_FILE_FINAL;
				break;
		}

		$reviewRoundDao =& DAORegistry::getDAO('ReviewRoundDAO');
		$internalReviewRounds =& $reviewRoundDao->getByMonographId($monograph->getId(), WORKFLOW_STAGE_ID_INTERNAL_REVIEW);
		$externalReviewRounds =& $reviewRoundDao->getByMonographId($monograph->getId(), WORKFLOW_STAGE_ID_EXTERNAL_REVIEW);
		$lastInternalReviewRound =& $reviewRoundDao->getLastReviewRoundByMonographId($monograph->getId(), WORKFLOW_STAGE_ID_INTERNAL_REVIEW);
		$lastExternalReviewRound =& $reviewRoundDao->getLastReviewRoundByMonographId($monograph->getId(), WORKFLOW_STAGE_ID_EXTERNAL_REVIEW);
		$lastInternalReviewRoundNumber = 0;
		$lastExternalReviewRoundNumber = 0;
		if ($lastInternalReviewRound) {
			$lastInternalReviewRoundNumber = $lastInternalReviewRound->getRound();
		}
		if ($lastExternalReviewRound) {
			$lastExternalReviewRoundNumber = $lastExternalReviewRound->getRound();
		}
		$lastReviewRoundNumber = array('internalReview' => $lastInternalReviewRoundNumber,
			'externalReview' => $lastExternalReviewRoundNumber);
		$templateMgr->assign_by_ref('internalReviewRounds', $internalReviewRounds);
		$templateMgr->assign_by_ref('externalReviewRounds', $externalReviewRounds);
		$templateMgr->assign('lastReviewRoundNumber', $lastReviewRoundNumber);

		// Get the last review round.
		$lastReviewRound =& $reviewRoundDao->getLastReviewRoundByMonographId($monograph->getId(), $currentStage);

		// Create and assign add file link action.
		if ($fileStage && is_a($lastReviewRound, 'ReviewRound')) {
			import('controllers.api.file.linkAction.AddFileLinkAction');
			$uploadFileAction = new AddFileLinkAction(
				$request, $monograph->getId(), $currentStage,
				array(ROLE_ID_AUTHOR), $fileStage, null, null, $lastReviewRound->getId());
			$templateMgr->assign('uploadFileAction', $uploadFileAction);
		}


		// If the submission is in or past the editorial stage,
		// assign the editor's copyediting emails to the template
		$monographEmailLogDao =& DAORegistry::getDAO('MonographEmailLogDAO');
		$user =& $request->getUser();

		if ($monograph->getStageId() >= WORKFLOW_STAGE_ID_EDITING) {
			$copyeditingEmails =& $monographEmailLogDao->getByEventType($monograph->getId(), MONOGRAPH_EMAIL_COPYEDIT_NOTIFY_AUTHOR, $user->getId());
			$templateMgr->assign_by_ref('copyeditingEmails', $copyeditingEmails);
		}

		// Same for production stage.
		if ($monograph->getStageId() == WORKFLOW_STAGE_ID_PRODUCTION) {
			$productionEmails =& $monographEmailLogDao->getByEventType($monograph->getId(), MONOGRAPH_EMAIL_PROOFREAD_NOTIFY_AUTHOR, $user->getId());
			$templateMgr->assign_by_ref('productionEmails', $productionEmails);
		}

		// Define the notification options.
		$monographAssocTypeAndIdArray = array(ASSOC_TYPE_MONOGRAPH, $monograph->getId());
		$notificationRequestOptions = array(
			NOTIFICATION_LEVEL_TASK => array(
				NOTIFICATION_TYPE_SIGNOFF_COPYEDIT => $monographAssocTypeAndIdArray,
				NOTIFICATION_TYPE_SIGNOFF_PROOF => $monographAssocTypeAndIdArray,
				NOTIFICATION_TYPE_PENDING_EXTERNAL_REVISIONS => $monographAssocTypeAndIdArray,
				NOTIFICATION_TYPE_PENDING_INTERNAL_REVISIONS => $monographAssocTypeAndIdArray),
			NOTIFICATION_LEVEL_NORMAL => array(
				NOTIFICATION_TYPE_EDITOR_DECISION_INITIATE_REVIEW => $monographAssocTypeAndIdArray,
				NOTIFICATION_TYPE_EDITOR_DECISION_ACCEPT => $monographAssocTypeAndIdArray,
				NOTIFICATION_TYPE_EDITOR_DECISION_EXTERNAL_REVIEW => $monographAssocTypeAndIdArray,
				NOTIFICATION_TYPE_EDITOR_DECISION_PENDING_REVISIONS => $monographAssocTypeAndIdArray,
				NOTIFICATION_TYPE_EDITOR_DECISION_RESUBMIT => $monographAssocTypeAndIdArray,
				NOTIFICATION_TYPE_EDITOR_DECISION_DECLINE => $monographAssocTypeAndIdArray,
				NOTIFICATION_TYPE_EDITOR_DECISION_SEND_TO_PRODUCTION => $monographAssocTypeAndIdArray),
			NOTIFICATION_LEVEL_TRIVIAL => array()
		);
		$templateMgr->assign('authorDashboardNotificationRequestOptions', $notificationRequestOptions);

		$templateMgr->display('authorDashboard/authorDashboard.tpl');
	}


	//
	// Protected helper methods
	//
	/**
	 * Setup common template variables.
	 */
	function setupTemplate($request) {
		parent::setupTemplate();
		AppLocale::requireComponents(LOCALE_COMPONENT_PKP_SUBMISSION, LOCALE_COMPONENT_OMP_SUBMISSION, LOCALE_COMPONENT_OMP_EDITOR, LOCALE_COMPONENT_PKP_GRID);
	}

	/**
	 * Get roles to assign to operations in this handler.
	 * @return array
	 */
	function _getAssignmentRoles() {
		return array(ROLE_ID_AUTHOR);
	}
}

?>
