{**
 * templates/common/header.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Common site header.
 *}
{strip}
{translate|assign:"applicationName" key="common.omp"}
{if !$pageTitleTranslated}{translate|assign:"pageTitleTranslated" key=$pageTitle}{/if}
{if $pageCrumbTitle}
	{translate|assign:"pageCrumbTitleTranslated" key=$pageCrumbTitle}
{elseif !$pageCrumbTitleTranslated}
	{assign var="pageCrumbTitleTranslated" value=$pageTitleTranslated}
{/if}
{/strip}<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset={$defaultCharset|escape}" />
	<title>{$pageTitleTranslated}</title>
	<meta name="description" content="{$metaSearchDescription|escape}" />
	<meta name="keywords" content="{$metaSearchKeywords|escape}" />
	<meta name="generator" content="{$applicationName} {$currentVersionString|escape}" />
	{$metaCustomHeaders}
	{if $displayFavicon}<link rel="icon" href="{$faviconDir}/{$displayFavicon.uploadName|escape:"url"}" />{/if}

	<link rel="stylesheet" type="text/css" media="all" href="{$baseUrl}/styles/lib.css" />
	<link rel="stylesheet" type="text/css" media="all" href="{$baseUrl}/styles/compiled.css" />

	{call_hook|assign:"leftSidebarCode" name="Templates::Common::LeftSidebar"}
	{call_hook|assign:"rightSidebarCode" name="Templates::Common::RightSidebar"}

	{foreach from=$stylesheets item=cssUrl}
		<link rel="stylesheet" href="{$cssUrl}" type="text/css" />
	{/foreach}

	<!-- Base Jquery -->
	{if $allowCDN}
		<script src="http://www.google.com/jsapi" type="text/javascript"></script>
		<script type="text/javascript">{literal}
			// Provide a local fallback if the CDN cannot be reached
			if (typeof google == 'undefined') {
				document.write(unescape("%3Cscript src='{/literal}{$baseUrl}{literal}/lib/pkp/js/lib/jquery/jquery.min.js' type='text/javascript'%3E%3C/script%3E"));
				document.write(unescape("%3Cscript src='{/literal}{$baseUrl}{literal}/lib/pkp/js/lib/jquery/plugins/jqueryUi.min.js' type='text/javascript'%3E%3C/script%3E"));
			} else {
				google.load("jquery", "{/literal}{$smarty.const.CDN_JQUERY_VERSION}{literal}");
				google.load("jqueryui", "{/literal}{$smarty.const.CDN_JQUERY_UI_VERSION}{literal}");
			}
		{/literal}</script>
	{else}
		<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/lib/jquery/jquery.min.js"></script>
		<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/lib/jquery/plugins/jqueryUi.min.js"></script>
	{/if}

	<!-- UI elements (menus, forms, etc) -->
	<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/lib/superfish/hoverIntent.js"></script>
	<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/lib/superfish/superfish.js"></script>

	<!-- Form validation -->
	<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/lib/jquery/plugins/validate/jquery.validate.min.js"></script>
	<script type="text/javascript">{literal}
		$(function(){
			// Include the appropriate validation localization.
			// FIXME: Replace with a smarty template that includes {translate} keys, see #6443.
			jqueryValidatorI18n("{/literal}{$baseUrl}{literal}", "{/literal}{$currentLocale}{literal}");
		});
	{/literal}</script>

	<!-- Plupload -->
	<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/lib/plupload/plupload.full.js"></script>
	<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/lib/plupload/jquery.ui.plupload/jquery.ui.plupload.js"></script>

	{* FIXME: Replace with a smarty template that includes {translate} keys, see #6443. *}
	{if $currentLocale !== 'en_US'}<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/lib/plupload/i18n/{$currentLocale|escape}.js"></script>{/if}

	<!-- Constants for JavaScript -->
	{include file="common/jsConstants.tpl"}

	<!-- Default global locale keys for JavaScript -->
	{include file="common/jsLocaleKeys.tpl" }

	<!-- Compiled scripts -->
	{if $useMinifiedJavaScript}
		<script type="text/javascript" src="{$baseUrl}/lib/pkp/js/pkp.min.js"></script>
	{else}
		{include file="common/minifiedScripts.tpl"}
	{/if}

	{* FIXME: This should eventually be moved into a theme plugin. *}
	<link rel="stylesheet" type="text/css" media="all" href="{$baseUrl}/lib/pkp/styles/themes/default/theme.css" />
	<link rel="stylesheet" type="text/css" media="all" href="{$baseUrl}/lib/pkp/js/lib/jquery/plugins/orbit-1.3.0.css" />

	{$additionalHeadData}
</head>
<body>
	<script type="text/javascript">
		// Initialise JS handler.
		$(function() {ldelim}
			$('body').pkpHandler(
				'$.pkp.controllers.SiteHandler',
				{ldelim}
					{include file="core:controllers/notification/notificationOptions.tpl"},
				{rdelim});
		{rdelim});
	</script>
	<div class="pkp_structure_page">
		<div class="pkp_structure_head">
			<div class="pkp_structure_content">
				<div class="unit size1of5">
					<div class="pkp_structure_masthead">
						<h1 style="margin: 0; padding: 0;">
							{if $displayPageHeaderLogo && is_array($displayPageHeaderLogo)}
								<img src="{$publicFilesDir}/{$displayPageHeaderLogo.uploadName|escape:"url"}" width="{$displayPageHeaderLogo.width|escape}" height="{$displayPageHeaderLogo.height|escape}" {if $displayPageHeaderLogoAltText != ''}alt="{$displayPageHeaderLogoAltText|escape}"{else}alt="{translate key="common.pageHeaderLogo.altText"}"{/if} />
							{/if}
							{if $displayPageHeaderTitle && is_array($displayPageHeaderTitle)}
								<img src="{$publicFilesDir}/{$displayPageHeaderTitle.uploadName|escape:"url"}" width="{$displayPageHeaderTitle.width|escape}" height="{$displayPageHeaderTitle.height|escape}" {if $displayPageHeaderTitleAltText != ''}alt="{$displayPageHeaderTitleAltText|escape}"{else}alt="{translate key="common.pageHeader.altText"}"{/if} />
							{elseif $displayPageHeaderTitle}
								{$displayPageHeaderTitle}
							{elseif $alternatePageHeader}
								{$alternatePageHeader}
							{else}
								<img src="{$baseUrl}/templates/images/structure/omp_logo.png" alt="{$applicationName|escape}" title="{$applicationName|escape}" width="180" height="90" />
							{/if}
						</h1>
					</div><!-- pkp_structure_masthead -->
				</div>
				<div class="unit size4of5 lastUnit">
					<div class="pkp_structure_navigation">
						{include file="common/sitenav.tpl"}
						{include file="common/localnav.tpl"}
					</div>
				</div>
			</div><!-- pkp_structure_content -->
		</div><!-- pkp_structure_head -->
		<div class="pkp_structure_body">
			<div class="pkp_structure_content">
				<!-- TODO: replace with breadcrumbs and wire up search -->
				<div class="unit size1of5">
					<div class="pkp_structure_search">&nbsp;</div>
				</div>
				<div class="unit size4of5 lastUnit">
					{include file="common/breadcrumbs.tpl"}
					{include file="common/search.tpl"}
				</div>

				{if !$leftSidebarCode && !$rightSidebarCode}
					{* Temporary fix for #7258 *}
					<div class="pkp_structure_nosidebar">
					</div>
				{/if}

				{if $leftSidebarCode}
					<div class="pkp_structure_sidebar pkp_structure_sidebar_left mod simple">
						{$leftSidebarCode}
					</div><!-- pkp_structure_sidebar_left -->
				{/if}
				{if $rightSidebarCode}
					<div class="pkp_structure_sidebar pkp_structure_sidebar_right mod simple">
						{$rightSidebarCode}
					</div><!-- pkp_structure_sidebar_right -->
				{/if}
				<script type="text/javascript">
					// Attach the JS page handler to the main content wrapper.
					$(function() {ldelim}
						$('div.pkp_structure_main').pkpHandler('$.pkp.controllers.PageHandler');
					{rdelim});
				</script>

				<div class="pkp_structure_main">
					{** allow pages to provide their own titles **}
					{if !$suppressPageTitle}
						<h2 class="title_left">{$pageTitleTranslated}</h2>
					{/if}
