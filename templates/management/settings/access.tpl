{**
 * templates/management/settings/access.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Access and Security page.
 *}

{strip}
{assign var="pageTitle" value="navigation.access"}
{include file="common/header.tpl"}
{/strip}

<script type="text/javascript">
	// Attach the JS file tab handler.
	$(function() {ldelim}
		$('#accessTabs').pkpHandler(
				'$.pkp.controllers.TabHandler');
	{rdelim});
</script>
<div id="accessTabs">
	<ul>
		<li><a href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.AccessSettingsTabHandler" op="showTab" tab="users"}">{translate key="manager.users"}</a></li>
		<li><a href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.AccessSettingsTabHandler" op="showTab" tab="roles"}">{translate key="manager.roles"}</a></li>
		<li><a href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.AccessSettingsTabHandler" op="showTab" tab="siteAccessOptions"}">{translate key="manager.siteAccessOptions.siteAccessOptions"}</a></li>
	</ul>
</div>

{include file="common/footer.tpl"}
