{**
 * index.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Displays the notification settings page and unchecks
 *
 *}
{strip}
{assign var="pageTitle" value="notification.settings"}
{include file="common/header.tpl"}
{/strip}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#notificationSettingsForm').pkpHandler('$.pkp.controllers.form.FormHandler');
	{rdelim});
</script>

<p>{translate key="notification.settingsDescription"}</p>

<form class="pkp_form" id="notificationSettingsForm" method="post" action="{url op="saveSettings"}">

{fbvFormArea id="notificationSettings"}
	{foreach from=$notificationSettingCategories item=notificationSettingCategory}
		<h4>{translate key=$notificationSettingCategory.categoryKey}</h4>
		{foreach from=$notificationSettingCategory.settings item=settingId}
			{assign var="settingName" value=$notificationSettings.$settingId.settingName}
			{assign var="emailSettingName" value=$notificationSettings.$settingId.emailSettingName}
			{assign var="settingKey" value=$notificationSettings.$settingId.settingKey}

			{fbvFormSection title=$settingKey list="true"}
				{if $settingId|in_array:$blockedNotifications}
					{assign var="checked" value="0"}
				{else}
					{assign var="checked" value="1"}
				{/if}
				{if $settingId|in_array:$emailSettings}
					{assign var="emailChecked" value="1"}
				{else}
					{assign var="emailChecked" value="0"}
				{/if}
				{fbvElement type="checkbox" id=$settingName checked=$checked label="notification.allow"}
				{fbvElement type="checkbox" id=$emailSettingName checked=$emailChecked label="notification.email"}
			{/fbvFormSection}
		{/foreach}
	{/foreach}

<br />

{url|assign:cancelUrl page="notification"}
{fbvFormButtons submitText="common.save" cancelUrl=$cancelUrl}

{/fbvFormArea}
</form>

{include file="common/footer.tpl"}

