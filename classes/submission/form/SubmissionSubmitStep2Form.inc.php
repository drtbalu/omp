<?php

/**
 * @file classes/submission/form/SubmissionSubmitStep2Form.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionSubmitStep2Form
 * @ingroup submission_form
 *
 * @brief Form for Step 2 of author manuscript submission.
 */


import('classes.submission.form.SubmissionSubmitForm');

class SubmissionSubmitStep2Form extends SubmissionSubmitForm {
	/**
	 * Constructor.
	 */
	function SubmissionSubmitStep2Form($press, $monograph) {
		parent::SubmissionSubmitForm($press, $monograph, 2);
	}

	/**
	 * Save changes to monograph.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return int the monograph ID
	 */
	function execute($args, &$request) {
		// Update monograph
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$monograph =& $this->monograph;

		if ($monograph->getSubmissionProgress() <= $this->step) {
			$monograph->stampStatusModified();
			$monograph->setSubmissionProgress($this->step + 1);
			$monographDao->updateMonograph($monograph);
		}

		return $this->monographId;
	}
}

?>
