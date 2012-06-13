<?php

/**
 * @file classes/monograph/MonographAgency.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographAgency
 * @ingroup monograph
 * @see MonographAgencyEntryDAO
 *
 * @brief Basic class describing a monograph agency
 */

import('lib.pkp.classes.controlledVocab.ControlledVocabEntry');

class MonographAgency extends ControlledVocabEntry {
	//
	// Get/set methods
	//

	/**
	 * Get the agency
	 * @return string
	 */
	function getAgency() {
		return $this->getData('monographAgency');
	}

	/**
	 * Set the agency text
	 * @param agency string
	 * @param locale string
	 */
	function setAgency($agency, $locale) {
		$this->setData('monographAgency', $agency, $locale);
	}

	function getLocaleMetadataFieldNames() {
		return array('monographAgency');
	}
}
?>
