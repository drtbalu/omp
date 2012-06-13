{**
 * controllers/tab/settings/masthead/form/mastheadForm.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Masthead management form.
 *
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#mastheadForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="mastheadForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.PressSettingsTabHandler" op="saveFormData" tab="masthead"}">

	{include file="controllers/tab/settings/wizardMode.tpl" wizardMode=$wizardMode}

	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="mastheadNotification"}

	{fbvFormArea id="mastheadFormArea"}
		{fbvFormSection title="manager.setup.pressName" for="name" required=true inline=true size=$fbvStyles.size.MEDIUM}
			{fbvElement type="text" multilingual=true name="name" id="name" value=$name}
		{/fbvFormSection}

		{fbvFormSection title="manager.setup.pressInitials" for="initials" required=true inline=true size=$fbvStyles.size.SMALL}
			{fbvElement type="text" multilingual=true name="initials" id="initials" value=$initials}
		{/fbvFormSection}

		{fbvFormSection label="manager.setup.pressDescription" for="description" description="manager.setup.pressDescription.description"}
			{fbvElement type="textarea" multilingual=true name="description" id="description" value=$description rich=true height=$fbvStyles.height.SHORT}
		{/fbvFormSection}

		{fbvFormSection list=true}
			{if $enabled}{assign var="enabled" value="checked"}{/if}
			{fbvElement type="checkbox" id="pressEnabled" value="1" checked=$enabled label="manager.setup.enablePressInstructions"}
		{/fbvFormSection}
		{fbvFormSection label="manager.masthead.title" for="masthead" description="manager.setup.masthead.description"}
			{fbvElement type="textarea" multilingual=true id="masthead" value=$masthead rich=true height=$fbvStyles.height.SHORT}
		{/fbvFormSection}
		<div {if $wizardMode}class="pkp_form_hidden"{/if}>
			{fbvFormSection label="common.mailingAddress" for="mailingAddress" group=true description="manager.setup.mailingAddress.description"}
				{fbvElement type="textarea" id="mailingAddress" value=$mailingAddress height=$fbvStyles.height.SHORT}
			{/fbvFormSection}
		</div>
	{/fbvFormArea}

	{if !$wizardMode}
		{fbvFormButtons id="mastheadFormSubmit" submitText="common.save" hideCancel=true}
	{/if}
</form>
