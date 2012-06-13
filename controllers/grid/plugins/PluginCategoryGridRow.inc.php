<?php

/**
 * @file controllers/grid/plugins/PluginCategoryGridRow.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PluginCategoryGridRow
 * @ingroup controllers_grid_plugins
 *
 * @brief Plugin category grid row definition.
 */

import('lib.pkp.classes.controllers.grid.GridCategoryRow');

class PluginCategoryGridRow extends GridCategoryRow {

	/**
	 * Constructor
	 */
	function PluginCategoryGridRow() {
		parent::GridCategoryRow();
	}

	//
	// Overridden methods from GridCategoryRow
	//
	/**
	 * @see GridCategoryRow::getCategoryLabel
	 */
	function getCategoryLabel() {
		$pluginCategory = $this->getData();
		return __("plugins.categories.$pluginCategory");
	}
}

?>
