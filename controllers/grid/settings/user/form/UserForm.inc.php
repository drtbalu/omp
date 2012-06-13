<?php

/**
 * @file controllers/grid/settings/user/form/UserForm.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UserForm
 * @ingroup controllers_grid_settings_user_form
 *
 * @brief Base class for user forms.
 */

import('lib.pkp.classes.form.Form');

class UserForm extends Form {

	/** @var Id of the user being edited */
	var $userId;

	/**
	 * Constructor.
	 * @param $request PKPRequest
	 * @param $userId int optional
	 * @param $author Author optional
	 */
	function UserForm($template, $userId = null) {
		parent::Form($template);

		$this->userId = isset($userId) ? (int) $userId : null;

		if (!is_null($userId)) {
			$this->addCheck(new FormValidatorListBuilder($this, 'roles', 'manager.users.roleRequired'));
		}
	}

	/**
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('roles'));
	}

	/**
	 * @see Form::execute()
	 */
	function execute($request) {
		ListbuilderHandler::unpack($request, $this->getData('roles'));
	}

	/**
	 * Persist a new entry insert.
	 * @see Listbuilder::insertentry
	 */
	function insertEntry(&$request, $newRowId) {
		$press =& $request->getPress();
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');

		$userGroupId = (int) $newRowId['name'];
		$userId = (int) $this->userId;

		// Ensure that:
		// $userGroupId is not empty
		// $userGroupId is valid for this press
		// user group assignment does not already exist
		if (
			empty($userGroupId) ||
			!$userGroupDao->contextHasGroup($press->getId(), $userGroupId) ||
			$userGroupDao->userInGroup($userId, $userGroupId)
		) {
			return false;
		} else {
			// Add the assignment
			$userGroupDao->assignUserToGroup($userId, $userGroupId);
		}

		return true;
	}

	/**
	 * Delete an entry.
	 * @see Listbuilder::deleteEntry
	 */
	function deleteEntry(&$request, $rowId) {
		$userGroupId = (int) $rowId;
		$userId = (int) $this->userId;

		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$press =& $request->getPress();

		$userGroupDao->removeUserFromGroup(
			$userId,
			(int) $userGroupId,
			$press->getId()
		);

		return true;
	}

}

?>
