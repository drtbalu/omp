<?php

/**
 * @file classes/press/FooterLink.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FooterLink
 * @ingroup press
 * @see FooterLinkDAO
 *
 * @brief Describes basic FooterLink properties.
 */

class FooterLink extends DataObject {
	/**
	 * Constructor.
	 */
	function FooterLink() {
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
	 * Get ID of link's category.
	 * @return int
	 */
	function getCategoryId() {
		return $this->getData('footerCategoryId');
	}

	/**
	 * Set ID of link's category.
	 * @param $parentId int
	 */
	function setCategoryId($footerCategoryId) {
		return $this->setData('footerCategoryId', $footerCategoryId);
	}

	/**
	 * Get link URL.
	 * @return string
	 */
	function getUrl() {
		return $this->getData('url');
	}

	/**
	 * Set link URL.
	 * @param $path string
	 */
	function setUrl($url) {
		return $this->setData('url', $url);
	}

	/**
	 * Get localized title of the link.
	 * @return string
	 */
	function getLocalizedTitle() {
		return $this->getLocalizedData('title');
	}

	/**
	 * Get title of link.
	 * @param $locale string
	 * @return string
	 */
	function getTitle($locale) {
		return $this->getData('title', $locale);
	}

	/**
	 * Set title of link.
	 * @param $title string
	 * @param $locale string
	 */
	function setTitle($title, $locale) {
		return $this->setData('title', $title, $locale);
	}
}

?>
