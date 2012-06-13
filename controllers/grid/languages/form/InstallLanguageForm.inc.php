<?php

/**
 * @file controllers/grid/languages/form/InstallLanguageForm.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class InstallLanguageForm
 * @ingroup controllers_grid_languages_form
 *
 * @brief Form for installing languages.
 */

// Import the base Form.
import('lib.pkp.classes.form.Form');

class InstallLanguageForm extends Form {

	/**
	 * Constructor.
	 */
	function InstallLanguageForm($wizardMode = false) {
		parent::Form('controllers/grid/languages/installLanguageForm.tpl');
	}

	//
	// Overridden methods from Form.
	//
	/**
	 * @see Form::initData()
	 */
	function initData(&$request) {
		parent::initData($request);

		$site =& $request->getSite();
		$this->setData('installedLocales', $site->getInstalledLocales());
	}

	/**
	 * @see Form::fetch()
	 */
	function fetch(&$request) {
		$site =& $request->getSite();
		$allLocales = AppLocale::getAllLocales();
		$installedLocales = $this->getData('installedLocales');
		$notInstalledLocales = array_diff(array_keys($allLocales), $installedLocales);

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('allLocales', $allLocales);
		$templateMgr->assign('notInstalledLocales', $notInstalledLocales);

		import('classes.i18n.LanguageAction');
		$languageAction = new LanguageAction();
		if ($languageAction->isDownloadAvailable()) {
			$downloadableLocales = $languageAction->getDownloadableLocales();
			$downloadableLocaleLinks = array();

			import('lib.pkp.classes.linkAction.request.AjaxAction');
			$router =& $request->getRouter();
			foreach ($downloadableLocales as $locale => $name) {
				$downloadableLocaleLinks[$locale] = new LinkAction($locale,
					new AjaxAction($router->url($request, null, null, 'downloadLocale', array('locale' => $locale))),
					$name . ' (' . $locale . ')');
			}

			$templateMgr->assign('downloadAvailable', true);
			$templateMgr->assign('downloadableLocaleLinks', $downloadableLocaleLinks);
		}

		return parent::fetch(&$request);
	}

	/**
	 * @see Form::readInputData()
	 */
	function readInputData(&$request) {
		parent::readInputData($request);

		$localesToInstall = $request->getUserVar('localesToInstall');
		$this->setData('localesToInstall', $localesToInstall);
	}

	/**
	 * @see Form::execute()
	 */
	function execute(&$request) {
		$site =& $request->getSite();
		$localesToInstall = $this->getData('localesToInstall');

		if (isset($localesToInstall) && is_array($localesToInstall)) {
			$installedLocales = $site->getInstalledLocales();
			$supportedLocales = $site->getSupportedLocales();

			foreach ($localesToInstall as $locale) {
				if (AppLocale::isLocaleValid($locale) && !in_array($locale, $installedLocales)) {
					array_push($installedLocales, $locale);
					// Activate/support by default.
					if (!in_array($locale, $supportedLocales)) array_push($supportedLocales, $locale);
					AppLocale::installLocale($locale);
				}
			}

			$site->setInstalledLocales($installedLocales);
			$site->setSupportedLocales($supportedLocales);
			$siteDao =& DAORegistry::getDAO('SiteDAO');
			$siteDao->updateObject($site);
		}
	}
}

?>
