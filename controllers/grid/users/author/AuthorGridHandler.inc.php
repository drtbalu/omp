<?php

/**
 * @file controllers/grid/users/author/AuthorGridHandler.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorGridHandler
 * @ingroup controllers_grid_users_author
 *
 * @brief Handle author grid requests.
 */

// import grid base classes
import('lib.pkp.classes.controllers.grid.GridHandler');


// import author grid specific classes
import('controllers.grid.users.author.AuthorGridCellProvider');
import('controllers.grid.users.author.AuthorGridRow');

// Link action & modal classes
import('lib.pkp.classes.linkAction.request.AjaxModal');

class AuthorGridHandler extends GridHandler {
	/** @var Monograph */
	var $_monograph;

	/** @var boolean */
	var $_readOnly;

	/**
	 * Constructor
	 */
	function AuthorGridHandler() {
		parent::GridHandler();
		$this->addRoleAssignment(
				array(ROLE_ID_PRESS_MANAGER, ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_ASSISTANT, ROLE_ID_AUTHOR),
				array('fetchGrid', 'fetchRow', 'addAuthor', 'editAuthor',
				'updateAuthor', 'deleteAuthor'));
		$this->addRoleAssignment(ROLE_ID_REVIEWER, array('fetchGrid', 'fetchRow'));
		$this->addRoleAssignment(array(ROLE_ID_PRESS_MANAGER, ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_ASSISTANT), array('addUser'));
	}


	//
	// Getters/Setters
	//
	/**
	 * Get the monograph associated with this author grid.
	 * @return Monograph
	 */
	function &getMonograph() {
		return $this->_monograph;
	}

	/**
	 * Set the MonographId
	 * @param Monograph
	 */
	function setMonograph($monograph) {
		$this->_monograph =& $monograph;
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
	// Overridden methods from PKPHandler
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

	/*
	 * Configure the grid
	 * @param $request PKPRequest
	 */
	function initialize(&$request) {
		parent::initialize($request);

		$this->setTitle('submission.contributors');
		$this->setInstructions('submission.contributorsDescription');

		// Retrieve the authorized monograph.
		$this->setMonograph($this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH));

		// Load submission-specific translations
		AppLocale::requireComponents(
			LOCALE_COMPONENT_OMP_SUBMISSION,
			LOCALE_COMPONENT_PKP_SUBMISSION,
			LOCALE_COMPONENT_PKP_USER,
			LOCALE_COMPONENT_OMP_DEFAULT_SETTINGS
		);

		$monograph =& $this->getMonograph();
		$userRoles = $this->getAuthorizedContextObject(ASSOC_TYPE_USER_ROLES);

		if ($monograph->getDateSubmitted() == null || array_intersect(array(ROLE_ID_PRESS_MANAGER, ROLE_ID_SERIES_EDITOR), $userRoles)) {
			$this->setReadOnly(false);
			// Grid actions
			$router =& $request->getRouter();
			$actionArgs = $this->getRequestArgs();
			$this->addAction(
				new LinkAction(
					'addAuthor',
					new AjaxModal(
						$router->url($request, null, null, 'addAuthor', null, $actionArgs),
						__('listbuilder.contributors.addContributor'),
						'addUser'
					),
					__('listbuilder.contributors.addContributor'),
					'add_user'
				)
			);
		} else {
			$this->setReadOnly(true);
		}

		// Columns
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
		$this->addColumn(
			new GridColumn(
				'principalContact',
				'author.users.contributor.principalContact',
				null,
				'controllers/grid/users/author/primaryContact.tpl',
				$cellProvider
			)
		);
	}


	//
	// Overridden methods from GridHandler
	//
	/**
	 * @see GridHandler::getRowInstance()
	 * @return AuthorGridRow
	 */
	function &getRowInstance() {
		$monograph =& $this->getMonograph();
		$row = new AuthorGridRow($monograph, $this->getReadOnly());
		return $row;
	}

