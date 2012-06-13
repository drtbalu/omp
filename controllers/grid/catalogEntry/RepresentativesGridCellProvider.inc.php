<?php

/**
 * @file controllers/grid/catalogEntry/RepresentativesGridCellProvider.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class RepresentativesGridCellProvider
 * @ingroup controllers_grid_catalogEntry
 *
 * @brief Base class for a cell provider that can retrieve labels for representatives
 */

import('lib.pkp.classes.controllers.grid.DataObjectGridCellProvider');

class RepresentativesGridCellProvider extends DataObjectGridCellProvider {
	/**
	 * Constructor
	 */
	function RepresentativesGridCellProvider() {
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
		switch ($columnId) {
			case 'role':
				return array('label' => $element->getNameForONIXCode());
			case 'name':
				return array('label' => $element->getName());
		}
	}
}

?>
