{**
 * templates/workflow/productionFormatsTab.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Production workflow stage tabs.
 *}

<script type="text/javascript">
// Attach the JS file tab handler.
$(function() {ldelim}
	$('#publicationFormatTabs').pkpHandler(
		'$.pkp.controllers.tab.publicationFormat.PublicationFormatsTabHandler',
		{ldelim}
			tabsUrl:'{url|escape:javascript router=$smarty.const.ROUTE_PAGE
				op='productionFormatsTab'
				monographId=$monograph->getId()
				stageId=$smarty.const.WORKFLOW_STAGE_ID_PRODUCTION escape=false}',
			{if $currentFormatTabId}currentFormatTabId: '{$currentFormatTabId}',{/if}
			emptyLastTab: true,
		{rdelim}
	);
{rdelim});
</script>
<div id="publicationFormatTabs">
	<ul>
		{foreach from=$publicationFormats item=format}
			<li>{* no need to bother with the published test, since unpublished monographs will not have formats assigned to them *}
				<a id="publication{$format->getId()|escape}"
					href="{url router=$smarty.const.ROUTE_PAGE op="fetchPublicationFormat"
					publicationFormatId=$format->getId()
					monographId=$format->getMonographId()
					stageId=$smarty.const.WORKFLOW_STAGE_ID_PRODUCTION}">{$format->getLocalizedTitle()|escape}</a>
			</li>
		{/foreach}
	</ul>
</div>

