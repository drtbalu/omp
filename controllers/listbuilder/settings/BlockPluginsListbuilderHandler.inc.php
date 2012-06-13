<?php

/**
 * @file controllers/listbuilder/settings/BlockPluginsListbuilderHandler.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class BlockPluginsListbuilderHandler
 * @ingroup controllers_listbuilder_settings
 *
 * @brief Class for block plugins administration.
 */

import('lib.pkp.classes.controllers.listbuilder.MultipleListsListbuilderHandler');

class BlockPluginsListbuilderHandler extends MultipleListsListbuilderHandler {
	/**
	 * Constructor
	 */
	function BlockPluginsListbuilderHandler() {
		parent::MultipleListsListbuilderHandler();
		$this->addRoleAssignment(
			ROLE_ID_PRESS_MANAGER,
			array('fetch')
		);
	}

	/**
	 * @see GridHandler::authorize($request, $args, $roleAssignments)
	 */
	function authorize(&$request, $args, $roleAssignments) {
		import('classes.security.authorization.OmpPressAccessPolicy');
		$this->addPolicy(new OmpPressAccessPolicy($request, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * @see ListbuilderHandler::initialize()
	 */
	function initialize(&$request) {
		parent::initialize($request);
		AppLocale::requireComponents(LOCALE_COMPONENT_PKP_MANAGER);

		// Basic configuration
		$this->setTitle('manager.setup.layout.blockManagement');
		$this->setSaveFieldName('blocks');

		// Name column
		$nameColumn = new ListbuilderGridColumn($this, 'name', 'common.name');

		// Add lists.
		$this->addList(new ListbuilderList('leftContext', 'manager.setup.layout.leftSidebar'));
		$this->addList(new ListbuilderList('unselected', 'manager.setup.layout.unselected'));
		$this->addList(new ListbuilderList('rightContext', 'manager.setup.layout.rightSidebar'));

		import('controllers.listbuilder.settings.BlockPluginsListbuilderGridCellProvider');
		$nameColumn->setCellProvider(new BlockPluginsListbuilderGridCellProvider());
		$this->addColumn($nameColumn);
	}


	//
	// Overridden template methods
	//
	/**
	 * @see MultipleListsListbuilderHandler::setListsData()
	 */
	function setListsData($request, $filter) {
		$leftBlockPlugins = $disabledBlockPlugins = $rightBlockPlugins = array();
		$plugins =& PluginRegistry::loadCategory('blocks');
		foreach ($plugins as $key => $junk) {
			if (!$plugins[$key]->getEnabled() || $plugins[$key]->getBlockContext() == '') {
				if (count(array_intersect($plugins[$key]->getSupportedContexts(), array(BLOCK_CONTEXT_LEFT_SIDEBAR, BLOCK_CONTEXT_RIGHT_SIDEBAR))) > 0) $disabledBlockPlugins[$key] =& $plugins[$key];
			} else switch ($plugins[$key]->getBlockContext()) {
				case BLOCK_CONTEXT_LEFT_SIDEBAR:
					$leftBlockPlugins[$key] =& $plugins[$key];
					break;
				case BLOCK_CONTEXT_RIGHT_SIDEBAR:
					$rightBlockPlugins[$key] =& $plugins[$key];
					break;
			}
		}

		$lists =& $this->getLists();
		$lists['leftContext']->setData($leftBlockPlugins);
		$lists['rightContext']->setData($rightBlockPlugins);
		$lists['unselected']->setData($disabledBlockPlugins);
	}
}

?>
