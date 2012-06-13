<?php

/**
 * @file pages/oai/OAIHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OAIHandler
 * @ingroup pages_oai
 *
 * @brief Handle OAI protocol requests.
 */

define('SESSION_DISABLE_INIT', 1); // FIXME?

import('classes.oai.omp.PressOAI');
import('classes.handler.Handler');

class OAIHandler extends Handler {
	/**
	 * Constructor
	 **/
	function OAIHandler() {
		parent::Handler();
	}

	function authorize($request, $args, $roleAssignments) {
		$returner = parent::authorize($request, $args, $roleAssignments);

		if (!Config::getVar('oai', 'oai')) {
			return false;
		} else {
			return $returner;
		}
	}

	function index() {
		PluginRegistry::loadCategory('oaiMetadataFormats', true);

		$oai = new PressOAI(new OAIConfig(Request::getRequestUrl(), Config::getVar('oai', 'repository_id')));
		$oai->execute();
	}
}

?>
