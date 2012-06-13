{**
 * templates/catalog/index.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Entry page for the public-facing catalog
 *}
{strip}
{assign var="pageTitle" value="navigation.catalog"}
{include file="common/header.tpl"}
{/strip}

{* Include the carousel view of featured content *}
{if $featuredMonographIds|@count}
	{include file="catalog/carousel.tpl" publishedMonographs=$publishedMonographs featuredMonographIds=$featuredMonographIds}
{/if}

{* Include the full monograph list *}
{include file="catalog/monographs.tpl" publishedMonographs=$publishedMonographs}

{include file="common/footer.tpl"}
