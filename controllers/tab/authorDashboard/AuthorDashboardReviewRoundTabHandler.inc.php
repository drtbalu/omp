<?php

/**
 * @file controllers/tab/authorDashboard/AuthorDashboardReviewRoundTabHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorDashboardReviewRoundTabHandler
 * @ingroup controllers_tab_authorDashboard
 *
 * @brief Handle AJAX operations for review round tabs on author dashboard page.
 */

// Import the base Handler.
import('pages.authorDashboard.AuthorDashboardHandler');
import('lib.pkp.classes.core.JSONMessage');

class AuthorDashboardReviewRoundTabHandler extends AuthorDashboardHandler {

	/**
	 * Constructor
	 */
	function AuthorDashboardReviewRoundTabHandler() {
		parent::Handler();
		$this->addRoleAssignment($this->_getAssignmentRoles(), array('fetchReviewRoundInfo'));
	}


	//
	// Extended methods from Handler
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, $args, $roleAssignments) {
		$stageId = (int)$request->getUserVar('stageId');

		// Authorize stage id.
		import('classes.security.authorization.internal.WorkflowStageRequiredPolicy');
		$this->addPolicy(new WorkflowStageRequiredPolicy($stageId));

		// We need a review round id in request.
		import('classes.security.authorization.internal.ReviewRoundRequiredPolicy');
		$this->addPolicy(new ReviewRoundRequiredPolicy($request, $args));

		return parent::authorize($request, $args, $roleAssignments);
	}


	//
	// Public handler operations
	//
	/**
	 * Fetch information for the author on the specified review round
	 * @param $args array
	 * @param $request Request
	 * @return string
	 */
	function fetchReviewRoundInfo($args, &$request) {
		$this->setupTemplate($request);
		$templateMgr =& TemplateManager::getManager();

		$stageId = $this->getAuthorizedContextObject(ASSOC_TYPE_WORKFLOW_STAGE);
		if ($stageId !== WORKFLOW_STAGE_ID_INTERNAL_REVIEW && $stageId !== WORKFLOW_STAGE_ID_EXTERNAL_REVIEW) {
			fatalError('Invalid Stage Id');
		}
		$templateMgr->assign('stageId', $stageId);

		$reviewRound =& $this->getAuthorizedContextObject(ASSOC_TYPE_REVIEW_ROUND);
		$templateMgr->assign('reviewRoundId', $reviewRound->getId());
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$templateMgr->assign_by_ref('monograph', $monograph);

		// Review round request notification options.
		$notificationRequestOptions = array(
			NOTIFICATION_LEVEL_NORMAL => array(
				NOTIFICATION_TYPE_REVIEW_ROUND_STATUS => array(ASSOC_TYPE_REVIEW_ROUND, $reviewRound->getId())),
			NOTIFICATION_LEVEL_TRIVIAL => array()
		);
		$templateMgr->assign('reviewRoundNotificationRequestOptions', $notificationRequestOptions);

		// Editor has taken an action and sent an email; Display the email
		import('classes.workflow.EditorDecisionActionsManager');
		if(EditorDecisionActionsManager::getEditorTakenActionInReviewRound($reviewRound)) {
			$monographEmailLogDao =& DAORegistry::getDAO('MonographEmailLogDAO');
			$user =& $request->getUser();
			$monographEmailFactory =& $monographEmailLogDao->getByEventType($monograph->getId(), MONOGRAPH_EMAIL_EDITOR_NOTIFY_AUTHOR, $user->getId());

			$templateMgr->assign_by_ref('monographEmails', $monographEmailFactory);
			$templateMgr->assign('showReviewAttachments', true);
		}

		return $templateMgr->fetchJson('authorDashboard/reviewRoundInfo.tpl');
	}

}

?>
