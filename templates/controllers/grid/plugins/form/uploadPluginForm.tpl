{**
 * controllers/grid/plugins/uploadPluginForm.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Form to upload plugin files.
 *}
<script type="text/javascript">
	$(function() {ldelim}
		// Attach the upload form handler.
		$('#uploadPluginForm').pkpHandler(
			'$.pkp.controllers.form.FileUploadFormHandler',
			{ldelim}
				$uploader: $('#plupload'),
				uploaderOptions: {ldelim}
					uploadUrl: '{url|escape:javascript router=$smarty.const.ROUTE_COMPONENT op="uploadPlugin" function=$function}',
					baseUrl: '{$baseUrl|escape:javascript}'
				{rdelim}
			{rdelim});
	{rdelim});
</script>

<form class="pkp_form" id="uploadPluginForm" action="{url router=$smarty.const.ROUTE_COMPONENT op="saveUploadPlugin" function=$function}" method="post">
	{include file="controllers/notification/inPlaceNotification.tpl" notificationId="uploadPluginNotification"}
	
	{fbvFormArea id="file"}
		{if $function == 'install'}
			<p>{translate key="manager.plugins.installDescription"}</p>
		{elseif $function == 'upgrade'}
			<p>{translate key="manager.plugins.upgradeDescription"}</p>
		{/if}
		{fbvFormSection title="manager.plugins.uploadPluginDir" required=true}
			{fbvElement type="hidden" id="temporaryFileId" value=""}
			{* The uploader widget *}
			<div id="plupload"></div>
		{/fbvFormSection}
	{/fbvFormArea}
	
	{fbvFormButtons id="mastheadFormSubmit" submitText="common.save"}
</form>