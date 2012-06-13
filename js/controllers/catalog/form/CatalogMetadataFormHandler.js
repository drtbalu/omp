/**
 * @defgroup js_controllers_catalog_form
 */
// Create the modal namespace.
jQuery.pkp.controllers.catalog =
			jQuery.pkp.controllers.catalog || { form: { } };

/**
 * @file js/controllers/catalog/form/CatalogMetadataFormHandler.js
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CatalogMetadataFormHandler
 * @ingroup js_controllers_catalog_form
 *
 * @brief Catalog Metadata form handler.
 */
(function($) {


	/**
	 * @constructor
	 *
	 * @extends $.pkp.controllers.form.FileUploadFormHandler
	 *
	 * @param {jQuery} $form A wrapped HTML element that
	 *  represents the tabbed interface element.
	 * @param {Object} options Tabbed modal options.
	 */
	$.pkp.controllers.catalog.form.CatalogMetadataFormHandler =
			function($form, options) {
		this.parent($form, options);

		$('#audienceRangeExact', $form).change(
				this.callbackWrapper(this.ensureValidAudienceRanges_));
	};
	$.pkp.classes.Helper.inherits(
			$.pkp.controllers.catalog.form.CatalogMetadataFormHandler,
			$.pkp.controllers.form.FileUploadFormHandler
	);


	//
	// Private properties
	//
	/**
	 * An array to store the temporary values of the audienceTo and From
	 * select items, in case the user decides to reuse them again.
	 * @private
	 * @type {array?}
	 */
	$.pkp.controllers.catalog.form.CatalogMetadataFormHandler.prototype.
			audienceValues_ = null;


	//
	// Private methods
	//
	/**
	 * Respond to someone toggling the audience range select item for an 'exact'
	 * range.  Disable the other two types of audience ranges and set their
	 * values to empty.
	 *
	 * @param {HTMLElement} sourceElement The element that
	 *  issued the event.
	 * @param {Event} event The triggering event.
	 * @private
	 */
	$.pkp.controllers.catalog.form.CatalogMetadataFormHandler.prototype.
			ensureValidAudienceRanges_ = function(sourceElement, event) {

		var $form = this.getHtmlElement();
		if ($(sourceElement).val() !== '') {
			this.audienceValues_ = [$form.find('#audienceRangeFrom').val(),
			                        $form.find('#audienceRangeTo').val()];
			$form.find('#audienceRangeFrom, #audienceRangeTo').val('').
					attr('disabled', 'disabled');
		} else {
			$form.find('#audienceRangeFrom, #audienceRangeTo').
					attr('disabled', '');
			$form.find('#audienceRangeFrom').val(this.audienceValues_[0]);
			$form.find('#audienceRangeTo').val(this.audienceValues_[1]);
		}
	};
/** @param {jQuery} $ jQuery closure. */
})(jQuery);