	/**
	 * Get the arguments that will identify the data in the grid
	 * In this case, the monograph.
	 * @return array
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
	function &loadData($request, $filter = null) {
		$monograph =& $this->getMonograph();
		$authorDao =& DAORegistry::getDAO('AuthorDAO');
		$data =& $authorDao->getAuthorsBySubmissionId($monograph->getId(), true);
		return $data;
	}

	//
	// Public Author Grid Actions
	//
	/**
	 * An action to manually add a new author
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function addAuthor($args, &$request) {
		// Calling editAuthor() with an empty row id will add
		// a new author.
		return $this->editAuthor($args, $request);
	}

	/**
	 * Edit a author
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function editAuthor($args, &$request) {
		// Identify the author to be updated
		$authorId = $request->getUserVar('authorId');
		$monograph =& $this->getMonograph();

		$authorDao =& DAORegistry::getDAO('AuthorDAO');
		$author = $authorDao->getAuthor($authorId, $monograph->getId());

		// Form handling
		import('controllers.grid.users.author.form.AuthorForm');
		$authorForm = new AuthorForm($monograph, $author);
		$authorForm->initData();

		$json = new JSONMessage(true, $authorForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Edit a author
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function updateAuthor($args, &$request) {
		// Identify the author to be updated
		$authorId = $request->getUserVar('authorId');
		$monograph =& $this->getMonograph();

		$authorDao =& DAORegistry::getDAO('AuthorDAO');
		$author =& $authorDao->getAuthor($authorId, $monograph->getId());

		// Form handling
		import('controllers.grid.users.author.form.AuthorForm');
		$authorForm = new AuthorForm($monograph, $author);
		$authorForm->readInputData();
		if ($authorForm->validate()) {
			$authorId = $authorForm->execute();

			if(!isset($author)) {
				// This is a new contributor
				$author =& $authorDao->getAuthor($authorId, $monograph->getId());
				// New added author action notification content.
				$notificationContent = __('notification.addedAuthor');
			} else {
				// Author edition action notification content.
				$notificationContent = __('notification.editedAuthor');
			}

			// Create trivial notification.
			$currentUser =& $request->getUser();
			$notificationMgr = new NotificationManager();
			$notificationMgr->createTrivialNotification($currentUser->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => $notificationContent));

			// Prepare the grid row data
			$row =& $this->getRowInstance();
			$row->setGridId($this->getId());
			$row->setId($authorId);
			$row->setData($author);
			$row->initialize($request);

			// Render the row into a JSON response
			if($author->getPrimaryContact()) {
				// If this is the primary contact, redraw the whole grid
				// so that it takes the checkbox off other rows.
				return DAO::getDataChangedEvent();
			} else {
				return DAO::getDataChangedEvent($authorId);
			}
		} else {
			$json = new JSONMessage(true, $authorForm->fetch($request));
			return $json->getString();
		}
	}

	/**
	 * Delete a author
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function deleteAuthor($args, &$request) {
		// Identify the submission Id
		$monographId = $request->getUserVar('monographId');
		// Identify the author to be deleted
		$authorId = $request->getUserVar('authorId');

		$authorDao =& DAORegistry::getDAO('AuthorDAO');
		$result = $authorDao->deleteAuthorById($authorId, $monographId);

		if ($result) {
			return DAO::getDataChangedEvent($authorId);
		} else {
			$json = new JSONMessage(false, __('submission.submit.errorDeletingAuthor'));
			return $json->getString();
		}
	}

	/**
	 * Add a user with data initialized from an existing author.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function addUser($args, &$request) {
		// Identify the author Id.
		$authorId = $request->getUserVar('authorId');

		$authorDao =& DAORegistry::getDAO('AuthorDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');
		$author =& $authorDao->getAuthor($authorId);

		if ($author !== null && $userDao->userExistsByEmail($author->getEmail())) {
			// We don't have administrative rights over this user.
			$json = new JSONMessage(false, __('grid.user.cannotAdminister'));
		} else {
			// Form handling.
			import('controllers.grid.settings.user.form.UserDetailsForm');
			$userForm = new UserDetailsForm($request, null, $author);
			$userForm->initData($args, $request);

			$json = new JSONMessage(true, $userForm->display($args, $request));
		}
		return $json->getString();
	}
}

?>
