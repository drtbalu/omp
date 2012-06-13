<?php

/**
 * @file controllers/grid/content/navigation/FooterGridCellProvider.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FooterGridCellProvider
 * @ingroup controllers_grid_content_navigation
 *
 * @brief Base class for a cell provider that can retrieve labels for footers
 */

import('lib.pkp.classes.controllers.grid.DataObjectGridCellProvider');

class FooterGridCellProvider extends DataObjectGridCellProvider {
	/**
	 * Constructor
	 */
	function FooterGridCellProvider() {
		parent::DataObjectGridCellProvider();
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
		$data =& $row->getData();
		$element =& $data;

		$columnId = $column->getId();
		assert(is_a($element, 'DataObject') && !empty($columnId));
		// there is only one column in the Footer grid.
		switch ($columnId) {
			case 'title':
				return array('label' => '<a href="' . String::stripUnsafeHtml($element->getUrl()) . '" target="_blank">' . $element->getLocalizedTitle() . '</a>');
		}
	}
}

?>
