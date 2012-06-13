{**
 * editMiscFile.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Misc. file editor dialog
 *}
{strip}
{translate|escape|assign:"pageTitleTranslated" key="plugins.generic.translator.file.edit" filename=$filename}
{include file="controllers/modals/legacyPlugin/header.tpl" pageTitleTranslated=$pageTitleTranslated}
{/strip}
 
{assign var=filenameEscaped value=$filename|escape:"url"|escape:"url"}
<form id="editor" class="pkp_form" method="post" action="{url op="saveMiscFile" path=$locale|to_array:$filenameEscaped}">

<h3>{translate key="plugins.generic.translator.file.reference"}</h3>
<textarea readonly="true" name="referenceContents" rows="12" cols="80" class="textArea">
{$referenceContents|escape}
</textarea><br/>

<h3>{translate key="plugins.generic.translator.file.translation"}</h3>
<textarea name="translationContents" rows="12" cols="80" class="textArea">
{$translationContents|escape}
</textarea><br/>

<input type="submit" class="button defaultButton" value="{translate key="common.save"}" /> <a href="{url op="edit" path=$locale escape=false}">{translate key="common.cancel"}<a/> <input type="reset" class="button" value="{translate key="plugins.generic.translator.file.reset"}" onclick="return confirm('{translate|escape:"jsparam" key="plugins.generic.translator.file.resetConfirm"}')" /> <input type="button" class="button" value="{translate key="plugins.generic.translator.file.resetToReference"}" onclick="if (confirm('{translate|escape:"jsparam" key="plugins.generic.translator.file.resetConfirm"}')) {literal}{document.getElementById('editor').translationContents.value = document.getElementById('editor').referenceContents.value}{/literal}" />
</form>