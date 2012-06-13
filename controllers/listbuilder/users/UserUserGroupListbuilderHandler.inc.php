<?php

/**
 * @file controllers/listbuilder/users/UserUserGroupListbuilderHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UserUserGroupListbuilderHandler
 * @ingroup controllers_listbuilder_users
 *
 * @brief Class assign/remove mappings of user user groups
 */

import('lib.pkp.classes.controllers.listbuilder.ListbuilderHandler');

class UserUserGroupListbuilderHandler extends ListbuilderHandler {
	/** @var integer the user id for which to map user groups */
	var $_userId;

	/** @var $press Press */
	var $_press;


	/**
	 * Constructor
	 */
	function UserUserGroupListbuilderHandler() {
		parent::ListbuilderHandler();
		$this->addRoleAssignment(
			ROLE_ID_PRESS_MANAGER,
			array('fetch', 'fetchRow', 'fetchOptions', 'save')
		);
	}


	//
	// Setters and Getters
	//
	/**
	 * Set the user id
	 * @param $userId integer
	 */
	function setUserId($userId) {
		$this->_userId = $userId;
	}


	/**
	 * Get the user id
	 * @return integer
	 */
	function getUserId() {
		return $this->_userId;
	}


	/**
	 * Set the press
	 * @param $press Press
	 */
	function setPress(&$press) {
		$this->_press =& $press;
	}


	/**
	 * Get the press
	 * @return Press
	 */
	function &getPress() {
		return $this->_press;
	}


	//
	// Overridden parent class functions
	//
	/**
	 * @see GridDataProvider::getRequestArgs()
	 */
	function getRequestArgs() {
		return array(
			'userId' => $this->getUserId()
		);
	}


	/**
	 * @see ListbuilderHandler::getOptions
	 * @param $includeDesignations boolean
	 */
	function getOptions($includeDesignations = false) {
		// Initialize the object to return
		$items = array(
			array(), // Names
			array() // Designations
		);

		// Fetch the user groups
		$press =& $this->getPress();
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$userGroups =& $userGroupDao->getByContextId($press->getId());
		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$roleNames = $roleDao->getRoleNames(true);

		// Assemble the array to return
		while (!$userGroups->eof()) {
			$userGroup =& $userGroups->next();
			$userGroupId = $userGroup->getId();
			$roleId = $userGroup->getRoleId();
			$roleName = __($roleNames[$roleId]);

			$items[0][$roleId][$userGroupId] = $userGroup->getLocalizedName();
			if ($includeDesignations) {
				$items[1][$userGroupId] = $userGroup->getLocalizedAbbrev();
			}

			// Add the optgroup label.
			$items[0][LISTBUILDER_OPTGROUP_LABEL][$roleId] = $roleName;

			unset($userGroup);
		}

		return $items;
	}


	/**
	 * Initialize the grid with the currently selected set of user groups.
	 */
	function loadData() {
		$press =& $this->getPress();
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$userGroups =& $userGroupDao->getByUserId($this->getUserId(), $press->getId());

		return $userGroups;
	}


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, $args, $roleAssignments) {
		import('classes.security.authorization.OmpPressAccessPolicy');
		$this->addPolicy(new OmpPressAccessPolicy($request, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}


	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request) {
		// FIXME Validate user ID?
		$this->setUserId((int) $request->getUserVar('userId'));

		$this->setPress($request->getPress());
		parent::initialize($request);

		// Basic configuration
		$this->setTitle($request->getUserVar('title'));
		$this->setSourceType(LISTBUILDER_SOURCE_TYPE_SELECT);
		$this->setSaveType(LISTBUILDER_SAVE_TYPE_EXTERNAL);
		$this->setSaveFieldName('roles');

		import('controllers.listbuilder.users.UserGroupListbuilderGridCellProvider');
		$cellProvider = new UserGroupListbuilderGridCellProvider();

		// Name column
		$nameColumn = new ListbuilderGridColumn($this, 'name', 'common.name');
		$nameColumn->setCellProvider($cellProvider);
		$this->addColumn($nameColumn);

		// Designation column
		$designationColumn = new ListbuilderGridColumn($this,
			'designation',
			'common.designation',
			null,
			'controllers/listbuilder/listbuilderNonEditGridCell.tpl'
		);
		$designationColumn->setCellProvider($cellProvider);
		$this->addColumn($designationColumn);
	}

	/**
	 * @see GridHandler::getRowDataElement
	 * Get the data element that corresponds to the current request
	 * Allow for a blank $rowId for when creating a not-yet-persisted row
	 */
	function getRowDataElement(&$request, $rowId) {
		// fallback on the parent if a rowId is found
		if ( !empty($rowId) ) {
			return parent::getRowDataElement($request, $rowId);
		}

		// Otherwise return from the $newRowId
		$newRowId = $this->getNewRowId($request);
		$userGroupId = $newRowId['name'];
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$press =& $this->getPress();
		$userGroup =& $userGroupDao->getById($userGroupId, $press->getId());
		return $userGroup;
	}
}

?>
