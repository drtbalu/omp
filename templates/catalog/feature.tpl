{**
 * templates/catalog/feature.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display a single random feature in the catalog.
 *}

{* Get a random feature. *}
{assign var=featureCount value=$featuredMonographIds|@count}
{assign var=randomOffset value=0|rand:$featureCount-1}
{assign var=randomCounter value=0}
{foreach from=$featuredMonographIds key=monographId item=featureSeq}
	{if $randomCounter == $randomOffset}
		{assign var=featuredMonograph value=$publishedMonographs.$monographId}
	{/if}
	{assign var=randomCounter value=$randomCounter+1}
{/foreach}
{* $featuredMonograph should now specify the random monograph, if any. *}

{if $featuredMonograph}
<div class="pkp_catalog_feature">
	<h3>{translate key="catalog.feature"}</h3>

	<div class="pkp_catalog_featureSpecs">
		<img src="{url router=$smarty.const.ROUTE_COMPONENT component="submission.CoverHandler" op="cover" monographId=$featuredMonograph->getId()}" />
		<!-- FIXME: Put specs for the feature here. -->
	</div>

	<div class="pkp_catalog_featureDetails">
		<div class="pkp_catalog_feature_title">{$featuredMonograph->getLocalizedTitle()|strip_unsafe_html}</div>
		<div class="pkp_catalog_feature_author">{$featuredMonograph->getAuthorString()|escape}</div>
		<div class="pkp_catalog_feature_abstract">{$featuredMonograph->getLocalizedAbstract()|strip_unsafe_html}</div>
	</div>
</div>
{/if}{* $featuredMonograph *}
