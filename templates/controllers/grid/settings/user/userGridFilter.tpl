{**
 * controllers/grid/settings/user/userGridFilter.tpl
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Filter template for user grid.
 *}
<script type="text/javascript">
	// Attach the form handler to the form.
	$('#userSearchForm').pkpHandler('$.pkp.controllers.form.ClientFormHandler');
</script>
<form class="pkp_form" id="userSearchForm" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.settings.user.UserGridHandler" op="fetchGrid"}" method="post">
	{fbvFormArea id="userSearchFormArea"}
		{fbvFormSection title="common.search" required="true" for="search"}
			{fbvElement type="text" name="search" id="search" value=$filterSelectionData.search size=$fbvStyles.size.LONG}
		{/fbvFormSection}
		{fbvFormSection size=$fbvStyles.size.MEDIUM inline=true}
			{fbvElement type="select" name="userGroup" id="userGroup" from=$filterData.userGroupOptions selected=$filterSelectionData.userGroup translate=false}
		{/fbvFormSection}
		{fbvFormSection list=true inline=true}
			{if $filterSelectionData.includeNoRole}{assign var="checked" value="checked"}{/if}
			{fbvElement type="checkbox" name="includeNoRole" id="includeNoRole" value="1" checked=$checked label="user.noRoles.selectUsersWithoutRoles" translate="true"}
		{/fbvFormSection}
		{fbvFormButtons hideCancel=true submitText="common.search"}
	{/fbvFormArea}
</form>
<div class="pkp_helpers_clear">&nbsp;</div>
