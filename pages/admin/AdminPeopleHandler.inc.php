<?php

/**
 * @file pages/admin/AdminPeopleHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AdminPeopleHandler
 * @ingroup pages_admin
 *
 * @brief Handle requests for people management functions.
 */


import('pages.admin.AdminHandler');

class AdminPeopleHandler extends AdminHandler {
	/**
	 * Constructor
	 */
	function AdminPeopleHandler() {
		parent::AdminHandler();

		$this->addRoleAssignment(
			array(ROLE_ID_SITE_ADMIN),
			array('mergeUsers')
		);
	}

	/**
	 * Allow the Site Administrator to merge user accounts, including attributed monographs etc.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function mergeUsers($args, &$request) {
		$this->setupTemplate($request, true);

		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$roleDao =& DAORegistry::getDAO('RoleDAO');

		$templateMgr =& TemplateManager::getManager();

		$oldUserId = $request->getUserVar('oldUserId');
		$newUserId = $request->getUserVar('newUserId');

		if (!empty($oldUserId) && !empty($newUserId)) {
			import('classes.user.UserAction');
			$userAction = new UserAction();
			$userAction->mergeUsers($oldUserId, $newUserId);
			$request->redirect(null, 'admin', 'mergeUsers');
		}

		if (!empty($oldUserId)) {
			// Get the old username for the confirm prompt.
			$oldUser =& $userDao->getUser($oldUserId);
			$templateMgr->assign('oldUsername', $oldUser->getUsername());
			unset($oldUser);
		}

		// The administrator must select one or both IDs.
		if ($request->getUserVar('roleSymbolic')!=null) $roleSymbolic = $request->getUserVar('roleSymbolic');
		else $roleSymbolic = isset($args[0])?$args[0]:'all';
		if ($roleSymbolic != 'all' && String::regexp_match_get('/^(\w+)$/', $roleSymbolic, $matches)) {
			$roleId = $roleDao->getRoleIdFromPath($matches[1]);
			if ($roleId == null) {
				$request->redirect(null, null, null, 'all');
			}
			$roleNames = $roleDao->getRoleNames(false, array($roleId));
			$roleName = array_shift($roleNames);
		} else {
			$roleId = 0;
			$roleName = 'admin.mergeUsers.allUsers';
		}

		$searchType = null;
		$searchMatch = null;
		$search = $request->getUserVar('search');
		$searchInitial = $request->getUserVar('searchInitial');
		if (!empty($search)) {
			$searchType = $request->getUserVar('searchField');
			$searchMatch = $request->getUserVar('searchMatch');

		} else if (!empty($searchInitial)) {
			$searchInitial = String::strtoupper($searchInitial);
			$searchType = USER_FIELD_LASTNAME;
			$searchMatch = 'startsWith';
			$search = $searchInitial;
		}

		$rangeInfo = Handler::getRangeInfo('users');

		if ($roleId) {
			$users =& $roleDao->getUsersByRoleId($roleId, null, $searchType, $search, $searchMatch, $rangeInfo);
			$templateMgr->assign('roleId', $roleId);
		} else {
			$users =& $userDao->getUsersByField($searchType, $searchMatch, $search, true, $rangeInfo);
		}

		$templateMgr->assign('currentUrl', $request->url(null, null, 'mergeUsers'));
		$templateMgr->assign('helpTopicId', 'site.administrativeFunctions');
		$templateMgr->assign_by_ref('users', $users);
		$templateMgr->assign_by_ref('thisUser', $request->getUser());
		$templateMgr->assign('isReviewer', $roleId == ROLE_ID_REVIEWER);
		$templateMgr->assign('roleName', $roleName);

		$templateMgr->assign('searchField', $searchType);
		$templateMgr->assign('searchMatch', $searchMatch);
		$templateMgr->assign('search', $search);
		$templateMgr->assign('searchInitial', $request->getUserVar('searchInitial'));

		$templateMgr->assign('fieldOptions', array(
			USER_FIELD_FIRSTNAME => 'user.firstName',
			USER_FIELD_LASTNAME => 'user.lastName',
			USER_FIELD_USERNAME => 'user.username',
			USER_FIELD_EMAIL => 'user.email',
			USER_FIELD_INTERESTS => 'user.interests',
		));

		$templateMgr->assign('roleSymbolicOptions', array(
			'all' => 'admin.mergeUsers.allUsers',
			'manager' => 'user.role.managers',
			'editor' => 'user.role.editors',
			'seriesEditor' => 'user.role.seriesEditors',
			'copyeditor' => 'user.role.copyeditors',
			'proofreader' => 'user.role.proofreaders',
			'reviewer' => 'user.role.reviewers',
			'author' => 'user.role.authors',
			'reader' => 'user.role.readers',
		));

		$templateMgr->assign('searchMatchOptions', array(
				'contains' => 'form.contains',
				'is' => 'form.is',
		));

		$templateMgr->assign_by_ref('userGroups', $userGroups);
		$templateMgr->assign('alphaList', explode(' ', __('common.alphaList')));
		$templateMgr->assign('oldUserId', $oldUserId);
		$templateMgr->assign('roleSymbolic', $roleSymbolic);
		$templateMgr->display('admin/selectMergeUser.tpl');
	}

}

?>
