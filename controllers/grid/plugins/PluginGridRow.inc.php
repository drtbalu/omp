<?php

/**
 * @file controllers/grid/plugins/PluginGridRow.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PluginGridRow
 * @ingroup controllers_grid_plugins
 *
 * @brief Plugin grid row definition
 */

import('lib.pkp.classes.controllers.grid.GridRow');

class PluginGridRow extends GridRow {

	/** @var Array */
	var $_userRoles;

	/** @var int */
	var $_contextLevel;

	/**
	 * Constructor
	 * @param $userRoles array
	 * @param $contextLevel int CONTEXT_...
	 */
	function PluginGridRow($userRoles, $contextLevel) {
		$this->_userRoles = $userRoles;
		$this->_contextLevel = $contextLevel;

		parent::GridRow();
	}


	//
	// Overridden methods from GridRow
	//
	/**
	 * @see GridRow::initialize()
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// Is this a new row or an existing row?
		$plugin =& $this->getData(); /* @var $plugin Plugin */
		assert(is_a($plugin, 'Plugin'));

		$rowId = $this->getId();

		// Only add row actions if this is an existing row
		if (!is_null($rowId)) {
			$router =& $request->getRouter(); /* @var $router PKPRouter */

			$actionArgs = array(
				'category' => $plugin->getCategory(),
				'plugin' => $plugin->getName());

			if ($this->_canEdit($plugin)) {

				$managementVerbs = $plugin->getManagementVerbs();

				// If plugin has not management verbs, we receive
				// null. Check for it before foreach.
				if (!is_null($managementVerbs)) {
					foreach ($managementVerbs as $verb) {
						list($verbName, $verbLocaleKey) = $verb;

						// Add the verb to action args array.
						$actionArgs['verb'] = $verbName;

						$defaultUrl = $router->url($request, null, null, 'plugin', null, $actionArgs);
						$linkAction = null;
						$actionRequest = null;
						$image = null;

						switch ($verbName) {
							case 'enable':
							case 'disable':
								// Do nothing. User interact with those verbs via enabled grid column.
								break;
							default:
								// Check if verb has a link action defined.
								$verbLinkAction = $plugin->getManagementVerbLinkAction($request, $verb, $defaultUrl);
								if (is_a($verbLinkAction, 'LinkAction')) {
									$linkAction = $verbLinkAction;
								} else {
									// Define a default ajax modal request.
									import('lib.pkp.classes.linkAction.request.AjaxModal');
									$actionRequest = new AjaxModal($defaultUrl, $verbLocaleKey);
								}
								break;
						}

						// Build link action for those verbs who don't define one.
						if (!$linkAction && $actionRequest) {
							$linkAction = new LinkAction(
								$verbName,
								$actionRequest,
								$verbLocaleKey,
								$image
							);
						}

						if ($linkAction) {
							// Insert row link action.
							$this->addAction($linkAction);

							unset($linkAction);
							unset($actionRequest);
						}
					}

					// Remove verb from action args array.
					unset($actionArgs['verb']);
				}
			}

			// Administrative functions.
			if (in_array(ROLE_ID_SITE_ADMIN, $this->_userRoles)) {
				import('lib.pkp.classes.linkAction.request.RemoteActionConfirmationModal');
				$this->addAction(new LinkAction(
						'delete',
						new RemoteActionConfirmationModal(
							__('manager.plugins.deleteConfirm'),
							__('common.delete'),
							$router->url($request, null, null, 'deletePlugin', null, $actionArgs)),
						__('common.delete'),
						'delete'));

				$this->addAction(new LinkAction(
						'upgrade',
						new AjaxModal(
							$router->url($request, null, null, 'upgradePlugin', null, $actionArgs),
							__('manager.plugins.upgrade')),
						__('grid.action.upgrade'),
						null));
			}

			if($this->getActions()) {
				// Set a non-default template that supports row actions
				$this->setTemplate('controllers/grid/gridRowWithActions.tpl');
			}
		}
	}


	//
	// Private helper methods
	//
	/**
	 * Return if user can edit a plugin settings or not.
	 * @param $plugin Plugin
	 * @return boolean
	 */
	function _canEdit(&$plugin) {
		if ($plugin->isSitePlugin()) {
			if (in_array(ROLE_ID_SITE_ADMIN, $this->_userRoles)) {
				return true;
			}
		} elseif ($this->_contextLevel & CONTEXT_PRESS) {
			if (in_array(ROLE_ID_PRESS_MANAGER, $this->_userRoles)) {
				return true;
			}
		}

		return false;
	}
}

?>
