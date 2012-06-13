<?php

/**
 * @file controllers/grid/users/reviewer/form/CreateReviewerForm.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CreateReviewerForm
 * @ingroup controllers_grid_users_reviewer_form
 *
 * @brief Form for creating and subsequently adding a reviewer to a submission.
 */

import('controllers.grid.users.reviewer.form.ReviewerForm');

class CreateReviewerForm extends ReviewerForm {
	/**
	 * Constructor.
	 * @param $monograph Monograph
	 * @param $reviewRound ReviewRound
	 */
	function CreateReviewerForm(&$monograph, &$reviewRound) {
		parent::ReviewerForm($monograph, $reviewRound);
		$this->setTemplate('controllers/grid/users/reviewer/form/createReviewerForm.tpl');

		$this->addCheck(new FormValidator($this, 'firstname', 'required', 'user.profile.form.firstNameRequired'));
		$this->addCheck(new FormValidator($this, 'lastname', 'required', 'user.profile.form.lastNameRequired'));
		$this->addCheck(new FormValidatorCustom($this, 'username', 'required', 'user.register.form.usernameExists', array(DAORegistry::getDAO('UserDAO'), 'userExistsByUsername'), array(), true));
		$this->addCheck(new FormValidatorAlphaNum($this, 'username', 'required', 'user.register.form.usernameAlphaNumeric'));
		$this->addCheck(new FormValidatorEmail($this, 'email', 'required', 'user.profile.form.emailRequired'));
		$this->addCheck(new FormValidatorCustom($this, 'email', 'required', 'user.register.form.emailExists', array(DAORegistry::getDAO('UserDAO'), 'userExistsByEmail'), array(), true));
		$this->addCheck(new FormValidator($this, 'userGroupId', 'required', 'user.profile.form.usergroupRequired'));
	}


	/**
	 * Fetch the form.
	 * @see Form::fetch()
	 */
	function fetch(&$request) {

		$searchByNameAction = $this->getSearchByNameAction($request);

		$this->setReviewerFormAction($searchByNameAction);
		return parent::fetch($request);
	}

	/**
	 * Assign form data to user-submitted data.
	 * @see Form::readInputData()
	 */
	function readInputData() {
		parent::readInputData();

		$this->readUserVars(array(
			'firstname',
			'middlename',
			'lastname',
			'affiliation',
			'keywords',
			'interestsTextOnly',
			'username',
			'email',
			'skipEmail',
			'userGroupId'
		));
	}

	/**
	 * Save review assignment
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function execute($args, &$request) {
		$userDao =& DAORegistry::getDAO('UserDAO');
		$user = new User();

		$user->setFirstName($this->getData('firstname'));
		$user->setMiddleName($this->getData('middlename'));
		$user->setLastName($this->getData('lastname'));
		$user->setEmail($this->getData('email'));

		$authDao =& DAORegistry::getDAO('AuthSourceDAO');
		$auth =& $authDao->getDefaultPlugin();
		$user->setAuthId($auth?$auth->getAuthId():0);

		$user->setUsername($this->getData('username'));
		$password = Validation::generatePassword();

		if (isset($auth)) {
			$user->setPassword($password);
			// FIXME Check result and handle failures
			$auth->doCreateUser($user);
			$user->setAuthId($auth->authId);
			$user->setPassword(Validation::encryptCredentials($user->getId(), Validation::generatePassword())); // Used for PW reset hash only
		} else {
			$user->setPassword(Validation::encryptCredentials($this->getData('username'), $password));
		}

		$user->setDateRegistered(Core::getCurrentDate());
		$reviewerId = $userDao->insertUser($user);

		// Set the reviewerId in the Form for the parent class to use
		$this->setData('reviewerId', $reviewerId);

		// Insert the user interests
		$interests = $this->getData('interestsKeywords') ? $this->getData('interestsKeywords') : $this->getData('interestsTextOnly');
		import('lib.pkp.classes.user.InterestManager');
		$interestManager = new InterestManager();
		$interestManager->setInterestsForUser($user, $interests);

		// Assign the selected user group ID to the user
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO'); /* @var $userGroupDao UserGroupDAO */
		$userGroupId = (int) $this->getData('userGroupId');
		$userGroupDao->assignUserToGroup($reviewerId, $userGroupId);

		if (!$this->getData('skipEmail')) {
			// Send welcome email to user
			import('classes.mail.MailTemplate');
			$mail = new MailTemplate('REVIEWER_REGISTER');
			if ($mail->isEnabled()) {
				$press =& $request->getPress();
				$mail->setFrom($press->getSetting('contactEmail'), $press->getSetting('contactName'));
				$mail->assignParams(array('username' => $this->getData('username'), 'password' => $password, 'userFullName' => $user->getFullName()));
				$mail->addRecipient($user->getEmail(), $user->getFullName());
				$mail->send($request);
			}
		}

		return parent::execute($args, $request);
	}
}

?>
