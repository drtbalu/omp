<?php

/**
 * @file controllers/grid/files/proof/AuthorProofingSignoffFilesCategoryGridDataProvider.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorProofingSignoffFilesCategoryGridDataProvider
 * @ingroup controllers_grid_files_proof
 *
 * @brief Provide access to author signoff proofing files data for category grids.
 */


import('lib.pkp.classes.controllers.grid.CategoryGridDataProvider');

class AuthorProofingSignoffFilesCategoryGridDataProvider extends CategoryGridDataProvider {

	/**
	 * Constructor
	 * @param $dataProviderInitParams array Other parameters to initiate the grid
	 * data provider that this category grid data provider will use to implement
	 * common behaviours and data.
	 */
	function AuthorProofingSignoffFilesCategoryGridDataProvider() {
		import("controllers.grid.files.fileSignoff.AuthorSignoffFilesGridDataProvider");
		$gridDataProvider = new AuthorSignoffFilesGridDataProvider('SIGNOFF_PROOFING', WORKFLOW_STAGE_ID_PRODUCTION);
		$this->setDataProvider($gridDataProvider);
	}

	/**
	 * Set user id on grid data provider.
	 * @param $userId int
	 */
	function setUserId($userId) {
		$dataProvider =& $this->getDataProvider();
		$dataProvider->setUserId($userId);
	}

	/**
	 * @see GridDataProvider::getAuthorizationPolicy()
	 */
	function getAuthorizationPolicy($request, $args, $roleAssignments) {
		$dataProvider =& $this->getDataProvider();
		return $dataProvider->getAuthorizationPolicy($request, $args, $roleAssignments);
	}

	/**
	 * @see GridDataProvider::getRequestArgs()
	 */
	function getRequestArgs() {
		$dataProvider =& $this->getDataProvider();
		$args = $dataProvider->getRequestArgs();
		return $args;
	}

	/**
	 * @see GridDataProvider::loadData()
	 */
	function loadData() {
		$monograph = $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$publicationFormatDao =& DAORegistry::getDAO('PublicationFormatDAO');
		$publicationFormatFactory =& $publicationFormatDao->getByMonographId($monograph->getId());

		return $publicationFormatFactory->toAssociativeArray();
	}

	/**
	 * @see CategoryGridDataProvider::getCategoryData()
	 */
	function &getCategoryData(&$publicationFormat, $filter = null) {
		$dataProvider =& $this->getDataProvider();
		$signoffFiles =& $dataProvider->loadData();

		$categoryData = array();
		foreach ($signoffFiles as $signoffId => $objects) {
			if ($objects['submissionFile']->getAssocType() == ASSOC_TYPE_PUBLICATION_FORMAT &&
			$objects['submissionFile']->getAssocId() == $publicationFormat->getId()) {
				$categoryData[$signoffId] = $objects;
			}
		}

		return $categoryData;
	}


	//
	// Public methods
	//
	/**
	 * @see AuthorSignoffFilesGridDataProvider::getAddFileAction()
	 */
	function &getAddSignoffFile($request) {
		$dataProvider =& $this->getDataProvider();
		return $dataProvider->getAddSignoffFile($request);
	}
}

?>
