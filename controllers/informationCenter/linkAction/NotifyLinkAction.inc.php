<?php
/**
 * @file controllers/informationCenter/linkAction/NotifyLinkAction.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class NotifyLinkAction
 * @ingroup controllers_informationCenter_linkAction
 *
 * @brief An action to open up the notify part of the IC.
 */

import('lib.pkp.classes.linkAction.LinkAction');

class NotifyLinkAction extends LinkAction {

	/**
	 * Constructor
	 * @param $request Request
	 * @param $monograph Monograph The monograph
	 * @param $stageId int
	 * @param $userId optional
	 *  to show information about.
	 */
	function NotifyLinkAction(&$request, &$monograph, $stageId, $userId = null) {
		AppLocale::requireComponents(LOCALE_COMPONENT_OMP_SUBMISSION);
		// Prepare request arguments
		$requestArgs['monographId'] = $monograph->getId();
		$requestArgs['stageId'] = $stageId;
		if ($userId) $requestArgs['userId'] = $userId;
		$requestArgs['tab'] = 'notify';

		import('lib.pkp.classes.linkAction.request.AjaxModal');
		$router =& $request->getRouter();
		$ajaxModal = new AjaxModal(
			$router->url(
				$request, null,
				'informationCenter.SubmissionInformationCenterHandler', 'viewInformationCenter',
				null, $requestArgs
			),
			__('submission.informationCenter.notify')
		);

		// Configure the file link action.
		parent::LinkAction(
			'notify', $ajaxModal,
			__('submission.informationCenter.notify'), 'notify'
		);
	}
}

?>
