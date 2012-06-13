<?php

/**
 * @file classes/submission/seriesEditor/SeriesEditorAction.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SeriesEditorAction
 * @ingroup submission
 *
 * @brief SeriesEditorAction class.
 */


// Access decision actions constants.
import('classes.workflow.EditorDecisionActionsManager');
import('classes.submission.common.Action');

class SeriesEditorAction extends Action {

	/**
	 * Constructor.
	 */
	function SeriesEditorAction() {
		parent::Action();
	}

	//
	// Actions.
	//
	/**
	 * Records an editor's submission decision.
	 * @param $request PKPRequest
	 * @param $seriesEditorSubmission SeriesEditorSubmission
	 * @param $decision integer
	 * @param $decisionLabels array(DECISION_CONSTANT => decision.locale.key, ...)
	 * @param $reviewRound ReviewRound Current review round that user is taking the decision, if any.
	 * @param $stageId int
	 */
	function recordDecision($request, $seriesEditorSubmission, $decision, $decisionLabels, $reviewRound = null, $stageId = null) {
		$stageAssignmentDao =& DAORegistry::getDAO('StageAssignmentDAO');

		// Define the stage and round data.
		if (!is_null($reviewRound)) {
			$stageId = $reviewRound->getStageId();
			$round = $reviewRound->getRound();
		} else {
			$round = REVIEW_ROUND_NONE;
			if ($stageId == null) {
				// We consider that the decision is being made for the current
				// submission stage.
				$stageId = $seriesEditorSubmission->getStageId();
			}
		}

		$editorAssigned = $stageAssignmentDao->editorAssignedToStage(
			$seriesEditorSubmission->getId(),
			$stageId
		);

		// Sanity checks
		if (!$editorAssigned || !isset($decisionLabels[$decision])) return false;

		$seriesEditorSubmissionDao =& DAORegistry::getDAO('SeriesEditorSubmissionDAO');
		$user =& $request->getUser();
		$editorDecision = array(
			'editDecisionId' => null,
			'editorId' => $user->getId(),
			'decision' => $decision,
			'dateDecided' => date(Core::getCurrentDate())
		);

		$result = true;
		if (!HookRegistry::call('SeriesEditorAction::recordDecision', array(&$seriesEditorSubmission, &$editorDecision, &$result))) {
			$seriesEditorSubmission->setStatus(STATUS_QUEUED);
			$seriesEditorSubmission->stampStatusModified();
			$seriesEditorSubmission->addDecision(
				$editorDecision,
				$stageId,
				$round
			);

			$seriesEditorSubmissionDao->updateSeriesEditorSubmission($seriesEditorSubmission);

			// Add log.
			import('classes.log.MonographLog');
			import('classes.log.MonographEventLogEntry');
			AppLocale::requireComponents(LOCALE_COMPONENT_APPLICATION_COMMON, LOCALE_COMPONENT_OMP_EDITOR);
			MonographLog::logEvent($request, $seriesEditorSubmission, MONOGRAPH_LOG_EDITOR_DECISION, 'log.editor.decision', array('editorName' => $user->getFullName(), 'monographId' => $seriesEditorSubmission->getId(), 'decision' => __($decisionLabels[$decision])));
		}
		return $result;
	}

	/**
	 * Assign the default participants to a workflow stage.
	 * @param $monograph Monograph
	 * @param $stageId int
	 * @param $request Request
	 */
	function assignDefaultStageParticipants(&$monograph, $stageId, &$request) {
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');

		// Managerial roles are skipped -- They have access by default and
		//  are assigned for informational purposes only

		// Series editor roles are skipped -- They are assigned by PM roles
		//  or by other series editors

		// Press roles -- For each press role user group assigned to this
		//  stage in setup, iff there is only one user for the group,
		//  automatically assign the user to the stage
		// But skip authors and reviewers, since these are very monograph specific
		$stageAssignmentDao =& DAORegistry::getDAO('StageAssignmentDAO');
		$submissionStageGroups =& $userGroupDao->getUserGroupsByStage($monograph->getPressId(), $stageId, true, true);
		while ($userGroup =& $submissionStageGroups->next()) {
			$users =& $userGroupDao->getUsersById($userGroup->getId());
			if($users->getCount() == 1) {
				$user =& $users->next();
				$stageAssignmentDao->build($monograph->getId(), $userGroup->getId(), $user->getId());
			}
		}

		// Update NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_...
		$notificationMgr = new NotificationManager();
		$notificationMgr->updateEditorAssignmentNotification($monograph, $stageId, $request);

		// Reviewer roles -- Do nothing. Reviewers are not included in the stage participant list, they
		// are administered via review assignments.

		// Author roles
		// Assign only the submitter in whatever ROLE_ID_AUTHOR capacity they were assigned previously
		$submitterAssignments =& $stageAssignmentDao->getBySubmissionAndStageId($monograph->getId(), null, null, $monograph->getUserId());
		while ($assignment =& $submitterAssignments->next()) {
			$userGroup =& $userGroupDao->getById($assignment->getUserGroupId());
			if ($userGroup->getRoleId() == ROLE_ID_AUTHOR) {
				$stageAssignmentDao->build($monograph->getId(), $userGroup->getId(), $assignment->getUserId());
				// Only assign them once, since otherwise we'll one assignment for each previous stage.
				// And as long as they are assigned once, they will get access to their monograph.
				break;
			}
			unset($assignment, $userGroup);
		}
	}

