<?php

/**
 * @file controllers/tab/settings/AdminSettingsTabHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AdminSettingsTabHandler
 * @ingroup controllers_tab_settings
 *
 * @brief Handle AJAX operations for tabs on administration settings pages.
 */

// Import the base Handler.
import('controllers.tab.settings.SettingsTabHandler');

class AdminSettingsTabHandler extends SettingsTabHandler {

	/**
	 * Constructor
	 */
	function AdminSettingsTabHandler() {
		$role = array(ROLE_ID_SITE_ADMIN);

		$this->addRoleAssignment(ROLE_ID_PRESS_MANAGER,
			array(
				'showFileUploadForm',
				'uploadFile',
				'saveFile',
				'deleteFile',
				'fetchFile'
			)
		);

		parent::SettingsTabHandler($role);
		$pageTabs = array(
			'siteSetup' => 'controllers.tab.settings.siteSetup.form.SiteSetupForm',
			'languages' => 'controllers/tab/admin/languages/languages.tpl',
			'plugins' => 'controllers/tab/admin/plugins/sitePlugins.tpl'
		);
		$this->setPageTabs($pageTabs);
	}


	//
	// Extended methods from SettingsTabHandler
	//
	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request, $args = null) {
		parent::initialize($request, $args);

		// Load grid-specific translations
		AppLocale::requireComponents(
			LOCALE_COMPONENT_PKP_ADMIN,
			LOCALE_COMPONENT_OMP_ADMIN,
			LOCALE_COMPONENT_PKP_MANAGER,
			LOCALE_COMPONENT_OMP_MANAGER
		);
	}


	//
	// Public methods.
	//
	/**
	 * Show the upload image form.
	 * @param $request Request
	 * @param $args array
	 * @return string JSON message
	 */
	function showFileUploadForm($args, &$request) {
		$fileUploadForm =& $this->_getFileUploadForm($request);
		$fileUploadForm->initData($request);

		$json = new JSONMessage(true, $fileUploadForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Upload a new file.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function uploadFile($args, &$request) {
		$fileUploadForm =& $this->_getFileUploadForm($request);
		$json = new JSONMessage();

		$temporaryFileId = $fileUploadForm->uploadFile($request);

		if ($temporaryFileId !== false) {
			$json->setAdditionalAttributes(array(
				'temporaryFileId' => $temporaryFileId
			));
		} else {
			$json->setStatus(false);
			$json->setContent(__('common.uploadFailed'));
		}

		return $json->getString();
	}

	/**
	 * Save an uploaded file.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function saveFile($args, &$request) {
		$fileUploadForm =& $this->_getFileUploadForm($request);
		$fileUploadForm->readInputData();

		if ($fileUploadForm->validate()) {
			if ($fileUploadForm->execute($request)) {
				// Generate a JSON message with an event
				$settingName = $request->getUserVar('fileSettingName');
				return DAO::getDataChangedEvent($settingName);
			}
		}
		$json = new JSONMessage(false, __('common.invalidFileType'));
		return $json->getString();
	}

	/**
	 * Deletes a press image.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function deleteFile($args, &$request) {
		$settingName = $request->getUserVar('fileSettingName');

		$tabForm = $this->getTabForm();
		$tabForm->initData($request);

		if ($tabForm->deleteFile($settingName, $request)) {
			return DAO::getDataChangedEvent($settingName);
		} else {
			return new JSONMessage(false);
		}
	}

	/**
	 * Fetch a file that have been uploaded.
	 *
	 * @param $args array
	 * @param $request Request
	 * @return string
	 */
	function fetchFile($args, &$request) {
		// Get the setting name.
		$settingName = $args['settingName'];

		// Try to fetch the file.
		$tabForm = $this->getTabForm();
		$tabForm->initData($request);

		$renderedElement = $tabForm->renderFileView($settingName, $request);

		$json = new JSONMessage();
		if ($renderedElement == false) {
			$json->setAdditionalAttributes(array('noData' => $settingName));
		} else {
			$json->setElementId($settingName);
			$json->setContent($renderedElement);
		}
		return $json->getString();
	}


	//
	// Private helper methods.
	//
	/**
	 * Returns a file upload form based on the uploaded file type.
	 * @param $request Request
	 * @return Form
	 */
	function &_getFileUploadForm($request) {
		$settingName = $request->getUserVar('fileSettingName');
		$fileType = $request->getUserVar('fileType');

		switch ($fileType) {
			case 'image':
				import('controllers.tab.settings.siteSetup.form.NewSiteImageFileForm');
				$fileUploadForm = new NewSiteImageFileForm($settingName);
				break;
			case 'css':
				import('controllers.tab.settings.siteSetup.form.NewSiteCssFileForm');
				$fileUploadForm = new NewSiteCssFileForm($settingName);
				break;
			default:
				assert(false);
				break;
		}

		return $fileUploadForm;
	}
}

?>
