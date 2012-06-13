<?php

/**
 * @file controllers/grid/files/review/AuthorReviewRevisionsGridHandler.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorReviewRevisionsGridHandler
 * @ingroup controllers_grid_files_review
 *
 * @brief Display to authors the file revisions that they have uploaded.
 */

import('controllers.grid.files.review.ReviewRevisionsGridHandler');

class AuthorReviewRevisionsGridHandler extends ReviewRevisionsGridHandler {
	/**
	 * Constructor
	 */
	function AuthorReviewRevisionsGridHandler() {
		$roleAssignments = array(
			array(ROLE_ID_AUTHOR),
			array('fetchGrid', 'fetchRow')
		);
		parent::ReviewRevisionsGridHandler($roleAssignments);
	}
}

?>
