<?php

/**
 * @file controllers/grid/users/chapter/ChapterGridCategoryRow.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ChapterGridCategoryRow
 * @ingroup controllers_grid_users_chapter
 *
 * @brief Chapter grid category row definition
 */

import('lib.pkp.classes.controllers.grid.GridCategoryRow');

// Link actions
import('lib.pkp.classes.linkAction.request.AjaxModal');
import('lib.pkp.classes.linkAction.request.RemoteActionConfirmationModal');

class ChapterGridCategoryRow extends GridCategoryRow {
	/** @var Monograph **/
	var $_monograph;

	/** @var Chapter **/
	var $_chapter;

	/** @var $_readOnly **/
	var $_readOnly;

	/**
	 * Constructor
	 */
	function ChapterGridCategoryRow(&$monograph, $readOnly = false) {
		$this->_monograph =& $monograph;
		$this->_readOnly = $readOnly;
		parent::GridCategoryRow();
	}

	//
	// Overridden methods from GridCategoryRow
	//
	/**
	 * @see GridCategoryRow::initialize()
	 * @param $request PKPRequest
	 */
	function initialize(&$request) {
		// Do the default initialization
		parent::initialize($request);

		// Retrieve the monograph id from the request
		$monograph =& $this->getMonograph();

		// Is this a new row or an existing row?
		$chapterId = $this->getId();
		if (!empty($chapterId) && is_numeric($chapterId)) {
			$chapter =& $this->getData();
			$this->_chapter =& $chapter;

			// Only add row actions if this is an existing row and the grid is not 'read only'
			if (!$this->isReadOnly()) {
				$router =& $request->getRouter();
				$actionArgs = array(
					'monographId' => $monograph->getId(),
					'chapterId' => $chapterId
				);

				$this->addAction(
					new LinkAction(
						'deleteChapter',
						new RemoteActionConfirmationModal(
							__('common.confirmDelete'),
							null,
							$router->url($request, null, null, 'deleteChapter', null, $actionArgs)
						),
						null,
						'delete'
					)
				);

				$this->addAction(new LinkAction(
					'editChapter',
					new AjaxModal(
						$router->url($request, null, null, 'editChapter', null, $actionArgs),
						__('submission.chapter.editChapter')
					),
					$chapter->getLocalizedTitle()
				), GRID_ACTION_POSITION_ROW_CLICK);
			}

		}
	}

	/**
	 * Get the monograph for this row (already authorized)
	 * @return Monograph
	 */
	function &getMonograph() {
		return $this->_monograph;
	}

	/**
	 * Get the chapter for this row
	 * @return Chapter
	 */
	function &getChapter() {
		return $this->_chapter;
	}

	/**
	 * Determine if this grid row should be read only.
	 * @return boolean
	 */
	function isReadOnly() {
		return $this->_readOnly;
	}

	/**
	 * Use a label if the actions in the grid are disabled.
	 * return string
	 */
	function getCategoryLabel() {
		if ($this->isReadOnly())
			return $this->getChapter()->getLocalizedTitle();
		else
			return '';
	}
}
?>
