<?php

/**
 * @file classes/controllers/grid/languages/LanguageGridHandler.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class LanguageGridHandler
 * @ingroup classes_controllers_grid_languages
 *
 * @brief Handle language grid requests.
 */

import('lib.pkp.classes.controllers.grid.GridHandler');

import('controllers.grid.languages.LanguageGridRow');
import('controllers.grid.languages.LanguageGridCellProvider');

class LanguageGridHandler extends GridHandler {
	/**
	 * Constructor
	 */
	function LanguageGridHandler() {
		parent::GridHandler();
		$this->addRoleAssignment(array(
			ROLE_ID_PRESS_MANAGER),
			array('saveLanguageSetting', 'setContextPrimaryLocale'));
	}


	//
	// Implement template methods from PKPHandler.
	//
	/**
	 * @see GridHandler::authorize()
	 */
	function authorize($request, $args, $roleAssignments) {
		import('classes.security.authorization.OmpPressAccessPolicy');
		$this->addPolicy(new OmpPressAccessPolicy($request, $roleAssignments));
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
			LOCALE_COMPONENT_OMP_MANAGER
		);

		// Basic grid configuration.
		$this->setTitle('common.languages');
	}


	//
	// Public handler methods.
	//
	/**
	 * Save language management settings.
	 * @param $args array
	 * @param $request Request
	 */
	function saveLanguageSetting($args, &$request) {
		$locale = (string) $request->getUserVar('rowId');
		$settingName = (string) $request->getUserVar('setting');
		$settingValue = (boolean) $request->getUserVar('value');
		$availableLocales = $this->getGridDataElements($request);
		$press =& $request->getPress();

		$permittedSettings = array('supportedFormLocales', 'supportedSubmissionLocales', 'supportedLocales');
		if (in_array($settingName, $permittedSettings) && $locale) {
			$currentSettingValue = $press->getSetting($settingName);
			if (AppLocale::isLocaleValid($locale) && array_key_exists($locale, $availableLocales)) {
				if ($settingValue) {
					array_push($currentSettingValue, $locale);
				} else {
					$key = array_search($locale, $currentSettingValue);
					if ($key !== false) unset($currentSettingValue[$key]);
				}
			}
		}

		$press->updateSetting($settingName, $currentSettingValue);
		$pressDao =& DAORegistry::getDAO('PressDAO');
		$pressDao->updateObject($press);

		$notificationManager = new NotificationManager();
		$user =& $request->getUser();
		$notificationManager->createTrivialNotification(
			$user->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => __('notification.localeSettingsSaved')));

		return DAO::getDataChangedEvent($locale);
	}

	/**
	 * Set context primary locale.
	 * @param $args array
	 * @param $request Request
	 */
	function setContextPrimaryLocale($args, &$request) {
		$locale = (string) $request->getUserVar('rowId');
		$press =& $request->getPress();
		$availableLocales = $this->getGridDataElements($request);

		if (AppLocale::isLocaleValid($locale) && array_key_exists($locale, $availableLocales)) {
			// Make sure at least the primary locale is chosen as available
			foreach (array('supportedLocales', 'supportedSubmissionLocales', 'supportedFormLocales') as $name) {
				$$name = $press->getSetting($name);
				if (!in_array($locale, $$name)) {
					array_push($$name, $locale);
					$press->updateSetting($name, $$name);
				}
			}

			$press->setPrimaryLocale($locale);
			$pressDao =& DAORegistry::getDAO('PressDAO');
			$pressDao->updateObject($press);

			$notificationManager = new NotificationManager();
			$user =& $request->getUser();
			$notificationManager->createTrivialNotification(
				$user->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => __('notification.localeSettingsSaved')));
		}

		return DAO::getDataChangedEvent();
	}

	//
	// Protected methods.
	//
	/**
	 * Return an instance of the cell provider
	 * used by this grid.
	 * @return GridCellProvider
	 */
	function getCellProvider() {
		return new LanguageGridCellProvider();
	}

	/**
	 * Add name column.
	 */
	function addNameColumn() {
		$cellProvider = $this->getCellProvider();

		// Locale name.
		$this->addColumn(
			new GridColumn(
				'locale',
				'grid.columns.locale',
				null,
				'controllers/grid/languages/localeNameCell.tpl',
				$cellProvider
			)
		);
	}

	/**
	 * Add primary column.
	 * @param $columnId string The column id.
	 */
	function addPrimaryColumn($columnId) {
		$cellProvider = $this->getCellProvider();

		$this->addColumn(
			new GridColumn(
				$columnId,
				'locale.primary',
				null,
				'controllers/grid/common/cell/radioButtonCell.tpl',
				$cellProvider
			)
		);
	}

	/**
	 * Add columns related to management settings.
	 */
	function addManagementColumns() {
		$cellProvider = $this->getCellProvider();
		$this->addColumn(
			new GridColumn(
				'uiLocale',
				'manager.language.ui',
				null,
				'controllers/grid/common/cell/selectStatusCell.tpl',
				$cellProvider
			)
		);

		$this->addColumn(
			new GridColumn(
				'submissionLocale',
				'manager.language.submissions',
				null,
				'controllers/grid/common/cell/selectStatusCell.tpl',
				$cellProvider
			)
		);

		$this->addColumn(
			new GridColumn(
				'formLocale',
				'manager.language.forms',
				null,
				'controllers/grid/common/cell/selectStatusCell.tpl',
				$cellProvider
			)
		);
	}

	/**
	 * Add data related to management settings.
	 * @param $request Request
	 * @param $data Array Data already loaded.
	 * @return Array Same passed array, but with
	 * the extra management data inserted.
	 */
	function addManagementData(&$request, $data) {
		$press =& $request->getPress();

		if (is_array($data)) {
			foreach ($data as $locale => $localeData) {
				foreach (array('supportedFormLocales', 'supportedSubmissionLocales', 'supportedLocales') as $name) {
					$data[$locale][$name] = in_array($locale, (array) $press->getSetting($name));
				}
			}
		} else {
			assert(false);
		}

		return $data;
	}
}
?>
