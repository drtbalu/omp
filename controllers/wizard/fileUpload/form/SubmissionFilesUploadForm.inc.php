<?php

/**
 * @file controllers/wizard/fileUpload/form/SubmissionFilesUploadForm.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionFilesUploadForm
 * @ingroup controllers_wizard_fileUpload_form
 *
 * @brief Form for adding/editing a submission file
 */


import('controllers.wizard.fileUpload.form.SubmissionFilesUploadBaseForm');

class SubmissionFilesUploadForm extends SubmissionFilesUploadBaseForm {

	/** @var array */
	var $_uploaderRoles;


	/**
	 * Constructor.
	 * @param $request Request
	 * @param $monographId integer
	 * @param $uploaderRoles array
	 * @param $stageId integer One of the WORKFLOW_STAGE_ID_* constants.
	 * @param $fileStage integer
	 * @param $revisionOnly boolean
	 * @param $stageId integer
	 * @param $reviewRound ReviewRound
	 * @param $revisedFileId integer
	 */
	function SubmissionFilesUploadForm(&$request, $monographId, $stageId, $uploaderRoles, $fileStage,
			$revisionOnly = false, $reviewRound = null, $revisedFileId = null, $assocType = null, $assocId = null) {

		// Initialize class.
		assert(is_null($uploaderRoles) || (is_array($uploaderRoles) && count($uploaderRoles) >= 1));
		$this->_uploaderRoles = $uploaderRoles;

		parent::SubmissionFilesUploadBaseForm(
			$request, 'controllers/wizard/fileUpload/form/fileUploadForm.tpl',
			$monographId, $stageId, $fileStage, $revisionOnly, $reviewRound, $revisedFileId, $assocType, $assocId
		);
	}


	//
	// Getters and Setters
	//
	/**
	 * Get the uploader roles.
	 * @return array
	 */
	function getUploaderRoles() {
		assert(!is_null($this->_uploaderRoles));
		return $this->_uploaderRoles;
	}


	//
	// Implement template methods from Form
	//
	/**
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('genreId', 'uploaderUserGroupId'));
		return parent::readInputData();
	}

	/**
	 * @see Form::validate()
	 */
	function validate(&$request) {
		// Is this a revision?
		$revisedFileId = $this->getRevisedFileId();
		if ($this->getData('revisionOnly')) {
			assert($revisedFileId > 0);
		}

		// Retrieve the request context.
		$router =& $request->getRouter();
		$context =& $router->getContext($request);

		if (!$revisedFileId) {
			// Add an additional check for the genre to the form.
			$this->addCheck(
				new FormValidatorCustom(
					$this, 'genreId', FORM_VALIDATOR_REQUIRED_VALUE,
					'submission.upload.noGenre',
					create_function(
						'$genreId,$genreDao,$context',
						'return is_a($genreDao->getById($genreId, $context->getId()), "Genre");'
					),
					array(DAORegistry::getDAO('GenreDAO'), $context)
				)
			);
		}

		// Validate the uploader's user group.
		$uploaderUserGroupId = $this->getData('uploaderUserGroupId');
		if ($uploaderUserGroupId) {
			$user =& $request->getUser();
			$this->addCheck(
				new FormValidatorCustom(
					$this, 'uploaderUserGroupId', FORM_VALIDATOR_REQUIRED_VALUE,
					'submission.upload.invalidUserGroup',
					create_function(
						'$userGroupId,$userGroupDao,$userId',
						'return $userGroupDao->userInGroup($userId, $userGroupId);'
					),
					array(DAORegistry::getDAO('UserGroupDAO'), $user->getId(), $context)
				)
			);
		}

		return parent::validate();
	}

