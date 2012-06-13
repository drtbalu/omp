<?php

/**
 * @file controllers/grid/admin/press/PressGridHandler.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PressGridHandler
 * @ingroup controllers_grid_admin_press
 *
 * @brief Handle press grid requests.
 */

import('lib.pkp.classes.controllers.grid.GridHandler');

import('controllers.grid.admin.press.PressGridRow');
import('controllers.grid.admin.press.form.PressSiteSettingsForm');

class PressGridHandler extends GridHandler {
	/**
	 * Constructor
	 */
	function PressGridHandler() {
		parent::GridHandler();
		$this->addRoleAssignment(array(
			ROLE_ID_SITE_ADMIN),
			array('fetchGrid', 'fetchRow', 'createPress', 'editPress', 'updatePress',
				'deletePress', 'saveSequence')
		);
	}


	//
	// Implement template methods from PKPHandler.
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, $args, $roleAssignments) {
		import('lib.pkp.classes.security.authorization.PolicySet');
		$rolePolicy = new PolicySet(COMBINING_PERMIT_OVERRIDES);

		import('lib.pkp.classes.security.authorization.RoleBasedHandlerOperationPolicy');
		foreach($roleAssignments as $role => $operations) {
			$rolePolicy->addPolicy(new RoleBasedHandlerOperationPolicy($request, $role, $operations));
		}
		$this->addPolicy($rolePolicy);

		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// Load user-related translations.
		AppLocale::requireComponents(
			LOCALE_COMPONENT_PKP_USER,
			LOCALE_COMPONENT_OMP_ADMIN,
			LOCALE_COMPONENT_OMP_MANAGER
		);

		// Basic grid configuration.
		$this->setTitle('press.presses');

		// Grid actions.
		$router =& $request->getRouter();

		import('lib.pkp.classes.linkAction.request.AjaxModal');
		$this->addAction(
			new LinkAction(
				'createPress',
				new AjaxModal(
					$router->url($request, null, null, 'createPress', null, null),
					__('admin.presses.addPress'),
					'addPress',
					true
					),
				__('admin.presses.addPress'),
				'add')
		);

		//
		// Grid columns.
		//
		import('controllers.grid.admin.press.PressGridCellProvider');
		$pressGridCellProvider = new PressGridCellProvider();

		// Press name.
		$this->addColumn(
			new GridColumn(
				'name',
				'manager.setup.pressName',
				null,
				'controllers/grid/gridCell.tpl',
				$pressGridCellProvider
			)
		);

		// Press path.
		$this->addColumn(
			new GridColumn(
				'path',
				'press.path',
				null,
				'controllers/grid/gridCell.tpl',
				$pressGridCellProvider
			)
		);
	}


	//
	// Implement methods from GridHandler.
	//
	/**
	 * @see GridHandler::getRowInstance()
	 * @return UserGridRow
	 */
	function &getRowInstance() {
		$row = new PressGridRow();
		return $row;
	}

	/**
	 * @see GridHandler::loadData()
	 * @param $request PKPRequest
	 * @return array Grid data.
	 */
	function loadData(&$request) {
		// Get all presses.
		$pressDao =& DAORegistry::getDAO('PressDAO');
		$presses =& $pressDao->getPresses();

		return $presses->toAssociativeArray('pressId');
	}

	/**
	 * @see lib/pkp/classes/controllers/grid/GridHandler::getRowDataElementSequence()
	 */
	function getRowDataElementSequence(&$press) {
		return $press->getSequence();
	}

	/**
	 * @see lib/pkp/classes/controllers/grid/GridHandler::saveRowDataElementSequence()
	 */
	function saveRowDataElementSequence(&$request, $rowId, &$press, $newSequence) {
		$pressDao = DAORegistry::getDAO('PressDAO'); /* @var $pressDao PressDAO */
		$press->setSequence($newSequence);
		$pressDao->updateObject($press);
	}

	/**
	 * @see GridHandler::addFeatures()
	 */
	function initFeatures($request, $args) {
		import('lib.pkp.classes.controllers.grid.feature.OrderGridItemsFeature');
		return array(new OrderGridItemsFeature());
	}


	//
	// Public grid actions.
	//
	/**
	 * Add a new press.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function createPress($args, &$request) {
		// Calling editPress with an empty row id will add a new press.
		return $this->editPress($args, $request);
	}

	/**
	 * Edit an existing press.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function editPress($args, &$request) {

		// Identify the press Id.
		$pressId = $request->getUserVar('rowId');

		// Form handling.
		if (checkPhpVersion('5.0.0')) { // WARNING: This form needs $this in constructor
			$settingsForm = new PressSiteSettingsForm(!isset($pressId) || empty($pressId) ? null : $pressId);
		} else {
			$settingsForm =& new PressSiteSettingsForm(!isset($pressId) || empty($pressId) ? null : $pressId);
		}
		$settingsForm->initData();
		$json = new JSONMessage(true, $settingsForm->fetch($args, $request));

		return $json->getString();
	}

	/**
	 * Update an existing press.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function updatePress($args, &$request) {
		// Identify the press Id.
		$pressId = $request->getUserVar('pressId');

		// Form handling.
		if (checkPhpVersion('5.0.0')) { // WARNING: This form needs $this in constructor
			$settingsForm = new PressSiteSettingsForm($pressId);
		} else {
			$settingsForm =& new PressSiteSettingsForm($pressId);
		}
		$settingsForm->readInputData();

		if ($settingsForm->validate()) {
			PluginRegistry::loadCategory('blocks');

			// The press settings form will return a press path in two cases:
			// 1 - if a new press was created;
			// 2 - if a press path of an existing press was edited.
			$newPressPath = $settingsForm->execute($request);

			// Create the notification.
			$notificationMgr = new NotificationManager();
			$user =& $request->getUser();
			$notificationMgr->createTrivialNotification($user->getId());

			// Check for the two cases above.
			if ($newPressPath) {
				$context = $request->getContext();

				if (is_null($pressId)) {
					// CASE 1: new press created.
					// Create notification related to payment method configuration.
					$pressDao =& DAORegistry::getDAO('PressDAO');
					$newPress =& $pressDao->getByPath($newPressPath);
					$notificationMgr->createNotification($request, null, NOTIFICATION_TYPE_CONFIGURE_PAYMENT_METHOD,
						$newPress->getId(), ASSOC_TYPE_PRESS, $newPress->getId(), NOTIFICATION_LEVEL_NORMAL);

					// redirect and set the parameter to open the press
					// setting wizard modal after redirection.
					return $this->_getRedirectEvent(&$request, $newPressPath, true);
				} else {
					// CASE 2: check if user is in the context of
					// the press being edited.
					if ($context->getId() == $pressId) {
						return $this->_getRedirectEvent(&$request, $newPressPath, false);
					}
				}
			}
			return DAO::getDataChangedEvent($pressId);
		} else {
			$json = new JSONMessage(false);
		}
		return $json->getString();
	}

	/**
	 * Delete a press.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function deletePress($args, &$request) {
		// Identify the current context.
		$context =& $request->getContext();

		// Identify the press Id.
		$pressId = $request->getUserVar('rowId');
		$pressDao =& DAORegistry::getDAO('PressDAO');
		$press =& $pressDao->getById($pressId);

		$json = new JSONMessage();

		if ($pressId) {
			if ($pressDao->deleteById($pressId)) {
				// Add publication formats tombstones for all press published monographs.
				import('classes.publicationFormat.PublicationFormatTombstoneManager');
				$publicationFormatTombstoneMgr = new PublicationFormatTombstoneManager();
				$publicationFormatTombstoneMgr->insertTombstonesByPress($press);

				// Delete press file tree
				// FIXME move this somewhere better.
				import('classes.file.PressFileManager');
				$pressFileManager = new PressFileManager($pressId);
				$pressFileManager->rmtree($pressFileManager->getBasePath());

				import('classes.file.PublicFileManager');
				$publicFileManager = new PublicFileManager();
				$publicFileManager->rmtree($publicFileManager->getPressFilesPath($pressId));

				// If user is deleting the same press where he is...
				if($context->getId() == $pressId) {
					// return a redirect js event to index handler.
					$dispatcher =& $request->getDispatcher();
					$url = $dispatcher->url($request, ROUTE_PAGE, null, 'index');
					return $request->redirectUrlJson($url);
				}

				return DAO::getDataChangedEvent($pressId);
			} else {
				$json->setStatus(false);
			}
		}

		return $json->getString();
	}


	//
	// Private helper methods.
	//
	/**
	 * Return a redirect event.
	 * @param $request Request
	 * @param $newPressPath string
	 * @param $openWizard boolean
	 */
	function _getRedirectEvent(&$request, $newPressPath, $openWizard) {
		$dispatcher =& $request->getDispatcher();

		$url = $dispatcher->url($request, ROUTE_PAGE, $newPressPath, 'admin', 'presses', null, array('openWizard' => $openWizard));
		return $request->redirectUrlJson($url);
	}
}
?>
