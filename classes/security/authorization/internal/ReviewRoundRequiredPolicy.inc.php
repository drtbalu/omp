<?php
/**
 * @file classes/security/authorization/internal/ReviewRoundRequiredPolicy.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewRoundRequiredPolicy
 * @ingroup security_authorization_internal
 *
 * @brief Policy that ensures that the request contains a valid review round.
 */

import('lib.pkp.classes.security.authorization.DataObjectRequiredPolicy');

class ReviewRoundRequiredPolicy extends DataObjectRequiredPolicy {
	/**
	 * Constructor
	 * @param $request PKPRequest
	 * @param $args array request parameters
	 * @param $submissionParameterName string the request parameter we expect
	 *  the submission id in.
	 */
	function ReviewRoundRequiredPolicy(&$request, &$args, $parameterName = 'reviewRoundId', $operations = null) {
		parent::DataObjectRequiredPolicy($request, $args, $parameterName, 'user.authorization.invalidReviewRound', $operations);
	}

	//
	// Implement template methods from AuthorizationPolicy
	//
	/**
	 * @see DataObjectRequiredPolicy::dataObjectEffect()
	 */
	function dataObjectEffect() {
		// Get the review round id.
		$reviewRoundId = $this->getDataObjectId();
		if ($reviewRoundId === false) return AUTHORIZATION_DENY;

		// Validate the review round id.
		$reviewRoundDao =& DAORegistry::getDAO('ReviewRoundDAO');
		$reviewRound =& $reviewRoundDao->getReviewRoundById($reviewRoundId);
		if (!is_a($reviewRound, 'ReviewRound')) return AUTHORIZATION_DENY;

		// Ensure that the review round actually belongs to the
		// authorized monograph.
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		if ($reviewRound->getSubmissionId() != $monograph->getId()) AUTHORIZATION_DENY;

		// Ensure that the review round is for this workflow stage
		$stageId = $this->getAuthorizedContextObject(ASSOC_TYPE_WORKFLOW_STAGE);
		if ($reviewRound->getStageId() != $stageId) return AUTHORIZATION_DENY;

		// Save the review round to the authorization context.
		$this->addAuthorizedContextObject(ASSOC_TYPE_REVIEW_ROUND, $reviewRound);
		return AUTHORIZATION_PERMIT;
	}
}

?>
