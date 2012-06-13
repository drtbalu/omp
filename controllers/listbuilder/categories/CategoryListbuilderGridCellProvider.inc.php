<?php

/**
 * @file classes/controllers/listbuilder/categories/CategoryListbuilderGridCellProvider.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CategoryListbuilderGridCellProvider
 * @ingroup controllers_grid
 *
 * @brief Base class for a cell provider that can retrieve labels from arrays
 */

import('lib.pkp.classes.controllers.grid.GridCellProvider');

class CategoryListbuilderGridCellProvider extends GridCellProvider {
	/**
	 * Constructor
	 */
	function CategoryListbuilderGridCellProvider() {
		parent::GridCellProvider();
	}

	//
	// Template methods from GridCellProvider
	//
	/**
	 * This implementation assumes a simple data element array that
	 * has column ids as keys.
	 * @see GridCellProvider::getTemplateVarsFromRowColumn()
	 * @param $row GridRow
	 * @param $column GridColumn
	 * @return array
	 */
	function getTemplateVarsFromRowColumn(&$row, $column) {
		$category =& $row->getData();
		$columnId = $column->getId();
		assert((is_a($category, 'Category')) && !empty($columnId));

		return array('labelKey' => $category->getId(), 'label' => $category->getLocalizedTitle());
	}
}

?>
