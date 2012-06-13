{**
 * templates/controllers/modals/editorDecision/form/promoteForm.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Form used to send reviews to author
 *
 *}

<script type="text/javascript">
	$(function() {ldelim}
		$('#promote').pkpHandler(
			'$.pkp.controllers.modals.editorDecision.form.EditorDecisionFormHandler',
			{ldelim} peerReviewUrl: '{$peerReviewUrl|escape:javascript}' {rdelim}
		);
	{rdelim});
</script>

<form class="pkp_form" id="promote" method="post" action="{url op=$saveFormOperation}" >
	<input type="hidden" name="monographId" value="{$monographId|escape}" />
	<input type="hidden" name="stageId" value="{$stageId|escape}" />
	<input type="hidden" name="decision" value="{$decision|escape}" />
	<input type="hidden" name="reviewRoundId" value="{$reviewRoundId|escape}" />

	{fbvFormSection title="user.role.author" for="authorName" size=$fbvStyles.size.MEDIUM}
		{fbvElement type="text" id="authorName" name="authorName" value=$authorName disabled=true}
	{/fbvFormSection}

	{if $stageId == $smarty.const.WORKFLOW_STAGE_ID_INTERNAL_REVIEW || $stageId == $smarty.const.WORKFLOW_STAGE_ID_EXTERNAL_REVIEW}
		<!--  Message to reviewer textarea -->
		<span style="float:right;line-height: 24px;"><a id="importPeerReviews" href="#" class="sprite import">{translate key="submission.comments.importPeerReviews"}</a></span>
	{/if}

	{fbvFormSection title="editor.review.personalMessageToAuthor" for="personalMessage"}
		{fbvElement type="textarea" name="personalMessage" id="personalMessage" value=$personalMessage}
	{/fbvFormSection}

	{fbvFormSection for="skipEmail" size=$fbvStyles.size.MEDIUM list=true}
		{fbvElement type="checkbox" id="skipEmail" name="skipEmail" label="editor.submissionReview.skipEmail"}
	{/fbvFormSection}

	{** Some decisions can be made before review is initiated (i.e. no attachments). **}
	{if $reviewRoundId}
		<div id="attachments" style="margin-top: 30px;">
			{url|assign:reviewAttachmentsGridUrl router=$smarty.const.ROUTE_COMPONENT  component="grid.files.attachment.EditorSelectableReviewAttachmentsGridHandler" op="fetchGrid" monographId=$monographId stageId=$stageId reviewRoundId=$reviewRoundId escape=false}
			{load_url_in_div id="reviewAttachmentsGridContainer" url="$reviewAttachmentsGridUrl"}
		</div>
	{/if}

	<div id="availableFiles" style="margin-top: 30px;">
		{* Show a different grid depending on whether we're in review or before the review stage *}
		{if $stageId == $smarty.const.WORKFLOW_STAGE_ID_SUBMISSION}
			{url|assign:filesToPromoteGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.submission.SelectableSubmissionDetailsFilesGridHandler" op="fetchGrid" monographId=$monographId stageId=$stageId escape=false}
		{elseif $reviewRoundId}
			{** a set $reviewRoundId var implies we are INTERNAL_REVIEW or EXTERNAL_REVIEW **}
			{url|assign:filesToPromoteGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.review.SelectableReviewRevisionsGridHandler" op="fetchGrid" monographId=$monographId stageId=$stageId reviewRoundId=$reviewRoundId escape=false}
		{elseif $stageId == $smarty.const.WORKFLOW_STAGE_ID_EDITING}
			{url|assign:filesToPromoteGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.fairCopy.SelectableFairCopyFilesGridHandler" op="fetchGrid" monographId=$monographId stageId=$stageId escape=false}
		{/if}
		{load_url_in_div id="filesToPromoteGrid" url=$filesToPromoteGridUrl}
	</div>
	{fbvFormButtons submitText="editor.submissionReview.recordDecision"}
</form>


