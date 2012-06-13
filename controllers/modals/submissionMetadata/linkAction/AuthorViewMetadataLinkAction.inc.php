<?php
/**
 * @file controllers/modals/submissionMetadata/linkAction/AuthorViewMetadataLinkAction.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorViewMetadataLinkAction
 * @ingroup controllers_modals_submissionMetadata_linkAction
 *
 * @brief An action to open the submission meta-data modal.
 */

import('lib.pkp.classes.linkAction.LinkAction');

class AuthorViewMetadataLinkAction extends LinkAction {

	/**
	 * Constructor
	 * @param $request Request
	 * @param $monographId integer The submission to show meta-data for.
	 */
	function AuthorViewMetadataLinkAction(&$request, $monographId) {
		$dispatcher =& $request->getDispatcher();
		import('lib.pkp.classes.linkAction.request.AjaxModal');
		parent::LinkAction(
			'viewMetadata',
			new AjaxModal(
				$dispatcher->url($request, ROUTE_COMPONENT, null,
					'modals.submissionMetadata.AuthorSubmissionMetadataHandler',
					'fetch', null, array('monographId' => $monographId)
				),
				__('submission.viewMetadata')
			),
			__('submission.viewMetadata'),
			'more_info'
		);
	}
}

?>
