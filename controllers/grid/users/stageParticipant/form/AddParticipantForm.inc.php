<?php

/**
 * @file controllers/grid/users/stageParticipant/form/AddParticipantForm.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AddParticipantForm
 * @ingroup controllers_grid_users_stageParticipant_form
 *
 * @brief Form for adding a stage participant
 */

import('lib.pkp.classes.form.Form');

class AddParticipantForm extends Form {
	/** The monograph associated with the submission contributor being edited **/
	var $_monograph;

	/** The stage Id **/
	var $_stageId;

	/** UserGroups **/
	var $_userGroups;

	/**
	 * Constructor.
	 */
	function AddParticipantForm(&$monograph, $stageId, &$userGroups) {
		parent::Form('controllers/grid/users/stageParticipant/addParticipantForm.tpl');
		$this->_monograph =& $monograph;
		$this->_stageId = $stageId;
		$this->_userGroups =& $userGroups;

		$this->addCheck(new FormValidator($this, 'userGroupId', 'required', 'editor.monograph.addStageParticipant.form.userGroupRequired'));
		// FIXME: should use a custom validator to check that the user belongs to this group.
		// validating in validate method for now.
		$this->addCheck(new FormValidator($this, 'userId', 'required', 'editor.monograph.addStageParticipant.form.userRequired'));
		$this->addCheck(new FormValidatorPost($this));
	}

	//
	// Getters and Setters
	//
	/**
	 * Get the Monograph
	 * @return Monograph
	 */
	function &getMonograph() {
		return $this->_monograph;
	}

	/**
	 * Get the stage ID
	 * @return int
	 */
	function getStageId() {
		return $this->_stageId;
	}

	/**
	 * Get the user groups allowed for this grid
	 */
	function &getUserGroups() {
		return $this->_userGroups;
	}

	/**
	 * Fetch the form.
	 * @see Form::fetch()
	 */
	function fetch(&$request) {
		$templateMgr =& TemplateManager::getManager();
		$userGroups =& $this->getUserGroups();

		$userGroupOptions = array();
		foreach ($userGroups as $userGroupId => $userGroup) {
			$userGroupOptions[$userGroupId] = $userGroup->getLocalizedName();
		}
		// assign the user groups options
		$templateMgr->assign_by_ref('userGroupOptions', $userGroupOptions);
		// assigned the first element as selected
		$templateMgr->assign('selectedUserGroupId', array_shift(array_keys($userGroupOptions)));

		// assign the vars required for the request
		$monograph =& $this->getMonograph();
		$templateMgr->assign('monographId', $monograph->getId());
		$templateMgr->assign('stageId', $this->getStageId());

		return parent::fetch($request);
	}

	/**
	 * Assign form data to user-submitted data.
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array(
			'userGroupId',
			'userId'
		));
	}

	/**
	 * Validate the form
	 * @see Form::validate()
	 */
	function validate() {
		$userGroupId = (int) $this->getData('userGroupId');
		$userId = (int) $this->getData('userId');

		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO'); /* @var $userGroupDao UserGroupDAO */
		return parent::validate() && $userGroupDao->userInGroup($userId, $userGroupId);
	}

	/**
	 * Save author
	 * @see Form::execute()
	 */
	function execute() {
		$stageAssignmentDao =& DAORegistry::getDAO('StageAssignmentDAO'); /* @var $stageAssignmentDao StageAssignmentDAO */
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO'); /* @var $userGroupDao UserGroupDAO */

		$monograph =& $this->getMonograph();
		$userGroupId = (int) $this->getData('userGroupId');
		$userId = (int) $this->getData('userId');

		// sanity check
		if ($userGroupDao->userGroupAssignedToStage($userGroupId, $this->getStageId())) {
			// insert the assignment
			$stageAssignmentDao->build($monograph->getId(), $userGroupId, $userId);
		}
		return $userGroupId;
	}
}

?>
