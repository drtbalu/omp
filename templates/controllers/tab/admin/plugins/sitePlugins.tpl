{**
 * templates/controllers/tab/admin/plugins/sitePlugins.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * List available plugins.
 *}

 <!-- Plugin grid -->
{url|assign:pluginGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.admin.plugins.AdminPluginGridHandler" op="fetchGrid"}
{load_url_in_div id="pluginGridContainer" url="$pluginGridUrl"}
 