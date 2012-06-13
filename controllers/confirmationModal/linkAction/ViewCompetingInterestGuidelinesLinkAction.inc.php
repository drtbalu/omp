<?php
/**
 * @defgroup controllers_confirmationModal_linkAction
 */

/**
 * @file controllers/modals/submissionMetadata/linkAction/ViewCompetingInterestGuidelinesLinkAction.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ViewCompetingInterestGuidelinesLinkAction
 * @ingroup controllers_confirmationModal_linkAction
 *
 * @brief An action to open the competing interests confirmation modal.
 */

import('lib.pkp.classes.linkAction.LinkAction');

class ViewCompetingInterestGuidelinesLinkAction extends LinkAction {

	/**
	 * Constructor
	 * @param $request Request
	 */
	function ViewCompetingInterestGuidelinesLinkAction(&$request) {
		$press =& $request->getPress();
		// Instantiate the view competing interests modal.
		import('lib.pkp.classes.linkAction.request.ConfirmationModal');
		$viewCompetingInterestsModal = new ConfirmationModal(
								$press->getLocalizedSetting('competingInterests'),
								__('reviewer.monograph.competingInterests'),
								null, null, false,
								false
							);

		// Configure the link action.
		parent::LinkAction('viewCompetingInterestGuidelines', $viewCompetingInterestsModal, __('reviewer.monograph.competingInterests'));
	}
}

?>
