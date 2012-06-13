<?php

/**
 * @file controllers/grid/files/copyedit/AuthorCopyeditingSignoffFilesGridHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorCopyeditingSignoffFilesGridHandler
 * @ingroup controllers_grid_files_copyedit
 *
 * @brief Display the files the author has been asked to sign off for copyediting.
 */

import('controllers.grid.files.fileSignoff.AuthorSignoffFilesGridHandler');

class AuthorCopyeditingSignoffFilesGridHandler extends AuthorSignoffFilesGridHandler {
	/**
	 * Constructor
	 */
	function AuthorCopyeditingSignoffFilesGridHandler() {
		parent::AuthorSignoffFilesGridHandler(WORKFLOW_STAGE_ID_EDITING, 'SIGNOFF_COPYEDITING');

		// Set the grid title.
		$this->setTitle('submission.copyediting');
	}
}

?>
