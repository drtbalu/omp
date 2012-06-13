{**
 * templates/controllers/grid/content/announcements/form/announcementForm.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Announcement form to read/create/edit announcements.
 *}

<script type="text/javascript">
	$(function() {ldelim}
		// Attach the form handler.
		$('#announcementForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="announcementForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT component="grid.content.announcements.ManageAnnouncementGridHandler" op="updateAnnouncement"}">
	{if $readOnly}
		{* Read only announcement *}

		{fbvFormArea id="announcementInfo"}
			{fbvFormSection title="common.title"}
				{$announcement->getLocalizedTitleFull()|escape}
			{/fbvFormSection}
			{fbvFormSection title="announcement.posted"}
				{$announcement->getDatePosted()|escape}
			{/fbvFormSection}
			{fbvFormSection title="common.description"}
				{$announcement->getLocalizedDescriptionShort()|strip_unsafe_html}<br />
				{$announcement->getLocalizedDescription()|strip_unsafe_html}
			{/fbvFormSection}
		{/fbvFormArea}
	{else}
		{* Editable announcement *}

		{include file="controllers/notification/inPlaceNotification.tpl" notificationId="announcementFormNotification"}
		{fbvFormArea id="announcementInfo"}
			{if $announcement}
				<input type="hidden" name="announcementId" value="{$announcement->getId()|escape}" />
			{/if}
			{if $announcementTypes}
				{fbvElement type="select" id="typeId" from=$announcementTypes selected=$selectedTypeId label="manager.announcements.form.typeId" translate=false}
			{/if}
			{fbvFormSection title="manager.announcements.form.title" for="title" required="true"}
				{fbvElement type="text" multilingual="true" id="title" value=$title maxlength="255"}
			{/fbvFormSection}
			{fbvFormSection title="manager.announcements.form.descriptionShort" for="descriptionShort" required="true"}
				{fbvElement type="textarea" multilingual="true" id="descriptionShort" value=$descriptionShort label="manager.announcements.form.descriptionShortInstructions" rich=true height=$fbvStyles.height.SHORT}
			{/fbvFormSection}
			{fbvFormSection title="manager.announcements.form.description" for="description"}
				{fbvElement type="textarea" multilingual="true" id="description" value=$description label="manager.announcements.form.descriptionInstructions" rich=true}
			{/fbvFormSection}
			<script type="text/javascript">
				$('input[id^="dateExpire"]').datepicker({ldelim} dateFormat: 'yy-mm-dd' {rdelim});
			</script>
			{fbvFormSection title="manager.announcements.form.dateExpire" for="dataExpire"}
				{fbvElement type="text" id="dateExpire" value=$dateExpire label="manager.announcements.form.dateExpireInstructions" size=$fbvStyles.size.MEDIUM}
			{/fbvFormSection}
		{/fbvFormArea}
		{fbvFormButtons id="announcementFormSubmit" submitText="common.save"}
	{/if}
</form>