	/**
	 * @see Form::fetch()
	 */
	function fetch($request) {
		// Retrieve available monograph file genres.
		$genreList =& $this->_retrieveGenreList($request);
		$this->setData('monographFileGenres', $genreList);

		// Retrieve the current context.
		$router =& $request->getRouter();
		$context =& $router->getContext($request);
		assert(is_a($context, 'Press'));

		// Retrieve the user's user groups.
		$user =& $request->getUser();
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO'); /* @var $userGroupDao UserGroupDAO */
		$assignedUserGroups =& $userGroupDao->getByUserId($user->getId(), $context->getId());

		// Check which of these groups make sense in the context
		// from which the uploader was instantiated.
		// FIXME: The series editor role may only be displayed if the
		// user is assigned to the current submission as a series
		// editor, see #6000.
		$uploaderRoles = $this->getUploaderRoles();
		$uploaderUserGroups = array();
		$highestAuthorityUserGroupId = null;
		$highestAuthorityRoleId = null;
		while($userGroup =& $assignedUserGroups->next()) { /* @var $userGroup UserGroup */
			// Add all user groups that belong to any of the uploader roles.
			if (in_array($userGroup->getRoleId(), $uploaderRoles)) {
				$uploaderUserGroups[$userGroup->getId()] = $userGroup->getLocalizedName();

				// Identify the first of the user groups that belongs
				// to the role with the lowest role id (=highest authority
				// level). We'll need this information to identify the default
				// selection, see below.
				if (is_null($highestAuthorityUserGroupId) || $userGroup->getRoleId() <= $highestAuthorityRoleId) {
					$highestAuthorityRoleId = $userGroup->getRoleId();
					if (is_null($highestAuthorityUserGroupId) || $userGroup->getId() < $highestAuthorityUserGroupId) {
						$highestAuthorityUserGroupId = $userGroup->getId();
					}
				}
			}

			unset($userGroup);
		}
		if (empty($uploaderUserGroups)) fatalError('Invalid uploader roles!');
		$this->setData('uploaderUserGroups', $uploaderUserGroups);

		// Identify the default user group (only required when there is
		// more than one group).
		$defaultUserGroupId = null;
		if (count($uploaderUserGroups) > 1) {
			// See whether the current user has been assigned as
			// a workflow stage participant.
			$stageAssignmentDao = & DAORegistry::getDAO('StageAssignmentDAO'); /* @var $stageAssignmentDao StageAssignmentDAO */
			$stageAssignments = $stageAssignmentDao->getBySubmissionAndStageId(
				$this->getData('monographId'),
				$this->getStageId(),
				null,
				$user->getId()
			);

			while ($stageAssignment =& $stageAssignments->next()) { /* @var $stageSignoff Signoff */
				if (isset($uploaderUserGroups[$stageAssignment->getUserGroupId()])) {
					$defaultUserGroupId = $stageAssignment->getUserGroupId();
					break;
				}
			}

			// If we didn't find a corresponding stage signoff then
			// use the user group with the highest authority as default.
			if (is_null($defaultUserGroupId)) $defaultUserGroupId = $highestAuthorityUserGroupId;
		}
		$this->setData('defaultUserGroupId', $defaultUserGroupId);

		$settingsDao =& DAORegistry::getDAO('PressSettingsDAO');
		$this->setData('pressSettings', $settingsDao->getPressSettings($context->getId()));

		// Include a status message for this installation's max file upload size.
		$this->setData('maxFileUploadSize', get_cfg_var('upload_max_filesize'));

		return parent::fetch($request);
	}

	/**
	 * @see Form::execute()
	 * @param $request Request
	 * @return MonographFile if successful, otherwise null
	 */
	function &execute($request) {
		// Identify the file genre.
		$revisedFileId = $this->getRevisedFileId();
		if ($revisedFileId) {
			// The file genre will be copied over from the revised file.
			$fileGenre = null;
		} else {
			// This is a new file so we need the file genre from the form.
			$fileGenre = $this->getData('genreId') ? (int)$this->getData('genreId') : null;
		}

		// Retrieve the uploader's user group.
		$uploaderUserGroupId = $this->getData('uploaderUserGroupId');
		if (!$uploaderUserGroupId) fatalError('Invalid uploader user group!');

		// Identify the uploading user.
		$user =& $request->getUser();
		assert(is_a($user, 'User'));

		$assocType = $this->getData('assocType') ? (int) $this->getData('assocType') : null;
		$assocId = $this->getData('assocId') ? (int) $this->getData('assocId') : null;

		// Upload the file.
		$press =& $request->getPress();
		import('classes.file.MonographFileManager');
		$monographFileManager = new MonographFileManager($press->getId(), $this->getData('monographId'));
		$fileStage = $this->getData('fileStage');
		$monographFile = $monographFileManager->uploadMonographFile(
			'uploadedFile', $fileStage,
			$user->getId(), $uploaderUserGroupId, $revisedFileId, $fileGenre, $assocType, $assocId
		);

		if ($monographFile && ($fileStage == MONOGRAPH_FILE_REVIEW_FILE || $fileStage == MONOGRAPH_FILE_REVIEW_ATTACHMENT || $fileStage == MONOGRAPH_FILE_REVIEW_REVISION)) {
			// Add the uploaded review file to the review round.
			$reviewRound =& $this->getReviewRound();
			$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO');
			$submissionFileDao->assignRevisionToReviewRound($monographFile->getFileId(), $monographFile->getRevision(), $reviewRound);
		}

		return $monographFile;
	}


	//
	// Private helper methods
	//
	/**
	 * Retrieve the genre list.
	 * @param $request Request
	 * @return array
	 */
	function &_retrieveGenreList(&$request) {
		$context =& $request->getContext();
		$genreDao =& DAORegistry::getDAO('GenreDAO'); /* @var $genreDao GenreDAO */
		$genres =& $genreDao->getEnabledByPressId($context->getId());

		// Transform the genres into an array and
		// assign them to the form.
		$genreList = array();
		while($genre =& $genres->next()){
			$genreId = $genre->getId();
			$genreList[$genreId] = $genre->getLocalizedName();
			unset($genre);
		}
		return $genreList;
	}
}

?>
