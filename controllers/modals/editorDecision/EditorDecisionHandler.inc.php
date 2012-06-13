<?php

/**
 * @file controllers/modals/editorDecision/EditorDecisionHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EditorDecisionHandler
 * @ingroup controllers_modals_editorDecision
 *
 * @brief Handle requests for editors to make a decision
 */

import('classes.handler.Handler');

// import JSON class for use with all AJAX requests
import('lib.pkp.classes.core.JSONMessage');

// Access decision actions constants.
import('classes.workflow.EditorDecisionActionsManager');

class EditorDecisionHandler extends Handler {
	/**
	 * Constructor.
	 */
	function EditorDecisionHandler() {
		parent::Handler();

		$this->addRoleAssignment(
			array(ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER),
			array_merge(array(
				'initiateReview', 'saveInitiateReview',
				'sendReviews', 'saveSendReviews',
				'promote', 'savePromote',
				'approveProofs', 'saveApproveProofs'
			), $this->_getReviewRoundOps())
		);
	}


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, $args, $roleAssignments) {
		$stageId = (int) $request->getUserVar('stageId');
		import('classes.security.authorization.OmpEditorDecisionAccessPolicy');
		$this->addPolicy(new OmpEditorDecisionAccessPolicy($request, $args, $roleAssignments, 'monographId', $stageId));

		// Some operations need a review round id in request.
		$reviewRoundOps = $this->_getReviewRoundOps();
		import('classes.security.authorization.internal.ReviewRoundRequiredPolicy');
		$this->addPolicy(new ReviewRoundRequiredPolicy($request, $args, 'reviewRoundId', $reviewRoundOps));

		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request, $args) {
		AppLocale::requireComponents(
			LOCALE_COMPONENT_APPLICATION_COMMON,
			LOCALE_COMPONENT_OMP_EDITOR,
			LOCALE_COMPONENT_PKP_SUBMISSION
		);
	}


	//
	// Public handler actions
	//
	/**
	 * Start a new review round
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function newReviewRound($args, &$request) {
		return $this->_initiateEditorDecision($args, $request, 'NewReviewRoundForm');
	}

	/**
	 * Start a new review round
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function saveNewReviewRound($args, &$request) {
		// Retrieve the authorized monograph.
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		// FIXME: this can probably all be managed somewhere.
		$stageId = $this->getAuthorizedContextObject(ASSOC_TYPE_WORKFLOW_STAGE);
		if ($stageId == WORKFLOW_STAGE_ID_INTERNAL_REVIEW) {
			$redirectOp = WORKFLOW_STAGE_PATH_INTERNAL_REVIEW;
		} elseif ($stageId == WORKFLOW_STAGE_ID_EXTERNAL_REVIEW) {
			$redirectOp = WORKFLOW_STAGE_PATH_EXTERNAL_REVIEW;
		} else {
			assert(false);
		}

		return $this->_saveEditorDecision($args, $request, 'NewReviewRoundForm', $redirectOp, SUBMISSION_EDITOR_DECISION_RESUBMIT);
	}

	/**
	 * Start a new review round
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function initiateReview($args, &$request) {
		return $this->_initiateEditorDecision($args, $request, 'InitiateReviewForm');
	}

	/**
	 * Start a new review round
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function saveInitiateReview($args, &$request) {
		// FIXME: this can probably all be managed somewhere.
		$stageId = $this->getAuthorizedContextObject(ASSOC_TYPE_WORKFLOW_STAGE);
		$decision = null;
		if ($stageId == WORKFLOW_STAGE_ID_SUBMISSION) {
			$redirectOp = WORKFLOW_STAGE_PATH_INTERNAL_REVIEW;
			$decision = SUBMISSION_EDITOR_DECISION_INITIATE_REVIEW;
		} elseif ($stageId == WORKFLOW_STAGE_ID_INTERNAL_REVIEW) {
			$redirectOp = WORKFLOW_STAGE_PATH_EXTERNAL_REVIEW;
		} else {
			assert(false);
		}

		return $this->_saveEditorDecision($args, $request, 'InitiateReviewForm', $redirectOp, $decision);
	}

	/**
	 * Show a save review form (responsible for decline submission modals when not in review stage)
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function sendReviews($args, &$request) {
		return $this->_initiateEditorDecision($args, $request, 'SendReviewsForm');
	}

	/**
	 * Show a save review form (responsible for request revisions,
	 * resubmit for review, and decline submission modals in review stages).
	 * We need this because the authorization in review stages is different
	 * when not in review stages (need to authorize review round id).
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function sendReviewsInReview($args, &$request) {
		return $this->_initiateEditorDecision($args, $request, 'SendReviewsForm');
	}

	/**
	 * Save the send review form when user is not in review stage.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function saveSendReviews($args, &$request) {
		return $this->_saveEditorDecision($args, $request, 'SendReviewsForm');
	}

	/**
	 * Save the send review form when user is in review stages.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function saveSendReviewsInReview($args, &$request) {
		return $this->_saveEditorDecision($args, $request, 'SendReviewsForm');
	}

	/**
	 * Show a promote form (responsible for accept submission modals outside review stage)
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function promote($args, &$request) {
		return $this->_initiateEditorDecision($args, $request, 'PromoteForm');
	}

	/**
	 * Show a promote form (responsible for external review and accept submission modals
	 * in review stages). We need this because the authorization for promoting in review
	 * stages is different when not in review stages (need to authorize review round id).
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function promoteInReview($args, &$request) {
		return $this->_initiateEditorDecision($args, $request, 'PromoteForm');
	}

	/**
	 * Save the send review form
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function savePromote($args, &$request) {
		return $this->_saveGeneralPromote($args, $request);
	}

	/**
	 * Save the send review form (same case of the
	 * promoteInReview() method, see description there).
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function savePromoteInReview($args, &$request) {
		return $this->_saveGeneralPromote($args, $request);
	}

	/**
	 * Import all free-text/review form reviews to paste into message
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function importPeerReviews($args, &$request) {
		// Retrieve the authorized submission.
		$seriesEditorSubmission =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);

		// Retrieve the current review round.
		$reviewRound =& $this->getAuthorizedContextObject(ASSOC_TYPE_REVIEW_ROUND);

		// Retrieve peer reviews.
		import('classes.submission.seriesEditor.SeriesEditorAction');
		$seriesEditorAction = new SeriesEditorAction();
		$peerReviews = $seriesEditorAction->getPeerReviews($seriesEditorSubmission, $reviewRound->getId());

		if(empty($peerReviews)) {
			$json = new JSONMessage(false, __('editor.review.noReviews'));
		} else {
			$json = new JSONMessage(true, $peerReviews);
		}
		return $json->getString();
	}

	/**
	 * Show the approve proofs modal
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function approveProofs($args, &$request) {
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);

		import('controllers.modals.editorDecision.form.ApproveProofsForm');
		$approveProofsForm = new ApproveProofsForm($monograph, $request->getUserVar('publicationFormatId'));
		$approveProofsForm->initData($args, $request);
		$json = new JSONMessage(true, $approveProofsForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Save the approved proofs
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function saveApproveProofs($args, &$request) {
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		import('controllers.modals.editorDecision.form.ApproveProofsForm');
		$approveProofsForm = new ApproveProofsForm($monograph, $request->getUserVar('publicationFormatId'));
		$approveProofsForm->readInputData();
		if ($approveProofsForm->validate()) {
			$approveProofsForm->execute($args, $request);
			// Create trivial notification.
			$user =& $request->getUser();
			$notificationMgr = new NotificationManager();
			$notificationMgr->createTrivialNotification($user->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => __('notification.proofsApproved')));
		} else {
			$json = new JSONMessage(true, $approveProofsForm->fetch($request));
			return $json->getString();
		}

		return DAO::getDataChangedEvent();
	}

	//
	// Private helper methods
	//
	/**
	 * Initiate an editor decision.
	 * @param $args array
	 * @param $request PKPRequest
	 * @param $formName string Name of form to call
	 * @return string Serialized JSON object
	 */
	function _initiateEditorDecision($args, &$request, $formName) {
		// Retrieve the decision
		$decision = (int)$request->getUserVar('decision');

		// Form handling
		$editorDecisionForm = $this->_getEditorDecisionForm($formName, $decision);
		$editorDecisionForm->initData($args, $request);

		$json = new JSONMessage(true, $editorDecisionForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Save an editor decision.
	 * @param $args array
	 * @param $request PKPRequest
	 * @param $formName string Name of form to call
	 * @param $redirectOp string A workflow stage operation to
	 *  redirect to if successful (if any).
	 * @return string Serialized JSON object
	 */
	function _saveEditorDecision($args, &$request, $formName, $redirectOp = null, $decision = null) {
		// Retrieve the authorized monograph.
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		// Retrieve the decision
		if (is_null($decision)) {
			$decision = (int)$request->getUserVar('decision');
		}

		$editorDecisionForm = $this->_getEditorDecisionForm($formName, $decision);
		$editorDecisionForm->readInputData();
		if ($editorDecisionForm->validate()) {
			$editorDecisionForm->execute($args, $request);

			$notificationMgr = new NotificationManager();
			$notificationMgr->updateEditorDecisionNotification($monograph, $decision, $request);

			// Update pending revisions task notifications.
			$stageId = $this->getAuthorizedContextObject(ASSOC_TYPE_WORKFLOW_STAGE);
			$notificationMgr->updatePendingRevisionsNotification($request, $monograph, $stageId, $decision);

			// Update "all reviews in" notification.
			$reviewRound =& $this->getAuthorizedContextObject(ASSOC_TYPE_REVIEW_ROUND);
			if ($reviewRound) {
				$notificationMgr->updateAllReviewsInNotification($request, $reviewRound);
			}

			if ($redirectOp) {
				$dispatcher =& $this->getDispatcher();
				$redirectUrl = $dispatcher->url($request, ROUTE_PAGE, null, 'workflow', $redirectOp, array($monograph->getId()));
				return $request->redirectUrlJson($redirectUrl);
			} else {
				// Needed to update review round status notifications.
				return DAO::getDataChangedEvent();
			}
		} else {
			$json = new JSONMessage(false);
		}
		return $json->getString();
	}

	/**
	 * Get operations that need a review round id policy.
	 * @return array
	 */
	function _getReviewRoundOps() {
		return array('promoteInReview', 'savePromoteInReview', 'newReviewRound', 'saveNewReviewRound', 'sendReviewsInReview', 'saveSendReviewsInReview', 'importPeerReviews');
	}

	/**
	 * Get an instance of an editor decision form.
	 * @param $formName string
	 * @param $decision int
	 * @return EditorDecisionForm
	 */
	function _getEditorDecisionForm($formName, $decision) {
		// Retrieve the authorized monograph.
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		// Retrieve the stage id
		$stageId = $this->getAuthorizedContextObject(ASSOC_TYPE_WORKFLOW_STAGE);

		import("controllers.modals.editorDecision.form.$formName");
		if ($stageId == WORKFLOW_STAGE_ID_INTERNAL_REVIEW || $stageId == WORKFLOW_STAGE_ID_EXTERNAL_REVIEW) {
			$reviewRound =& $this->getAuthorizedContextObject(ASSOC_TYPE_REVIEW_ROUND);
			$editorDecisionForm = new $formName($monograph, $decision, $stageId, $reviewRound);
			// We need a different save operation in review stages to authorize
			// the review round object.
			if (is_a($editorDecisionForm, 'PromoteForm')) {
				$editorDecisionForm->setSaveFormOperation('savePromoteInReview');
			} else if (is_a($editorDecisionForm, 'SendReviewsForm')) {
				$editorDecisionForm->setSaveFormOperation('saveSendReviewsInReview');
			}
		} else {
			$editorDecisionForm = new $formName($monograph, $decision, $stageId);
		}

		if (is_a($editorDecisionForm, $formName)) {
			return $editorDecisionForm;
		} else {
			assert(false);
			return null;
		}
	}

	function _saveGeneralPromote($args, &$request) {
		// Redirect to the next workflow page after
		// promoting the submission.
		$decision = (int)$request->getUserVar('decision');

		$redirectOp = null;

		if ($decision == SUBMISSION_EDITOR_DECISION_ACCEPT) {
			$redirectOp = WORKFLOW_STAGE_PATH_EDITING;
		} elseif ($decision == SUBMISSION_EDITOR_DECISION_EXTERNAL_REVIEW) {
			$redirectOp = WORKFLOW_STAGE_PATH_EXTERNAL_REVIEW;
		} elseif ($decision == SUBMISSION_EDITOR_DECISION_SEND_TO_PRODUCTION) {
			$redirectOp = WORKFLOW_STAGE_PATH_PRODUCTION;
		}

		// Make sure user has access to the workflow stage.
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$redirectWorkflowStage = $userGroupDao->getIdFromPath($redirectOp);
		$userAccessibleWorkflowStages = $this->getAuthorizedContextObject(ASSOC_TYPE_ACCESSIBLE_WORKFLOW_STAGES);
		if (!array_key_exists($redirectWorkflowStage, $userAccessibleWorkflowStages)) {
			$redirectOp = null;
		}

		return $this->_saveEditorDecision($args, $request, 'PromoteForm', $redirectOp);
	}
}

?>
