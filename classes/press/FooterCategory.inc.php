<?php

/**
 * @file classes/press/FooterCategory.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FooterCategory
 * @ingroup press
 * @see FooterCategoryDAO
 *
 * @brief Describes basic FooterCategory properties.
 */

class FooterCategory extends DataObject {
	/**
	 * Constructor.
	 */
	function FooterCategory() {
		parent::DataObject();
	}

	/**
	 * Get ID of press.
	 * @return int
	 */
	function getPressId() {
		return $this->getData('pressId');
	}

	/**
	 * Set ID of press.
	 * @param $pressId int
	 */
	function setPressId($pressId) {
		return $this->setData('pressId', $pressId);
	}

	/**
	 * Get category path.
	 * @return string
	 */
	function getPath() {
		return $this->getData('path');
	}

	/**
	 * Set category path.
	 * @param $path string
	 */
	function setPath($path) {
		return $this->setData('path', $path);
	}

	/**
	 * Get localized title of the category.
	 * @return string
	 */
	function getLocalizedTitle() {
		return $this->getLocalizedData('title');
	}

	/**
	 * Get title of category.
	 * @param $locale string
	 * @return string
	 */
	function getTitle($locale) {
		return $this->getData('title', $locale);
	}

	/**
	 * Set title of category.
	 * @param $title string
	 * @param $locale string
	 */
	function setTitle($title, $locale) {
		return $this->setData('title', $title, $locale);
	}

	/**
	 * Get localized description of the category.
	 * @return string
	 */
	function getLocalizedDescription() {
		return $this->getLocalizedData('description');
	}

	/**
	 * Get description of category.
	 * @param $locale string
	 * @return string
	 */
	function getDescription($locale) {
		return $this->getData('description', $locale);
	}

	/**
	 * Set description of category.
	 * @param $description string
	 * @param $locale string
	 */
	function setDescription($description, $locale) {
		return $this->setData('description', $description, $locale);
	}

	/**
	 * Retrieve the links in this category.
	 * @return array
	 */
	function getLinks() {
		$footerLinkDao =& DAORegistry::getDAO('FooterLinkDAO');
		$footerLinks =& $footerLinkDao->getByCategoryId($this->getId(), $this->getPressId());
		return $footerLinks->toArray();
	}
}

?>
