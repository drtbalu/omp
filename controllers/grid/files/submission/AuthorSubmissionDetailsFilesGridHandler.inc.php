<?php

/**
 * @file controllers/grid/files/submission/AuthorSubmissionDetailsFilesGridHandler.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorSubmissionDetailsFilesGridHandler
 * @ingroup controllers_grid_files_submission
 *
 * @brief Handle submission file grid requests on the author's submission details pages.
 */

// Import the grid layout.
import('controllers.grid.files.fileList.FileListGridHandler');

class AuthorSubmissionDetailsFilesGridHandler extends FileListGridHandler {
	/**
	 * Constructor
	 */
	function AuthorSubmissionDetailsFilesGridHandler() {
		import('controllers.grid.files.SubmissionFilesGridDataProvider');
		$dataProvider = new SubmissionFilesGridDataProvider(MONOGRAPH_FILE_SUBMISSION);
		parent::FileListGridHandler($dataProvider, WORKFLOW_STAGE_ID_SUBMISSION, FILE_GRID_DOWNLOAD_ALL);

		$this->addRoleAssignment(
			array(ROLE_ID_PRESS_MANAGER, ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_ASSISTANT, ROLE_ID_AUTHOR),
			array('fetchGrid', 'fetchRow')
		);
	}
}

?>
