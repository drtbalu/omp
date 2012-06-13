<?php

/**
 * @file controllers/listbuilder/users/UserGroupListbuilderGridCellProvider.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UserGroupListbuilderGridCellProvider
 * @ingroup controllers_listbuilder_users
 *
 * @brief Base class for a cell provider that can retrieve labels from arrays
 */

import('lib.pkp.classes.controllers.grid.GridCellProvider');

class UserGroupListbuilderGridCellProvider extends GridCellProvider {
	/**
	 * Constructor
	 */
	function UserGroupListbuilderGridCellProvider() {
		parent::GridCellProvider();
	}

	//
	// Template methods from GridCellProvider
	//
	/**
	 * This implementation assumes a simple data element array that
	 * has column ids as keys.
	 * @see GridCellProvider::getTemplateVarsFromRowColumn()
	 * @param $column GridColumn
	 * @return array
	 */
	function getTemplateVarsFromRowColumn(&$row, $column) {
		$userGroup =& $row->getData();
		$columnId = $column->getId();
		assert(is_a($userGroup, 'UserGroup') && !empty($columnId));
		switch ( $columnId ) {
			case 'name':
				return array('labelKey' => $userGroup->getId(), 'label' => $userGroup->getLocalizedName());
			case 'designation':
				return array('labelKey' => $userGroup->getId(), 'label' => $userGroup->getLocalizedAbbrev());
		}
		// we got an unexpected column
		assert(false);
	}
}

?>
