<?php

/**
 * @file classes/monograph/MonographKeywordDAO.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographKeywordDAO
 * @ingroup monograph
 * @see Monograph
 *
 * @brief Operations for retrieving and modifying a monograph's assigned keywords
 */

import('lib.pkp.classes.controlledVocab.ControlledVocabDAO');

define('CONTROLLED_VOCAB_MONOGRAPH_KEYWORD', 'monographKeyword');

class MonographKeywordDAO extends ControlledVocabDAO {
	/**
	 * Constructor
	 */
	function MonographKeywordDAO() {
		parent::ControlledVocabDAO();
	}

	/**
	 * Build/fetch and return a controlled vocabulary for keywords.
	 * @param $monographId int
	 * @return ControlledVocab
	 */
	function build($monographId) {
		// may return an array of ControlledVocabs
		return parent::build(CONTROLLED_VOCAB_MONOGRAPH_KEYWORD, ASSOC_TYPE_MONOGRAPH, $monographId);
	}

	/**
	 * Get the list of localized additional fields to store.
	 * @return array
	 */
	function getLocaleFieldNames() {
		return array('monographKeyword');
	}

	/**
	 * Get keywords for a monograph.
	 * @param $monographId int
	 * @param $locales array
	 * @return array
	 */
	function getKeywords($monographId, $locales) {

		$returner = array();
		foreach ($locales as $locale) {
			$returner[$locale] = array();
			$keywords = $this->build($monographId);
			$monographKeywordEntryDao =& DAORegistry::getDAO('MonographKeywordEntryDAO');
			$monographKeywords = $monographKeywordEntryDao->getByControlledVocabId($keywords->getId());

			while ($keyword =& $monographKeywords->next()) {
				$keyword = $keyword->getKeyword();
				if (array_key_exists($locale, $keyword)) { // quiets PHP when there are no keywords for a given locale
					$returner[$locale][] = $keyword[$locale];
					unset($keyword);
				}
			}
		}
		return $returner;
	}

	/**
	 * Get an array of all of the monograph's keywords
	 * @return array
	 */
	function getAllUniqueKeywords() {
		$keywords = array();

		$result =& $this->retrieve(
			'SELECT DISTINCT setting_value FROM controlled_vocab_entry_settings WHERE setting_name = ?', CONTROLLED_VOCAB_MONOGRAPH_KEYWORD
		);

		while (!$result->EOF) {
			$keywords[] = $result->fields[0];
			$result->MoveNext();
		}

		$result->Close();
		unset($result);

		return $keywords;
	}

	/**
	 * Get an array of monographIds that have a given keyword
	 * @param $content string
	 * @return array
	 */
	function getMonographIdsByKeyword($keyword) {
		$result =& $this->retrieve(
			'SELECT assoc_id
			 FROM controlled_vocabs cv
			 LEFT JOIN controlled_vocab_entries cve ON cv.controlled_vocab_id = cve.controlled_vocab_id
			 INNER JOIN controlled_vocab_entry_settings cves ON cve.controlled_vocab_entry_id = cves.controlled_vocab_entry_id
			 WHERE cves.setting_name = ? AND cves.setting_value = ?',
			array(CONTROLLED_VOCAB_MONOGRAPH_KEYWORD, $keyword)
		);

		$returner = array();
		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);
			$returner[] = $row['assoc_id'];
			$result->MoveNext();
		}
		$result->Close();
		return $returner;
	}

	/**
	 * Add an array of keywords
	 * @param $keywords array
	 * @param $monographId int
	 * @param $deleteFirst boolean
	 * @return int
	 */
	function insertKeywords($keywords, $monographId, $deleteFirst = true) {
		$keywordDao =& DAORegistry::getDAO('MonographKeywordDAO');
		$monographKeywordEntryDao =& DAORegistry::getDAO('MonographKeywordEntryDAO');
		$currentKeywords = $this->build($monographId);

		if ($deleteFirst) {
			$existingEntries = $keywordDao->enumerate($currentKeywords->getId(), CONTROLLED_VOCAB_MONOGRAPH_KEYWORD);

			foreach ($existingEntries as $id => $entry) {
				$entry = trim($entry);
				$monographKeywordEntryDao->deleteObjectById($id);
			}
		}
		if (is_array($keywords)) { // localized, array of arrays

			foreach ($keywords as $locale => $list) {
				if (is_array($list)) {
					$list = array_unique($list); // Remove any duplicate keywords
					$i = 1;
					foreach ($list as $keyword) {
						$keywordEntry = $monographKeywordEntryDao->newDataObject();
						$keywordEntry->setControlledVocabId($currentKeywords->getID());
						$keywordEntry->setKeyword(urldecode($keyword), $locale);
						$keywordEntry->setSequence($i);
						$i ++;
						$keywordEntryId = $monographKeywordEntryDao->insertObject($keywordEntry);
					}
				}
			}
		}
	}
}

?>
