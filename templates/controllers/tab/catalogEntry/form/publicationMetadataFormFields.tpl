{**
 * templates/controllers/tab/catalogEntry/form/publicationMetadataFormFields.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 *}
{* generate a unique form id since this form is used on several tabs *}
{capture assign=publicationFormId}publicationMetadataEntryForm-{$publicationFormatId}{/capture}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#{$publicationFormId|escape:"javascript"}').pkpHandler(
			'$.pkp.controllers.form.AjaxFormHandler',
			{ldelim}
				trackFormChanges: true
			{rdelim}
		);
	{rdelim});
</script>

<form class="pkp_form" id="{$publicationFormId|escape}" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT op="saveForm"}">
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId=$publicationFormId|concat:"-notification" requestOptions=$notificationRequestOptions}

	<input type="hidden" name="monographId" value="{$monographId|escape}" />
	<input type="hidden" name="stageId" value="{$stageId|escape}" />
	<input type="hidden" name="tabPos" value="{$formParams.tabPos|escape}" />
	<input type="hidden" name="publicationFormatId" value="{$publicationFormatId|escape}" />
	<input type="hidden" name="displayedInContainer" value="{$formParams.displayedInContainer|escape}" />
	<input type="hidden" name="tab" value="publication" />

	{* in catalog status *}
	<h3>{if $isAvailable && $submissionApproved}{translate key="monograph.publicationFormat.inCatalog"}{else}{translate key="monograph.publicationFormat.notInCatalog"}{/if}</h3>

	{fbvFormArea id="catalogInclusion"}
		{fbvFormSection list="true"}
			{fbvElement type="checkbox" id="isAvailable" checked=$isAvailable label="monograph.publicationFormat.isAvailable"}
		{/fbvFormSection}
	{/fbvFormArea}

	{* E-commerce settings *}
	{if $paymentConfigured}
		{url|assign:approvedProofGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.proof.ApprovedProofFilesGridHandler" op="fetchGrid" monographId=$monographId publicationFormatId=$publicationFormatId escape=false}
		{load_url_in_div id="approvedProofGrid-$publicationFormatId" url=$approvedProofGridUrl}
	{/if}

	{fbvFormArea id="productIdentifier"}
		{fbvFormSection}
			<!-- Product Identification Codes -->
			{assign var="divId" value="identificationCodeGridContainer"|concat:$publicationFormatId|escape}
			{url|assign:identGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.catalogEntry.IdentificationCodeGridHandler" op="fetchGrid" monographId=$monographId publicationFormatId=$publicationFormatId escape=false}
			{load_url_in_div id="$divId" url="$identGridUrl"}
		{/fbvFormSection}
	{/fbvFormArea}

	{fbvFormArea id="salesRights"}
		{fbvFormSection}
			<!-- Sales rights and regions -->
			{assign var="divId" value="salesRightsGridContainer"|concat:$publicationFormatId|escape}
			{url|assign:salesGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.catalogEntry.SalesRightsGridHandler" op="fetchGrid" monographId=$monographId publicationFormatId=$publicationFormatId escape=false}
			{load_url_in_div id="$divId" url="$salesGridUrl"}
		{/fbvFormSection}
	{/fbvFormArea}

	{fbvFormArea id="marketRegions"}
		{fbvFormSection}
			<!-- Market regions -->
			{assign var="divId" value="marketsGridContainer"|concat:$publicationFormatId|escape}
			{url|assign:marketsGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.catalogEntry.MarketsGridHandler" op="fetchGrid" monographId=$monographId publicationFormatId=$publicationFormatId escape=false}
			{load_url_in_div id="$divId" url="$marketsGridUrl"}
		{/fbvFormSection}
	{/fbvFormArea}

	{fbvFormArea id="publicationDates"}
		{fbvFormSection}
			<!-- Product Publication/Embargo dates -->
			{assign var="divId" value="publicationDateGridContainer"|concat:$publicationFormatId|escape}
			{url|assign:dateGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.catalogEntry.PublicationDateGridHandler" op="fetchGrid" monographId=$monographId publicationFormatId=$publicationFormatId escape=false}
			{load_url_in_div id="$divId" url="$dateGridUrl"}
		{/fbvFormSection}
	{/fbvFormArea}

	{fbvFormArea id="productComposition" border="true"}
		{fbvFormSection for="productCompositionCode" title="monograph.publicationFormat.productComposition" required="true"}
			{fbvElement type="select" from=$productCompositionCodes selected=$productCompositionCode translate=false id="productCompositionCode" required="true" defaultValue="" defaultLabel="" size=$fbvStyles.size.MEDIUM inline=true}
			{fbvElement type="select" label="monograph.publicationFormat.productFormDetailCode" from=$productFormDetailCodes selected=$productFormDetailCode translate=false id="productFormDetailCode" defaultValue="" defaultLabel="" size=$fbvStyles.size.MEDIUM inline=true}
		{/fbvFormSection}
	{/fbvFormArea}

	{fbvFormArea id="productAvailability" title="monograph.publicationFormat.productAvailability" border="true"}
		{fbvFormSection for="productAvailability" required="true"}
			{fbvElement type="select" from=$productAvailabilityCodes required=true selected=$productAvailabilityCode translate=false id="productAvailabilityCode"}
		{/fbvFormSection}
	{/fbvFormArea}

	{fbvFormArea id="imprintFormArea" title="monograph.publicationFormat.imprint"}
		{fbvFormSection for="imprint"}
			{fbvElement type="text" name="imprint" id="imprint" value=$imprint maxlength="255"}
		{/fbvFormSection}
	{/fbvFormArea}

	{if $isPhysicalFormat}
		{include file="controllers/tab/catalogEntry/form/physicalPublicationFormat.tpl"}
	{else}
		{include file="controllers/tab/catalogEntry/form/digitalPublicationFormat.tpl"}
	{/if}

	{fbvFormButtons id="publicationMetadataFormSubmit" submitText="common.save"}
</form>

