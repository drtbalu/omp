<?php
/**
 * @defgroup controllers_informationCenter_linkAction
 */

/**
 * @file controllers/informationCenter/linkAction/FileInfoCenterLinkAction.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FileInfoCenterLinkAction
 * @ingroup controllers_informationCenter_linkAction
 *
 * @brief An action to open up the information center for a file.
 */

import('controllers.api.file.linkAction.FileLinkAction');

class FileInfoCenterLinkAction extends FileLinkAction {

	/**
	 * Constructor
	 * @param $request Request
	 * @param $monographFile MonographFile the monograph file
	 * to show information about.
	 * @param $stageId int (optional) The stage id that user is looking at.
	 */
	function FileInfoCenterLinkAction(&$request, &$monographFile, $stageId = null) {
		// Instantiate the information center modal.
		$router =& $request->getRouter();
		import('lib.pkp.classes.linkAction.request.AjaxModal');
		$ajaxModal = new AjaxModal(
			$router->url(
				$request, null,
				'informationCenter.FileInformationCenterHandler', 'viewInformationCenter',
				null, $this->getActionArgs($monographFile, $stageId)
			),
			__('informationCenter.informationCenter'),
			'informationCenter'
		);

		// Configure the file link action.
		parent::FileLinkAction(
			'moreInformation', $ajaxModal,
			__('grid.action.moreInformation'), 'more_info'
		);
	}
}

?>
