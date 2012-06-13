{**
 * controllers/grid/plugins/pluginGridFilter.tpl
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Filter template for plugin grid.
 *}
<script type="text/javascript">
	// Attach the form handler to the form.
	$('#pluginSearchForm').pkpHandler('$.pkp.controllers.form.ClientFormHandler');
</script>
<form class="pkp_form" id="pluginSearchForm" action="{url router=$smarty.const.ROUTE_COMPONENT op="fetchGrid"}" method="post">
	{fbvFormArea id="userSearchFormArea"}
		{fbvFormSection title="common.search" for="search"}
			{fbvElement type="text" id="pluginName" value=$filterSelectionData.pluginName size=$fbvStyles.size.SMALL inline=true}
			{fbvElement type="select" id="category" from=$filterData.categories selected=$filterSelectionData.category translate=false size=$fbvStyles.size.SMALL inline=true}
			{fbvFormButtons hideCancel=true submitText="common.search"}
		{/fbvFormSection}
	{/fbvFormArea}
</form>
