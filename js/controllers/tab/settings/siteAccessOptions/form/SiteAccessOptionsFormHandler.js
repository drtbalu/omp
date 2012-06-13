/**
 * @defgroup js_controllers_tab_settings_siteAccessOptions_form
 */
// Create the namespace.
jQuery.pkp.controllers.tab.settings.siteAccessOptions =
			jQuery.pkp.controllers.tab.settings.siteAccessOptions || {form: { } };


/**
 * @file js/controllers/tab/settings/siteAccessOptions/form/SiteAccessOptionsFormHandler.js
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SiteAccessOptionsFormHandler
 * @ingroup js_controllers_tab_settings_siteAccessOptions_form
 *
 * @brief Handle the site access options form.
 */
(function($) {


	/**
	 * @constructor
	 *
	 * @extends $.pkp.controllers.form.AjaxFormHandler
	 *
	 * @param {jQuery} $form the wrapped HTML form element.
	 * @param {Object} options form options.
	 */
	$.pkp.controllers.tab.settings.siteAccessOptions.form.
			SiteAccessOptionsFormHandler = function($form, options) {

		this.parent($form, options);

		// Attach form elements events.
		$('#disableUserReg-0', $form).click(
				this.callbackWrapper(this.changeRegOptsState));
		$('#disableUserReg-1', $form).click(
				this.callbackWrapper(this.changeRegOptsState));

	};
	$.pkp.classes.Helper.inherits(
			$.pkp.controllers.tab.settings.siteAccessOptions.form.
			SiteAccessOptionsFormHandler,
			$.pkp.controllers.form.AjaxFormHandler);


	//
	// Public methods.
	//
	/**
	 * Event handler that is called when the suggest username button is clicked.
	 * @param {HTMLElement} element The checkbox input element.
	 */
	$.pkp.controllers.tab.settings.siteAccessOptions.form.
			SiteAccessOptionsFormHandler.prototype.
			changeRegOptsState = function(element) {
		if (element.id === 'disableUserReg-0') {
			this.setRegOptsDisabled_(false);
		} else {
			this.setRegOptsDisabled_(true);
			this.setRegOptsChecked_(false);
		}
	};


	//
	// Private helper methods
	//
	/**
	 * Change the disabled state of the user registration options.
	 * @private
	 * @param {boolean} state The state of the disabled attribute.
	 */
	$.pkp.controllers.tab.settings.siteAccessOptions.form.
			SiteAccessOptionsFormHandler.prototype.
			setRegOptsDisabled_ = function(state) {
		$('#allowRegAuthor').attr('disabled', state);
		$('#allowRegReviewer').attr('disabled', state);
	};


	/**
	 * Change the checked state of the user registration options.
	 * @private
	 * @param {boolean} state The state of the checked attribute.
	 */
	$.pkp.controllers.tab.settings.siteAccessOptions.form.
			SiteAccessOptionsFormHandler.prototype.
			setRegOptsChecked_ = function(state) {
		$('#allowRegAuthor').attr('checked', state);
		$('#allowRegReviewer').attr('checked', state);
	};


/** @param {jQuery} $ jQuery closure. */
})(jQuery);
