/**
 * @defgroup js_controllers_grid_users_stageParticipant_form
 */
// Create the namespace.
jQuery.pkp.controllers.grid.users.stageParticipant =
			jQuery.pkp.controllers.grid.users.stageParticipant ||
			{ form: { } };

/**
 * @file js/controllers/grid/users/stageParticipant/AddParticipantFormHandler.js
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AddParticipantFormHandler
 * @ingroup js_controllers_grid_users_stageParticipant_form
 *
 * @brief Handle the "add participant" form.
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
	$.pkp.controllers.grid.users.stageParticipant.form.AddParticipantFormHandler =
			function($form, options) {

		this.parent($form, options);

		// store the URL for fetching users not assigned to a particular user group.
		this.fetchUserListUrl_ = options.fetchUserListUrl;

		$('#userGroupId', $form).change(
				this.callbackWrapper(this.updateUserList));

		// initially populate the selector.
		this.updateUserList();

	};
	$.pkp.classes.Helper.inherits(
			$.pkp.controllers.grid.users.stageParticipant.form.
					AddParticipantFormHandler,
			$.pkp.controllers.form.AjaxFormHandler);


	//
	// Private properties
	//
	/**
	 * The URL to be called to fetch a list of users for a given user group.
	 * @private
	 * @type {string}
	 */
	$.pkp.controllers.grid.users.stageParticipant.form.AddParticipantFormHandler.
			prototype.fetchUserListUrl_ = null;


	//
	// Public methods
	//
	/**
	 * Method to add the userGroupId to autocomplete URL for finding users
	 * @param {Object} eventObject The html element that changed.
	 */
	$.pkp.controllers.grid.users.stageParticipant.form.AddParticipantFormHandler.
			prototype.updateUserList = function(eventObject) {

		var oldUrl = this.fetchUserListUrl_;
		
		var $form = this.getHtmlElement();
		var $userGroupSelector = $form.find('#userGroupId');
		// Match with &amp;userGroupId or without and append userGroupId
		var newUrl = oldUrl.replace(
				/(&userGroupId=\d+)?$/, '&userGroupId=' + $userGroupSelector.val());

		$.get(newUrl, null, this.callbackWrapper(
				this.updateUserListHandler_), 'json');
	};


	/**
	 * A callback to update the user list selector on the interface.
	 *
	 * @private
	 *
	 * @param {Object} ajaxContext The AJAX request context.
	 * @param {Object} data A parsed JSON response object.
	 */
	$.pkp.controllers.grid.users.stageParticipant.form.AddParticipantFormHandler.
			prototype.updateUserListHandler_ = function(ajaxContext, data) {

		var jsonData = this.handleJson(data);
		var $element = this.getHtmlElement();

		var $select = $element.find('#userId');
		// clear any previous items.
		$select.find('option[value!=""]').remove();

		for (var optionId in jsonData.content) {
			var $option = $('<option/>');
			$option.attr('value', optionId);
			$option.text(jsonData.content[optionId]);
			$select.append($option);
		}
	};

/** @param {jQuery} $ jQuery closure. */
})(jQuery);
