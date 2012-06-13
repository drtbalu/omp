<?php

/**
 * @file controllers/grid/files/attachment/ProofFilesGridDataProvider.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ProofFilesGridDataProvider
 * @ingroup controllers_grid_files_attachment
 *
 * @brief Provide the reviewers access to their own review attachments data for grids.
 */


import('controllers.grid.files.SubmissionFilesGridDataProvider');

class ProofFilesGridDataProvider extends SubmissionFilesGridDataProvider {
	/** @var integer */
	var $_publicationFormatId;

	/**
	 * Constructor
	 */
	function ProofFilesGridDataProvider() {
		parent::SubmissionFilesGridDataProvider(MONOGRAPH_FILE_PROOF);
	}


	//
	// Implement template methods from GridDataProvider
	//
	/**
	 * @see GridDataProvider::getAuthorizationPolicy()
	 */
	function getAuthorizationPolicy(&$request, $args, $roleAssignments) {
		// Retrieve the current policy.
		$authorizationPolicy = parent::getAuthorizationPolicy($request, $args, $roleAssignments);

		// Append the publication format policy.
		import('classes.security.authorization.internal.PublicationFormatRequiredPolicy');
		$authorizationPolicy->addPolicy(new PublicationFormatRequiredPolicy($request, $args));

		return $authorizationPolicy;
	}

	/**
	 * @see GridDataProvider::getRequestArgs()
	 */
	function getRequestArgs() {
		return array_merge(parent::getRequestArgs(), array('publicationFormatId', $this->_getPublicationFormatId()));
	}

	/**
	 * @see GridHandler::loadData
	 */
	function &loadData() {
		// Grab the files to display as categories
		$monograph =& $this->getMonograph();
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$monographFiles =& $submissionFileDao->getLatestRevisionsByAssocId(
			ASSOC_TYPE_PUBLICATION_FORMAT,
			$this->_getPublicationFormatId(),
			$monograph->getId(),
			$this->getFileStage()
		);

		return $this->prepareSubmissionFileData($monographFiles);
	}

	//
	// Private helper methods
	//

	/**
	 * Get the publication Format Id id.
	 * @return integer
	 */
	function _getPublicationFormatId() {
		$publicationFormat =& $this->getAuthorizedContextObject(ASSOC_TYPE_PUBLICATION_FORMAT);
		return $publicationFormat->getId();
	}
}

?>
