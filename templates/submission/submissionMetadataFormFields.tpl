{**
 * submission/submissionMetadataFormFields.tpl
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Submission's metadata form fields. To be included in any form that wants to handle
 * submission metadata. Use classes/submission/SubmissionMetadataFormImplementation.inc.php
 * to handle this fields data.
 *}

{fbvFormArea id="generalInformation"}
	<p>{translate key="common.prefixAndTitle.tip"}</p>
	{fbvFormSection for="title" title="common.prefix" inline="true" size=$fbvStyles.size.MEDIUM}
		{fbvElement type="text" multilingual=true id="prefix" value="$prefix" disabled=$readOnly maxlength="32"}
	{/fbvFormSection}
	{fbvFormSection for="title" title="monograph.title" inline="true" size=$fbvStyles.size.MEDIUM}
		{fbvElement type="text" multilingual=true name="title" id="title" value=$title disabled=$readOnly maxlength="255"}
	{/fbvFormSection}
	{fbvFormSection title="monograph.subtitle" for="subtitle"}
		{fbvElement type="text" multilingual=true name="subtitle" id="subtitle" value=$subtitle disabled=$readOnly}
	{/fbvFormSection}
	{fbvFormSection title="submission.submit.briefSummary" for="abstract"}
		{fbvElement type="textarea" multilingual=true name="abstract" id="abstract" value=$abstract rich=true disabled=$readOnly}
	{/fbvFormSection}
{/fbvFormArea}

{fbvFormArea id="coverageInformation" title="monograph.coverage" border="true"}
	{fbvFormSection title="monograph.coverage.chron" for="coverageChron" description="monograph.coverage.tip"}
		{fbvElement type="text" multilingual=true name="coverageChron" id="coverageChron" value=$coverageChron maxlength="255" disabled=$readOnly}
	{/fbvFormSection}
	{fbvFormSection title="monograph.coverage.geo" for="coverageGeo"}
		{fbvElement type="text" multilingual=true name="coverageGeo" id="coverageGeo" value=$coverageGeo maxlength="255" disabled=$readOnly}
	{/fbvFormSection}
	{fbvFormSection title="monograph.coverage.sample" for="coverageSample"}
		{fbvElement type="text" multilingual=true name="coverageSample" id="coverageSample" value=$coverageSample maxlength="255" disabled=$readOnly}
	{/fbvFormSection}
{/fbvFormArea}

{fbvFormArea id="additionalDublinCore"}
	{fbvFormSection label="monograph.type" for="type" description="monograph.title.tip"}
		{fbvElement type="text" multilingual=true name="type" id="type" value=$type maxlength="255" disabled=$readOnly}
	{/fbvFormSection}
	{fbvFormSection label="monograph.subjectClass" for="subjectClass" description="monograph.subjectClass.tip"}
		{fbvElement type="text" multilingual=true name="subjectClass" id="subjectClass" value=$subjectClass maxlength="255" disabled=$readOnly}
	{/fbvFormSection}
	{fbvFormSection label="monograph.source" for="source" description="monograph.source.tip"}
		{fbvElement type="text" multilingual=true name="source" id="source" value=$source maxlength="255" disabled=$readOnly}
	{/fbvFormSection}
	{fbvFormSection label="monograph.rights" for="rights" description="monograph.rights.tip"}
		{fbvElement type="text" multilingual=true name="rights" id="rights" value=$rights maxlength="255" disabled=$readOnly}
	{/fbvFormSection}
{/fbvFormArea}

{fbvFormArea id="tagitFields" title="submission.submit.metadataForm" border="true"}
	{fbvFormSection description="submission.submit.metadataForm.tip" title="monograph.languages"}
		{url|assign:languagesSourceUrl router=$smarty.const.ROUTE_PAGE page="submission" op="fetchChoices" codeList="74"}
		{fbvElement type="keyword" id="languages" subLabelTranslate=true multilingual=true current=$languages source=$languagesSourceUrl disabled=$readOnly}
	{/fbvFormSection}
	{fbvFormSection label="monograph.subjects"}
		{fbvElement type="keyword" id="subjects" subLabelTranslate=true multilingual=true current=$subjects disabled=$readOnly}
	{/fbvFormSection}
	{fbvFormSection label="search.discipline"}
		{fbvElement type="keyword" id="disciplines" subLabelTranslate=true multilingual=true current=$disciplines disabled=$readOnly}
	{/fbvFormSection}
	{fbvFormSection label="common.keywords"}
		{fbvElement type="keyword" id="keyword" subLabelTranslate=true multilingual=true current=$keywords disabled=$readOnly}
	{/fbvFormSection}
	{fbvFormSection label="submission.supportingAgencies"}
		{fbvElement type="keyword" id="agencies" multilingual=true subLabelTranslate=true current=$agencies disabled=$readOnly}
	{/fbvFormSection}
{/fbvFormArea}
