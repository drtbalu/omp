<?php

/**
 * @file controllers/grid/users/author/form/AuthorForm.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorForm
 * @ingroup controllers_grid_users_author_form
 *
 * @brief Form for adding/editing a author
 */

import('lib.pkp.classes.form.Form');

class AuthorForm extends Form {
	/** The monograph associated with the submission contributor being edited **/
	var $_monograph;

	/** Author the author being edited **/
	var $_author;

	/**
	 * Constructor.
	 */
	function AuthorForm($monograph, $author) {
		parent::Form('controllers/grid/users/author/form/authorForm.tpl');
		$this->setMonograph($monograph);
		$this->setAuthor($author);

		// Validation checks for this form
		$this->addCheck(new FormValidator($this, 'firstName', 'required', 'submission.submit.form.authorRequiredFields'));
		$this->addCheck(new FormValidator($this, 'lastName', 'required', 'submission.submit.form.authorRequiredFields'));
		$this->addCheck(new FormValidatorEmail($this, 'email', 'required', 'form.emailRequired'));
		$this->addCheck(new FormValidatorUrl($this, 'url', 'optional', 'user.profile.form.urlInvalid'));
		$this->addCheck(new FormValidator($this, 'userGroupId', 'required', 'submission.submit.form.contributorRoleRequired'));
		$this->addCheck(new FormValidatorPost($this));
	}

	//
	// Getters and Setters
	//
	/**
	 * Get the author
	 * @return Author
	 */
	function getAuthor() {
		return $this->_author;
	}

	/**
	 * Set the author
	 * @param @author Author
	 */
	function setAuthor($author) {
		$this->_author =& $author;
	}

	/**
	 * Get the Monograph
	 * @return Monograph
	 */
	function getMonograph() {
		return $this->_monograph;
	}

	/**
	 * Set the MonographId
	 * @param Monograph
	 */
	function setMonograph($monograph) {
		$this->_monograph =& $monograph;
	}


	//
	// Overridden template methods
	//
	/**
	 * Initialize form data from the associated author.
	 * @param $author Author
	 */
	function initData() {
		$author =& $this->getAuthor();

		if ( $author ) {
			$this->_data = array(
				'authorId' => $author->getId(),
				'firstName' => $author->getFirstName(),
				'middleName' => $author->getMiddleName(),
				'lastName' => $author->getLastName(),
				'suffix' => $author->getSuffix(),
				'affiliation' => $author->getAffiliation(AppLocale::getLocale()),
				'country' => $author->getCountry(),
				'email' => $author->getEmail(),
				'url' => $author->getUrl(),
				'userGroupId' => $author->getUserGroupId(),
				'biography' => $author->getBiography(AppLocale::getLocale()),
				'primaryContact' => $author->getPrimaryContact()
			);
		}
	}

	/**
	 * Fetch the form.
	 * @see Form::fetch()
	 */
	function fetch(&$request) {
		$author =& $this->getAuthor();

		$templateMgr =& TemplateManager::getManager();
		$countryDao =& DAORegistry::getDAO('CountryDAO');
		$countries =& $countryDao->getCountries();
		$templateMgr->assign_by_ref('countries', $countries);

		$router =& $request->getRouter();
		$context =& $router->getContext($request);

		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$authorUserGroups =& $userGroupDao->getByRoleId($context->getId(), ROLE_ID_AUTHOR);
		$templateMgr->assign_by_ref('authorUserGroups', $authorUserGroups);

		$monograph =& $this->getMonograph();
		$templateMgr->assign('monographId', $monograph->getId());

		return parent::fetch($request);
	}

	/**
	 * Assign form data to user-submitted data.
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array(
			'authorId',
			'firstName',
			'middleName',
			'lastName',
			'suffix',
			'affiliation',
			'country',
			'email',
			'url',
			'userGroupId',
			'biography',
			'primaryContact'
		));
	}

	/**
	 * Save author
	 * @see Form::execute()
	 * @see Form::execute()
	 */
	function execute() {
		$authorDao =& DAORegistry::getDAO('AuthorDAO');
		$monograph = $this->getMonograph();

		$author =& $this->getAuthor();
		if (!$author) {
			// this is a new submission contributor
			$author = new Author();
			$author->setSubmissionId($monograph->getId());
			$existingAuthor = false;
		} else {
			$existingAuthor = true;
			if ($monograph->getId() !== $author->getSubmissionId()) fatalError('Invalid author!');
		}

		$author->setFirstName($this->getData('firstName'));
		$author->setMiddleName($this->getData('middleName'));
		$author->setLastName($this->getData('lastName'));
		$author->setSuffix($this->getData('suffix'));
		$author->setAffiliation($this->getData('affiliation'), AppLocale::getLocale()); // localized
		$author->setCountry($this->getData('country'));
		$author->setEmail($this->getData('email'));
		$author->setUrl($this->getData('url'));
		$author->setUserGroupId($this->getData('userGroupId'));
		$author->setBiography($this->getData('biography'), AppLocale::getLocale()); // localized
		$author->setPrimaryContact(($this->getData('primaryContact') ? true : false));

		if ($existingAuthor) {
			$authorDao->updateAuthor($author);
			$authorId = $author->getId();
		} else {
			$authorId = $authorDao->insertAuthor($author);
		}

		return $authorId;
	}
}

?>
