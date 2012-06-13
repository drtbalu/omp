<?php

/**
 * @file pages/user/ProfileHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ProfileHandler
 * @ingroup pages_user
 *
 * @brief Handle requests for modifying user profiles.
 */


import('pages.user.UserHandler');

class ProfileHandler extends UserHandler {
	/**
	 * Constructor
	 */
	function ProfileHandler() {
		parent::UserHandler();
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
		$operations = array('profile', 'saveProfile', 'changePassword', 'savePassword');

		// Site access policy
		import('lib.pkp.classes.security.authorization.PKPSiteAccessPolicy');
		$this->addPolicy(new PKPSiteAccessPolicy($request, $operations, SITE_ACCESS_ALL_ROLES));

		// User must be logged in
		import('lib.pkp.classes.security.authorization.UserRequiredPolicy');
		$this->addPolicy(new UserRequiredPolicy($request));

		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * Display form to edit user's profile.
	 */
	function profile($args, &$request) {
		$this->setupTemplate(true);

		$user =& $request->getUser();
		import('classes.user.form.ProfileForm');
		$profileForm = new ProfileForm($user);
		if ($profileForm->isLocaleResubmit()) {
			$profileForm->readInputData();
		} else {
			$profileForm->initData($args, $request);
		}
		$profileForm->display($args, $request);
	}

	/**
	 * Validate and save changes to user's profile.
	 */
	function saveProfile($args, &$request) {
		$this->setupTemplate($request);
		$dataModified = false;
		$user =& $request->getUser();

		import('classes.user.form.ProfileForm');
		$profileForm = new ProfileForm($user);
		$profileForm->readInputData();

		if ($request->getUserVar('uploadProfileImage')) {
			if (!$profileForm->uploadProfileImage()) {
				$profileForm->addError('profileImage', __('user.profile.form.profileImageInvalid'));
			}
			$dataModified = true;
		} else if ($request->getUserVar('deleteProfileImage')) {
			$profileForm->deleteProfileImage();
			$dataModified = true;
		}

		if (!$dataModified && $profileForm->validate()) {
			$profileForm->execute($request);
			$request->redirect(null, $request->getRequestedPage());
		} else {
			$profileForm->display($args, $request);
		}
	}

	/**
	 * Display form to change user's password.
	 */
	function changePassword($args, &$request) {
		$this->setupTemplate(true);

		$user =& $request->getUser();
		$site =& $request->getSite();

		import('classes.user.form.ChangePasswordForm');
		if (checkPhpVersion('5.0.0')) { // WARNING: This form needs $this in constructor
			$passwordForm = new ChangePasswordForm($user, $site);
		} else {
			$passwordForm =& new ChangePasswordForm($user, $site);
		}
		$passwordForm->initData($args, $request);
		$passwordForm->display($args, $request);
	}

	/**
	 * Save user's new password.
	 */
	function savePassword($args, &$request) {
		$this->setupTemplate(true);

		$user =& $request->getUser();
		$site =& $request->getSite();

		import('classes.user.form.ChangePasswordForm');
		if (checkPhpVersion('5.0.0')) { // WARNING: This form needs $this in constructor
			$passwordForm = new ChangePasswordForm($user, $site);
		} else {
			$passwordForm =& new ChangePasswordForm($user, $site);
		}
		$passwordForm->readInputData();

		$this->setupTemplate(true);
		if ($passwordForm->validate()) {
			$passwordForm->execute($request);
			$request->redirect(null, $request->getRequestedPage());

		} else {
			$passwordForm->display($args, $request);
		}
	}
}

?>
