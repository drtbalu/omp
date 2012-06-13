<?php
/**
 * @devgroup controllers_wizard_fileUpload_form
 */

/**
 * @file controllers/wizard/fileUpload/form/SubmissionFilesArtworkMetadataForm.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionFilesArtworkMetadataForm
 * @ingroup controllers_wizard_fileUpload_form
 *
 * @brief Form for editing artwork file metadata.
 */

import('controllers.wizard.fileUpload.form.SubmissionFilesMetadataForm');

class SubmissionFilesArtworkMetadataForm extends SubmissionFilesMetadataForm {
	/**
	 * Constructor.
	 * @param $submissionFile SubmissionFile
	 * @param $stageId integer One of the WORKFLOW_STAGE_ID_* constants.
	 * @param $reviewRound ReviewRound (optional) Current review round, if any.
	 */
	function SubmissionFilesArtworkMetadataForm(&$submissionFile, $stageId, $reviewRound = null) {
		parent::SubmissionFilesMetadataForm(&$submissionFile, $stageId, $reviewRound);
	}


	//
	// Implement template methods from Form
	//
	/**
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array(
			'artworkCaption', 'artworkCredit', 'artworkCopyrightOwner',
			'artworkCopyrightOwnerContact', 'artworkPermissionTerms'
		));
		parent::readInputData();
	}

	/**
	 * @see Form::execute()
	 */
	function execute($args, $request) {
		//
		// FIXME: Should caption, credit, or any other fields be
		// localized?
		// FIXME: How to upload a permissions file?
		// FIXME: How to select a contact author from the submission
		// author list?
		//

		// Update the sumbission file by reference.
		$submissionFile =& $this->getSubmissionFile();
		$submissionFile->setCaption($this->getData('artworkCaption'));
		$submissionFile->setCredit($this->getData('artworkCredit'));
		$submissionFile->setCopyrightOwner($this->getData('artworkCopyrightOwner'));
		$submissionFile->setCopyrightOwnerContactDetails($this->getData('artworkCopyrightOwnerContact'));
		$submissionFile->setPermissionTerms($this->getData('artworkPermissionTerms'));

		// Persist the submission file.
		parent::execute($args, $request);
	}
}

?>
