<?php

/**
 * @file classes/monograph/reviewRound/ReviewRoundDAO.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewRoundDAO
 * @ingroup monograph_reviewRound
 * @see ReviewRound
 *
 * @brief Operations for retrieving and modifying ReviewRound objects.
 */

import('classes.monograph.reviewRound.ReviewRound');

class ReviewRoundDAO extends DAO {
	/**
	 * Constructor
	 */
	function ReviewRoundDAO() {
		parent::DAO();
	}

	//
	// Public methods
	//
	/**
	 * Fetch a review round, creating it if needed.
	 * @param $submissionId integer
	 * @param $stageId integer One of the WORKFLOW_*_REVIEW_STAGE_ID constants.
	 * @param $round integer
	 * @param $status integer One of the REVIEW_ROUND_STATUS_* constants.
	 * @return ReviewRound
	 */
	function build($submissionId, $stageId, $round, $status = null) {
		// If one exists, fetch and return.
		$reviewRound = $this->getReviewRound($submissionId, $stageId, $round);
		if ($reviewRound) return $reviewRound;

		// Otherwise, check the args to build one.
		if ($stageId == WORKFLOW_STAGE_ID_INTERNAL_REVIEW ||
		$stageId == WORKFLOW_STAGE_ID_EXTERNAL_REVIEW &&
		$round > 0) {
			unset($reviewRound);
			$reviewRound =& $this->newDataObject();
			$reviewRound->setSubmissionId($submissionId);
			$reviewRound->setRound($round);
			$reviewRound->setStageId($stageId);
			$reviewRound->setStatus($status);
			$this->insertObject($reviewRound);
			$reviewRound->setId($this->getInsertReviewRoundId());

			return $reviewRound;
		} else {
			assert(false);
			return null;
		}
	}

	/**
	 * Construct a new data object corresponding to this DAO.
	 * @return SignoffEntry
	 */
	function newDataObject() {
		return new ReviewRound();
	}

	/**
	 * Insert a new review round.
	 * @param $reviewRound ReviewRound
	 * @return int
	 */
	function insertObject(&$reviewRound) {
		$this->update(
				'INSERT INTO review_rounds
				(submission_id, stage_id, round, status)
				VALUES
				(?, ?, ?, ?)',
				array(
					(int)$reviewRound->getSubmissionId(),
					(int)$reviewRound->getStageId(),
					(int)$reviewRound->getRound(),
					(int)$reviewRound->getStatus()
				)
		);
		return $reviewRound;
	}

	/**
	 * Update an existing review round.
	 * @param $reviewRound ReviewRound
	 * @return boolean
	 */
	function updateObject(&$reviewRound) {
		$returner = $this->update(
			'UPDATE	review_rounds
			SET	status = ?
			WHERE	submission_id = ? AND
				stage_id = ? AND
				round = ?',
			array(
				(int)$reviewRound->getStatus(),
				(int)$reviewRound->getSubmissionId(),
				(int)$reviewRound->getStageId(),
				(int)$reviewRound->getRound()
			)
		);
		return $returner;
	}

