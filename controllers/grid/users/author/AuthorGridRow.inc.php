<?php

/**
 * @file controllers/grid/users/author/AuthorGridRow.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorGridRow
 * @ingroup controllers_grid_users_author
 *
 * @brief Author grid row definition
 */

import('lib.pkp.classes.controllers.grid.GridRow');

class AuthorGridRow extends GridRow {
	/** @var Monograph **/
	var $_monograph;

	/** @var boolean */
	var $_reaadOnly;

	/**
	 * Constructor
	 */
	function AuthorGridRow(&$monograph, $readOnly = false) {
		$this->_monograph =& $monograph;
		$this->_readOnly = $readOnly;
		parent::GridRow();
	}

	//
	// Overridden methods from GridRow
	//
	/**
	 * @see GridRow::initialize()
	 * @param $request PKPRequest
	 */
	function initialize(&$request) {
		// Do the default initialization
		parent::initialize($request);

		// Retrieve the monograph from the request
		$monograph =& $this->getMonograph();

		// Is this a new row or an existing row?
		$rowId = $this->getId();
		if (!empty($rowId) && is_numeric($rowId)) {
			// Only add row actions if this is an existing row
			$router =& $request->getRouter();
			$actionArgs = array(
				'monographId' => $monograph->getId(),
				'authorId' => $rowId
			);

			// Add row-level actions
			import('lib.pkp.classes.linkAction.request.AjaxModal');
			$this->addAction(
				new LinkAction(
					'editAuthor',
					new AjaxModal(
						$router->url($request, null, null, 'editAuthor', null, $actionArgs),
						__('grid.action.editContributor'),
						'edit'
					),
					__('grid.action.edit'),
					'edit'
				)
			);

			import('lib.pkp.classes.linkAction.request.RemoteActionConfirmationModal');
			$this->addAction(
				new LinkAction(
					'deleteAuthor',
					new RemoteActionConfirmationModal(
						__('common.confirmDelete'),
						null,
						$router->url($request, null, null, 'deleteAuthor', null, $actionArgs)
					),
					__('grid.action.delete'),
					'delete'
				)
			);

			$user =& $request->getUser();
			$stageAssignmentDao =& DAORegistry::getDAO('StageAssignmentDAO');
			$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');

			$allowedToCreateUser = false;

			$stageAssignments =& $stageAssignmentDao->getBySubmissionAndStageId($monograph->getId(), $monograph->getStageId(), null, $user->getId());
			while ($stageAssignment =& $stageAssignments->next()) {
				$userGroup =& $userGroupDao->getById($stageAssignment->getUserGroupId());
				if (in_array($userGroup->getRoleId(), array(ROLE_ID_PRESS_MANAGER, ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_ASSISTANT))) {
					$allowedToCreateUser = true;
					break;
				}
			}

			if ($allowedToCreateUser) {

				$authorDao =& DAORegistry::getDAO('AuthorDAO');
				$userDao =& DAORegistry::getDAO('UserDAO');
				$author =& $authorDao->getAuthor($rowId);

				if ($author && !$userDao->userExistsByEmail($author->getEmail())) {
					$this->addAction(
						new LinkAction(
							'addUser',
							new AjaxModal(
								$router->url($request, null, null, 'addUser', null, $actionArgs),
								__('grid.user.add'),
								'modal_add_user',
								true
								),
							__('grid.user.add'),
							'add_user')
					);
				}
			}
			// Set a non-default template that supports row actions if not read only
			if (!$this->isReadOnly()) {
				$this->setTemplate('controllers/grid/gridRowWithActions.tpl');
			} else {
				$this->setTemplate('controllers/grid/gridRow.tpl');
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
	 * Determine if this grid row should be read only.
	 * @return boolean
	 */
	function isReadOnly() {
		return $this->_readOnly;
	}
}

?>
