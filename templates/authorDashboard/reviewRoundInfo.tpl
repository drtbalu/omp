{**
 * templates/authorDashboard/reviewRoundInfo.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Display monograph review details in author dashboard page.
 *}

<!--  Display round status -->
{include file="controllers/notification/inPlaceNotification.tpl" notificationId="reviewRoundNotification_"|concat:$reviewRoundId requestOptions=$reviewRoundNotificationRequestOptions}

<!-- Display editor's message to the author -->
{include file="authorDashboard/monographEmail.tpl" monographEmails=$monographEmails textAreaIdPrefix="reviewRoundEmail"}

<!-- Display review attachments grid -->
{if $showReviewAttachments}
	{** need to use the stage id in the div because two of these grids can appear in the dashboard at the same time (one for each stage). *}
	{url|assign:reviewAttachmentsGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.attachment.AuthorReviewAttachmentsGridHandler" op="fetchGrid" monographId=$monograph->getId() stageId=$stageId reviewRoundId=$reviewRoundId escape=false}
	{load_url_in_div id="reviewAttachmentsGridContainer-`$stageId`-`$reviewRoundId`" url="$reviewAttachmentsGridUrl"}

	{url|assign:revisionsGridUrl router=$smarty.const.ROUTE_COMPONENT component="grid.files.review.AuthorReviewRevisionsGridHandler" op="fetchGrid" monographId=$monograph->getId() stageId=$stageId reviewRoundId=$reviewRoundId escape=false}
	{load_url_in_div id="revisionsGrid-`$stageId`-`$reviewRoundId`" url=$revisionsGridUrl}
{/if}