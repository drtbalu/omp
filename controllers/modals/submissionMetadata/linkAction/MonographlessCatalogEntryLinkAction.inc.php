<?php
/**
 * @file controllers/modals/submissionMetadata/linkAction/MonographlessCatalogEntryLinkAction
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographlessCatalogEntryLinkAction
 * @ingroup controllers_modals_submissionMetadata_linkAction
 *
 * @brief Add a catalog entry, including first selecting a monograph.
 */

import('lib.pkp.classes.linkAction.LinkAction');
import('lib.pkp.classes.linkAction.request.AjaxModal');

class MonographlessCatalogEntryLinkAction extends LinkAction {

	/**
	 * Constructor
	 * @param $request Request
	 */
	function MonographlessCatalogEntryLinkAction($request) {
		$router =& $request->getRouter();
		$dispatcher =& $router->getDispatcher();

		AppLocale::requireComponents(LOCALE_COMPONENT_OMP_SUBMISSION);

		$modal = new AjaxModal(
			$dispatcher->url(
				$request, ROUTE_COMPONENT, null,
				'modals.submissionMetadata.SelectMonographHandler',
				'fetch', null
			),
			__('submission.catalogEntry.new')
		);

		// Configure the link action.
		if (!isset($action)) {
			$action = 'newCatalogEntry';
		}
		parent::LinkAction($action, $modal, __('submission.catalogEntry.new'), 'information');
	}
}

?>
