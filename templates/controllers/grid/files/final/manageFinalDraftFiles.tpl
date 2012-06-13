{**
 * templates/controllers/grid/files/final/manageFinalDraftFiles.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Allows editor to add more file to the review (that weren't added when the submission was sent to review)
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#manageFinalDraftFilesForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<!-- Current final draft files -->
<p>{translate key="editor.monograph.final.manageFinalDraftFilesDescription"}</p>

<div id="existingFilesContainer">
	<form class="pkp_form" id="manageFinalDraftFilesForm" action="{url component="grid.files.final.ManageFinalDraftFilesGridHandler" op="updateFinalDraftFiles" monographId=$monographId} method="post">
		{fbvFormArea id="manageFinalDraftFiles"}
			{fbvFormSection}
				<input type="hidden" name="monographId" value="{$monographId|escape}" />
				<input type="hidden" name="stageId" value="{$smarty.const.WORKFLOW_STAGE_ID_EDITING}" />
				{url|assign:availableReviewFilesGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.final.ManageFinalDraftFilesGridHandler" op="fetchGrid" monographId=$monographId}
				{load_url_in_div id="availableReviewFilesGrid" url=$availableReviewFilesGridUrl}
			{/fbvFormSection}

			{fbvFormButtons}
		{/fbvFormArea}
	</form>
</div>
