{**
 * templates/controllers/grid/catalogEntry/form/salesRightsForm.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Sales Rights form.
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#addSalesRightsForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="addSalesRightsForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.catalogEntry.SalesRightsGridHandler" op="updateRights"}">
	<input type="hidden" name="monographId" value="{$monographId|escape}" />
	<input type="hidden" name="publicationFormatId" value="{$publicationFormatId|escape}" />
	<input type="hidden" name="salesRightsId" value="{$salesRightsId|escape}" />
	{fbvFormArea id="addRights"}
		{fbvFormSection title="grid.catalogEntry.salesRightsType" for="type" required="true"}
			{fbvElement type="select" from=$salesRights selected=$type id="type" translate=false}
		{/fbvFormSection}
		{fbvFormSection for="value" list="true" description="grid.catalogEntry.salesRightsROW.tip"}
		
			{if $ROWSetting}
				{assign var="checked" value=true}
			{else}
				{assign var="checked" value=false}
			{/if}

			{fbvElement type="checkbox" id="ROWSetting" checked=$checked list="true" label="grid.catalogEntry.salesRightsROW"}
		{/fbvFormSection}
		
		{include file="controllers/grid/catalogEntry/form/countriesAndRegions.tpl"}

		{fbvFormButtons}
	{/fbvFormArea}
</form>
