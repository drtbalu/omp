{**
 * controllers/tab/settings/paymentMethod/form/paymentMethodForm.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Payment method management form.
 *
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#paymentMethodContainerDiv').pkpHandler(
			'$.pkp.controllers.tab.settings.paymentMethod.PaymentMethodHandler',
			{ldelim}
				paymentMethodFormUrlTemplate: '{url|escape:"javascript" router=$smarty.const.ROUTE_COMPONENT op="getPaymentFormContents" paymentPluginName=PAYMENT_PLUGIN_NAME escape=false}'
			{rdelim}
		);
		// Attach the form handler. (Triggers selectMonograph event.)
		$('#selectPaymentMethodForm').pkpHandler(
			'$.pkp.controllers.form.DropdownFormHandler',
			{ldelim}
				getOptionsUrl: '{url|escape:"javascript" router=$smarty.const.ROUTE_COMPONENT op="getPaymentMethods" escape=false}',
				defaultKey: '{$paymentPluginName|escape:"javascript"}',
				eventName: 'selectPaymentMethod'
			{rdelim}
		);
		// Attach the AJAX form handler to the actual payment method config form.
		$('#paymentMethodForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<div id="paymentMethodContainerDiv">
	<form class="pkp_form" id="selectPaymentMethodForm">
		{fbvFormArea id="paymentMethod"}
			{fbvFormSection label="manager.paymentMethod.method" description="manager.paymentMethod.description"}
				{fbvElement type="select" id="pluginSelect" from=$pluginNames translate=false size=$fbvStyles.size.MEDIUM}
			{/fbvFormSection}
		{/fbvFormArea}
	</form>

	<form class="pkp_form" id="paymentMethodForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.DistributionSettingsTabHandler" op="saveFormData" tab="paymentMethod"}">
	<input type="hidden" name="paymentPluginName" id="paymentPluginName" />
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="paymentMethodFormNotification"}
	{include file="controllers/tab/settings/wizardMode.tpl" wizardMode=$wizardMode}

	{fbvFormArea id="selectcurrency"}
		{fbvFormSection label="manager.paymentMethod.currency" description="manager.paymentMethod.currency.description" inline=true size=$fbvStyles.size.MEDIUM}
			{fbvElement required="true" type="select" id="pressCurrency" from=$currencies selected=$pressCurrency translate=false}
		{/fbvFormSection}
	{/fbvFormArea}

	<div id="paymentMethodFormContainer">
		{* The form will be loaded into this container *}
	</div>

	<div class="separator"></div>

	{if !$wizardMode}
		{fbvFormButtons id="paymentFormSubmit" submitText="common.save" hideCancel=true}
	{/if}
	</form>
</div>
