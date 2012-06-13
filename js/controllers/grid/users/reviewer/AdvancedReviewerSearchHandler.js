/**
 * @defgroup js_controllers_grid_users_stageParticipant_form
 */
// Create the namespace.
jQuery.pkp.controllers.grid.users = jQuery.pkp.controllers.grid.users ||
			{ reviewer: { form: { } } };

/**
 * @defgroup js_controllers_modal_editorDecision_form
 */
// Create the namespace.
jQuery.pkp.controllers.modals = jQuery.pkp.controllers.modals ||
			{ editorDecision: { form: { } } };

/**
 * @file js/controllers/AdvancedReviewerSearchHandler.js
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AdvancedReviewerSearchHandler
 * @ingroup js_controllers
 *
 * @brief Handle the advanced reviewer search tab in the add reviewer modal.
 */
(function($) {


	/**
	 * @constructor
	 *
	 * @extends $.pkp.classes.Handler
	 *
	 * @param {jQuery} $container the wrapped page element.
	 * @param {Object} options handler options.
	 */
	$.pkp.controllers.grid.users.reviewer.AdvancedReviewerSearchHandler =
			function($container, options) {
		this.parent($container, options);

		$container.find('.button').button();

		$('#selectReviewerButton').click(
				this.callbackWrapper(this.reviewerSelected));

		$('#regularReviewerForm').hide();

		this.bind('refreshForm', this.handleRefresh_);
	};
	$.pkp.classes.Helper.inherits(
			$.pkp.controllers.grid.users.reviewer.AdvancedReviewerSearchHandler,
			$.pkp.classes.Handler);


	//
	// Public methods
	//
	/**
	 * Callback that is triggered when a reviewer is selected.
	 *
	 * @param {HTMLElement} button The button element clicked.
	 */
	$.pkp.controllers.grid.users.reviewer.AdvancedReviewerSearchHandler.prototype.
			reviewerSelected = function(button) {

		// Get the selected reviewer's ID
		var $selectedInput = this.getHtmlElement().
				find('#reviewerSelectGridContainer').find('input:checked');
		var reviewerId = $selectedInput.val();

		if (reviewerId) {
			var reviewerName = $selectedInput.parent().next().
					children('span').html().trim();

			// Update the hidden review id input
			$('#reviewerId').val(reviewerId);

			// Update the selected reviewer name container
			$('input[id^="selectedReviewerName"]', this.getHtmlElement()).
					val(reviewerName);

			// Hide the grid now
			$('#searchGridAndButton').hide();
			$('#regularReviewerForm').show();
		}
	};


	//
	// Private helper methods.
	//
	/**
	 * Handle the form refresh event.
	 * @private
	 * @param {HTMLElement} sourceElement The element that issued the event.
	 * @param {Event} event The triggering event.
	 * @param {string} content HTML contents to replace element contents.
	 */
	$.pkp.controllers.grid.users.reviewer.AdvancedReviewerSearchHandler.prototype.
			handleRefresh_ = function(sourceElement, event, content) {

		if (content) {
			// Get the element that we're updating
			var $element = this.getHtmlElement();

			// Replace the grid content
			$element.replaceWith(content);
		}
	};


/** @param {jQuery} $ jQuery closure. */
})(jQuery);
