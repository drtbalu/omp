{**
 * templates/controllers/modals/legacyPlugin/header.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Header for plugins management in modals. This must be used only by plugins
 * that don't have its UI adapted to OMP yet. 
 * FIXME: this must be removed after we modernize all plugins interface.
 *}
{strip}
{if !$pageTitleTranslated}{translate|assign:"pageTitleTranslated" key=$pageTitle}{/if}
{if $pageCrumbTitle}
	{translate|assign:"pageCrumbTitleTranslated" key=$pageCrumbTitle}
{elseif !$pageCrumbTitleTranslated}
	{assign var="pageCrumbTitleTranslated" value=$pageTitleTranslated}
{/if}
{/strip}
{include file="controllers/modals/legacyPlugin/breadcrumbs.tpl" pageCrumbTitleTranslated=$pageCrumbTitleTranslated}
<h2>{$pageTitleTranslated}</h2>