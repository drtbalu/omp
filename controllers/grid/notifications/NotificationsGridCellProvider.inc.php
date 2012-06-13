<?php

/**
 * @file controllers/grid/notifications/NotificationsGridCellProvider.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class NotificationsGridCellProvider
 * @ingroup controllers_grid_notifications
 *
 * @brief Class for a cell provider that can retrieve labels from notifications
 */


import('lib.pkp.classes.controllers.grid.GridCellProvider');
import('lib.pkp.classes.linkAction.request.RedirectAction');

class NotificationsGridCellProvider extends GridCellProvider {
	/**
	 * Constructor
	 */
	function NotificationsGridCellProvider() {
		parent::GridCellProvider();
	}

	/**
	 * Get cell actions associated with this row/column combination
	 * @param $row GridRow
	 * @param $column GridColumn
	 * @return array an array of LinkAction instances
	 */
	function getCellActions(&$request, &$row, &$column, $position = GRID_ACTION_POSITION_DEFAULT) {
		if ( $column->getId() == 'title' ) {
			return array();
		} elseif ($column->getId() == 'task') {
			$notification =& $row->getData();

			$notificationMgr = new NotificationManager();
			return array(new LinkAction(
				'details',
				new RedirectAction(
					$notificationMgr->getNotificationUrl($request, $notification)
				),
				$notificationMgr->getNotificationMessage($request, $notification)
			));
		}
		// This should be unreachable.
		assert(false);
	}

	//
	// Template methods from GridCellProvider
	//
	/**
	 * Extracts variables for a given column from a data element
	 * so that they may be assigned to template before rendering.
	 * @param $row GridRow
	 * @param $column GridColumn
	 * @return array
	 */
	function getTemplateVarsFromRowColumn(&$row, $column) {
		$notification =& $row->getData();

		switch ($column->getId()) {
			case 'title':
				switch ($notification->getAssocType()) {
					case ASSOC_TYPE_MONOGRAPH:
						$monographId = $notification->getAssocId();
						break;
					case ASSOC_TYPE_MONOGRAPH_FILE:
						$fileId = $notification->getAssocId();
						break;
					case ASSOC_TYPE_SIGNOFF:
						$signoffDao =& DAORegistry::getDAO('SignoffDAO'); /* @var $signoffDao SignoffDAO */
						$signoff =& $signoffDao->getById($notification->getAssocId());
						if ($signoff->getAssocType() == ASSOC_TYPE_MONOGRAPH) {
							$monographId = $signoff->getAssocId();
						} elseif ($signoff->getAssocType() == ASSOC_TYPE_MONOGRAPH_FILE) {
							$fileId = $signoff->getAssocId();
						} else {
							// Don't know of SIGNOFFs with other ASSOC types for TASKS
							assert(false);
						}
						break;
					case ASSOC_TYPE_REVIEW_ASSIGNMENT:
						$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO'); /* @var $reviewAssignmentDao ReviewAssignmentDAO */
						$reviewAssignment =& $reviewAssignmentDao->getById($notification->getAssocId());
						assert(is_a($reviewAssignment, 'ReviewAssignment'));
						$monographId = $reviewAssignment->getSubmissionId();
						break;
					case ASSOC_TYPE_REVIEW_ROUND:
						$reviewRoundDao =& DAORegistry::getDAO('ReviewRoundDAO');
						$reviewRound =& $reviewRoundDao->getReviewRoundById($notification->getAssocId());
						assert(is_a($reviewRound, 'ReviewRound'));
						$monographId = $reviewRound->getSubmissionId();
						break;
					default:
						// Don't know of other ASSOC_TYPEs for TASK notifications
						assert(false);
				}

				if (!isset($monographId) && isset($fileId)) {
					assert(is_numeric($fileId));
					$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
					$monographFile =& $submissionFileDao->getLatestRevision($fileId);
					assert(is_a($monographFile, 'MonographFile'));
					$monographId = $monographFile->getMonographId();
				}
				assert(is_numeric($monographId));
				$monographDao =& DAORegistry::getDAO('MonographDAO'); /* @var $monographDao MonographDAO */
				$monograph =& $monographDao->getById($monographId);
				assert(is_a($monograph, 'Monograph'));

				$title = $monograph->getLocalizedTitle();
				if ( empty($title) ) $title = __('common.untitled');
				return array('label' => $title);
				break;
			case 'task':
				// The action has the label
				return array();
				break;
		}
	}
}

?>
