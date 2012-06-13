<?php

/**
 * @file controllers/informationCenter/form/NewNoteForm.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class NewNoteForm
 * @ingroup informationCenter_form
 *
 * @brief Form to display and post notes on a file
 */


import('lib.pkp.classes.form.Form');

class NewNoteForm extends Form {
	/**
	 * Constructor.
	 */
	function NewNoteForm() {
		parent::Form('controllers/informationCenter/notes.tpl');

		$this->addCheck(new FormValidatorPost($this));
	}

	/**
	 * Return the assoc type for this note.
	 * @return int
	 */
	function getAssocType() {
		assert(false);
	}

	/**
	 * Return the assoc ID for this note.
	 * @return int
	 */
	function getAssocId() {
		assert(false);
	}

	/**
	 * Fetch the form.
	 * @see Form::fetch()
	 */
	function fetch(&$request) {
		$templateMgr =& TemplateManager::getManager();

		$noteDao =& DAORegistry::getDAO('NoteDAO');
		$notes =& $noteDao->getByAssoc($this->getAssocType(), $this->getAssocId());
		$templateMgr->assign_by_ref('notes', $notes);

		return parent::fetch($request);
	}

	/**
	 * Assign form data to user-submitted data.
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array(
			'newNote'
		));

	}

	/**
	 * Register a new user.
	 * @return userId int
	 * @see Form::execute()
	 */
	function execute(&$request) {
		$user =& $request->getUser();

		$noteDao =& DAORegistry::getDAO('NoteDAO');
		$note = $noteDao->newDataObject();

		$note->setUserId($user->getId());
		$note->setContents($this->getData('newNote'));
		$note->setAssocType($this->getAssocType());
		$note->setAssocId($this->getAssocId());

		return $noteDao->insertObject($note);
	}
}

?>
