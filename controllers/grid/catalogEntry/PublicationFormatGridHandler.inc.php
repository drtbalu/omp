<?php

/**
 * @file controllers/grid/catalogEntry/PublicationFormatGridHandler.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PublicationFormatGridHandler
 * @ingroup controllers_grid_catalogEntry
 *
 * @brief Handle publication format grid requests.
 */

// import grid base classes
import('lib.pkp.classes.controllers.grid.GridHandler');


// import format grid specific classes
import('controllers.grid.catalogEntry.PublicationFormatGridCellProvider');
import('controllers.grid.catalogEntry.PublicationFormatGridRow');

// Link action & modal classes
import('lib.pkp.classes.linkAction.request.AjaxModal');

class PublicationFormatGridHandler extends GridHandler {
	/** @var Monograph */
	var $_monograph;

	/** @var boolean */
	var $_inCatalogEntryModal;

	/**
	 * Constructor
	 */
	function PublicationFormatGridHandler() {
		parent::GridHandler();
		$this->addRoleAssignment(
			array(ROLE_ID_PRESS_MANAGER),
			array(
				'fetchGrid', 'fetchRow', 'addFormat',
				'editFormat', 'updateFormat', 'deleteFormat'
			)
		);
	}


	//
	// Getters/Setters
	//
	/**
	 * Get the monograph associated with this publication format grid.
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
	 * Get flag indicating if this grid is loaded
	 * inside a catalog entry modal or not.
	 * @return boolean
	 */
	function getInCatalogEntryModal() {
		return $this->_inCatalogEntryModal;
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

		$this->setTitle('monograph.publicationFormats');
		$this->setInstructions('editor.monograph.production.publicationFormatDescription');
		$this->_inCatalogEntryModal = (boolean) $request->getUserVar('inCatalogEntryModal');

		// Retrieve the authorized monograph.
		$this->setMonograph($this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH));

		// Load submission-specific translations
		AppLocale::requireComponents(
			LOCALE_COMPONENT_OMP_SUBMISSION,
			LOCALE_COMPONENT_PKP_SUBMISSION,
			LOCALE_COMPONENT_PKP_USER,
			LOCALE_COMPONENT_OMP_DEFAULT_SETTINGS,
			LOCALE_COMPONENT_OMP_EDITOR
		);

		// Grid actions
		$router =& $request->getRouter();
		$actionArgs = $this->getRequestArgs();
		$this->addAction(
			new LinkAction(
				'addFormat',
				new AjaxModal(
					$router->url($request, null, null, 'addFormat', null, $actionArgs),
					__('grid.action.addItem'),
					'addFormat'
				),
				__('grid.action.addItem'),
				'add_item'
			)
		);

