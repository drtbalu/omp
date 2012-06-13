{**
 * controllers/tab/settings/affiliation/form/affiliationForm.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Contact management form.
 *
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#affiliationForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="affiliationForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="tab.settings.PressSettingsTabHandler" op="saveFormData" tab="affiliationAndSupport"}">

	{fbvFormArea id="sponsorsFormArea"}
		{fbvFormSection label="manager.setup.sponsors" description="manager.setup.sponsors.description"}
			{url|assign:sponsorGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.sponsor.sponsorGridHandler" op="fetchGrid"}
			{load_url_in_div id="sponsorGridDiv" url=$sponsorGridUrl}
			{fbvElement type="textarea" multilingual=true id="sponsorNote" value=$sponsorNote rich=true label="manager.setup.sponsors.note"}
		{/fbvFormSection}
		{fbvFormSection label="manager.setup.contributors" description="manager.setup.contributors.description"}
			{url|assign:contributorGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.settings.contributor.ContributorGridHandler" op="fetchGrid"}
			{load_url_in_div id="contributorGridDiv" url=$contributorGridUrl}
			{fbvElement type="textarea" multilingual=true id="contributorNote" value=$contributorNote rich=true label="manager.setup.contributors.note"}
		{/fbvFormSection}
	{/fbvFormArea}

	{fbvFormButtons id="affiliationFormSubmit" submitText="common.save" hideCancel=true}
</form>