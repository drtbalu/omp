{**
 * templates/controllers/grid/settings/preparedEmails/form/emailTemplateForm.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Form to edit or create a prepared email
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#managePreparedEmailForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" method="post" id="managePreparedEmailForm" action="{url op="updatePreparedEmail"}">
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="emailTemplateFormNotification"}

	{if $isNewTemplate}
		{fbvFormArea id="emailTemplateData"}
			<h3>{translate key="manager.emails.data"}</h3>
			{fbvFormSection title="common.name" required="true" for="emailKey"}
				{fbvElement type="text" name="emailKey" id="emailKey" maxlength="120"}
			{/fbvFormSection}
		{/fbvFormArea}
	{else}
		{fbvFormArea id="emailTemplateData"}
			<h3>{translate key="manager.emails.data"}</h3>
			{if $description}
				{fbvFormSection title="common.description"}
					<p>{$description|escape}</p>
				{/fbvFormSection}
			{/if}

			{fbvFormSection title="manager.emails.emailKey" for="emailKey"}
				{fbvElement type="text" name="emailKey" value=$emailKey id="emailKey" disabled=true}
				<input type="hidden" name="emailKey" value="{$emailKey|escape}" />
			{/fbvFormSection}
		{/fbvFormArea}
	{/if}

	{fbvFormArea id="emailTemplateDetails"}
		<h3>{translate key="manager.emails.details"}</h3>
		{fbvFormSection title="email.subject" required="true" for="subject"}
			{fbvElement type="text" multilingual="true" name="subject" id="subject" value=$subject maxlength="120"}
		{/fbvFormSection}

		{fbvFormSection title="email.body" required="true" for="body"}
			{fbvElement type="textarea" multilingual="true" name="body" id="body" value=$body size=$fbvStyles.size.LARGE}
		{/fbvFormSection}
	{/fbvFormArea}

	{fbvFormButtons submitText="common.save"}
</form>
