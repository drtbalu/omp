<?php
/**
 * @file classes/security/authorization/internal/PluginRequiredPolicy.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PluginRequiredPolicy
 * @ingroup security_authorization_internal
 *
 * @brief Class to make sure we have a valid plugin in request.
 *
 */

import('lib.pkp.classes.security.authorization.AuthorizationPolicy');

class PluginRequiredPolicy extends AuthorizationPolicy {

	/** @var Request */
	var $_request;

	/**
	 * Constructor
	 * @param $request PKPRequest
	 */
	function PluginRequiredPolicy(&$request) {
		parent::AuthorizationPolicy();
		$this->_request =& $request;
	}

	//
	// Implement template methods from AuthorizationPolicy
	//
	/**
	 * @see AuthorizationPolicy::effect()
	 */
	function effect() {
		// Get the plugin request data.
		$category = $this->_request->getUserVar('category');
		$pluginName = $this->_request->getUserVar('plugin');

		// Load the plugin.
		$plugins =& PluginRegistry::loadCategory($category);
		$foundPlugin = null;
		foreach ($plugins as $plugin) { /* @var $plugin Plugin */
			if ($plugin->getName() == $pluginName) {
				$foundPlugin = $plugin;
				break;
			}
		}
		if (!is_a($foundPlugin, 'Plugin')) return AUTHORIZATION_DENY;

		// Add the plugin to the authorized context.
		$this->addAuthorizedContextObject(ASSOC_TYPE_PLUGIN, $foundPlugin);
		return AUTHORIZATION_PERMIT;
	}
}

?>
