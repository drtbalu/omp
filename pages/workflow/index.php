<?php

/**
 * @defgroup pages_workflow
 */

/**
 * @file pages/workflow/index.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup pages_workflow
 * @brief Handle requests for workflow functions.
 *
 */

switch ($op) {
	case 'access':
	case 'submission':
	case 'internalReview':
	case 'externalReview':
	case 'editorial':
	case 'production':
	case 'productionFormatsTab':
	case 'editorDecisionActions':
	case 'submissionProgressBar':
		define('HANDLER_CLASS', 'WorkflowHandler');
		import('pages.workflow.WorkflowHandler');
		break;
	case 'fetchPublicationFormat':
		define('HANDLER_CLASS', 'PublicationFormatHandler');
		import('pages.workflow.PublicationFormatHandler');
		break;
}

?>
