<?php

/**
 * @file controllers/grid/content/spotlights/SpotlightsGridRow.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SpotlightsGridRow
 * @ingroup controllers_grid_content_spotlights
 *
 * @brief Spotlights grid row definition
 */

import('lib.pkp.classes.controllers.grid.GridRow');

class SpotlightsGridRow extends GridRow {
	/** @var Press **/
	var $_press;

	/**
	 * Constructor
	 */
	function SpotlightsGridRow(&$press) {
		$this->setPress($press);
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

		$press =& $this->getPress();

		// Is this a new row or an existing row?
		$spotlight = $this->_data;
		if ($spotlight != null && is_numeric($spotlight->getId())) {
			$router =& $request->getRouter();
			$actionArgs = array(
				'pressId' => $press->getId(),
				'spotlightId' => $spotlight->getId()
			);

			// Add row-level actions
			import('lib.pkp.classes.linkAction.request.AjaxModal');
			$this->addAction(
				new LinkAction(
					'editSpotlight',
					new AjaxModal(
						$router->url($request, null, null, 'editSpotlight', null, $actionArgs),
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
					'deleteSpotlight',
					new RemoteActionConfirmationModal(
						__('common.confirmDelete'),
						null,
						$router->url($request, null, null, 'deleteSpotlight', null, $actionArgs)
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
	 * Get the press for this row (already authorized)
	 * @return Press
	 */
	function &getPress() {
		return $this->_press;
	}

	/**
	 * Set the press for this row (already authorized)
	 * @return Press
	 */
	function setPress($press) {
		$this->_press =& $press;
	}
}
?>
