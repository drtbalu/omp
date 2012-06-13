<?php

/**
 * @file controllers/grid/languages/LanguageGridRow.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class LanguageGridRow
 * @ingroup controllers_grid_languages
 *
 * @brief Language grid row definition
 */

import('lib.pkp.classes.controllers.grid.GridRow');
import('lib.pkp.classes.linkAction.request.RemoteActionConfirmationModal');

class LanguageGridRow extends GridRow {
	/**
	 * Constructor
	 */
	function LanguageGridRow() {
		parent::GridRow();
	}


	//
	// Overridden methods from GridRow
	//
	/**
	 * @see GridRow::initialize()
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// Is this a new row or an existing row?
		$rowId = $this->getId();
		$rowData = $this->getData();

		if (!empty($rowId)) {
			// Only add row actions if this is an existing row
			$router =& $request->getRouter();
			$actionArgs = array(
				'gridId' => $this->getGridId(),
				'rowId' => $rowId
			);

			if (Validation::isSiteAdmin()) {
				if (!$rowData['primary']) {
					$this->addAction(
						new LinkAction(
							'uninstall',
							new RemoteActionConfirmationModal(
								__('admin.languages.confirmUninstall'),
								__('grid.action.remove'),
								$router->url($request, null, null, 'uninstallLocale', null, $actionArgs)
								),
							__('grid.action.remove'),
							'delete')
					);
				}
				$this->addAction(
					new LinkAction(
						'reload',
						new RemoteActionConfirmationModal(
							__('manager.language.confirmDefaultSettingsOverwrite'),
							__('manager.language.reloadLocalizedDefaultSettings'),
							$router->url($request, null, null, 'reloadLocale', null, $actionArgs)
							),
						__('manager.language.reloadLocalizedDefaultSettings')
						)
				);

				// Set a non-default template that supports row actions
				$this->setTemplate('controllers/grid/gridRowWithActions.tpl');
			}
		}
	}
}

?>
