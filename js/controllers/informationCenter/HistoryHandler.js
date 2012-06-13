/**
 * @file js/controllers/informationCenter/HistoryHandler.js
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class HistoryHandler
 * @ingroup js_controllers_informationCenter
 *
 * @brief Information center "history" tab handler.
 */
(function($) {


	/**
	 * @constructor
	 *
	 * @extends $.pkp.classes.Handler
	 *
	 * @param {jQuery} $historyDiv A wrapped HTML element that
	 *  represents the "history" interface element.
	 * @param {Object} options Tabbed modal options.
	 */
	$.pkp.controllers.informationCenter.HistoryHandler =
			function($historyDiv, options) {
		this.parent($historyDiv, options);

		// Store the list fetch URLs for later
		this.fetchHistoryUrl_ = options.fetchHistoryUrl;
		this.fetchPastHistoryUrl_ = options.fetchPastHistoryUrl;

		// Initialize an accordion for the "past events" list, if it's
		// available (e.g. for a file information center).
		$('#historyAccordion').accordion();

		// Load a list of the current events.
		this.loadHistoryList_();
		this.loadPastHistoryList_();
	};
	$.pkp.classes.Helper.inherits(
			$.pkp.controllers.informationCenter.HistoryHandler,
			$.pkp.classes.Handler
	);


	//
	// Private properties
	//
	/**
	 * The URL to be called to fetch a list of events.
	 * @private
	 * @type {string}
	 */
	$.pkp.controllers.informationCenter.HistoryHandler.
			prototype.fetchHistoryUrl_ = '';


	/**
	 * The URL to be called to fetch a list of prior events.
	 * @private
	 * @type {string}
	 */
	$.pkp.controllers.informationCenter.HistoryHandler.
			prototype.fetchPastHistoryUrl_ = '';


	//
	// Private methods
	//
	$.pkp.controllers.informationCenter.HistoryHandler.prototype.
			loadHistoryList_ = function() {

		$.get(this.fetchHistoryUrl_,
				this.callbackWrapper(this.setHistoryList_), 'json');
	};

	$.pkp.controllers.informationCenter.HistoryHandler.prototype.
			setHistoryList_ = function(formElement, jsonData) {

		jsonData = this.handleJson(jsonData);
		$('#historyList').replaceWith(jsonData.content);
	};


	$.pkp.controllers.informationCenter.HistoryHandler.prototype.
			loadPastHistoryList_ = function() {

		// Only attempt to load the past history list if it's in the UI
		if ($('#pastHistoryList').length) {
			$.get(this.fetchPastHistoryUrl_,
					this.callbackWrapper(this.setPastHistoryList_), 'json');
		}
	};


	$.pkp.controllers.informationCenter.HistoryHandler.prototype.
			setPastHistoryList_ = function(formElement, jsonData) {

		jsonData = this.handleJson(jsonData);
		$('#pastHistoryList').replaceWith(jsonData.content);
	};


/** @param {jQuery} $ jQuery closure. */
})(jQuery);
