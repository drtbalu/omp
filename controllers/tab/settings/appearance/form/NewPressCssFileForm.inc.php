<?php

/**
 * @file controllers/tab/settings/appearance/form/NewPressCssFileForm.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class NewPressCssFileForm
 * @ingroup controllers_tab_settings_appearance_form
 *
 * @brief Form to upload an css file.
 */

import('controllers.tab.settings.form.SettingsFileUploadForm');

class NewPressCssFileForm extends SettingsFileUploadForm {

	/**
	 * Constructor.
	 * @param $imageSettingName string
	 */
	function NewPressCssFileForm($cssSettingName) {
		parent::SettingsFileUploadForm();
		$this->setFileSettingName($cssSettingName);
	}


	//
	// Extend methods from SettingsFileUploadForm.
	//
	/**
	 * @see SettingsFileUploadForm::fetch()
	 */
	function fetch(&$request) {
		$params = array('fileType' => 'css');
		return parent::fetch($request, $params);
	}


	//
	// Extend methods from Form.
	//
	/**
	 * Save the new image file.
	 * @param $request Request.
	 */
	function execute(&$request) {
		$temporaryFile = $this->fetchTemporaryFile($request);

		import('classes.file.PublicFileManager');
		$publicFileManager = new PublicFileManager();

		if (is_a($temporaryFile, 'TemporaryFile')) {
			$type = $temporaryFile->getFileType();
			if ($type != 'text/plain' && $type != 'text/css') {
				return false;
			}

			$settingName = $this->getFileSettingName();
			$uploadName = $settingName . '.css';
			$press = $request->getPress();
			if($publicFileManager->copyPressFile($press->getId(), $temporaryFile->getFilePath(), $uploadName)) {
				$value = array(
					'name' => $temporaryFile->getOriginalFileName(),
					'uploadName' => $uploadName,
					'dateUploaded' => Core::getCurrentDate()
				);

				$settingsDao =& DAORegistry::getDAO('PressSettingsDAO');
				$settingsDao->updateSetting($press->getId(), $settingName, $value, 'object');

				// Clean up the temporary file
				$this->removeTemporaryFile($request);

				return true;
			}
		}
		return false;
	}
}

?>
