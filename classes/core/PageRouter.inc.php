<?php

/**
 * @file classes/core/PageRouter.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PageRouter
 * @ingroup core
 *
 * @brief Class providing OMP-specific page routing.
 */


import('lib.pkp.classes.core.PKPPageRouter');

class PageRouter extends PKPPageRouter {
	/**
	 * get the cacheable pages
	 * @return array
	 */
	function getCacheablePages() {
		return array('about', 'announcement', 'help', 'index', 'information', 'rt', '');
	}

	/**
	 * Redirect to user home page (or the user group home page if the user has one user group).
	 * @param $request PKPRequest the request to be routed
	 */
	function redirectHome(&$request) {
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$user = $request->getUser();
		$userId = $user->getId();

		if ($press =& $this->getContext($request, 1)) {
			// The user is in the press context, see if they have one role only
			$userGroups =& $userGroupDao->getByUserId($userId, $press->getId());
			if($userGroups->getCount() == 1) {
				$userGroup =& $userGroups->next();
				if ($userGroup->getRoleId() == ROLE_ID_READER) $request->redirect(null, 'index');
			}
			$request->redirect(null, 'dashboard');
		} else {
			// The user is at the site context, check to see if they are
			// only registered in one place w/ one role
			$userGroups =& $userGroupDao->getByUserId($userId, CONTEXT_ID_NONE);

			if($userGroups->getCount() == 1) {
				$pressDao =& DAORegistry::getDAO('PressDAO');
				$userGroup =& $userGroups->next();
				$press =& $pressDao->getById($userGroup->getContextId());
				if (!isset($press)) $request->redirect('index', 'index');
				if ($userGroup->getRoleId() == ROLE_ID_READER) $request->redirect(null, 'index');
			}
			$request->redirect('index', 'index');
		}
	}
}

?>