		// Columns
		$monograph =& $this->getMonograph();
		$cellProvider = new PublicationFormatGridCellProvider($monograph->getId(), $this->getInCatalogEntryModal());
		$this->addColumn(
			new GridColumn(
				'title',
				'grid.catalogEntry.publicationFormatTitle',
				null,
				'controllers/grid/gridCell.tpl',
				$cellProvider,
				array('width' => 50, 'alignment' => COLUMN_ALIGNMENT_LEFT)
			)
		);
		$this->addColumn(
			new GridColumn(
				'format',
				'grid.catalogEntry.publicationFormatType',
				null,
				'controllers/grid/gridCell.tpl',
				$cellProvider
			)
		);
		$this->addColumn(
			new GridColumn(
				'proofComplete',
				'grid.catalogEntry.proofComplete',
				null,
				'controllers/grid/common/cell/statusCell.tpl',
				$cellProvider
			)
		);
		$this->addColumn(
			new GridColumn(
				'price',
				'payment.directSales.price',
				null,
				'controllers/grid/common/cell/statusCell.tpl',
				$cellProvider
			)
		);
		$this->addColumn(
			new GridColumn(
				'isAvailable',
				'grid.catalogEntry.isAvailable',
				null,
				'controllers/grid/common/cell/statusCell.tpl',
				$cellProvider
			)
		);
	}


	//
	// Overridden methods from GridHandler
	//
	/**
	 * @see GridHandler::getRowInstance()
	 * @return PublicationFormatGridRow
	 */
	function &getRowInstance() {
		$monograph =& $this->getMonograph();
		$row = new PublicationFormatGridRow($monograph);
		return $row;
	}

	/**
	 * Get the arguments that will identify the data in the grid
	 * In this case, the monograph.
	 * @return array
	 */
	function getRequestArgs() {
		$monograph =& $this->getMonograph();

		return array(
			'monographId' => $monograph->getId(),
			'inCatalogEntryModal' => $this->getInCatalogEntryModal()
		);
	}

	/**
	 * @see GridHandler::loadData
	 */
	function &loadData($request, $filter = null) {
		$monograph =& $this->getMonograph();
		$publicationFormatDao =& DAORegistry::getDAO('PublicationFormatDAO');
		$data =& $publicationFormatDao->getByMonographId($monograph->getId());
		return $data->toAssociativeArray();
	}


	//
	// Public Publication Format Grid Actions
	//

	function addFormat($args, $request) {
		return $this->editFormat($args, $request);
	}

	/**
	 * Edit a format
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function editFormat($args, &$request) {
		// Identify the format to be updated
		$publicationFormatId = (int) $request->getUserVar('publicationFormatId');
		$monograph =& $this->getMonograph();

		$publicationFormatDao =& DAORegistry::getDAO('PublicationFormatDAO');
		$publicationFormat = $publicationFormatDao->getById($publicationFormatId);

		// Form handling
		import('controllers.grid.catalogEntry.form.PublicationFormatForm');
		$publicationFormatForm = new PublicationFormatForm($monograph, $publicationFormat);
		$publicationFormatForm->initData();

		$json = new JSONMessage(true, $publicationFormatForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Edit a format
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function updateFormat($args, &$request) {
		// Identify the format to be updated
		$publicationFormatId = (int) $request->getUserVar('publicationFormatId');
		$monograph =& $this->getMonograph();

		$publicationFormatDao =& DAORegistry::getDAO('PublicationFormatDAO');
		$publicationFormat = $publicationFormatDao->getById($publicationFormatId);

		// Form handling
		import('controllers.grid.catalogEntry.form.PublicationFormatForm');
		$publicationFormatForm = new PublicationFormatForm($monograph, $publicationFormat);
		$publicationFormatForm->readInputData();
		if ($publicationFormatForm->validate()) {
			$publicationFormatId = $publicationFormatForm->execute();

			if(!isset($publicationFormat)) {
				// This is a new format
				$publicationFormat =& $publicationFormatDao->getById($publicationFormatId);
				// New added format action notification content.
				$notificationContent = __('notification.addedPublicationFormat');
			} else {
				// Format edit action notification content.
				$notificationContent = __('notification.editedPublicationFormat');
			}

			// Create trivial notification.
			$currentUser =& $request->getUser();
			$notificationMgr = new NotificationManager();
			$notificationMgr->createTrivialNotification($currentUser->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => $notificationContent));

			// Prepare the grid row data
			$row =& $this->getRowInstance();
			$row->setGridId($this->getId());
			$row->setId($publicationFormatId);
			$row->setData($publicationFormat);
			$row->initialize($request);

			// Render the row into a JSON response
			return DAO::getDataChangedEvent();

		} else {
			$json = new JSONMessage(true, $publicationFormatForm->fetch($request));
			return $json->getString();
		}
	}

	/**
	 * Delete a format
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function deleteFormat($args, &$request) {

		// Identify the publication format to be deleted
		$publicationFormatId = (int) $request->getUserVar('publicationFormatId');

		$publicationFormatDao =& DAORegistry::getDAO('PublicationFormatDAO');
		$publicationFormat =& $publicationFormatDao->getById($publicationFormatId);
		$result = $publicationFormatDao->deleteById($publicationFormatId);

		if ($result) {
			// Create a tombstone for this publication format.
			import('classes.publicationFormat.PublicationFormatTombstoneManager');
			$publicationFormatTombstoneMgr = new PublicationFormatTombstoneManager();
			$press =& $request->getPress();
			$publicationFormatTombstoneMgr->insertTombstoneByPublicationFormat($publicationFormat, $press);

			$currentUser =& $request->getUser();
			$notificationMgr = new NotificationManager();
			$notificationMgr->createTrivialNotification($currentUser->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => __('notification.removedPublicationFormat')));
			return DAO::getDataChangedEvent();
		} else {
			$json = new JSONMessage(false, __('manager.setup.errorDeletingItem'));
			return $json->getString();
		}

	}
}

?>