	/**
	 * Increment a monograph's workflow stage.
	 * @param $monograph Monograph
	 * @param $newStage integer One of the WORKFLOW_STAGE_* constants.
	 * @param $request Request
	 */
	function incrementWorkflowStage(&$monograph, $newStage, &$request) {
		// Change the monograph's workflow stage.
		$monograph->setStageId($newStage);
		$monographDao =& DAORegistry::getDAO('MonographDAO'); /* @var $monographDao MonographDAO */
		$monographDao->updateMonograph($monograph);

		// Assign the default users to the next workflow stage.
		$this->assignDefaultStageParticipants($monograph, $newStage, $request);
	}

	/**
	 * Assigns a reviewer to a submission.
	 * @param $request PKPRequest
	 * @param $seriesEditorSubmission object
	 * @param $reviewerId int
	 * @param $reviewRound ReviewRound
	 * @param $reviewDueDate datetime optional
	 * @param $responseDueDate datetime optional
	 */
	function addReviewer($request, $seriesEditorSubmission, $reviewerId, &$reviewRound, $reviewDueDate = null, $responseDueDate = null, $reviewMethod = null) {
		$seriesEditorSubmissionDao =& DAORegistry::getDAO('SeriesEditorSubmissionDAO');
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');

		$reviewer =& $userDao->getUser($reviewerId);

		// Check to see if the requested reviewer is not already
		// assigned to review this monograph.
		$assigned = $seriesEditorSubmissionDao->reviewerExists($reviewRound->getId(), $reviewerId);

		// Only add the reviewer if he has not already
		// been assigned to review this monograph.
		$stageId = $reviewRound->getStageId();
		$round = $reviewRound->getRound();
		if (!$assigned && isset($reviewer) && !HookRegistry::call('SeriesEditorAction::addReviewer', array(&$seriesEditorSubmission, $reviewerId))) {
			$reviewAssignment = new ReviewAssignment();
			$reviewAssignment->setSubmissionId($seriesEditorSubmission->getId());
			$reviewAssignment->setReviewerId($reviewerId);
			$reviewAssignment->setDateAssigned(Core::getCurrentDate());
			$reviewAssignment->setStageId($stageId);
			$reviewAssignment->setRound($round);
			$reviewAssignment->setReviewRoundId($reviewRound->getId());
			if (isset($reviewMethod)) {
				$reviewAssignment->setReviewMethod($reviewMethod);
			}
			$reviewAssignmentDao->insertObject($reviewAssignment);

			$seriesEditorSubmission->addReviewAssignment($reviewAssignment);
			$seriesEditorSubmissionDao->updateSeriesEditorSubmission($seriesEditorSubmission);

			$this->setDueDates($request, $seriesEditorSubmission, $reviewAssignment, $reviewDueDate, $responseDueDate);

			// Add notification
			$notificationMgr = new NotificationManager();
			$notificationMgr->createNotification(
				$request,
				$reviewerId,
				NOTIFICATION_TYPE_REVIEW_ASSIGNMENT,
				$seriesEditorSubmission->getPressId(),
				ASSOC_TYPE_REVIEW_ASSIGNMENT,
				$reviewAssignment->getId(),
				NOTIFICATION_LEVEL_TASK
			);

			// Insert a trivial notification to indicate the reviewer was added successfully.
			$currentUser =& $request->getUser();
			$notificationMgr = new NotificationManager();
			$notificationMgr->createTrivialNotification($currentUser->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => __('notification.addedReviewer')));

			// Update the review round status.
			$reviewRoundDao =& DAORegistry::getDAO('ReviewRoundDAO'); /* @var $reviewRoundDao ReviewRoundDAO */
			$reviewAssignments = $seriesEditorSubmission->getReviewAssignments($stageId, $round);
			$reviewRoundDao->updateStatus($reviewRound, $reviewAssignments);

			// Update "all reviews in" notification.
			$notificationMgr->updateAllReviewsInNotification($request, $reviewRound);

			// Add log
			import('classes.log.MonographLog');
			import('classes.log.MonographEventLogEntry');
			MonographLog::logEvent($request, $seriesEditorSubmission, MONOGRAPH_LOG_REVIEW_ASSIGN, 'log.review.reviewerAssigned', array('reviewerName' => $reviewer->getFullName(), 'monographId' => $seriesEditorSubmission->getId(), 'stageId' => $stageId, 'round' => $round));
		}
	}

	/**
	 * Clears a review assignment from a submission.
	 * @param $seriesEditorSubmission object
	 * @param $reviewId int
	 */
	function clearReview($request, $submissionId, $reviewId) {
		$seriesEditorSubmissionDao =& DAORegistry::getDAO('SeriesEditorSubmissionDAO'); /* @var $seriesEditorSubmissionDao SeriesEditorSubmissionDAO */
		$seriesEditorSubmission =& $seriesEditorSubmissionDao->getById($submissionId);
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');

		$reviewAssignment =& $reviewAssignmentDao->getById($reviewId);

		if (isset($reviewAssignment) && $reviewAssignment->getSubmissionId() == $seriesEditorSubmission->getId() && !HookRegistry::call('SeriesEditorAction::clearReview', array(&$seriesEditorSubmission, $reviewAssignment))) {
			$reviewer =& $userDao->getUser($reviewAssignment->getReviewerId());
			if (!isset($reviewer)) return false;
			$seriesEditorSubmission->removeReviewAssignment($reviewId);
			$seriesEditorSubmissionDao->updateSeriesEditorSubmission($seriesEditorSubmission);

			$notificationDao =& DAORegistry::getDAO('NotificationDAO');
			$notificationDao->deleteByAssoc(
				ASSOC_TYPE_REVIEW_ASSIGNMENT,
				$reviewAssignment->getId(),
				$reviewAssignment->getReviewerId(),
				NOTIFICATION_TYPE_REVIEW_ASSIGNMENT
			);

			// Insert a trivial notification to indicate the reviewer was removed successfully.
			$currentUser =& $request->getUser();
			$notificationMgr = new NotificationManager();
			$notificationMgr->createTrivialNotification($currentUser->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => __('notification.removedReviewer')));

			// Update the review round status, if needed.
			$reviewRoundDao =& DAORegistry::getDAO('ReviewRoundDAO');
			$reviewRound =& $reviewRoundDao->getReviewRoundById($reviewAssignment->getReviewRoundId());
			$reviewAssignments = $seriesEditorSubmission->getReviewAssignments($reviewRound->getStageId(), $reviewRound->getRound());
			$reviewRoundDao->updateStatus($reviewRound, $reviewAssignments);

			// Update "all reviews in" notification.
			$notificationMgr->updateAllReviewsInNotification($request, $reviewRound);

			// Add log
			import('classes.log.MonographLog');
			import('classes.log.MonographEventLogEntry');
			MonographLog::logEvent($request, $seriesEditorSubmission, MONOGRAPH_LOG_REVIEW_CLEAR, 'log.review.reviewCleared', array('reviewerName' => $reviewer->getFullName(), 'monographId' => $seriesEditorSubmission->getId(), 'stageId' => $reviewAssignment->getStageId(), 'round' => $reviewAssignment->getRound()));

			return true;
		} else return false;
	}

	/**
	 * Sets the due date for a review assignment.
	 * @param $request PKPRequest
	 * @param $monograph Object
	 * @param $reviewId int
	 * @param $dueDate string
	 * @param $numWeeks int
	 * @param $logEntry boolean
	 */
	function setDueDates($request, $monograph, $reviewAssignment, $reviewDueDate = null, $responseDueDate = null, $logEntry = false) {
		$userDao =& DAORegistry::getDAO('UserDAO');
		$press =& $request->getContext();

		$reviewer =& $userDao->getUser($reviewAssignment->getReviewerId());
		if (!isset($reviewer)) return false;

		if ($reviewAssignment->getSubmissionId() == $monograph->getId() && !HookRegistry::call('SeriesEditorAction::setDueDates', array(&$reviewAssignment, &$reviewer, &$reviewDueDate, &$responseDueDate))) {

			// Set the review due date
			$defaultNumWeeks = $press->getSetting('numWeeksPerReview');
			$reviewAssignment->setDateDue(DAO::formatDateToDB($reviewDueDate, $defaultNumWeeks, false));

			// Set the response due date
			$defaultNumWeeks = $press->getSetting('numWeeksPerReponse');
			$reviewAssignment->setDateResponseDue(DAO::formatDateToDB($reviewDueDate, $defaultNumWeeks, false));

			// update the assignment (with both the new dates)
			$reviewAssignment->stampModified();
			$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO'); /* @var $reviewAssignmentDao ReviewAssignmentDAO */
			$reviewAssignmentDao->updateObject($reviewAssignment);

			// N.B. Only logging Date Due
			if ($logEntry) {
				// Add log
				import('classes.log.MonographLog');
				import('classes.log.MonographEventLogEntry');
				MonographLog::logEvent(
					$request,
					$monograph,
					MONOGRAPH_LOG_REVIEW_SET_DUE_DATE,
					'log.review.reviewDueDateSet',
					array(
						'reviewerName' => $reviewer->getFullName(),
						'dueDate' => strftime(
							Config::getVar('general', 'date_format_short'),
							strtotime($reviewAssignment->getDateDue())
						),
						'monographId' => $monograph->getId(),
						'stageId' => $reviewAssignment->getStageId(),
						'round' => $reviewAssignment->getRound()
					)
				);
			}
		}
	}

	/**
	 * Get the text of all peer reviews for a submission
	 * @param $seriesEditorSubmission SeriesEditorSubmission
	 * @param $reviewRoundId int
	 * @return string
	 */
	function getPeerReviews($seriesEditorSubmission, $reviewRoundId) {
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$monographCommentDao =& DAORegistry::getDAO('MonographCommentDAO');
		$reviewFormResponseDao =& DAORegistry::getDAO('ReviewFormResponseDAO');
		$reviewFormElementDao =& DAORegistry::getDAO('ReviewFormElementDAO');

		$reviewAssignments =& $reviewAssignmentDao->getBySubmissionId($seriesEditorSubmission->getId(), $reviewRoundId);
		$reviewIndexes =& $reviewAssignmentDao->getReviewIndexesForRound($seriesEditorSubmission->getId(), $reviewRoundId);
		AppLocale::requireComponents(LOCALE_COMPONENT_PKP_SUBMISSION);

		$body = '';
		$textSeparator = "------------------------------------------------------";
		foreach ($reviewAssignments as $reviewAssignment) {
			// If the reviewer has completed the assignment, then import the review.
			if ($reviewAssignment->getDateCompleted() != null && !$reviewAssignment->getCancelled()) {
				// Get the comments associated with this review assignment
				$monographComments =& $monographCommentDao->getMonographComments($seriesEditorSubmission->getId(), COMMENT_TYPE_PEER_REVIEW, $reviewAssignment->getId());

				$body .= "\n\n$textSeparator\n";
				// If it is not a double blind review, show reviewer's name.
				if ($reviewAssignment->getReviewMethod() != SUBMISSION_REVIEW_METHOD_DOUBLEBLIND) {
					$body .= $reviewAssignment->getReviewerFullName() . "\n";
				} else {
					$body .= __('submission.comments.importPeerReviews.reviewerLetter', array('reviewerLetter' => String::enumerateAlphabetically($reviewIndexes[$reviewAssignment->getId()]))) . "\n";
				}

				while ($comment =& $monographComments->next()) {
					// If the comment is viewable by the author, then add the comment.
					if ($comment->getViewable()) {
						$body .= String::html2text($comment->getComments()) . "\n\n";
					}
					unset($comment);
				}
				$body .= "$textSeparator\n\n";

				if ($reviewFormId = $reviewAssignment->getReviewFormId()) {
					$reviewId = $reviewAssignment->getId();


					$reviewFormElements =& $reviewFormElementDao->getReviewFormElements($reviewFormId);
					if(!$monographComments) {
						$body .= "$textSeparator\n";

						$body .= __('submission.comments.importPeerReviews.reviewerLetter', array('reviewerLetter' => String::enumerateAlphabetically($reviewIndexes[$reviewAssignment->getId()]))) . "\n\n";
					}
					foreach ($reviewFormElements as $reviewFormElement) {
						$body .= String::html2text($reviewFormElement->getLocalizedQuestion()) . ": \n";
						$reviewFormResponse = $reviewFormResponseDao->getReviewFormResponse($reviewId, $reviewFormElement->getId());

						if ($reviewFormResponse) {
							$possibleResponses = $reviewFormElement->getLocalizedPossibleResponses();
							if (in_array($reviewFormElement->getElementType(), $reviewFormElement->getMultipleResponsesElementTypes())) {
								if ($reviewFormElement->getElementType() == REVIEW_FORM_ELEMENT_TYPE_CHECKBOXES) {
									foreach ($reviewFormResponse->getValue() as $value) {
										$body .= "\t" . String::htmltext($possibleResponses[$value-1]['content']) . "\n";
									}
								} else {
									$body .= "\t" . String::html2text($possibleResponses[$reviewFormResponse->getValue()-1]['content']) . "\n";
								}
								$body .= "\n";
							} else {
								$body .= "\t" . String::html2text($reviewFormResponse->getValue()) . "\n\n";
							}
						}

					}
					$body .= "$textSeparator\n\n";

				}


			}
		}

		return $body;
	}
}

?>
