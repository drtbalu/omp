<?php

/**
 * @file controllers/grid/catalogEntry/RepresentativesGridHandler.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class RepresentativesGridHandler
 * @ingroup controllers_grid_catalogEntry
 *
 * @brief Handle publication format grid requests for representatives.
 */

// import grid base classes
import('lib.pkp.classes.controllers.grid.CategoryGridHandler');


// import format grid specific classes
import('controllers.grid.catalogEntry.RepresentativesGridCellProvider');
import('controllers.grid.catalogEntry.RepresentativesGridCategoryRow');
import('controllers.grid.catalogEntry.RepresentativesGridRow');

// Link action & modal classes
import('lib.pkp.classes.linkAction.request.AjaxModal');

class RepresentativesGridHandler extends CategoryGridHandler {
	/** @var Monograph */
	var $_monograph;

	/**
	 * Constructor
	 */
	function RepresentativesGridHandler() {
		parent::CategoryGridHandler();
		$this->addRoleAssignment(
				array(ROLE_ID_PRESS_MANAGER),
				array('fetchGrid', 'fetchRow', 'addRepresentative', 'editRepresentative',
				'updateRepresentative', 'deleteRepresentative'));
	}


	//
	// Getters/Setters
	//
	/**
	 * Get the monograph associated with this grid.
	 * @return Monograph
	 */
	function &getMonograph() {
		return $this->_monograph;
	}

	/**
	 * Set the Monograph
	 * @param Monograph
	 */
	function setMonograph($monograph) {
		$this->_monograph =& $monograph;
	}


