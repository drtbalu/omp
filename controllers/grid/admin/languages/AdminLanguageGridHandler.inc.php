<?php

/**
 * @file controllers/grid/admin/languages/AdminLanguageGridHandler.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AdminLanguageGridHandler
 * @ingroup controllers_grid_admin_languages
 *
 * @brief Handle administrative language grid requests. If in single press installation,
 * this grid can also handle language management requests. See _canManage().
 */

import('classes.controllers.grid.languages.LanguageGridHandler');

import('controllers.grid.languages.LanguageGridRow');
import('controllers.grid.languages.form.InstallLanguageForm');

class AdminLanguageGridHandler extends LanguageGridHandler {
	/**
	 * Constructor
	 */
	function AdminLanguageGridHandler() {
		parent::LanguageGridHandler();
		$this->addRoleAssignment(array(
			ROLE_ID_SITE_ADMIN),
			array('fetchGrid', 'fetchRow', 'installLocale', 'saveInstallLocale', 'uninstallLocale',
				'downloadLocale', 'disableLocale', 'enableLocale', 'reloadLocale', 'setPrimaryLocale'));
	}


	//
	// Implement template methods from PKPHandler.
	//
	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request) {
		parent::initialize($request);

		AppLocale::requireComponents(
			LOCALE_COMPONENT_OMP_ADMIN,
			LOCALE_COMPONENT_PKP_ADMIN
		);

		// Grid actions.
		$router =& $request->getRouter();

		import('lib.pkp.classes.linkAction.request.AjaxModal');
		$this->addAction(
			new LinkAction(
				'installLocale',
				new AjaxModal(
					$router->url($request, null, null, 'installLocale', null, null),
					__('admin.languages.installLocale'),
					null,
					true
					),
				__('admin.languages.installLocale'),
				'add')
		);

		$cellProvider = $this->getCellProvider();

		// Columns.
		// Enable locale.
		$this->addColumn(
			new GridColumn(
				'enable',
				'common.enable',
				null,
				'controllers/grid/common/cell/selectStatusCell.tpl',
				$cellProvider,
				array('width' => 10)
			)
		);

		// Locale name.
		$this->addNameColumn();

		// Primary locale.
		if ($this->_canManage($request)) {
			$primaryId = 'pressPrimary';
		} else {
			$primaryId = 'sitePrimary';
		}
		$this->addPrimaryColumn($primaryId);

		if ($this->_canManage($request)) {
			$this->addManagementColumns();
		}

		if ($this->_canManage($request)) {
			$instructions = 'manager.languages.languageInstructions';
		} else {
			$instructions = 'admin.languages.supportedLocalesInstructions';
		}
		$this->setInstructions($instructions);
		$this->setFootNote('admin.locale.maybeIncomplete');
	}


	//
	// Implement methods from GridHandler.
	//
	/**
	 * @see GridHandler::getRowInstance()
	 */
	function &getRowInstance() {
		$row = new LanguageGridRow();
		return $row;
	}

	/**
	 * @see GridHandler::loadData()
	 */
	function loadData(&$request, $filter) {
		$site =& $request->getSite();
		$data = array();

		$allLocales = AppLocale::getAllLocales();
		$installedLocales = $site->getInstalledLocales();
		$supportedLocales = $site->getSupportedLocales();
		$primaryLocale = $site->getPrimaryLocale();

		foreach($installedLocales as $localeKey) {
			$data[$localeKey] = array();
			$data[$localeKey]['name'] = $allLocales[$localeKey];
			$data[$localeKey]['incomplete'] = !AppLocale::isLocaleComplete($localeKey);
			if (in_array($localeKey, $supportedLocales)) {
				$supported = true;
			} else {
				$supported = false;
			}
			$data[$localeKey]['supported'] = $supported;

			if ($this->_canManage($request)) {
				$press =& $request->getPress();
				$primaryLocale = $press->getPrimaryLocale();
			}

			if ($localeKey == $primaryLocale) {
				$primary = true;
			} else {
				$primary = false;
			}
			$data[$localeKey]['primary'] = $primary;
		}

		if ($this->_canManage($request)) {
			$data = $this->addManagementData($request, $data);
		}

		return $data;
	}


	//
	// Public grid actions.
	//
	/**
	 * Open a form to select locales for installation.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function installLocale($args, &$request) {
		// Form handling.
		$installLanguageForm = new InstallLanguageForm();
		$installLanguageForm->initData($request);
		$json = new JSONMessage(true, $installLanguageForm->fetch($request));

		return $json->getString();
	}

	/**
	 * Save the install language form.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function saveInstallLocale($args, &$request) {
		$installLanguageForm = new InstallLanguageForm();
		$installLanguageForm->readInputData($request);

		if ($installLanguageForm->validate($request)) {
			$installLanguageForm->execute($request);
			$this->_updatePressLocaleSettings($request);

			$notificationManager = new NotificationManager();
			$user =& $request->getUser();
			$notificationManager->createTrivialNotification(
				$user->getId(), NOTIFICATION_TYPE_SUCCESS,
				array('contents' => __('notification.localeInstalled'))
			);
		}
		return DAO::getDataChangedEvent();
	}

	/**
	 * Download a locale from the PKP web site.
	 * @param $args array
	 * @param $request object
	 */
	function downloadLocale($args, &$request) {
		$this->setupTemplate($request, true);
		$locale = $request->getUserVar('locale');

		import('classes.i18n.LanguageAction');
		$languageAction = new LanguageAction();

		if (!$languageAction->isDownloadAvailable() || !preg_match('/^[a-z]{2}_[A-Z]{2}$/', $locale)) {
			$request->redirect(null, 'admin', 'settings');
		}

		$notificationManager = new NotificationManager();
		$user =& $request->getUser();
		$json = new JSONMessage(true);

		$errors = array();
		if (!$languageAction->downloadLocale($locale, $errors)) {
			$notificationManager->createTrivialNotification(
				$user->getId(),
				NOTIFICATION_TYPE_ERROR,
				array('contents' => $errors));
			$json->setEvent('refreshForm', $this->_fetchReviewerForm($args, $request));
		} else {
			$notificationManager->createTrivialNotification(
				$user->getId(),
				NOTIFICATION_TYPE_SUCCESS,
				array('contentLocaleKey' => __('admin.languages.localeInstalled'),
					 'params' => array('locale' => $locale)));
		}

		// Refresh form.
		$installLanguageForm = new InstallLanguageForm();
		$installLanguageForm->initData($request);
		$json->setEvent('refreshForm', $installLanguageForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Uninstall a locale.
	 * @param $args array
	 * @param $request Request
	 */
	function uninstallLocale($args, &$request) {
		$site =& $request->getSite();
		$locale = $request->getUserVar('rowId');
		$gridData = $this->getGridDataElements($request);
		$site =& $request->getSite();

		if (array_key_exists($locale, $gridData)) {
			$localeData = $gridData[$locale];
			if (!$localeData['primary']) {
				$installedLocales = $site->getInstalledLocales();

				if (in_array($locale, $installedLocales)) {
					$installedLocales = array_diff($installedLocales, array($locale));
					$site->setInstalledLocales($installedLocales);
					$supportedLocales = $site->getSupportedLocales();
					$supportedLocales = array_diff($supportedLocales, array($locale));
					$site->setSupportedLocales($supportedLocales);
					$siteDao =& DAORegistry::getDAO('SiteDAO');
					$siteDao->updateObject($site);

					$this->_updatePressLocaleSettings($request);
					AppLocale::uninstallLocale($locale);

					$notificationManager = new NotificationManager();
					$user =& $request->getUser();
					$notificationManager->createTrivialNotification(
						$user->getId(), NOTIFICATION_TYPE_SUCCESS,
						array('contents' => __('notification.localeUninstalled', array('locale' => $localeData['name'])))
					);
				}
			}

		}

		return DAO::getDataChangedEvent($locale);
	}

	/**
	 * Enable an existing locale.
	 * @param $args array
	 * @param $request Request
	 */
	function enableLocale($args, &$request) {
		$rowId = $request->getUserVar('rowId');
		$gridData = $this->getGridDataElements($request);

		if (array_key_exists($rowId, $gridData)) {
			$this->_updateLocaleSupportState($request, $rowId, true);

			$notificationManager = new NotificationManager();
			$user =& $request->getUser();
			$notificationManager->createTrivialNotification(
				$user->getId(), NOTIFICATION_TYPE_SUCCESS,
				array('contents' => __('notification.localeEnabled'))
			);
		}

		return DAO::getDataChangedEvent($rowId);
	}

	/**
	 * Disable an existing locale.
	 * @param $args array
	 * @param $request Request
	 */
	function disableLocale($args, &$request) {
		$rowId = $request->getUserVar('rowId');
		$gridData = $this->getGridDataElements($request);
		$notificationManager = new NotificationManager();
		$user =& $request->getUser();

		if (array_key_exists($rowId, $gridData)) {
			// Don't disable primary locales.
			if ($gridData[$rowId]['primary']) {
				$notificationManager->createTrivialNotification(
					$user->getId(), NOTIFICATION_TYPE_ERROR,
					array('contents' => __('admin.languages.cantDisable'))
				);
			} else {
				$locale = $rowId;
				$this->_updateLocaleSupportState($request, $rowId, false);

				$notificationManager->createTrivialNotification(
					$user->getId(), NOTIFICATION_TYPE_SUCCESS,
					array('contents' => __('notification.localeDisabled'))
				);
			}
		}

		return DAO::getDataChangedEvent($rowId);
	}

	/**
	 * Reload locale.
	 * @param $args array
	 * @param $request Request
	 */
	function reloadLocale($args, &$request) {
		$site =& $request->getSite();
		$locale = $request->getUserVar('rowId');

		$gridData = $this->getGridDataElements($request);
		if (array_key_exists($locale, $gridData)) {
			AppLocale::reloadLocale($locale);
			$notificationManager = new NotificationManager();
			$user =& $request->getUser();
			$notificationManager->createTrivialNotification(
				$user->getId(), NOTIFICATION_TYPE_SUCCESS,
				array('contents' => __('notification.localeReloaded', array('locale' => $gridData[$locale]['name'])))
			);
		}

		return DAO::getDataChangedEvent($locale);
	}


	/**
	 * Set primary locale.
	 * @param $args array
	 * @param $request Request
	 */
	function setPrimaryLocale($args, &$request) {
		$rowId = $request->getUserVar('rowId');
		$gridData = $this->getGridDataElements($request);
		$localeData = $gridData[$rowId];
		$notificationManager = new NotificationManager();
		$user =& $request->getUser();
		$site =& $request->getSite();

		if (array_key_exists($rowId, $gridData)) {
			if (AppLocale::isLocaleValid($rowId)) {
				$site->setPrimaryLocale($rowId);
				$siteDao =& DAORegistry::getDAO('SiteDAO');
				$siteDao->updateObject($site);

				$notificationManager->createTrivialNotification(
					$user->getId(), NOTIFICATION_TYPE_SUCCESS,
					array('contents' => __('notification.primaryLocaleDefined', array('locale' => $localeData['name'])))
				);
			}
		}

		// Need to refresh whole grid to remove the check in others
		// primary locale radio buttons.
		return DAO::getDataChangedEvent();
	}


	//
	// Helper methods.
	//
	/**
	 * Update the locale support state (enabled or disabled).
	 * @param $request Request
	 * @param $rowId string The locale row id.
	 * @param $enable boolean Enable locale flag.
	 */
	function _updateLocaleSupportState(&$request, $rowId, $enable) {
		$newSupportedLocales = array();
		$gridData = $this->getGridDataElements($request);

		foreach ($gridData as $locale => $data) {
			if ($data['supported']) {
				array_push($newSupportedLocales, $locale);
			}
		}

		if (AppLocale::isLocaleValid($rowId)) {
			if ($enable) {
				array_push($newSupportedLocales, $rowId);
			} else {
				$key = array_search($rowId, $newSupportedLocales);
				if ($key !== false) unset($newSupportedLocales[$key]);
			}
		}

		$site =& $request->getSite();
		$site->setSupportedLocales($newSupportedLocales);

		$siteDao =& DAORegistry::getDAO('SiteDAO');
		$siteDao->updateObject($site);

		$this->_updatePressLocaleSettings($request);
	}

	/**
	 * Helper function to update locale settings in all
	 * installed presses, based on site locale settings.
	 * @param $request object
	 */
	function _updatePressLocaleSettings(&$request) {
		$site =& $request->getSite();
		$siteSupportedLocales = $site->getSupportedLocales();

		$pressDao =& DAORegistry::getDAO('PressDAO');
		$settingsDao =& DAORegistry::getDAO('PressSettingsDAO');
		$presses =& $pressDao->getPresses();
		$presses =& $presses->toArray();
		foreach ($presses as $press) {
			$primaryLocale = $press->getPrimaryLocale();
			$supportedLocales = $press->getSetting('supportedLocales');

			if (isset($primaryLocale) && !in_array($primaryLocale, $siteSupportedLocales)) {
				$press->setPrimaryLocale($site->getPrimaryLocale());
				$pressDao->updateObject($press);
			}

			if (is_array($supportedLocales)) {
				$supportedLocales = array_intersect($supportedLocales, $siteSupportedLocales);
				$settingsDao->updateSetting($press->getId(), 'supportedLocales', $supportedLocales, 'object');
			}
		}
	}

	/**
	 * This grid can also present management functions
	 * if the conditions above are true.
	 * @param $request Request
	 * @return boolean
	 */
	function _canManage($request) {
		$pressDao =& DAORegistry::getDAO('PressDAO');
		$presses =& $pressDao->getPresses();
		$userRoles = $this->getAuthorizedContextObject(ASSOC_TYPE_USER_ROLES);
		$press =& $request->getPress();
		return ($presses->getCount() == 1 && $press && in_array(ROLE_ID_PRESS_MANAGER, $userRoles));
	}
}
?>
