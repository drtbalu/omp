<?php

/**
 * @file controllers/tab/settings/ManagerSettingsTabHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ManagerSettingsTabHandler
 * @ingroup controllers_tab_settings
 *
 * @brief Handle AJAX operations for tabs on press manangement settings pages.
 * Implements the wizard mode, to let tabs show basic or advanced settings.
 */

// Import the base Handler.
import('controllers.tab.settings.SettingsTabHandler');

class ManagerSettingsTabHandler extends SettingsTabHandler {

	/** @var boolean */
	var $_wizardMode;

	/**
	 * Constructor
	 */
	function ManagerSettingsTabHandler() {
		$role = array(ROLE_ID_PRESS_MANAGER);
		parent::SettingsTabHandler($role);
	}


	//
	// Getters and Setters
	//
	/**
	 * Get if the current tab is in wizard mode.
	 * @return boolean
	 */
	function getWizardMode() {
		return $this->_wizardMode;
	}

	/**
	 * Set if the current tab is in wizard mode.
	 * @param $wizardMode boolean
	 */
	function setWizardMode($wizardMode) {
		$this->_wizardMode = (boolean)$wizardMode;
	}


	//
	// Extended methods from SettingsTabHandler
	//
	/**
	 * @see SettingsTabHandler::initialize()
	 */
	function initialize(&$request, $args = null) {
		$this->setWizardMode($request->getUserVar('wizardMode'));

		parent::initialize($request, $args);

		// Load handler specific translations.
		AppLocale::requireComponents(LOCALE_COMPONENT_PKP_MANAGER, LOCALE_COMPONENT_OMP_MANAGER);
	}
}

?>
