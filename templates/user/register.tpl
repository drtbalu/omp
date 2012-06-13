{**
 * templates/user/register.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * User registration form.
 *}
{strip}
{assign var="pageTitle" value="user.register"}
{include file="common/header.tpl"}
{/strip}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#register').pkpHandler('$.pkp.controllers.form.FormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="register" method="post" action="{url op="registerUser"}">

<p>{translate key="user.register.completeForm"}</p>

{if !$implicitAuth}
	{if !$existingUser}
		{url|assign:"url" page="user" op="register" existingUser=1}
		<p>{translate key="user.register.alreadyRegisteredOtherPress" registerUrl=$url}</p>
	{else}
		{url|assign:"url" page="user" op="register"}
		<p>{translate key="user.register.notAlreadyRegisteredOtherPress" registerUrl=$url}</p>
		<input type="hidden" name="existingUser" value="1"/>
	{/if}

	{if $existingUser}
		<p>{translate key="user.register.loginToRegister"}</p>
	{/if}
{/if}{* !$implicitAuth *}

{if $source}
	<input type="hidden" name="source" value="{$source|escape}" />
{/if}

{fbvFormArea id="registration"}

{if !$implicitAuth}
	{fbvFormSection title="user.accountInformation"}
		{fbvElement type="text" label="user.username" id="username" value=$username required=true size=$fbvStyles.size.MEDIUM}
		{fbvElement type="text" label="user.password" id="password" value=$password required=true password=true size=$fbvStyles.size.MEDIUM}
		{if !$existingUser}
			{fbvElement type="text" label="user.repeatPassword" id="password2" value=$password2 required=true password=true size=$fbvStyles.size.MEDIUM}
		{/if}{* !$existingUser *}
	{/fbvFormSection}

	{if !$existingUser}
		{fbvFormSection title="common.name"}
		{fbvElement type="text" label="user.salutation" id="salutation" value=$salutation size=$fbvStyles.size.SMALL inline="true"}
			{fbvElement type="text" label="user.firstName" id="firstName" required=true value=$firstName size=$fbvStyles.size.SMALL inline=true}
			{fbvElement type="text" label="user.middleName" id="middleName" value=$middleName size=$fbvStyles.size.SMALL inline=true}
			{fbvElement type="text" label="user.lastName" id="lastName" required=true value=$lastName size=$fbvStyles.size.SMALL inline=true}
			{fbvElement type="text" label="user.suffix" id="suffix" value=$suffix  size=$fbvStyles.size.SMALL inline="true"}
			{fbvElement type="text" label="user.initials" id="initials" value=$initials size=$fbvStyles.size.SMALL inline=true}
		{/fbvFormSection}

		{fbvFormSection title="user.gender" for="gender"  size=$fbvStyles.size.SMALL}
			{fbvElement type="select" from=$genderOptions selected=$gender|escape id="gender" translate=true}
		{/fbvFormSection}

		{fbvFormSection title="user.phone" for="phone"}
			{fbvElement type="text" id="phone" value=$phone|escape size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}

		{fbvFormSection title="user.fax" for="fax"}
			{fbvElement type="text" id="fax" value=$fax|escape size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}

		{fbvFormSection title="user.email" for="email" required=true}
			{fbvElement type="text" id="email" value=$email|escape size=$fbvStyles.size.MEDIUM} <br />
			{fbvElement type="text" label="user.confirmEmail" id="confirmEmail" value=$confirmEmail|escape size=$fbvStyles.size.MEDIUM}
		 	{if $privacyStatement}<a class="action" href="#privacyStatement">{translate key="user.register.privacyStatement"}</a>{/if}
		{/fbvFormSection}

		{fbvFormSection title="user.url" for="userUrl"}
			{fbvElement type="text" id="userUrl" value=$userUrl|escape size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}

		{fbvFormSection title="user.affiliation" for="affiliation"}
			{fbvElement type="textarea" id="affiliation" multilingual=true value=$affiliation|escape label="user.affiliation.description" size=$fbvStyles.size.MEDIUM}<br/>
		{/fbvFormSection}

		{fbvFormSection title="user.mailingAddress" for="mailingAddress"}
			{fbvElement type="textarea" id="mailingAddress" value=$mailingAddress|escape rich=true size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}

		{fbvFormSection title="user.biography" for="biography"}
			{fbvElement type="textarea" id="biography" name="biography" multilingual=true value=$biography|escape rich=true size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}

		{fbvFormSection title="user.signature" for="signature"}
			{fbvElement type="textarea" id="signature" name="signature" multilingual=true value=$signature|escape size=$fbvStyles.size.MEDIUM}
		{/fbvFormSection}

		{fbvFormSection title="common.country" for="country" size=$fbvStyles.size.SMALL required="true"}
			{fbvElement type="select" from=$countries selected=$country translate=false id="country" defaultValue="" defaultLabel="" required=true}
		{/fbvFormSection}

		{if count($availableLocales) > 1}
		{fbvFormSection title="user.workingLanguages" list=true}
			{foreach from=$availableLocales key=localeKey item=localeName}
				{assign var="controlId" value=userLocales-$localeKey}
				{if in_array($localeKey, $userLocales)}
					{fbvElement type="checkbox" name="userLocales[]" id=$controlId value="1" label=$localeName translate=false checked="checked"}
				{else}
					{fbvElement type="checkbox" name="userLocales[]" id=$controlId value="1" label=$localeName translate=false}
				{/if}
			{/foreach}
		{/fbvFormSection}
		{/if}{* count($availableLocales) > 1 *}

		{fbvFormSection label="user.sendPassword" list=true}
			{if $sendPassword}
				{fbvElement type="checkbox" id="sendPassword" value="1" label="user.sendPassword.description" checked="checked"}
			{else}
				{fbvElement type="checkbox" id="sendPassword" value="1" label="user.sendPassword.description"}
			{/if}
		{/fbvFormSection}
	{/if} {* !$existingUser *}
{/if}{* !$implicitAuth *}

	{if $currentPress && ($allowRegAuthor || $allowRegReviewer)}
		{fbvFormSection title="user.register.registerAs" list=true}
			{if $allowRegAuthor}
				{iterate from=authorUserGroups item=userGroup}
					{assign var="userGroupId" value=$userGroup->getId()}
					{if $authorGroup == $userGroupId}{assign var="checked" value=true}{else}{assign var="checked" value=false}{/if}
					{fbvElement type="radio" id="authorGroup-$userGroupId" name="authorGroup" value=$userGroupId label=$userGroup->getLocalizedName() translate=false checked=$checked}
				{/iterate}
			{/if}
			<div class="pkp_helpers_clear"></div>
			{if $allowRegReviewer}
				{iterate from=reviewerUserGroups item=userGroup}
					{assign var="userGroupId" value=$userGroup->getId()}
					{if $reviewerGroup[$userGroupId] != ''}{assign var="checked" value=true}{else}{assign var="checked" value=false}{/if}
					{fbvElement type="checkbox" id="reviewerGroup-$userGroupId" name="reviewerGroup[$userGroupId]" label=$userGroup->getLocalizedName() checked=$checked translate=false}
				{/iterate}
			{/if}
		{/fbvFormSection}
		{if $allowRegReviewer}
			{fbvFormSection id="reviewerInterestsContainer" label="user.register.reviewerInterests"}
				{fbvElement type="interests" id="interests" interestsKeywords=$interestsKeywords interestsTextOnly=$interestsTextOnly}
			{/fbvFormSection}
		{/if}
	{/if}

	{if !$implicitAuth && !$existingUser && $captchaEnabled}
		<li>
		{fieldLabel name="captcha" required=true key="common.captchaField" class="desc"}
		<span>
			<img src="{url page="user" op="viewCaptcha" path=$captchaId}" alt="{translate key="common.captchaField.altText"}" /><br />
			<p>{translate key="common.captchaField.description"}</p>
			<input name="captcha" id="captcha" value="" size="20" maxlength="32" class="field text" />
			<input type="hidden" name="captchaId" value="{$captchaId|escape:"quoted"}" />
		</span>
		</li>
	{/if}
{/fbvFormArea}
{url|assign:"url" page="index" escape=false}
{fbvFormButtons submitText="user.register" cancelUrl=$url}

{if ! $implicitAuth}
	<p><span class="formRequired">{translate key="common.requiredField"}</span></p>
{/if}{* !$implicitAuth *}

<div id="privacyStatement">
{if $privacyStatement}
	<h3>{translate key="user.register.privacyStatement"}</h3>
	<p>{$privacyStatement|nl2br}</p>
{/if}
</div>

</form>

{include file="common/footer.tpl"}

