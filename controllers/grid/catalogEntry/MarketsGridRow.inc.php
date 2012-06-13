<?php

/**
 * @file controllers/grid/catalogEntry/MarketsGridRow.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MarketsGridRow
 * @ingroup controllers_grid_catalogEntry
 *
 * @brief Markets grid row definition
 */

import('lib.pkp.classes.controllers.grid.GridRow');

class MarketsGridRow extends GridRow {
	/** @var Monograph **/
	var $_monograph;

	/**
	 * Constructor
	 */
	function MarketsGridRow(&$monograph) {
		$this->_monograph =& $monograph;
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
		// Do the default initialization
		parent::initialize($request);

		$monograph =& $this->getMonograph();

		// Is this a new row or an existing row?
		$market = $this->_data;

		if ($market != null && is_numeric($market->getId())) {
			$router =& $request->getRouter();
			$actionArgs = array(
				'monographId' => $monograph->getId(),
				'marketId' => $market->getId()
			);

			// Add row-level actions
			import('lib.pkp.classes.linkAction.request.AjaxModal');
			$this->addAction(
				new LinkAction(
					'editMarket',
					new AjaxModal(
						$router->url($request, null, null, 'editMarket', null, $actionArgs),
						__('grid.action.edit'),
						'edit'
					),
					__('grid.action.edit'),
					'edit'
				)
			);

			import('lib.pkp.classes.linkAction.request.RemoteActionConfirmationModal');
			$this->addAction(
				new LinkAction(
					'deleteMarket',
					new RemoteActionConfirmationModal(
						__('common.confirmDelete'),
						null,
						$router->url($request, null, null, 'deleteMarket', null, $actionArgs)
					),
					__('grid.action.delete'),
					'delete'
				)
			);

			// Set a non-default template that supports row actions
			$this->setTemplate('controllers/grid/gridRowWithActions.tpl');
		}
	}

	/**
	 * Get the monograph for this row (already authorized)
	 * @return Monograph
	 */
	function &getMonograph() {
		return $this->_monograph;
	}
}
?>
