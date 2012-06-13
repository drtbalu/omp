{**
 * templates/user/lostPassword.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Password reset form.
 *}
{strip}
{assign var="registerOp" value="register"}
{assign var="registerLocaleKey" value="user.login.registerNewAccount"}
{assign var="pageTitle" value="user.login.resetPassword"}
{include file="common/header.tpl"}
{/strip}

{if !$registerLocaleKey}
	{assign var="registerLocaleKey" value="user.login.registerNewAccount"}
{/if}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#lostPasswordForm').pkpHandler('$.pkp.controllers.form.FormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="lostPasswordForm" action="{url page="login" op="requestResetPassword"}" method="post">
<p>{translate key="user.login.resetPasswordInstructions"}</p>
{if $error}
	<p><span class="pkp_form_error">{translate key="$error"}</span></p>
{/if}
{fbvFormArea id="lostPassword"}
	{fbvFormSection label="user.login.registeredEmail"}
		{fbvElement type="text" id="email" value=$username maxlength="90" size=$fbvStyles.size.MEDIUM}
	{/fbvFormSection}
	{if !$hideRegisterLink}
		{url|assign:cancelUrl page="user" op=$registerOp}
		{fbvFormButtons cancelUrl=$cancelUrl cancelText=$registerLocaleKey submitText="user.login.resetPassword"}
	{else}
		{fbvFormButtons hideCancel=true submitText="user.login.resetPassword"}
	{/if}
{/fbvFormArea}
</form>

{include file="common/footer.tpl"}
