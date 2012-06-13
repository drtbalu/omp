<?php

/**
 * @file controllers/grid/users/reviewer/ReviewerGridRow.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewerGridRow
 * @ingroup controllers_grid_users_reviewer
 *
 * @brief Reviewer grid row definition
 */

import('lib.pkp.classes.controllers.grid.GridRow');

class ReviewerGridRow extends GridRow {
	/**
	 * Constructor
	 */
	function ReviewerGridRow() {
		parent::GridRow();
	}

	//
	// Overridden methods from GridRow
	//
	/**
	 * @see GridRow::initialize()
	 * @param $request PKPRequest
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// Retrieve the monograph id from the request
		// These parameters need not be validated as we're just
		// passing them along to another request, where they will be
		// checked before they're used.
		$monographId = (int) $request->getUserVar('monographId');
		$stageId = (int) $request->getUserVar('stageId');
		$round = (int) $request->getUserVar('round');

		// Is this a new row or an existing row?
		$rowId = $this->getId();
		if (!empty($rowId) && is_numeric($rowId)) {
			// Only add row actions if this is an existing row
			$router =& $request->getRouter();
			$actionArgs = array(
				'monographId' => $monographId,
				'reviewAssignmentId' => $rowId,
				'stageId' => $stageId,
				'round' => $round
			);

			$this->addAction(
				new LinkAction(
					'email',
					new AjaxModal(
						$router->url($request, null, null, 'sendEmail', null, $actionArgs),
						__('grid.user.email'),
						'notify',
						true
					),
				__('grid.user.email'),
				'notify'
				)
			);

			$reviewAssignment =& $this->getData();
			// Only assign this action if the reviewer has not acknowledged yet.
			if (!$reviewAssignment->getDateAcknowledged()) {
				import('lib.pkp.classes.linkAction.request.RemoteActionConfirmationModal');
				$this->addAction(
					new LinkAction(
						'remove',
						new RemoteActionConfirmationModal(
							__('common.confirmDelete'), null,
							$router->url($request, null, null, 'deleteReviewer', null, $actionArgs)
						),
					__('grid.action.remove'),
					'delete'
					)
				);
			}

			// Set a non-default template that supports row actions
			$this->setTemplate('controllers/grid/gridRowWithActions.tpl');
		}
	}
}

?>
