{**
 * fileUploadForm.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Files upload form.
 *
 * Parameters:
 *   $monographId: The monograph for which a file is being uploaded.
 *   $stageId: The workflow stage in which the file uploader was called.
 *   $uploaderUserGroups: An array of user groups that are allowed
 *    to upload.
 *   $defaultUserGroupId: A pre-selected user group (optional).
 *   $revisionOnly: Whether the user can upload new files or not.
 *   $revisedFileId: The id of the file to be revised (optional).
 *    When set to a number then the user may not choose the file
 *    to be revised.
 *   $revisedFileName: The name of the file to be revised (if any).
 *   $genreId: The preset genre of the file to be uploaded (optional).
 *   $monographFileOptions: A list of monograph files that can be
 *    revised.
 *   $currentMonographFileGenres: An array that assigns genres to the monograph
 *    files that can be revised.
 *   $monographFileGenres: A list of all available monograph file genres.
 *
 * This form implements several states:
 *
 * 1) Uploading of a revision to an existing file with the
 *    file to be revised already known:
 *    - $revisionOnly is true.
 *    - $revisedFileId is set to a number.
 *    - $monographFileOptions will be ignored.
 *    -> No file selector will be shown.
 *    -> A file genre cannot be set.
 *
 * 2) Uploading of a revision to an existing file where the
 *    file to be revised must still be selected by the user.
 *    - $revisionOnly is true.
 *    - $revisedFileId is not set to a number.
 *    - $monographFileOptions must not be empty.
 *    -> A selector with files that can be revised will
 *       be shown. Selection of a revised file is mandatory.
 *       If a revised file id is given then that file will
 *       be pre-selected.
 *    -> A file genre cannot be set.
 *
 * 3) Uploading of a file that may or may not be a revision
 *    of an existing file (free upload).
 *    - $revisionOnly is false.
 *    - $revisedFileId does not have to be a number.
 *    - $monographFileOptions is not empty.
 *    -> A selector with files that can be revised will
 *       be shown. Selection of a revised file is optional.
 *       If the revised file id is set then this file will
 *       be pre-selected in the drop-down.
 *    -> A file genre selector will be shown but will be
 *       deactivated as soon as the user selects a file
 *       to be revised. Otherwise selection of a genre is
 *       mandatory.
 *    -> Uploaded files will be checked against existing
 *       files to identify possible revisions.
 *
 * 4) Uploading of a new file when no previous files
 *    exist at all at this workflow stage.
 *    - $revisionOnly is false.
 *    - $revisedFileId must not be a number.
 *    - $monographFileOptions is empty.
 *    -> No file selector will be shown.
 *    -> A file genre selector will be shown. Selection of
 *       a genre is mandatory.
 *
 * The following decision tree shows the input parameters
 * and the corresponding use cases (RO: $revisionOnly,
 * RF: $revisedFileId, FO: $monographFileOptions,
 * y=given, n=not given, o=any/ignored):
 *
 *   RO  RF  FO
 *   y   y   o  -> 1)
 *   |   n   y  -> 2)
 *   |   |   n  -> not allowed (skip loading form and show a message to user)
 *
 *       FO  RF
 *   n   y   o  -> 3)
 *   |   n   y  -> not allowed
 *   |   |   n  -> 4)
 *}

{* Implement the above decision tree and configure the form based on the identified use case. *}
{assign var="showFileNameOnly" value=false}
{if $revisionOnly}
	{assign var="showGenreSelector" value=false}
	{if is_numeric($revisedFileId)}
		{* Use case 1: Revision of a known file *}
		{assign var="showFileSelector" value=false}
		{assign var="showFileNameOnly" value=true}
	{else}
		{* Use case 2: Revision of a file which still must be chosen *}
		{if empty($monographFileOptions)}{assign var="revisionOnlyWithoutFileOptions" value=true}{/if}
		{assign var="showFileSelector" value=true}
	{/if}
{else}
	{assign var="showGenreSelector" value=true}
	{if empty($monographFileOptions)}
		{* Use case 4: Upload a new file *}
		{if is_numeric($revisedFileId)}{"A revised file id cannot be given when uploading a new file!"|fatalError}{/if}
		{assign var="showFileSelector" value=false}
	{else}
		{* Use case 3: Upload a new file or a revision *}
		{assign var="showFileSelector" value=true}
	{/if}
{/if}

