<?php

/**
 * @file controllers/grid/files/SubmissionFilesGridDataProvider.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionFilesGridDataProvider
 * @ingroup controllers_grid_files
 *
 * @brief Provide access to submission file data for grids.
 */


import('controllers.grid.files.FilesGridDataProvider');

class SubmissionFilesGridDataProvider extends FilesGridDataProvider {

	/** @var integer */
	var $_stageId;

	/** @var integer */
	var $_fileStage;


	/**
	 * Constructor
	 * @param $fileStage integer One of the MONOGRAPH_FILE_* constants.
	 */
	function SubmissionFilesGridDataProvider($fileStage) {
		assert(is_numeric($fileStage) && $fileStage > 0);
		$this->_fileStage = (int)$fileStage;
		parent::FilesGridDataProvider();
	}


	//
	// Getters and setters.
	//
	/**
	 * Set the workflow stage.
	 */
	function setStageId($stageId) {
		$this->_stageId = $stageId;
	}

	/**
	 * Get the workflow stage.
	 * @return integer
	 */
	function getStageId() {
		return $this->_stageId;
	}


	//
	// Implement template methods from GridDataProvider
	//
	/**
	 * @see GridDataProvider::getAuthorizationPolicy()
	 */
	function getAuthorizationPolicy(&$request, $args, $roleAssignments) {
		$this->setUploaderRoles($roleAssignments);

		import('classes.security.authorization.OmpWorkflowStageAccessPolicy');
		$policy = new OmpWorkflowStageAccessPolicy($request, $args, $roleAssignments, 'monographId', $this->getStageId());
		return $policy;
	}

	/**
	 * @see GridDataProvider::getRequestArgs()
	 */
	function getRequestArgs() {
		$monograph =& $this->getMonograph();
		return array(
			'monographId' => $monograph->getId(),
			'stageId' => $this->getStageId(),
			'fileStage' => $this->getFileStage()
		);
	}

	/**
	 * Get the file stage.
	 * @return integer
	 */
	function getFileStage() {
		return $this->_fileStage;
	}

	/**
	 * @see GridDataProvider::loadData()
	 */
	function &loadData($viewableOnly = false) {
		// Retrieve all monograph files for the given file stage.
		$monograph =& $this->getMonograph();
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$monographFiles =& $submissionFileDao->getLatestRevisions($monograph->getId(), $this->getFileStage());
		return $this->prepareSubmissionFileData($monographFiles, $viewableOnly);
	}


	//
	// Overridden public methods from FilesGridDataProvider
	//
	/**
	 * @see FilesGridDataProvider::getAddFileAction()
	 */
	function &getAddFileAction($request) {
		import('controllers.api.file.linkAction.AddFileLinkAction');
		$monograph =& $this->getMonograph();
		$addFileAction = new AddFileLinkAction(
			$request, $monograph->getId(), $this->getStageId(),
			$this->getUploaderRoles(), $this->getFileStage()
		);
		return $addFileAction;
	}
}

?>
