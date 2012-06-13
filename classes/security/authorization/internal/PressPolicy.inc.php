<?php
/**
 * @file classes/security/authorization/internal/PressPolicy.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PressPolicy
 * @ingroup security_authorization_internal
 *
 * @brief Basic policy that ensures availability of an OMP press in
 *  the request context and a valid user group. All press based policies
 *  extend this policy.
 */

import('lib.pkp.classes.security.authorization.PolicySet');

class PressPolicy extends PolicySet {
	/**
	 * Constructor
	 * @param $request PKPRequest
	 */
	function PressPolicy(&$request) {
		parent::PolicySet();

		// Ensure we're in a press
		import('lib.pkp.classes.security.authorization.ContextRequiredPolicy');
		$this->addPolicy(new ContextRequiredPolicy($request, 'user.authorization.noPress'));
	}
}

?>