	/**
	 * Retrieve a review round
	 * @param $submissionId integer
	 * @param $stageId int One of the Stage_id_* constants.
	 * @param $round int The review round to be retrieved.
	 */
	function getReviewRound($submissionId, $stageId, $round) {
		$result =& $this->retrieve(
				'SELECT * FROM review_rounds WHERE submission_id = ? AND stage_id = ? AND round = ?',
				array((int)$submissionId, (int)$stageId, (int)$round));

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner = $this->_fromRow($result->GetRowAssoc(false));
		}
		$result->Close();
		return $returner;
	}

	/**
	 * Retrieve a review round by its id.
	 * @param int $reviewRoundId
	 * @return ReviewRound
	 */
	function &getReviewRoundById($reviewRoundId) {
		$result =& $this->retrieve(
				'SELECT * FROM review_rounds WHERE review_round_id = ?',
				array((int)$reviewRoundId));

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner =& $this->_fromRow($result->GetRowAssoc(false));
		}
		$result->Close();
		return $returner;
	}

	/**
	 * Retrieve a review round by a monograph file id.
	 * @param $monographFileId int
	 * @return ReviewRound
	 */
	function &getByMonographFileId($monographFileId) {
		$result =& $this->retrieve(
				'SELECT * FROM review_rounds rr
				INNER JOIN review_round_files rrf
				ON rr.review_round_id = rrf.review_round_id
				WHERE rrf.file_id = ?',
				array((int) $monographFileId));

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner =& $this->_fromRow($result->GetRowAssoc(false));
		}
		$result->Close();
		return $returner;
	}

	/**
	 * Check if a review round exists for a specified monograph.
	 * @param $monographId int
	 * @param $round int
	 * @return boolean
	 */
	function reviewRoundExists($monographId, $stageId, $round) {
		$result =& $this->retrieve(
				'SELECT COUNT(*) FROM review_rounds WHERE submission_id = ? AND stage_id = ? AND round = ?',
				array((int)$monographId, (int)$stageId, (int)$round));
		$returner = isset($result->fields[0]) && $result->fields[0] == 1 ? true : false;

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Get an iterator of review round objects associated with this monograph
	 * @param $monographId int
	 * @param $stageId int (optional)
	 * @param $round int (optional)
	 */
	function &getByMonographId($monographId, $stageId = null, $round = null) {
		$params = array($monographId);
		if ($stageId) $params[] = $stageId;
		if ($round) $params[] = $round;

		$result =& $this->retrieve(
				'SELECT * FROM review_rounds WHERE submission_id = ?' .
				($stageId ? ' AND stage_id = ?' : '') .
				($round ? ' AND round = ?' : '') .
				' ORDER BY stage_id ASC, round ASC',
				$params
				);

		$returner = new DAOResultFactory($result, $this, '_fromRow');
		return $returner;
	}

	/**
	 * Get the current review round for a given stage (or for the latest stage)
	 * @param $monographId int
	 * @param $stageId int
	 * @return int
	 */
	function getCurrentRoundByMonographId($monographId, $stageId = null) {
		$params = array((int)$monographId);
		if ($stageId) $params[] = (int) $stageId;
		$result =& $this->retrieve('SELECT MAX(stage_id) as stage_id, MAX(round) as round
									FROM review_rounds
									WHERE submission_id = ?' .
									($stageId ? ' AND stage_id = ?' : ''),
									$params);
		$returner = isset($result->fields['round']) ? (int)$result->fields['round'] : 1;
		$result->Close();
		return $returner;
	}

	/**
	 * Get the last review round for a give stage (or for the latest stage)
	 * @param $monographId int
	 * @param $stageId int
	 * @return ReviewRound
	 */
	function &getLastReviewRoundByMonographId($monographId, $stageId = null) {
		$params = array((int)$monographId);
		if ($stageId) $params[] = (int) $stageId;
		$result =& $this->retrieve(
			'SELECT	*
			FROM	review_rounds
			WHERE	submission_id = ?
			' . ($stageId ? ' AND stage_id = ?' : '') . '
			ORDER BY stage_id DESC, round DESC
			LIMIT 1',
			$params
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner =& $this->_fromRow($result->GetRowAssoc(false));
		}
		$result->Close();
		return $returner;
	}

	/**
	 * Get the ID of the last inserted review round.
	 * @return int
	 */
	function getInsertReviewRoundId() {
		return $this->getInsertId('review_rounds', 'user_id');
	}

	/**
	 * FIXME #7386#
	 * Update the review round status. If review assignments is passed and
	 * no status, then this method will find the correct review round status
	 * based on the review round assignments state.
	 * @param $reviewRound ReviewRound
	 * @param $reviewAssignments array Review round review assignments.
	 * @param $status int
	 */
	function updateStatus(&$reviewRound, $reviewAssignments = array(), $status = null) {
		assert(is_a($reviewRound, 'ReviewRound'));
		$currentStatus = $reviewRound->getStatus();

		if (is_null($status)) {
			assert(is_array($reviewAssignments));

			$viewsDao =& DAORegistry::getDAO('ViewsDAO'); /* @var $viewsDao ViewsDAO */
			$anyUnreadReview = false;
			$anyIncompletedReview = false;

			foreach ($reviewAssignments as $reviewAssignment) { /* @var $reviewAssignment ReviewAssignment */
				// Skip cancelled and declined reviews.
				if ($reviewAssignment->getCancelled() ||
				$reviewAssignment->getDeclined()) {
					continue;
				}

				// Check for an incomplete review.
				if (!$reviewAssignment->getDateCompleted()) {
					$anyIncompletedReview = true;
				}

				// Check for an unread review.
				if (!$viewsDao->getLastViewDate(ASSOC_TYPE_REVIEW_RESPONSE, $reviewAssignment->getId())) {
					$anyUnreadReview = true;
				}
			}

			// Find the correct review round status based on the state of
			// the current review assignments. The check order matters: the
			// first conditions override the others.
			if (empty($reviewAssignments)) {
				$status = REVIEW_ROUND_STATUS_PENDING_REVIEWERS;
			} else if ($anyIncompletedReview) {
				$status = REVIEW_ROUND_STATUS_PENDING_REVIEWS;
			} else if ($anyUnreadReview) {
				$status = REVIEW_ROUND_STATUS_REVIEWS_READY;
			} else {
				$status = REVIEW_ROUND_STATUS_REVIEWS_COMPLETED;
			}

			// Check for special cases where we don't want to update the status.
			if (in_array($status, array(REVIEW_ROUND_STATUS_REVIEWS_COMPLETED,
			REVIEW_ROUND_STATUS_REVIEWS_READY))) {
				if (in_array($reviewRound->getStatus(), $this->getEditorDecisionRoundStatus())) {
					// We will skip changing the current review round status to
					// "reviews completed" or "reviews ready" if the current round
					// status is related with an editor decision.
					return;
				}
			}

			// Don't update the review round status if it isn't the
			// stage's current one.
			$lastReviewRound =& $this->getLastReviewRoundByMonographId($reviewRound->getSubmissionId(), $reviewRound->getStageId());
			if ($lastReviewRound->getId() != $reviewRound->getId()) {
				return;
			}
		}

		// Avoid unnecessary database access.
		if ($status != $currentStatus) {
			$params = array((int)$status, (int)$reviewRound->getId());
			$this->update('UPDATE review_rounds SET status = ? WHERE review_round_id = ?', $params);
			// Update the data in object too.
			$reviewRound->setStatus($status);
		}
	}

	/**
	 * Return review round status that are related
	 * with editor decisions.
	 * @return array
	 */
	function getEditorDecisionRoundStatus() {
		return array(
			REVIEW_ROUND_STATUS_REVISIONS_REQUESTED,
			REVIEW_ROUND_STATUS_RESUBMITTED,
			REVIEW_ROUND_STATUS_SENT_TO_EXTERNAL,
			REVIEW_ROUND_STATUS_ACCEPTED,
			REVIEW_ROUND_STATUS_DECLINED
		);
	}


	//
	// Private methods
	//
	/**
	 * Internal function to return a review round object from a row.
	 * @param $row array
	 * @return Signoff
	 */
	function &_fromRow(&$row) {
		$reviewRound = $this->newDataObject();

		$reviewRound->setId((int)$row['review_round_id']);
		$reviewRound->setSubmissionId((int)$row['submission_id']);
		$reviewRound->setStageId((int)$row['stage_id']);
		$reviewRound->setRound((int)$row['round']);
		$reviewRound->setStatus((int)$row['status']);

		return $reviewRound;
	}
}

?>
