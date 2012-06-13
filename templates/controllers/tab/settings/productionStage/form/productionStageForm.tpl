{**
 * controllers/tab/settings/productionStage/form/productionStageForm.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Production Stage settings management form.
 *
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#productionStageForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="productionStageForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.PublicationSettingsTabHandler" op="saveFormData" tab="productionStage"}">
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="productionStageFormNotification"}

	{fbvFormArea id="publisherInformation"}
		{fbvFormSection id="publisher" label="manager.settings.publisher"}
			{fbvElement type="text" name="publisher" required="true" id="publisher" value=$publisher maxlength="255"}
		{/fbvFormSection}
		{fbvFormSection id="location" label="manager.settings.location"}
			{fbvElement type="text" name="location" required="true" id="location" value=$location maxlength="255"}
		{/fbvFormSection}
	{/fbvFormArea}

	{fbvFormArea id="audienceInformation" title="manager.settings.publisherCode" border="true"}
		{fbvFormSection for="codeType" description="manager.settings.publisherCodeType.tip"}
			{fbvElement type="select" from=$codeTypes selected=$codeType translate=false id="codeType" defaultValue="" defaultLabel=""}
		{/fbvFormSection}
		{fbvFormSection  description="manager.settings.publisherCode" for="codeValue"}
			{fbvElement type="text" id="codeValue" value="$codeValue"}
		{/fbvFormSection}
	{/fbvFormArea}
	
	<h3 class="pkp_grid_title">{translate key="manager.setup.productionLibrary"}</h3>
	<p class="pkp_grid_description">{translate key="manager.setup.productionLibraryDescription"}</p>
	{url|assign:productionLibraryGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.library.LibraryFileGridHandler" op="fetchGrid" fileType=$smarty.const.LIBRARY_FILE_TYPE_PRODUCTION}
	{load_url_in_div id="productionLibraryGridDiv" url=$productionLibraryGridUrl}

	<div class="separator"></div>

	<h3 class="pkp_grid_title">{translate key="manager.setup.productionTemplateLibrary"}</h3>
	<p class="pkp_grid_description">{translate key="manager.setup.productionTemplateLibraryDescription"}</p>
	{url|assign:productionTemplateLibraryUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.library.LibraryFileGridHandler" op="fetchGrid" fileType=$smarty.const.LIBRARY_FILE_TYPE_PRODUCTION_TEMPLATE}
	{load_url_in_div id="productionTemplateLibraryDiv" url=$productionTemplateLibraryUrl}

	{if !$wizardMode}
		{fbvFormButtons id="productionStageFormSubmit" submitText="common.save" hideCancel=true}
	{/if}
</form>
