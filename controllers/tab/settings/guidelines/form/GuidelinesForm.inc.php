<?php

/**
 * @file controllers/tab/settings/guidelines/form/GuidelinesForm.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class GuidelinesForm
 * @ingroup controllers_tab_settings_guidelines_form
 *
 * @brief Form to edit press guidelines information.
 */


// Import the base Form.
import('controllers.tab.settings.form.PressSettingsForm');

class GuidelinesForm extends PressSettingsForm {

	/**
	 * Constructor.
	 */
	function GuidelinesForm($wizardMode = false) {
		$settings = array(
			'authorGuidelines' => 'string'
		);

		parent::PressSettingsForm($settings, 'controllers/tab/settings/guidelines/form/guidelinesForm.tpl', $wizardMode);
	}


	//
	// Implement template methods from Form.
	//
	/**
	 * @see Form::getLocaleFieldNames()
	 */
	function getLocaleFieldNames() {
		return array('authorGuidelines');
	}
}

?>