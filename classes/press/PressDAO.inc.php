<?php
/**
 * @file classes/press/PressDAO.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PressDAO
 * @ingroup press
 * @see Press
 *
 * @brief Operations for retrieving and modifying Press objects.
 */

import('classes.press.Press');

class PressDAO extends DAO {
	/**
	 * Constructor
	 */
	function PressDAO() {
		parent::DAO();
	}

	/**
	 * Retrieve a press by press ID.
	 * @param $pressId int
	 * @return Press
	 */
	function getById($pressId) {
		$result =& $this->retrieve('SELECT * FROM presses WHERE press_id = ?', (int) $pressId);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner =& $this->_fromRow($result->GetRowAssoc(false));
		}
		$result->Close();
		return $returner;
	}

	/**
	 * Retrieve the IDs and names of all presses in an associative array.
	 * @return array
	 */
	function getNames() {
		$presses = array();

		$pressIterator =& $this->getPresses();
		while ($press =& $pressIterator->next()) {
			$presses[$press->getId()] = $press->getLocalizedName();
			unset($press);
		}
		unset($pressIterator);

		return $presses;
	}

	/**
	 * Construct a new data object corresponding to this DAO.
	 * @return Press
	 */
	function newDataObject() {
		return new Press();
	}

	/**
	 * Internal function to return a Press object from a row.
	 * @param $row array
	 * @return Press
	 */
	function &_fromRow(&$row) {
		$press = $this->newDataObject();
		$press->setId($row['press_id']);
		$press->setPath($row['path']);
		$press->setSequence($row['seq']);
		$press->setEnabled($row['enabled']);
		$press->setPrimaryLocale($row['primary_locale']);

		HookRegistry::call('PressDAO::_fromRow', array(&$press, &$row));

		return $press;
	}

	/**
	 * Check if a press exists with a specified path.
	 * @param $path the path for the press
	 * @return boolean
	 */
	function pressExistsByPath($path) {
		$result =& $this->retrieve(
			'SELECT COUNT(*) FROM presses WHERE path = ?', $path
		);
		$returner = isset($result->fields[0]) && $result->fields[0] == 1 ? true : false;

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Retrieve a press by path.
	 * (Required by PKPPageRouter; should eventually be removed
	 * in favour of getByPath.)
	 * @see PressDAO::getByPath
	 */
	function &getPressByPath($path) {
		$returner =& $this->getByPath($path);
		return $returner;
	}

	/**
	 * Retrieve a press by path.
	 * @param $path string
	 * @return Press
	 */
	function &getByPath($path) {
		$returner = null;
		$result =& $this->retrieve(
			'SELECT * FROM presses WHERE path = ?', (string) $path
		);

		if ($result->RecordCount() != 0) {
			$returner =& $this->_fromRow($result->GetRowAssoc(false));
		}
		$result->Close();
		unset($result);
		return $returner;
	}

	/**
	 * Retrieve all presses.
	 * @return DAOResultFactory containing matching presses
	 */
	function &getPresses($rangeInfo = null) {
		$result =& $this->retrieveRange(
			'SELECT * FROM presses ORDER BY seq',
			false, $rangeInfo
		);

		$returner = new DAOResultFactory($result, $this, '_fromRow');
		return $returner;
	}

	/**
	 * Insert a new press.
	 * @param $press Press
	 */
	function insertObject(&$press) {
		$this->update(
			'INSERT INTO presses
				(path, seq, enabled, primary_locale)
				VALUES
				(?, ?, ?, ?)',
			array(
				$press->getPath(),
				(int) $press->getSequence() == null ? 0 : $press->getSequence(),
				$press->getEnabled() ? 1 : 0,
				$press->getPrimaryLocale()
			)
		);

		$press->setId($this->getInsertPressId());
		return $press->getId();
	}

	/**
	 * Update an existing press.
	 * @param $press Press
	 */
	function updateObject(&$press) {
		return $this->update(
			'UPDATE presses
				SET
					path = ?,
					seq = ?,
					enabled = ?,
					primary_locale = ?
				WHERE press_id = ?',
			array(
				$press->getPath(),
				(int) $press->getSequence(),
				$press->getEnabled() ? 1 : 0,
				$press->getPrimaryLocale(),
				(int) $press->getId()
			)
		);
	}

	/**
	 * Retrieve all enabled presses
	 * @return array Presses ordered by sequence
	 */
	function &getEnabledPresses() {
		$result =& $this->retrieve(
			'SELECT * FROM presses WHERE enabled=1 ORDER BY seq'
		);

		$resultFactory = new DAOResultFactory($result, $this, '_fromRow');
		return $resultFactory;
	}

	/**
	 * Get the ID of the last inserted press.
	 * @return int
	 */
	function getInsertPressId() {
		return $this->getInsertId('presses', 'press_id');
	}

	/**
	 * Delete a press by ID, INCLUDING ALL DEPENDENT ITEMS.
	 * @param $pressId int
	 */
	function deleteById($pressId) {
		$pressSettingsDao =& DAORegistry::getDAO('PressSettingsDAO');
		$pressSettingsDao->deleteSettingsByPress($pressId);

		$seriesDao =& DAORegistry::getDAO('SeriesDAO');
		$seriesDao->deleteByPressId($pressId);

		$emailTemplateDao =& DAORegistry::getDAO('EmailTemplateDAO');
		$emailTemplateDao->deleteEmailTemplatesByPress($pressId);

		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$monographDao->deleteByPressId($pressId);

		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$userGroupDao->deleteAssignmentsByContextId($pressId);
		$userGroupDao->deleteByContextId($pressId);

		$pluginSettingsDao =& DAORegistry::getDAO('PluginSettingsDAO');
		$pluginSettingsDao->deleteSettingsByPressId($pressId);

		$reviewFormDao =& DAORegistry::getDAO('ReviewFormDAO');
		$reviewFormDao->deleteByAssocId(ASSOC_TYPE_PRESS, $pressId);

		$genreDao =& DAORegistry::getDAO('GenreDAO');
		$genreDao->deleteByPressId($pressId);

		$featureDao =& DAORegistry::getDAO('FeatureDAO');
		$featureDao->deleteByAssoc(ASSOC_TYPE_PRESS, $pressId);

		$newReleaseDao =& DAORegistry::getDAO('NewReleaseDAO');
		$newReleaseDao->deleteByAssoc(ASSOC_TYPE_PRESS, $pressId);

		$this->update('DELETE FROM press_defaults WHERE press_id = ?', (int) $pressId);

		return $this->update(
			'DELETE FROM presses WHERE press_id = ?', (int) $pressId
		);
	}

	/**
	 * Sequentially renumber each press according to their sequence order.
	 */
	function resequencePresses() {
		$result =& $this->retrieve(
			'SELECT press_id FROM presses ORDER BY seq'
		);

		for ($i=1; !$result->EOF; $i++) {
			list($pressId) = $result->fields;
			$this->update(
				'UPDATE presses SET seq = ? WHERE press_id = ?',
				array(
					$i,
					$pressId
				)
			);

			$result->MoveNext();
		}

		$result->Close();
		unset($result);
	}
}

?>
