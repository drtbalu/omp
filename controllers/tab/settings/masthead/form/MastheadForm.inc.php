<?php

/**
 * @file controllers/tab/settings/masthead/form/MastheadForm.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MastheadForm
 * @ingroup controllers_tab_settings_masthead_form
 *
 * @brief Form to edit press general information settings.
 */

// Import the base Form.
import('controllers.tab.settings.form.PressSettingsForm');

class MastheadForm extends PressSettingsForm {

	/**
	 * Constructor.
	 */
	function MastheadForm($wizardMode = false) {
		$settings = array(
			'name' => 'string',
			'initials' => 'string',
			'description' => 'string',
			'mailingAddress' => 'string',
			'pressEnabled' => 'bool',
			'customAboutItems' => 'object',
			'masthead' => 'string'
		);

		parent::PressSettingsForm($settings, 'controllers/tab/settings/masthead/form/mastheadForm.tpl', $wizardMode);

		$this->addCheck(new FormValidatorLocale($this, 'name', 'required', 'manager.setup.form.pressNameRequired'));
		$this->addCheck(new FormValidatorLocale($this, 'initials', 'required', 'manager.setup.form.pressInitialsRequired'));
	}

	//
	// Implement template methods from Form.
	//
	/**
	 * Get all locale field names
	 */
	function getLocaleFieldNames() {
		return array('name', 'initials', 'description', 'customAboutItems', 'masthead');
	}

	//
	// Overridden methods from PressSettingsForm.
	//
	/**
	 * @see PressSettingsForm::initData.
	 * @param $request Request
	 */
	function initData(&$request) {
		parent::initData($request);

		$press =& $request->getPress();
		$this->setData('enabled', (int)$press->getEnabled());
		if ($this->getData('initials') == null) {
			$initials = array();
			foreach (array_keys($this->supportedLocales) as $locale) {
				$initials[$locale] = $press->getPath();
			}
			$this->setData('initials', $initials);
		}
	}

	/**
	 * @see PressSettingsForm::execute()
	 * @param $request Request
	 */
	function execute(&$request) {
		$press =& $request->getPress();

		if ($press->getEnabled() !== $this->getData('pressEnabled')) {
			$pressDao =& DAORegistry::getDAO('PressDAO');
			$press->setEnabled($this->getData('pressEnabled'));
			$pressDao->updateObject($press);
		}

		parent::execute($request);
	}
}

?>
