<?php
/**
 * @file classes/security/authorization/internal/PressManagerRequiredPolicy.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PressManagerRequiredPolicy
 * @ingroup security_authorization_internal
 *
 * @brief Class to prevent access unless a press manager is assigned to the stage.
 *
 * NB: This policy expects a previously authorized monograph and a stage id
 * in the authorization context.
 */

import('lib.pkp.classes.security.authorization.AuthorizationPolicy');

class PressManagerRequiredPolicy extends AuthorizationPolicy {
	/** @var PKPRequest */
	var $_request;

	/**
	 * Constructor
	 * @param $request PKPRequest
	 */
	function PressManagerRequiredPolicy(&$request) {
		parent::AuthorizationPolicy('user.authorization.pressManagerRequired');
		$this->_request =& $request;
	}

	//
	// Implement template methods from AuthorizationPolicy
	//
	/**
	 * @see AuthorizationPolicy::effect()
	 */
	function effect() {
		// Get the monograph
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		if (!is_a($monograph, 'Monograph')) return AUTHORIZATION_DENY;

		// Get the stage
		$stageId = $this->getAuthorizedContextObject(ASSOC_TYPE_WORKFLOW_STAGE);
		if (!is_numeric($stageId)) return AUTHORIZATION_DENY;

		$stageAssignmentDao =& DAORegistry::getDAO('StageAssignmentDAO'); /* @var $stageAssignmentDao StageAssignmentDAO */
		if ($stageAssignmentDao->editorAssignedToStage($monograph->getId(), $stageId)) {
			return AUTHORIZATION_PERMIT;
		} else {
			return AUTHORIZATION_DENY;
		}
	}
}

?>
