<?php

/**
 * @file controllers/grid/users/chapter/ChapterGridHandler.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ChapterGridHandler
 * @ingroup controllers_grid_users_chapter
 *
 * @brief Handle chapter grid requests.
 */

// import grid base classes
import('lib.pkp.classes.controllers.grid.CategoryGridHandler');
import('lib.pkp.classes.controllers.grid.DataObjectGridCellProvider');

// import chapter grid specific classes
import('controllers.grid.users.author.AuthorGridCellProvider');
import('controllers.grid.users.chapter.ChapterGridCategoryRow');

// Link action & modal classes
import('lib.pkp.classes.linkAction.request.AjaxModal');

class ChapterGridHandler extends CategoryGridHandler {
	/** @var boolean */
	var $_readOnly;

	/**
	 * Constructor
	 */
	function ChapterGridHandler() {
		parent::CategoryGridHandler();
		$this->addRoleAssignment(
			array(ROLE_ID_AUTHOR, ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER, ROLE_ID_PRESS_ASSISTANT),
			array(
				'fetchGrid', 'fetchRow', 'saveSequence',
				'addChapter', 'editChapter', 'updateChapter', 'deleteChapter',
				'addAuthor', 'editAuthor', 'updateAuthor', 'deleteAuthor'
			)
		);
	}


	//
	// Getters and Setters
	//
	/**
	 * Get the monograph associated with this chapter grid.
	 * @return Monograph
	 */
	function &getMonograph() {
		return $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
	}

	/**
	 * Get whether or not this grid should be 'read only'
	 * @return boolean
	 */
	function getReadOnly() {
		return $this->_readOnly;
	}

