<?php

/**
 * @file controllers/grid/catalogEntry/SalesRightsGridHandler.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SalesRightsGridHandler
 * @ingroup controllers_grid_catalogEntry
 *
 * @brief Handle publication format grid requests for sales rights.
 */

// import grid base classes
import('lib.pkp.classes.controllers.grid.GridHandler');


// import format grid specific classes
import('controllers.grid.catalogEntry.SalesRightsGridCellProvider');
import('controllers.grid.catalogEntry.SalesRightsGridRow');

// Link action & modal classes
import('lib.pkp.classes.linkAction.request.AjaxModal');

class SalesRightsGridHandler extends GridHandler {
	/** @var Monograph */
	var $_monograph;

	/** @var PublicationFormat */
	var $_publicationFormat;

	/**
	 * Constructor
	 */
	function SalesRightsGridHandler() {
		parent::GridHandler();
		$this->addRoleAssignment(
				array(ROLE_ID_PRESS_MANAGER),
				array('fetchGrid', 'fetchRow', 'addRights', 'editRights',
				'updateRights', 'deleteRights'));
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

	/**
	 * Get the publication format assocated with these sales rights
	 * @return PublicationFormat
	 */
	function &getPublicationFormat() {
		return $this->_publicationFormat;
	}

	/**
	 * Set the publication format
	 * @param PublicationFormat
	 */
	function setPublicationFormat($publicationFormat) {
		$this->_publicationFormat =& $publicationFormat;
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
		$publicationFormatDao =& DAORegistry::getDAO('PublicationFormatDAO');
		$publicationFormatId = null;

		// Retrieve the associated publication format for this grid.
		$salesRightsId = (int) $request->getUserVar('salesRightsId'); // set if editing or deleting a sales rights entry

		if ($salesRightsId != '') {
			$salesRightsDao =& DAORegistry::getDAO('SalesRightsDAO');
			$salesRights =& $salesRightsDao->getById($salesRightsId, $this->getMonograph()->getId());
			if ($salesRights) {
				$publicationFormatId =& $salesRights->getPublicationFormatId();
			}
		} else { // empty form for new SalesRights
			$publicationFormatId = (int) $request->getUserVar('publicationFormatId');
		}

		$monograph =& $this->getMonograph();
		$publicationFormat =& $publicationFormatDao->getById($publicationFormatId, $monograph->getId());

		if ($publicationFormat) {
			$this->setPublicationFormat($publicationFormat);
		} else {
			fatalError('The publication format is not assigned to authorized monograph!');
		}

		// Load submission-specific translations
		AppLocale::requireComponents(
			LOCALE_COMPONENT_OMP_SUBMISSION,
			LOCALE_COMPONENT_PKP_SUBMISSION,
			LOCALE_COMPONENT_PKP_USER,
			LOCALE_COMPONENT_OMP_DEFAULT_SETTINGS
		);

		// Basic grid configuration
		$this->setTitle('grid.catalogEntry.salesRights');

		// Grid actions
		$router =& $request->getRouter();
		$actionArgs = $this->getRequestArgs();
		$this->addAction(
			new LinkAction(
				'addRights',
				new AjaxModal(
					$router->url($request, null, null, 'addRights', null, $actionArgs),
					__('grid.action.addItem'),
					'addRights'
				),
				__('grid.action.addItem'),
				'add_item'
			)
		);

		// Columns
		$cellProvider = new SalesRightsGridCellProvider();
		$this->addColumn(
			new GridColumn(
				'type',
				'grid.catalogEntry.salesRightsType',
				null,
				'controllers/grid/gridCell.tpl',
				$cellProvider
			)
		);
		$this->addColumn(
			new GridColumn(
				'ROW',
				'grid.catalogEntry.salesRightsROW',
				null,
				'controllers/grid/common/cell/checkMarkCell.tpl',
				$cellProvider
			)
		);
	}


	//
	// Overridden methods from GridHandler
	//
	/**
	 * @see GridHandler::getRowInstance()
	 * @return SalesRightsGridRow
	 */
	function &getRowInstance() {
		$row = new SalesRightsGridRow($this->getMonograph());
		return $row;
	}

	/**
	 * Get the arguments that will identify the data in the grid
	 * In this case, the monograph.
	 * @return array
	 */
	function getRequestArgs() {
		$monograph =& $this->getMonograph();
		$publicationFormat =& $this->getPublicationFormat();

		return array(
			'monographId' => $monograph->getId(),
			'publicationFormatId' => $publicationFormat->getId()
		);
	}

	/**
	 * @see GridHandler::loadData
	 */
	function &loadData($request, $filter = null) {
		$publicationFormat =& $this->getPublicationFormat();
		$salesRightsDao =& DAORegistry::getDAO('SalesRightsDAO');
		$data =& $salesRightsDao->getByPublicationFormatId($publicationFormat->getId());
		return $data->toArray();
	}


	//
	// Public Sales Rights Grid Actions
	//

	function addRights($args, $request) {
		return $this->editRights($args, $request);
	}

	/**
	 * Edit a sales rights entry
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function editRights($args, &$request) {
		// Identify the sales rights entry to be updated
		$salesRightsId = (int) $request->getUserVar('salesRightsId');
		$monograph =& $this->getMonograph();

		$salesRightsDao =& DAORegistry::getDAO('SalesRightsDAO');
		$salesRights = $salesRightsDao->getById($salesRightsId, $monograph->getId());

		// Form handling
		import('controllers.grid.catalogEntry.form.SalesRightsForm');
		$salesRightsForm = new SalesRightsForm($monograph, $salesRights);
		$salesRightsForm->initData();

		$json = new JSONMessage(true, $salesRightsForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Update a sales rights entry
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function updateRights($args, &$request) {
		// Identify the sales rights entry to be updated
		$salesRightsId = $request->getUserVar('salesRightsId');
		$monograph =& $this->getMonograph();

		$salesRightsDao =& DAORegistry::getDAO('SalesRightsDAO');
		$salesRights = $salesRightsDao->getById($salesRightsId, $monograph->getId());

		// Form handling
		import('controllers.grid.catalogEntry.form.SalesRightsForm');
		$salesRightsForm = new SalesRightsForm($monograph, $salesRights);
		$salesRightsForm->readInputData();
		if ($salesRightsForm->validate()) {
			$salesRightsId = $salesRightsForm->execute();

			if(!isset($salesRights)) {
				// This is a new entry
				$salesRights =& $salesRightsDao->getById($salesRightsId, $monograph->getId());
				// New added entry action notification content.
				$notificationContent = __('notification.addedSalesRights');
			} else {
				// entry edit action notification content.
				$notificationContent = __('notification.editedSalesRights');
			}

			// Create trivial notification.
			$currentUser =& $request->getUser();
			$notificationMgr = new NotificationManager();
			$notificationMgr->createTrivialNotification($currentUser->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => $notificationContent));

			// Prepare the grid row data
			$row =& $this->getRowInstance();
			$row->setGridId($this->getId());
			$row->setId($salesRightsId);
			$row->setData($salesRights);
			$row->initialize($request);

			// Render the row into a JSON response
			return DAO::getDataChangedEvent();

		} else {
			$json = new JSONMessage(true, $salesRightsForm->fetch($request));
			return $json->getString();
		}
	}

	/**
	 * Delete a sales rights entry
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function deleteRights($args, &$request) {

		// Identify the sales rights entry to be deleted
		$salesRightsId = $request->getUserVar('salesRightsId');

		$salesRightsDao =& DAORegistry::getDAO('SalesRightsDAO');
		$salesRights =& $salesRightsDao->getById($salesRightsId, $this->getMonograph()->getId());
		if ($salesRights != null) { // authorized

			$result = $salesRightsDao->deleteObject($salesRights);

			if ($result) {
				$currentUser =& $request->getUser();
				$notificationMgr = new NotificationManager();
				$notificationMgr->createTrivialNotification($currentUser->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => __('notification.removedSalesRights')));
				return DAO::getDataChangedEvent();
			} else {
				$json = new JSONMessage(false, __('manager.setup.errorDeletingItem'));
				return $json->getString();
			}
		}
	}
}

?>
