/**
 * @defgroup js_controllers_tab_catalogEntry
 */
// Define the namespace.
jQuery.pkp.controllers.tab.catalogEntry =
			jQuery.pkp.controllers.tab.catalogEntry || {};


/**
 * @file js/controllers/tab/catalogEntry/CatalogEntryTabHandler.js
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CatalogEntryTabHandler
 * @ingroup js_controllers_tab_catalogEntry
 *
 * @brief A subclass of TabHandler for handling the catalog entry tabs. It adds
 * a listener for grid refreshes, so the tab interface can be reloaded.
 */
(function($) {


	/**
	 * @constructor
	 *
	 * @extends $.pkp.controllers.TabHandler
	 *
	 * @param {jQuery} $tabs A wrapped HTML element that
	 *  represents the tabbed interface.
	 * @param {Object} options Handler options.
	 */
	$.pkp.controllers.tab.catalogEntry.CatalogEntryTabHandler =
			function($tabs, options) {
		if (options.selectedFormatId) {
			options.selected =
				this.getTabPositionByFormatId_(options.selectedFormatId, $tabs);
		}

		this.parent($tabs, options);

		// Attach the tabs grid refresh handler.
		this.bind('gridRefreshRequested', this.gridRefreshRequested);

		if (options.tabsUrl) {
			this.tabsUrl_ = options.tabsUrl;
		}

		if (options.tabContentUrl) {
			this.tabContentUrl_ = options.tabContentUrl;
		}

		this.bind('gridInitialized', this.addFormatsGridRowActionHandlers_);
	};
	$.pkp.classes.Helper.inherits(
			$.pkp.controllers.tab.catalogEntry.CatalogEntryTabHandler,
			$.pkp.controllers.TabHandler);


	//
	// Private properties
	//
	/**
	 * The URL for retrieving a tab's content.
	 * @private
	 * @type {string}
	 */
	$.pkp.controllers.tab.catalogEntry.CatalogEntryTabHandler.prototype.
			tabContentUrl_ = null;


	//
	// Public methods
	//
	/**
	 * This listens for grid refreshes from the publication formats grid. It
	 * requests a list of the current publication formats from the
	 * CatalogEntryHandler and calls a callback which updates the tab state
	 * accordingly as they are changed.
	 *
	 * @param {HTMLElement} sourceElement The parent DIV element
	 *  which contains the tabs.
	 * @param {Event} event The triggered event (gridRefreshRequested).
	 */
	$.pkp.controllers.tab.catalogEntry.CatalogEntryTabHandler.prototype.
			gridRefreshRequested = function(sourceElement, event) {

		var $updateSourceElement = $(event.target);
		if ($updateSourceElement.attr('id').match(/^formatsGridContainer/)) {

			if (this.tabsUrl_ && this.tabContentUrl_) {
				var $element = this.getHtmlElement();
				$.get(this.tabsUrl_, null, this.callbackWrapper(
						this.updateTabsHandler_), 'json');
			}
		}

		if ($updateSourceElement.attr('id').match(/approvedProofGrid/)) {
			this.trigger('dataChanged');
		}
	};


	//
	// Private methods
	//
	/**
	 * A callback to update the tabs on the interface.
	 *
	 * @private
	 *
	 * @param {Object} ajaxContext The AJAX request context.
	 * @param {Object} data A parsed JSON response object.
	 */
	$.pkp.controllers.tab.catalogEntry.CatalogEntryTabHandler.prototype.
			updateTabsHandler_ = function(ajaxContext, data) {

		var jsonData = this.handleJson(data);
		var $element = this.getHtmlElement();
		var currentTabs = $element.find('li a');
		var currentIndexes = {};

		// only interested in publication format tabs, so filter out the others
		var regexp = /publication(\d+)/;

		for (var j = 0; j < currentTabs.length; j++) {
			var id = currentTabs[j].getAttribute('id');
			var match = regexp.exec(id);
			if (match !== null) {
				// match[1] is the id of a current format.
				// j also happens to be the zero-based index of the tab
				// position which will be useful if we have to remove it.
				currentIndexes[match[1]] = j;
			}
		}

		for (var i in jsonData.formats) {
			// i is the formatId, formats[i] is the localized name.
			if (!(i in currentIndexes)) { // this is a tab that has been added
				var url = this.tabContentUrl_ + '&publicationFormatId=' +
						encodeURIComponent(i);
				// replace dollar signs in $$$call$$$ so the .add() call
				// interpolates correctly. Is this a bug in jqueryUI?
				url = url.replace(/[$]/g, '$$$$');
				$element.tabs('add', url, jsonData.formats[i]);
				$element.find('li a').filter(':last').
						attr('id', 'publication' + i);
			}
		}

		// now check our existing tabs to see if any should be removed
		for (i in currentIndexes) {
			// this is a tab that has been removed
			if (!(i in jsonData.formats)) {
				$element.tabs('remove', currentIndexes[i]);
			} else { // tab still exists, update localized name if necessary
				$element.find('li a').filter('[id="publication' + i + '"]').
						html(jsonData.formats[i]);
			}
		}
	};


	/**
	 * Add handlers to grid row links inside
	 * the publication formats grid.
	 *
	 * @private
	 */
	$.pkp.controllers.tab.catalogEntry.CatalogEntryTabHandler.prototype.
			addFormatsGridRowActionHandlers_ = function() {

		var $formatsGrid = $('[id^="formatsGridContainer"]', this.getHtmlElement());
		if ($formatsGrid.length) {
			var $links = $('a[id*="publicationFormatTab"]', $formatsGrid);
			$links.click(this.callbackWrapper(this.formatsGridLinkClickHandler_));
		}
	};


	/**
	 * Publication format grid link click handler to open a
	 * publication format tab.
	 *
	 * @private
	 *
	 * @param {HTMLElement} sourceElement The clicked link.
	 * @param {Event} event The triggered event (click).
	 */
	$.pkp.controllers.tab.catalogEntry.CatalogEntryTabHandler.prototype.
			formatsGridLinkClickHandler_ = function(sourceElement, event) {

		var $grid = $('[id^="formatsGridContainer"]',
				this.getHtmlElement()).children('div');
		var gridHandler = $.pkp.classes.Handler.getHandler($grid);
		var $gridRow = gridHandler.getParentRow($(sourceElement));
		var publicationFormatId = gridHandler.getRowDataId($gridRow);
		this.getHtmlElement().tabs('select',
				this.getTabPositionByFormatId_(publicationFormatId,
						this.getHtmlElement()));
	};


	/**
	 * Get the tab position using the passed publication format id.
	 * @param {integer} formatId The publication format id.
	 * @param {jQuery} $tabs The current tabs container element.
	 * @return {integer?} The publication format tab position or null.
	 */
	$.pkp.controllers.tab.catalogEntry.CatalogEntryTabHandler.prototype.
			getTabPositionByFormatId_ = function(formatId, $tabs) {

		// Find the correspondent tab position.
		var $linkId = 'publication' + formatId;
		var $tab = $('#' + $linkId, $tabs).parent('li');
		if ($tab.length) {
			return $tabs.children().children().index($tab);
		} else {
			return null;
		}
	};


/** @param {jQuery} $ jQuery closure. */
})(jQuery);