	/**
	 * Set the boolean for 'read only' status
	 * @param boolean
	 */
	function setReadOnly($readOnly) {
		$this->_readOnly =& $readOnly;
	}


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 * @param $request PKPRequest
	 * @param $args array
	 * @param $roleAssignments array
	 */
	function authorize(&$request, $args, $roleAssignments) {
		import('classes.security.authorization.OmpSubmissionAccessPolicy');
		$this->addPolicy(new OmpSubmissionAccessPolicy($request, $args, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * Configure the grid
	 * @param $request PKPRequest
	 */
	function initialize(&$request) {
		parent::initialize($request);

		$this->setTitle('submission.chapters');
		$this->setInstructions('submission.chaptersDescription');

		AppLocale::requireComponents(LOCALE_COMPONENT_OMP_DEFAULT_SETTINGS, LOCALE_COMPONENT_OMP_SUBMISSION);

		if (!$this->getReadOnly()) {
			// Grid actions
			$router =& $request->getRouter();
			$actionArgs = $this->getRequestArgs();

			$this->addAction(
				new LinkAction(
					'addChapter',
					new AjaxModal(
						$router->url($request, null, null, 'addChapter', null, $actionArgs),
						__('submission.chapter.addChapter'),
						'fileManagement'
					),
					__('submission.chapter.addChapter'),
					'add_item'
				)
			);
		}

		// Columns
		// reuse the cell providers for the AuthorGrid
		$cellProvider = new AuthorGridCellProvider();
		$this->addColumn(
			new GridColumn(
				'name',
				'author.users.contributor.name',
				null,
				'controllers/grid/gridCell.tpl',
				$cellProvider,
				array('width' => 50, 'alignment' => COLUMN_ALIGNMENT_LEFT)
			)
		);
		$this->addColumn(
			new GridColumn(
				'email',
				'author.users.contributor.email',
				null,
				'controllers/grid/gridCell.tpl',
				$cellProvider
			)
		);
		$this->addColumn(
			new GridColumn(
				'role',
				'author.users.contributor.role',
				null,
				'controllers/grid/gridCell.tpl',
				$cellProvider
			)
		);
	}

	/**
	 * @see GridHandler::initFeatures()
	 */
	function initFeatures($request, $args) {
		$monograph =& $this->getMonograph();
		$userRoles = $this->getAuthorizedContextObject(ASSOC_TYPE_USER_ROLES);

		if ($monograph->getDateSubmitted() == null || array_intersect(array(ROLE_ID_PRESS_MANAGER, ROLE_ID_SERIES_EDITOR), $userRoles)) {
			$this->setReadOnly(false);
			import('lib.pkp.classes.controllers.grid.feature.OrderCategoryGridItemsFeature');
			return array(new OrderCategoryGridItemsFeature(ORDER_CATEGORY_GRID_CATEGORIES_AND_ROWS));
		} else {
			$this->setReadOnly(true);
		}
	}

	/**
	 * @see GridDataProvider::getRequestArgs()
	 */
	function getRequestArgs() {
		$monograph =& $this->getMonograph();
		return array(
			'monographId' => $monograph->getId()
		);
	}

	/**
	 * @see GridHandler::loadData
	 */
	function &loadData(&$request, $filter) {
		$monograph =& $this->getMonograph();
		$chapterDao =& DAORegistry::getDAO('ChapterDAO');
		$chapters =& $chapterDao->getChapters($monograph->getId());
		return $chapters;
	}


	//
	// Extended methods from GridHandler
	//
	/**
	 * @see GridHandler::getRowInstance()
	 */
	function getRowInstance() {
		$row = parent::getRowInstance();
		return $row;
	}

	/**
	 * @see GridHandler::getRowDataElementSequence()
	 */
	function getRowDataElementSequence($author) {
		return $author->getSequence();
	}

	/**
	 * @see CategoryGridHandler::saveRowDataElementSequence()
	 */
	function saveRowDataElementSequence($author, $chapterId, $newSequence) {
		$monograph =& $this->getMonograph();

		// Remove the chapter author id.
		$chapterAuthorDao =& DAORegistry::getDAO('ChapterAuthorDAO');
		$chapterAuthorDao->deleteChapterAuthorById($author->getId(), $chapterId);

		// Add it again with the correct sequence value.
		// FIXME: primary authors not set for chapter authors.
		$chapterAuthorDao->insertChapterAuthor($author->getId(), $chapterId, $monograph->getId(), false, $newSequence);
	}


	//
	// Implement template methods from CategoryGridHandler
	//
	/**
	 * @see CategoryGridHandler::getCategoryRowInstance()
	 */
	function &getCategoryRowInstance() {
		$monograph =& $this->getMonograph();
		$row = new ChapterGridCategoryRow($monograph, $this->getReadOnly());
		return $row;
	}

	/**
	 * @see CategoryGridHandler::getCategoryData()
	 */
	function getCategoryData(&$chapter) {
		$authorFactory =& $chapter->getAuthors(); /* @var $authorFactory DAOResultFactory */
		$authors = $authorFactory->toAssociativeArray();
		return $authors;
	}

	/**
	 * @see CategoryGridHandler::getCategoryDataElementSequence()
	 */
	function getCategoryDataElementSequence($chapter) {
		return $chapter->getSequence();
	}

	/**
	 * @see CategoryGridHandler::saveCategoryDataElementSequence()
	 */
	function saveCategoryDataElementSequence($chapter, $newSequence) {
		$chapterDao =& DAORegistry::getDAO('ChapterDAO');
		$chapter->setSequence($newSequence);
		$chapterDao->updateObject($chapter);
	}


	//
	// Public Chapter Grid Actions
	//
	/**
	 * Add a chapter.
	 * @param $args array
	 * @param $request Request
	 */
	function addChapter($args, &$request) {
		// Calling editChapter() with an empty row id will add
		// a new chapter.
		return $this->editChapter($args, $request);
	}

	/**
	 * Edit a chapter
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function editChapter($args, &$request) {
		$chapter =& $this->_getChapterFromRequest($request);

		// Form handling
		import('controllers.grid.users.chapter.form.ChapterForm');
		$chapterForm = new ChapterForm($this->getMonograph(), $chapter);
		$chapterForm->initData();

		$json = new JSONMessage(true, $chapterForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Update a chapter
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function updateChapter($args, &$request) {
		// Identify the chapter to be updated
		$chapter =& $this->_getChapterFromRequest($request);

		// Form initialization
		import('controllers.grid.users.chapter.form.ChapterForm');
		$chapterForm = new ChapterForm($this->getMonograph(), $chapter);
		$chapterForm->readInputData();

		// Form validation
		if ($chapterForm->validate()) {
			$chapterForm->execute();

			$newChapter =& $chapterForm->getChapter();

			return DAO::getDataChangedEvent($newChapter->getId());
		} else {
			// Return an error
			$json = new JSONMessage(false);
		}

		// Return the serialized JSON response
		return $json->getString();
	}

	/**
	 * Delete a chapter
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function deleteChapter($args, &$request) {
		// Identify the chapter to be deleted
		$chapter =& $this->_getChapterFromRequest($request);
		$chapterId = $chapter->getId();

		// remove Authors assigned to this chapter first
		$chapterAuthorDao =& DAORegistry::getDAO('ChapterAuthorDAO');
		$assignedAuthorIds = $chapterAuthorDao->getAuthorIdsByChapterId($chapterId);

		foreach ($assignedAuthorIds as $authorId) {
			$chapterAuthorDao->deleteChapterAuthorById($authorId, $chapterId);
		}

		$chapterDao = DAORegistry::getDAO('ChapterDAO');
		$result = $chapterDao->deleteObject($chapter);

		if ($result) {
			return DAO::getDataChangedEvent();
		} else {
			$json = new JSONMessage(false, __('submission.chapters.grid.errorDeletingChapter'));
		}
		return $json->getString();
	}

	/**
	 * Fetch and validate the chapter from the request arguments
	 */
	function &_getChapterFromRequest(&$request) {
		$monograph =& $this->getMonograph();
		$chapterDao =& DAORegistry::getDAO('ChapterDAO');
		$chapter =& $chapterDao->getChapter((int) $request->getUserVar('chapterId'), $monograph->getId());
		return $chapter;
	}
}

?>
