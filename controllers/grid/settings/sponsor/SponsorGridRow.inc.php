<?php

/**
 * @file controllers/grid/settings/sponsor/SponsorGridRow.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SponsorGridRow
 * @ingroup controllers_grid_settings_sponsor
 *
 * @brief Handle sponsor grid row requests.
 */

import('lib.pkp.classes.controllers.grid.GridRow');

class SponsorGridRow extends GridRow {
	/**
	 * Constructor
	 */
	function SponsorGridRow() {
		parent::GridRow();
	}

	//
	// Overridden template methods
	//
	/*
	 * Configure the grid row
	 * @param $request PKPRequest
	 */
	function initialize(&$request) {
		parent::initialize($request);
		// add Grid Row Actions

		// Is this a new row or an existing row?
		$rowId = $this->getId();
		if (!empty($rowId)) {
			// Actions
			$router =& $request->getRouter();
			$actionArgs = array(
				'gridId' => $this->getGridId(),
				'rowId' => $rowId
			);
			$this->addAction(
				new LinkAction(
					'editSponsor',
					new AjaxModal(
						$router->url($request, null, null, 'editSponsor', null, $actionArgs),
						__('grid.action.edit'),
						'edit',
						true
						),
					__('grid.action.edit'),
					'edit')
			);

			import('lib.pkp.classes.linkAction.request.RemoteActionConfirmationModal');

			$this->addAction(
				new LinkAction(
					'deleteSponsor',
					new RemoteActionConfirmationModal(
						__('common.confirmDelete'),
						null,
						$router->url($request, null, null, 'deleteSponsor', null, $actionArgs)
					),
					__('grid.action.delete'),
					'delete')
			);

			$this->setTemplate('controllers/grid/gridRowWithActions.tpl');
		}
	}
}

?>