	//
	// Overridden methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 * @param $request PKPRequest
	 * @param $args array
	 * @param $roleAssignments array
	 */
	function authorize(&$request, $args, $roleAssignments) {
		import('classes.security.authorization.OmpSubmissionAccessPolicy');
		$this->addPolicy(new OmpSubmissionAccessPolicy($request, $args, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/*
	 * Configure the grid
	 * @param $request PKPRequest
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// Retrieve the authorized monograph.
		$this->setMonograph($this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH));

		$representativeId = (int) $request->getUserVar('representativeId'); // set if editing or deleting a representative entry

		if ($representativeId != '') {
			$representativeDao =& DAORegistry::getDAO('RepresentativeDAO');
			$representative =& $representativeDao->getById($representativeId, $this->getMonograph()->getId());
			if (!isset($representative)) {
				fatalError('Representative referenced outside of authorized monograph context!');
			}
		}

		// Load submission-specific translations
		AppLocale::requireComponents(
			LOCALE_COMPONENT_OMP_SUBMISSION,
			LOCALE_COMPONENT_PKP_SUBMISSION,
			LOCALE_COMPONENT_PKP_USER,
			LOCALE_COMPONENT_OMP_DEFAULT_SETTINGS
		);

		// Basic grid configuration
		$this->setTitle('grid.catalogEntry.representatives');

		// Grid actions
		$router =& $request->getRouter();
		$actionArgs = $this->getRequestArgs();
		$this->addAction(
			new LinkAction(
				'addRepresentative',
				new AjaxModal(
					$router->url($request, null, null, 'addRepresentative', null, $actionArgs),
					__('grid.action.addItem'),
					'addRepresentative'
				),
				__('grid.action.addItem'),
				'add_item'
			)
		);

		// Columns
		$cellProvider = new RepresentativesGridCellProvider();
		$this->addColumn(
			new GridColumn(
				'name',
				'grid.catalogEntry.representativeName',
				null,
				'controllers/grid/gridCell.tpl',
				$cellProvider
			)
		);
		$this->addColumn(
			new GridColumn(
				'role',
				'grid.catalogEntry.representativeRole',
				null,
				'controllers/grid/gridCell.tpl',
				$cellProvider
			)
		);
	}


	//
	// Overridden methods from GridHandler
	//
	/**
	 * @see GridHandler::getRowInstance()
	 * @return RepresentativesGridRow
	 */
	function &getRowInstance() {
		$row = new RepresentativesGridRow($this->getMonograph());
		return $row;
	}

	/**
	 * @see CategoryGridHandler::getCategoryRowInstance()
	 * @return RepresentativesGridCategoryRow
	 */
	function &getCategoryRowInstance() {
		$row = new RepresentativesGridCategoryRow();
		return $row;
	}

	/**
	 * @see CategoryGridHandler::getCategoryData()
	 */
	function getCategoryData($category) {
		$representativeDao =& DAORegistry::getDAO('RepresentativeDAO');
		$representatives = null;
		if ($category['isSupplier']) {
			$representatives =& $representativeDao->getSuppliersByMonographId($this->getMonograph()->getId());
		} else {
			$representatives =& $representativeDao->getAgentsByMonographId($this->getMonograph()->getId());
		}
		return $representatives->toArray();
	}

	/**
	 * Get the arguments that will identify the data in the grid
	 * In this case, the monograph.
	 * @return array
	 */
	function getRequestArgs() {
		return array(
			'monographId' => $this->getMonograph()->getId()
		);
	}

	/**
	 * @see GridHandler::loadData
	 */
	function loadData($request, $filter = null) {
		// set our labels for the two Representative categories
		$categories = array(
				array('name' => 'grid.catalogEntry.agentsCategory', 'isSupplier' => false),
				array('name' => 'grid.catalogEntry.suppliersCategory', 'isSupplier' => true)
			);

		return $categories;
	}


	//
	// Public Representatives Grid Actions
	//

	function addRepresentative($args, $request) {
		return $this->editRepresentative($args, $request);
	}

	/**
	 * Edit a representative entry
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function editRepresentative($args, &$request) {
		// Identify the representative entry to be updated
		$representativeId = (int) $request->getUserVar('representativeId');
		$monograph =& $this->getMonograph();

		$representativeDao =& DAORegistry::getDAO('RepresentativeDAO');
		$representative = $representativeDao->getById($representativeId, $monograph->getId());

		// Form handling
		import('controllers.grid.catalogEntry.form.RepresentativeForm');
		$representativeForm = new RepresentativeForm($monograph, $representative);
		$representativeForm->initData();

		$json = new JSONMessage(true, $representativeForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Update a representative entry
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function updateRepresentative($args, &$request) {
		// Identify the representative entry to be updated
		$representativeId = $request->getUserVar('representativeId');
		$monograph =& $this->getMonograph();

		$representativeDao =& DAORegistry::getDAO('RepresentativeDAO');
		$representative = $representativeDao->getById($representativeId, $monograph->getId());

		// Form handling
		import('controllers.grid.catalogEntry.form.RepresentativeForm');
		$representativeForm = new RepresentativeForm($monograph, $representative);
		$representativeForm->readInputData();
		if ($representativeForm->validate()) {
			$representativeId = $representativeForm->execute();

			if(!isset($representative)) {
				// This is a new entry
				$representative =& $representativeDao->getById($representativeId, $monograph->getId());
				// New added entry action notification content.
				$notificationContent = __('notification.addedRepresentative');
			} else {
				// entry edit action notification content.
				$notificationContent = __('notification.editedRepresentative');
			}

			// Create trivial notification.
			$currentUser =& $request->getUser();
			$notificationMgr = new NotificationManager();
			$notificationMgr->createTrivialNotification($currentUser->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => $notificationContent));

			// Prepare the grid row data
			$row =& $this->getRowInstance();
			$row->setGridId($this->getId());
			$row->setId($representativeId);
			$row->setData($representative);
			$row->initialize($request);

			// Render the row into a JSON response
			return DAO::getDataChangedEvent();

		} else {
			$json = new JSONMessage(true, $representativeForm->fetch($request));
			return $json->getString();
		}
	}

	/**
	 * Delete a representative entry
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function deleteRepresentative($args, &$request) {

		// Identify the representative entry to be deleted
		$representativeId = $request->getUserVar('representativeId');

		$representativeDao =& DAORegistry::getDAO('RepresentativeDAO');
		$representative =& $representativeDao->getById($representativeId, $this->getMonograph()->getId());
		if ($representative != null) { // authorized

			$result = $representativeDao->deleteObject($representative);

			if ($result) {
				$currentUser =& $request->getUser();
				$notificationMgr = new NotificationManager();
				$notificationMgr->createTrivialNotification($currentUser->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => __('notification.removedRepresentative')));
				return DAO::getDataChangedEvent();
			} else {
				$json = new JSONMessage(false, __('manager.setup.errorDeletingItem'));
				return $json->getString();
			}
		}
	}
}

?>
