{**
 * controllers/tab/settings/guidelines/form/guidelinesForm.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Guidelines management form.
 *
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#guidelinesForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="guidelinesForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.PressSettingsTabHandler" op="saveFormData" tab="guidelines"}">
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="guidelinesFormNotification"}

	{fbvFormArea id="focusAndScopeDescription"}
		{fbvFormSection label="manager.setup.authorGuidelines" description="manager.setup.authorGuidelines.description"}
			{fbvElement type="textarea" multilingual=true name="authorGuidelines" id="authorGuidelines" value=$authorGuidelines rich=true}
		{/fbvFormSection}
	{/fbvFormArea}

	{if !$wizardMode}
		{fbvFormButtons id="guidelinesFormSubmit" submitText="common.save" hideCancel=true}
	{/if}
</form>
