<?php

/**
 * @file controllers/grid/files/proof/AuthorProofingGridCategoryRow.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorProofingGridCategoryRow
 * @ingroup controllers_grid_files_proof
 *
 * @brief Class defining data for an author proofing grid category row.
 *
 */
import('lib.pkp.classes.controllers.grid.GridCategoryRow');

class AuthorProofingGridCategoryRow extends GridCategoryRow {

	/**
	 * Constructor.
	 */
	function AuthorProofingGridCategoryRow() {
		parent::GridCategoryRow();
	}

	/**
	 * @see GridCategoryRow::getCategoryLabel()
	 */
	function getCategoryLabel() {
		$publicationFormat =& $this->getData();
		return $publicationFormat->getLocalizedTitle();
	}
}

?>
