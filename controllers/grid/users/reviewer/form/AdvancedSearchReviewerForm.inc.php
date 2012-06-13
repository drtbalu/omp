<?php

/**
 * @file controllers/grid/users/reviewer/form/AdvancedSearchReviewerForm.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AdvancedSearchReviewerForm
 * @ingroup controllers_grid_users_reviewer_form
 *
 * @brief Form for an advanced search and for adding a reviewer to a submission.
 */

import('controllers.grid.users.reviewer.form.ReviewerForm');

class AdvancedSearchReviewerForm extends ReviewerForm {
	/**
	 * Constructor.
	 * @param $monograph Monograph
	 * @param $reviewRound ReviewRound
	 */
	function AdvancedSearchReviewerForm(&$monograph, &$reviewRound) {
		parent::ReviewerForm($monograph, $reviewRound);
		$this->setTemplate('controllers/grid/users/reviewer/form/advancedSearchReviewerForm.tpl');

		$this->addCheck(new FormValidator($this, 'reviewerId', 'required', 'editor.review.mustSelect'));
	}

	/**
	 * Assign form data to user-submitted data.
	 * @see Form::readInputData()
	 */
	function readInputData() {
		parent::readInputData();

		$this->readUserVars(array('reviewerId'));
	}

	/**
	 * Fetch the form.
	 * @see Form::fetch()
	 */
	function fetch(&$request) {

		$searchByNameAction = $this->getSearchByNameAction($request);

		$this->setReviewerFormAction($searchByNameAction);
		return parent::fetch($request);
	}
}

?>
