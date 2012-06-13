<?php
/**
 * @defgroup controllers_api_file_linkAction
 */

/**
 * @file controllers/api/file/linkAction/AddFileLinkAction.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AddFileLinkAction
 * @ingroup controllers_api_file_linkAction
 *
 * @brief An action to add a submission file.
 */

import('controllers.api.file.linkAction.BaseAddFileLinkAction');

class AddFileLinkAction extends BaseAddFileLinkAction {

	/**
	 * Constructor
	 * @param $request Request
	 * @param $monographId integer The monograph the file should be
	 *  uploaded to.
	 * @param $stageId integer The workflow stage in which the file
	 *  uploader is being instantiated (one of the WORKFLOW_STAGE_ID_*
	 *  constants).
	 * @param $uploaderRoles array The ids of all roles allowed to upload
	 *  in the context of this action.
	 * @param $fileStage integer The file stage the file should be
	 *  uploaded to (one of the MONOGRAPH_FILE_* constants).
	 * @param $assocType integer The type of the element the file should
	 *  be associated with (one fo the ASSOC_TYPE_* constants).
	 * @param $assocId integer The id of the element the file should be
	 *  associated with.
	 * @param $reviewRound ReviewRound The current review round (if any).
	 */
	function AddFileLinkAction(&$request, $monographId, $stageId, $uploaderRoles,
			$fileStage, $assocType = null, $assocId = null, $reviewRoundId = null) {

		// Create the action arguments array.
		$actionArgs = array('fileStage' => $fileStage, 'reviewRoundId' => $reviewRoundId);
		if (is_numeric($assocType) && is_numeric($assocId)) {
			$actionArgs['assocType'] = (int)$assocType;
			$actionArgs['assocId'] = (int)$assocId;
		}

		// Identify text labels based on the file stage.
		$textLabels = AddFileLinkAction::_getTextLabels($fileStage);

		// Call the parent class constructor.
		parent::BaseAddFileLinkAction(
			$request, $monographId, $stageId, $uploaderRoles, $actionArgs,
			__($textLabels['wizardTitle']), __($textLabels['buttonLabel'])
		);
	}


	//
	// Private methods
	//
	/**
	 * Static method to return text labels
	 * for upload to different file stages.
	 *
	 * @param $fileStage integer One of the
	 *  MONOGRAPH_FILE_* constants.
	 * @return array
	 */
	function _getTextLabels($fileStage) {
		static $textLabels = array(
			MONOGRAPH_FILE_SUBMISSION => array(
				'wizardTitle' => 'submission.submit.uploadSubmissionFile',
				'buttonLabel' => 'submission.addFile'
			),
			MONOGRAPH_FILE_REVIEW_FILE => array(
				'wizardTitle' => 'editor.submissionReview.uploadFile',
				'buttonLabel' => 'editor.submissionReview.uploadFile'
			),
			MONOGRAPH_FILE_REVIEW_ATTACHMENT => array(
				'wizardTitle' => 'editor.submissionReview.uploadAttachment',
				'buttonLabel' => 'editor.submissionReview.uploadAttachment'
			),
			MONOGRAPH_FILE_REVIEW_REVISION => array(
				'wizardTitle' => 'editor.submissionReview.uploadFile',
				'buttonLabel' => 'editor.submissionReview.uploadFile'
			),
			MONOGRAPH_FILE_FINAL => array(
				'wizardTitle' => 'submission.upload.finalDraft',
				'buttonLabel' => 'submission.upload.finalDraft'
			),
			MONOGRAPH_FILE_COPYEDIT => array(
				'wizardTitle' => 'submission.upload.copyeditedVersion',
				'buttonLabel' => 'submission.upload.copyeditedVersion'
			),
			MONOGRAPH_FILE_FAIR_COPY => array(
				'wizardTitle' => 'submission.upload.fairCopy',
				'buttonLabel' => 'submission.upload.fairCopy'
			),
			MONOGRAPH_FILE_PRODUCTION_READY => array(
				'wizardTitle' => 'submission.upload.productionReady',
				'buttonLabel' => 'submission.upload.productionReady'
			),
			MONOGRAPH_FILE_PROOF => array(
				'wizardTitle' => 'submission.upload.proof',
				'buttonLabel' => 'submission.upload.proof'
			)
		);

		assert(isset($textLabels[$fileStage]));
		return $textLabels[$fileStage];
	}
}

?>
