<?php

/**
 * @file controllers/grid/files/FileNameGridColumn.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FileNameGridColumn
 * @ingroup controllers_grid_files
 *
 * @brief Implements a file name column.
 */

import('lib.pkp.classes.controllers.grid.GridColumn');

class FileNameGridColumn extends GridColumn {
	/** @var $_includeNotes boolean */
	var $_includeNotes;

	/** @var $_stageId int */
	var $_stageId;

	/**
	 * Constructor
	 * @param $includeNotes boolean
	 * @param $stageId int (optional)
	 */
	function FileNameGridColumn($includeNotes = true, $stageId = null) {
		$this->_includeNotes = $includeNotes;
		$this->_stageId = $stageId;

		import('lib.pkp.classes.controllers.grid.ColumnBasedGridCellProvider');
		$cellProvider = new ColumnBasedGridCellProvider();
		parent::GridColumn('name', 'common.name', null, 'controllers/grid/gridCell.tpl', $cellProvider,
			array('width' => 60, 'alignment' => COLUMN_ALIGNMENT_LEFT));
	}


	//
	// Public methods
	//
	/**
	 * Method expected by ColumnBasedGridCellProvider
	 * to render a cell in this column.
	 *
	 * @see ColumnBasedGridCellProvider::getTemplateVarsFromRowColumn()
	 */
	function getTemplateVarsFromRow($row) {
		// We do not need any template variables because
		// the only content of this column's cell will be
		// an action. See FileNameGridColumn::getCellActions().
		return array();
	}


	//
	// Override methods from GridColumn
	//
	/**
	 * @see GridColumn::getCellActions()
	 */
	function getCellActions(&$request, &$row, $position = GRID_ACTION_POSITION_DEFAULT) {
		$cellActions = parent::getCellActions($request, $row, $position);

		// Retrieve the monograph file.
		$submissionFileData =& $row->getData();
		assert(isset($submissionFileData['submissionFile']));
		$monographFile = $submissionFileData['submissionFile']; /* @var $monographFile MonographFile */

		// Create the cell action to download a file.
		import('controllers.api.file.linkAction.DownloadFileLinkAction');
		$cellActions[] = new DownloadFileLinkAction($request, $monographFile, $this->_getStageId());

		if ($this->_getIncludeNotes()) {
			import('controllers.informationCenter.linkAction.FileNotesLinkAction');
			$user =& $request->getUser();
			$cellActions[] = new FileNotesLinkAction($request, $monographFile, $user, $this->_getStageId());
		}
		return $cellActions;
	}

	//
	// Private methods
	//
	/**
	 * Determine whether or not submission note status should be included.
	 */
	function _getIncludeNotes() {
		return $this->_includeNotes;
	}

	/**
	 * Get stage id, if any.
	 * @return mixed int or null
	 */
	function _getStageId() {
		return $this->_stageId;
	}
}

?>
