{**
 * templates/workflow/editorialLinkActions.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Show editorial link actions.
 *}
{if !empty($editorActions)}
	{if array_intersect(array(ROLE_ID_PRESS_MANAGER, ROLE_ID_SERIES_EDITOR), $userRoles)}
		{assign var="editorDecisionActionsId" value="editor_decision_actions_"|concat:$stageId}
		<script type="text/javascript">
		// Initialise JS handler.
		$(function() {ldelim}
			$('#{$editorDecisionActionsId}').pkpHandler(
				'$.pkp.pages.workflow.EditorDecisionsHandler'
			);
		{rdelim});
		</script>
		<div id="{$editorDecisionActionsId}" class="grid_actions editor_decision_actions">
			{foreach from=$editorActions item=action}
				{include file="linkAction/linkAction.tpl" action=$action contextId=$contextId}
			{/foreach}
		</div>
	{/if}
{/if}
