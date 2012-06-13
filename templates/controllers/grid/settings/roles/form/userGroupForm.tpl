{**
 * templates/controllers/grid/settings/roles/form/userGroupForm.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Form to edit or create a user group
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#userGroupForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="userGroupForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.settings.roles.UserGroupGridHandler" op="updateUserGroup"}">
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="userGroupFormNotification"}

	{if $userGroupId}
		<input type="hidden" id="userGroupId" name="userGroupId" value="{$userGroupId|escape}" />
	{/if}
	{fbvFormArea id="userGroupDetails"}
		<h3>{translate key="settings.roles.roleDetails"}</h3>
		{fbvFormSection title="settings.roles.from" for="roleId" required="true"}
			{fbvElement type="select" name="roleId" from=$roleOptions id="roleId" selected=$roleId disabled=$disableRoleSelect}
		{/fbvFormSection}
		{fbvFormSection title="settings.roles.roleName" for="name" required="true"}
			{fbvElement type="text" multilingual="true" name="name" value=$name id="name"}
		{/fbvFormSection}
		{fbvFormSection title="settings.roles.roleAbbrev" for="abbrev" required="true"}
			{fbvElement type="text" multilingual="true" name="abbrev" value=$abbrev id="abbrev"}
		{/fbvFormSection}
	{/fbvFormArea}
	<div id="userGroupStageContainer" class="full left">
		{fbvFormArea id="userGroupRoles"}
				{fbvFormSection title="grid.roles.stageAssignment" for="assignedStages[]" required="true" list="true"}
					{fbvElement type="checkboxgroup" name="assignedStages" id="assignedStages" from=$stages selected=$assignedStages}
				{/fbvFormSection}
				<label for="stages[]" class="error pkp_form_hidden">{translate key=settings.roles.stageIdRequired}</label>
		{/fbvFormArea}
	</div>
	{fbvFormButtons}
</form>
