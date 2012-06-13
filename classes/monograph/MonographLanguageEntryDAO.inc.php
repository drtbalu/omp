<?php

/**
 * @file classes/monograph/MonographLanguageEntryDAO.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographLanguageEntryDAO
 * @ingroup monograph
 * @see Monograph
 *
 * @brief Operations for retrieving and modifying a monograph's languages
 */

import('classes.monograph.MonographLanguage');
import('lib.pkp.classes.controlledVocab.ControlledVocabEntryDAO');

class MonographLanguageEntryDAO extends ControlledVocabEntryDAO {
	/**
	 * Constructor
	 */
	function MonographLanguageEntryDAO() {
		parent::ControlledVocabEntryDAO();
	}

	/**
	 * Construct a new data object corresponding to this DAO.
	 * @return MonographLanguage
	 */
	function newDataObject() {
		return new MonographLanguage();
	}

	/**
	 * Retrieve an iterator of controlled vocabulary entries matching a
	 * particular controlled vocabulary ID.
	 * @param $controlledVocabId int
	 * @return object DAOResultFactory containing matching CVE objects
	 */
	function getByControlledVocabId($controlledVocabId, $rangeInfo = null) {
		$result =& $this->retrieveRange(
			'SELECT cve.* FROM controlled_vocab_entries cve WHERE cve.controlled_vocab_id = ? ORDER BY seq',
			array((int) $controlledVocabId),
			$rangeInfo
		);

		$returner = new DAOResultFactory($result, $this, '_fromRow');
		return $returner;
	}
}

?>
