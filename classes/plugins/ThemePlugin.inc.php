<?php

/**
 * @file classes/plugins/ThemePlugin.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ThemePlugin
 * @ingroup plugins
 *
 * @brief Abstract class for theme plugins
 */



import('classes.plugins.Plugin');

class ThemePlugin extends Plugin {
	function ThemePlugin() {
		parent::Plugin();
	}

	/**
	 * Get the name of this plugin. The name must be unique within
	 * its category.
	 * @return String name of plugin
	 */
	function getName() {
		assert(false); // Should always be overridden
	}

	/**
	 * Get the display name of this plugin.
	 * @return String
	 */
	function getDisplayName() {
		assert(false); // Should always be overridden
	}

	/**
	 * Get a description of the plugin.
	 */
	function getDescription() {
		assert(false); // Should always be overridden
	}

	/**
	 * Activate the theme.
	 */
	function activate(&$templateMgr) {
		// Subclasses may override this function.

		if (($stylesheetFilename = $this->getStylesheetFilename()) != null) {
			$path = Request::getBaseUrl() . '/' . $this->getPluginPath() . '/' . $stylesheetFilename;
			$templateMgr->addStyleSheet($path);
		}
	}
}

?>
