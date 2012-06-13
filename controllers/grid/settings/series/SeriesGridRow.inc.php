<?php

/**
 * @file controllers/grid/settings/series/SeriesGridRow.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SeriesGridRow
 * @ingroup controllers_grid_settings_series
 *
 * @brief Handle series grid row requests.
 */

import('lib.pkp.classes.controllers.grid.GridRow');

class SeriesGridRow extends GridRow {
	/**
	 * Constructor
	 */
	function SeriesGridRow() {
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
		$this->setTemplate('controllers/grid/gridRowWithActions.tpl');
		$this->setupTemplate();

		// Is this a new row or an existing row?
		$seriesId = $this->getId();
		if (!empty($seriesId) && is_numeric($seriesId)) {
			$router =& $request->getRouter();

			import('lib.pkp.classes.linkAction.request.AjaxModal');
			$this->addAction(
				new LinkAction(
					'editSeries',
					new AjaxModal(
						$router->url($request, null, null, 'editSeries', null, array('seriesId' => $seriesId)),
						__('grid.action.edit'),
						null,
						true),
					__('grid.action.edit'),
					'edit'
				)
			);

			import('lib.pkp.classes.linkAction.request.RemoteActionConfirmationModal');
			$this->addAction(
				new LinkAction(
					'deleteSeries',
					new RemoteActionConfirmationModal(
						__('common.confirmDelete'),
						__('grid.action.delete'),
						$router->url($request, null, null, 'deleteSeries', null, array('seriesId' => $seriesId))
					),
					__('grid.action.delete'),
					'delete'
				)
			);
		}
	}

	/**
	 * @see PKPHandler::setupTemplate()
	 */
	function setupTemplate() {
		// Load manager translations. FIXME are these needed?
		AppLocale::requireComponents(
			LOCALE_COMPONENT_OMP_MANAGER,
			LOCALE_COMPONENT_PKP_COMMON,
			LOCALE_COMPONENT_PKP_USER,
			LOCALE_COMPONENT_APPLICATION_COMMON
		);
	}
}

?>
