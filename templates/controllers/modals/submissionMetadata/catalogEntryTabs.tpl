{**
 * controllers/modals/submissionMetadata/form/catalogEntryTabs.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display a submission's catalog entry form.
 *
 *}

<script type="text/javascript">
	// Attach the JS file tab handler.
	$(function() {ldelim}
		$('#newCatalogEntryTabs').pkpHandler(
				'$.pkp.controllers.tab.catalogEntry.CatalogEntryTabHandler',
				{ldelim}
					{if $selectedTab}selected:{$selectedTab},{/if}
					{if $selectedFormatId}selectedFormatId:{$selectedFormatId},{/if}
					{if $tabsUrl}tabsUrl:'{$tabsUrl}',{/if}
					{if $tabContentUrl}tabContentUrl:'{$tabContentUrl}',{/if}
					emptyLastTab: true,
				{rdelim});
	{rdelim});
</script>
<div id="newCatalogEntryTabs">
	<p>{translate key="catalog.manage.entryDescription"}</p>
	<ul>
		<li>
			<a title="submission" href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.catalogEntry.CatalogEntryTabHandler" tab="submission" op="submissionMetadata" monographId="$monographId" stageId=$stageId tabPos="0"}">{translate key="submission.catalogEntry.monographMetadata"}</a>
		</li>
		<li>
			<a title="catalog" href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.catalogEntry.CatalogEntryTabHandler" tab="catalog" op="catalogMetadata" monographId="$monographId" stageId=$stageId tabPos="1"}">{translate key="submission.catalogEntry.catalogMetadata"}</a>
		</li>
		{counter start=2 assign="counter"}
		{foreach from=$publicationFormats item=format}
			<li>
				<a id="publication{$format->getId()|escape}"
					href="{url router=$smarty.const.ROUTE_COMPONENT component="tab.catalogEntry.CatalogEntryTabHandler"
					tab="publication"|concat:$format->getId()
					op="publicationMetadata"
					publicationFormatId=$format->getId()
					monographId=$monographId
					stageId=$stageId
					tabPos=$counter}">{$format->getLocalizedTitle()|escape}</a>
			</li>
			{counter} {* increment our counter, assign to $counter variable *}
		{/foreach}
</ul>
