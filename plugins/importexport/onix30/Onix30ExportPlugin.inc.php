<?php

/**
 * @file plugins/importexport/onix30/Onix30ExportPlugin.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Onix30ExportPlugin
 * @ingroup plugins_importexport_onix30
 *
 * @brief ONIX 3.0 XML export plugin for monographs
 */

import('classes.plugins.ImportExportPlugin');
import('lib.pkp.classes.xml.XMLCustomWriter');

class Onix30ExportPlugin extends ImportExportPlugin {
	/**
	 * Called as a plugin is registered to the registry
	 * @param $category String Name of category plugin was registered to
	 * @return boolean True iff plugin initialized successfully; if false,
	 * 	the plugin will not be registered.
	 */
	function register($category, $path) {
		$success = parent::register($category, $path);
		$this->addLocaleData();
		return $success;
	}

	/**
	 * Get the name of this plugin. The name must be unique within
	 * its category.
	 * @return String name of plugin
	 */
	function getName() {
		return 'Onix30ExportPlugin';
	}

	function getDisplayName() {
		return __('plugins.importexport.onix30.displayName');
	}

	function getDescription() {
		return __('plugins.importexport.onix30.description');
	}

	function display(&$args) {
		$templateMgr =& TemplateManager::getManager();
		parent::display($args);

		$press =& Request::getPress();

		switch (array_shift($args)) {
			case 'exportMonograph':

				$publicationFormatId = (int) Request::getUserVar('publicationFormatId');
				$publicationFormatDao =& DAORegistry::getDAO('PublicationFormatDAO');
				$publicationFormat =& $publicationFormatDao->getById($publicationFormatId);
				if ($publicationFormat != null) {
					$monographId = $publicationFormat->getMonographId();
					$publishedMonographDao =& DAORegistry::getDAO('PublishedMonographDAO');

					/* check to make sure the requested Monograph is in this press */
					$monograph =& $publishedMonographDao->getById($monographId, $press->getId());
					if ($monograph != null) {
						$this->exportMonograph($press, $monograph, $publicationFormat);
					}
				}
				break;

			default:
				// Display a list of monographs for export
				$this->setBreadcrumbs();
				AppLocale::requireComponents(LOCALE_COMPONENT_PKP_SUBMISSION);
				$publishedMonographDao =& DAORegistry::getDAO('PublishedMonographDAO');
				$rangeInfo = Handler::getRangeInfo('monographs');
				$monographs = $publishedMonographDao->getByPressId($press->getId())->toArray();

				import('lib.pkp.classes.core.VirtualArrayIterator');
				$iterator = new VirtualArrayIterator($monographs, count($monographs), $rangeInfo->getPage(), $rangeInfo->getCount());
				$templateMgr->assign_by_ref('monographs', $iterator);
				$templateMgr->assign('urlPath', array('plugin', 'Onix30ExportPlugin', 'exportMonograph'));
				$templateMgr->display($this->getTemplatePath() . 'index.tpl');
				break;
		}
	}

	function exportMonograph(&$press, &$monograph, $publicationFormat, $outputFile = null) {
		$this->import('Onix30ExportDom');
		$doc =& XMLCustomWriter::createDocument();
		$onix30ExportDom = new Onix30ExportDom();

		$monographNode =& $onix30ExportDom->generateMonographDom($doc, $press, $monograph, $publicationFormat);
		XMLCustomWriter::appendChild($doc, $monographNode);

		if (!empty($outputFile)) {
			if (($h = fopen($outputFile, 'wb'))===false) return false;
			fwrite($h, XMLCustomWriter::getXML($doc));
			fclose($h);
		} else {
			header('Content-Type: application/xml');
			header('Cache-Control: private');
			header('Content-Disposition: attachment; filename="onix30-' . $monograph->getId() . '-' . $publicationFormat->getId() . '.xml"');
			XMLCustomWriter::printXML($doc);
		}
		return true;
	}

	/**
	 * Execute export tasks using the command-line interface.
	 * @param $args Parameters to the plugin
	 */
	function executeCLI($scriptName, &$args) {
		$xmlFile = array_shift($args);
		$pressPath = array_shift($args);
		$monographId = array_shift($args);

		$pressDao =& DAORegistry::getDAO('PressDAO');
		$publishedMonographDao =& DAORegistry::getDAO('PublishedMonographDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');

		$press =& $pressDao->getByPath($pressPath);

		if (!$press) {
			if ($pressPath != '') {
				echo __('plugins.importexport.onix30.cliError') . "\n";
				echo __('plugins.importexport.onix30.error.unknownPress', array('pressPath' => $pressPath)) . "\n\n";
			}
			$this->usage($scriptName);
			return;
		}

		$monograph =& $publishedMonographDao->getById($monographId);

		if ($monograph == null) {
			echo __('plugins.importexport.onix30.cliError') . "\n";
			echo __('plugins.importexport.onix30.export.error.monographNotFound', array('monographId' => $monographId)) . "\n\n";
			return;
		}

		if (!$this->exportMonograph($press, $monograph, $xmlFile)) {
			echo __('plugins.importexport.onix30.cliError') . "\n";
			echo __('plugins.importexport.onix30.export.error.couldNotWrite', array('fileName' => $xmlFile)) . "\n\n";
		}
	}

	/**
	 * Display the command-line usage information
	 */
	function usage($scriptName) {
		echo __('plugins.importexport.onix30.cliUsage', array(
			'scriptName' => $scriptName,
			'pluginName' => $this->getName()
		)) . "\n";
	}
}

?>