{if $revisionOnlyWithoutFileOptions}
	<br /><br />
	{translate key="submission.upload.noAvailableReviewFiles"}
	<br /><br />
{else}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the upload form handler.
		$('#uploadForm').pkpHandler(
			'$.pkp.controllers.wizard.fileUpload.form.FileUploadFormHandler',
			{ldelim}
				hasFileSelector: {if $showFileSelector}true{else}false{/if},
				hasGenreSelector: {if $showGenreSelector}true{else}false{/if},
				presetRevisedFileId: '{$revisedFileId}',
				// File genres currently assigned to monograph files.
				fileGenres: {ldelim}
					{foreach name=currentMonographFileGenres from=$currentMonographFileGenres key=monographFileId item=fileGenre}
						{$monographFileId}: {$fileGenre}{if !$smarty.foreach.currentMonographFileGenres.last},{/if}
					{/foreach}
				{rdelim},
				$uploader: $('#plupload'),
				uploaderOptions: {ldelim}
					uploadUrl: '{url|escape:javascript op="uploadFile" monographId=$monographId stageId=$stageId fileStage=$fileStage reviewRoundId=$reviewRoundId assocType=$assocType assocId=$assocId escape=false}',
					baseUrl: '{$baseUrl|escape:javascript}',
				{rdelim}
			{rdelim});
	{rdelim});
</script>

<form class="pkp_form" id="uploadForm" action="#" method="post">
	{fbvFormArea id="file"}
		{if $assocType && $assocId}
			<input type="hidden" name="assocType" value="{$assocType|escape}" />
			<input type="hidden" name="assocId" value="{$assocId|escape}" />
		{/if}
		{if count($uploaderUserGroups) > 1}
			{fbvFormSection label="submission.upload.userGroup" required=true}
				{fbvElement type="select" name="uploaderUserGroupId" id="uploaderUserGroupId" from=$uploaderUserGroups selected=$defaultUserGroupId translate=false}
			{/fbvFormSection}
		{else}
			<input type="hidden" id="uploaderUserGroupId" name="uploaderUserGroupId" value="{$uploaderUserGroups|@key}" />
		{/if}

		{if $showFileNameOnly}
			{fbvFormSection title="submission.submit.currentFile"}
				{$revisedFileName}
			{/fbvFormSection}

			{* Save the revised file ID in a hidden input field. *}
			<input type="hidden" id="revisedFileId" name="revisedFileId" value="{$revisedFileId}" />
		{elseif $showFileSelector}
			{* TODO: This should be a radio button selection, where the select is displayed only if the user chooses to replace a file *}
			{fbvFormSection title="submission.upload.revisingExistingFile" required=$revisionOnly}
				{fbvElement type="select" name="revisedFileId" label='submission.upload.selectMandatoryFileToRevise' id="revisedFileId" from=$monographFileOptions selected=$revisedFileId translate=false}
			{/fbvFormSection}
		{/if}

		{if $showGenreSelector}
			{fbvFormSection title="submission.upload.fileContents" required=true}
				{fbvElement type="select" name="genreId" id="genreId" from=$monographFileGenres translate=false defaultLabel="" defaultValue="" required="true" selected=$genreId}
			{/fbvFormSection}
		{/if}

		{fbvFormSection title="submission.submit.selectFile" required=true}
			{* The uploader widget *}
			<div id="plupload"></div>
		{/fbvFormSection}

		<p>{translate key="common.fileUpload.maxFileSizeStatus" fileSize=$maxFileUploadSize supportName=$pressSettings.supportName supportEmail=$pressSettings.supportEmail}</p>

		{if $ensuringLink}
			<div id="{$ensuringLink->getId()}" class="pkp_linkActions">
				{include file="linkAction/linkAction.tpl" action=$ensuringLink contextId="uploadForm"}
			</div>
		{/if}
	{/fbvFormArea}
</form>
{/if}