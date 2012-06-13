<?php

/**
 * @file pages/workflow/PublicationFormatHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PublicationFormatHandler
 * @ingroup controllers_template_workflow
 *
 * @brief Publication format sub-page handler
 */

import('classes.handler.Handler');

// import UI base classes
import('lib.pkp.classes.linkAction.LinkAction');
import('lib.pkp.classes.linkAction.request.AjaxModal');

class PublicationFormatHandler extends Handler {
	/**
	 * Constructor
	 */
	function PublicationFormatHandler() {
		parent::Handler();

		$this->addRoleAssignment(
			array(ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER, ROLE_ID_PRESS_ASSISTANT),
			array('fetchPublicationFormat')
		);
	}


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, $args, $roleAssignments) {
		// Get the publication Format Policy
		import('classes.security.authorization.internal.PublicationFormatRequiredPolicy');
		$publicationFormatPolicy = new PublicationFormatRequiredPolicy($request, $args);

		// Get the workflow stage policy
		import('classes.security.authorization.OmpWorkflowStageAccessPolicy');
		$stagePolicy = new OmpWorkflowStageAccessPolicy($request, $args, $roleAssignments, 'monographId', WORKFLOW_STAGE_ID_PRODUCTION);

		// Add the Publication Format policy to the stage policy.
		$stagePolicy->addPolicy($publicationFormatPolicy);

		// Add the augmented policy to the handler.
		$this->addPolicy($stagePolicy);
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request, $args) {
		$this->setupTemplate();
	}

	/**
	 * Setup variables for the template
	 * @param $request Request
	 */
	function setupTemplate() {
		parent::setupTemplate();
		AppLocale::requireComponents(LOCALE_COMPONENT_PKP_SUBMISSION, LOCALE_COMPONENT_OMP_SUBMISSION, LOCALE_COMPONENT_OMP_EDITOR);

		$templateMgr =& TemplateManager::getManager();

		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$stageId = $this->getAuthorizedContextObject(ASSOC_TYPE_WORKFLOW_STAGE);
		$publicationFormat =& $this->getAuthorizedContextObject(ASSOC_TYPE_PUBLICATION_FORMAT);

		// Assign the authorized monograph.
		$templateMgr->assign_by_ref('monograph', $monograph);
		$templateMgr->assign('stageId', $stageId);
		$templateMgr->assign_by_ref('publicationFormat', $publicationFormat);
	}


	//
	// Public operations
	//
	/**
	 * Display the publication format template (grid + actions).
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function fetchPublicationFormat($args, $request) {
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$stageId = $this->getAuthorizedContextObject(ASSOC_TYPE_WORKFLOW_STAGE);
		$publicationFormat =& $this->getAuthorizedContextObject(ASSOC_TYPE_PUBLICATION_FORMAT);

		import('controllers.api.proof.linkAction.ApproveProofsLinkAction');
		$approveProofAction = new ApproveProofsLinkAction($request, $publicationFormat->getMonographId(), $publicationFormat->getId());

		// Fetch the template
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('approveProofAction', $approveProofAction);
		return $templateMgr->fetchJson('workflow/publicationFormat.tpl');
	}
}

?>
