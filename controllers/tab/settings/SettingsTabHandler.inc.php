<?php

/**
 * @file controllers/tab/settings/SettingsTabHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SettingsTabHandler
 * @ingroup controllers_tab_settings
 *
 * @brief Handle AJAX operations for tabs on settings pages, under administration or management pages.
 */

// Import the base Handler.
import('classes.handler.Handler');
import('lib.pkp.classes.core.JSONMessage');

class SettingsTabHandler extends Handler {

	/** @var string */
	var $_currentTab;

	/** @var array */
	var $_pageTabs;


	/**
	 * Constructor
	 * @param $role string The role keys to be used in role assignment.
	 */
	function SettingsTabHandler($role) {
		parent::Handler();
		$this->addRoleAssignment(
			$role,
			array('saveFormData', 'showTab')
		);
	}


	//
	// Getters and Setters
	//
	/**
	 * Get the current tab name.
	 * @return string
	 */
	function getCurrentTab() {
		return $this->_currentTab;
	}

	/**
	 * Set the current tab name.
	 * @param $currentTab string
	 */
	function setCurrentTab($currentTab) {
		$this->_currentTab = $currentTab;
	}

	/**
	 * Get an array with current page tabs and its respective forms or templates.
	 * @return array
	 */
	function getPageTabs() {
		return $this->_pageTabs;
	}

	/**
	 * Set an array with current page tabs and its respective forms or templates.
	 * @param array
	 */
	function setPageTabs($pageTabs) {
		$this->_pageTabs = $pageTabs;
	}

	//
	// Extended methods from Handler
	//
	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request) {
		$this->setCurrentTab($request->getUserVar('tab'));
	}

	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, $args, $roleAssignments) {
		import('classes.security.authorization.OmpPressAccessPolicy');
		$this->addPolicy(new OmpPressAccessPolicy($request, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}

	//
	// Public handler methods
	//
	/**
	 * Show a tab.
	 */
	function showTab($args, &$request) {
		$this->setupTemplate();
		if ($this->_isValidTab()) {
			if ($this->_isTabTemplate()) {
				$this->setupTemplate(true);
				$templateMgr =& TemplateManager::getManager();
				if ($this->_isManagementHandler()) {
					// Pass to template if we are in wizard mode.
					$templateMgr->assign('wizardMode', $this->getWizardMode());
				}
				return $templateMgr->fetchJson($this->_getTabTemplate());
			} else {
				$tabForm = $this->getTabForm();
				$tabForm->initData($request);
				$json = new JSONMessage(true, $tabForm->fetch($request));
				return $json->getString();
			}
		}
	}

	/**
	 * Handle forms data (save or edit).
	 * @param $request Request
	 */
	function saveFormData($args, &$request) {
		$json = new JSONMessage();

		if ($this->_isValidTab()) {
			$tabForm = $this->getTabForm();

			// Try to save the form data.
			$tabForm->readInputData($request);
			if($tabForm->validate()) {
				$result = $tabForm->execute($request);
				if ($result !== false) {
					$notificationManager = new NotificationManager();
					$user =& $request->getUser();
					$notificationManager->createTrivialNotification($user->getId());
				}
			} else {
				$json->setStatus(false);
			}
		}

		return $json->getString();
	}

	/**
	 * Return an instance of the form based on the current tab.
	 * @return Form
	 */
	function getTabForm() {
		$currentTab = $this->getCurrentTab();
		$pageTabs = $this->getPageTabs();

		// Search for a form using the tab name.
		import($pageTabs[$currentTab]);
		$tabFormClassName = $this->_getFormClassName($pageTabs[$currentTab]);

		if ($this->_isManagementHandler()) {
			$tabForm = new $tabFormClassName($this->getWizardMode());
		} else {
			$tabForm = new $tabFormClassName();
		}

		assert(is_a($tabForm, 'Form'));

		return $tabForm;
	}


	//
	// Private helper methods.
	//
	/**
	 * Return the tab template file
	 * @return string
	 */
	function _getTabTemplate() {
		$currentTab = $this->getCurrentTab();
		$pageTabs = $this->getPageTabs();

		return $pageTabs[$currentTab];
	}

	/**
	 * Check if the current tab value exists in pageTabsAndForms array.
	 * @return boolean
	 */
	function _isValidTab() {
		if (array_key_exists($this->getCurrentTab(), $this->getPageTabs())) {
			return true;
		} else {
			assert(false);
			return false;
		}
	}

	/**
	 * Check if the tab use a template or not.
	 * @return boolean
	 */
	function _isTabTemplate() {
		$currentTab = $this->getCurrentTab();
		$pageTabs = $this->getPageTabs();

		return (strstr($pageTabs[$currentTab], '.tpl'));
	}

	/**
	 * Return the form class name based on the current tab name.
	 * @param $classPath string
	 * @return string
	 */
	function _getFormClassName($classPath) {
		$needle = '.form.';
		$formClassName = strstr($classPath, $needle);
		$formClassName = trim(str_replace($needle, ' ', $formClassName));
		return $formClassName;
	}

	/**
	 * Check if this handles management settings.
	 * @return boolean
	 */
	function _isManagementHandler() {
		return is_subclass_of($this, 'ManagerSettingsTabHandler');
	}
}

?>
