{**
 * controllers/modals/submissionMetadata/form/catalogEntrySubmissionReviewForm.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display a submission's catalog entry form.
 *
 *}
{* generate a unique ID for the form *}
{assign var="submissionMetadataViewFormId" value="submissionMetadataViewForm-"|uniqid|escape}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#{$submissionMetadataViewFormId}').pkpHandler(
			'$.pkp.controllers.form.AjaxFormHandler',
			{ldelim}
				trackFormChanges: true
			{rdelim}
		);
	{rdelim});
</script>

<form class="pkp_form" id="{$submissionMetadataViewFormId}" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT op="saveForm"}">
	{assign var="notificationId" value="submissionMetadataViewFormNotification-"|uniqid|escape}
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId=$notificationId}

	<input type="hidden" name="monographId" value="{$monographId|escape}" />
	<input type="hidden" name="stageId" value="{$stageId|escape}" />
	<input type="hidden" name="displayedInContainer" value="{$formParams.displayedInContainer|escape}" />
	<input type="hidden" name="tab" value="submission" />

	{if !$formParams.hideSubmit}
		{fbvFormSection list="true"}
			{if $isPublished}{assign var=confirm value=true}{else}{assign var=confirm value=false}{/if}
			{fbvElement type="checkbox" id="confirm" checked=$confirm label="submission.catalogEntry.confirm" value="confirm"}
		{/fbvFormSection}
	{/if}

	{include file="submission/form/seriesAndCategories.tpl" readOnly=$formParams.readOnly}

	{include file="submission/submissionMetadataFormFields.tpl" readOnly=$formParams.readOnly}

	<!--  Contributors -->

	{if !$formParams.hideSubmit || !$formParams.anonymous}
		{* generate a unique ID for the form *}
		{assign var="authorsGridContainer" value="authorsGridContainer-"|uniqid|escape}
		{url|assign:authorGridUrl router=$smarty.const.ROUTE_COMPONENT  component="grid.users.author.AuthorGridHandler" op="fetchGrid" monographId=$monographId stageId=$stageId escape=false}
		{load_url_in_div id=$authorsGridContainer url="$authorGridUrl"}

		<!--  Chapters -->
		{if $isEditedVolume}
			{assign var="chaptersGridContainer" value="authorsGridContainer-"|uniqid|escape}
			{url|assign:chaptersGridUrl router=$smarty.const.ROUTE_COMPONENT  component="grid.users.chapter.ChapterGridHandler" op="fetchGrid" monographId=$monographId}
			{load_url_in_div id=$chaptersGridContainer url="$chaptersGridUrl"}
		{/if}
	{/if}

	{if !$formParams.hideSubmit}
		{fbvFormButtons id="submissionMetadataFormSubmit" submitText="common.save"}
	{else}
		{fbvElement type="button" class="cancelFormButton" id="cancelFormButton" label="common.close"}
	{/if}

</form>
