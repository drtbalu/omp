<?php

/**
 * @file controllers/informationCenter/InformationCenterHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class InformationCenterHandler
 * @ingroup controllers_informationCenter
 *
 * @brief Parent class for file/submission information center handlers.
 */

import('classes.handler.Handler');
import('lib.pkp.classes.core.JSONMessage');
import('classes.log.MonographEventLogEntry');

class InformationCenterHandler extends Handler {
	/**
	 * Constructor
	 */
	function InformationCenterHandler() {
		parent::Handler();

		// Author can do everything except delete notes.
		// (Review-related log entries are hidden from the author, but
		// that's not implemented here.)
		$this->addRoleAssignment(
			array(ROLE_ID_AUTHOR),
			$authorOps = array(
				'viewInformationCenter', // Information Center
				'fetchTemplateBody', // Notify tab
				'metadata', 'saveForm', // Metadata
				'viewNotes', 'listNotes', 'saveNote', // Notes
				'viewNotify', 'sendNotification', // Notify tab
				'viewHistory', 'listHistory', // History tab
			)
		);
		$this->addRoleAssignment(
			array(ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER, ROLE_ID_PRESS_ASSISTANT),
			array_merge($authorOps, array(
				'deleteNote' // Notes tab
			))
		);
	}


	//
	// Implement template methods from PKPHandler.
	//
	/**
	 * @see PKPHandler::authorize()
	 * @param $request PKPRequest
	 * @param $args array
	 * @param $roleAssignments array
	 */
	function authorize(&$request, $args, $roleAssignments) {
		import('classes.security.authorization.OmpSubmissionAccessPolicy');
		$this->addPolicy(new OmpSubmissionAccessPolicy($request, $args, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}


	//
	// Public operations
	//
	/**
	 * Display the main information center modal.
	 * @param $request PKPRequest
	 */
	function viewInformationCenter(&$request) {
		$this->setupTemplate($request);
		$templateMgr =& TemplateManager::getManager();
		return $templateMgr->fetchJson('controllers/informationCenter/informationCenter.tpl');
	}

	/**
	 * Display the metadata tab.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function metadata($args, &$request) {
		assert(false);
	}

	/**
	 * Save the metadata tab.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function saveForm($args, &$request) {
		assert(false);
	}

	/**
	 * View a list of notes posted on the item.
	 * Subclasses must implement.
	 */
	function viewNotes($args, &$request) {
		assert(false);
	}

	/**
	 * Save a note.
	 * Subclasses must implement.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function saveNote($args, &$request) {
		assert(false);
	}

	/**
	 * Display the list of existing notes.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function listNotes($args, &$request) {
		$this->setupTemplate($request);

		$templateMgr =& TemplateManager::getManager();
		$noteDao =& DAORegistry::getDAO('NoteDAO');
		$templateMgr->assign('notes', $noteDao->getByAssoc($this->_getAssocType(), $this->_getAssocId()));

		$user =& $request->getUser();
		$templateMgr->assign('currentUserId', $user->getId());
		$templateMgr->assign('notesDeletable', true);

		return $templateMgr->fetchJson('controllers/informationCenter/notesList.tpl');
	}

	/**
	 * Delete a note.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function deleteNote($args, &$request) {
		$this->setupTemplate($request);

		$noteId = (int) $request->getUserVar('noteId');
		$noteDao =& DAORegistry::getDAO('NoteDAO');
		$note =& $noteDao->getById($noteId);
		if (!$note || $note->getAssocType() != $this->_getAssocType() || $note->getAssocId() != $this->_getAssocId()) fatalError('Invalid note!');
		$noteDao->deleteById($noteId);

		$user =& $request->getUser();
		NotificationManager::createTrivialNotification($user->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => __('notification.removedNote')));

		$json = new JSONMessage(true);
		return $json->getString();
	}

	/**
	 * Display the notify tab.
	 * NB: sub-classes must implement this method.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function viewNotify ($args, &$request) {
		assert(false);
	}

	/**
	 * Send a notification from the notify tab.
	 * NB: sub-classes must implement this method.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function sendNotification ($args, &$request) {
		assert(false);
	}

	/**
	 * Display the history tab.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function viewHistory($args, &$request) {
		$this->setupTemplate($request);

		$templateMgr =& TemplateManager::getManager();
		return $templateMgr->fetchJson('controllers/informationCenter/history.tpl');
	}

	/**
	 * Fetch a list of log entries.
	 * NB: sub-classes must implement this method.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function listHistory($args, &$request) {
		assert(false);
	}
	
	/**
	 * Log an event for this item.
	 * NB: sub-classes must implement this method.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function _logEvent ($itemId, $eventType, $userId) {
		assert(false);
	}

	/**
	 * Get an array representing link parameters that subclasses
	 * need to have passed to their various handlers (i.e. monograph ID to
	 * the delete note handler). Subclasses should implement.
	 */
	function _getLinkParams() {
		assert(false);
	}

	function setupTemplate(&$request) {
		AppLocale::requireComponents(LOCALE_COMPONENT_OMP_SUBMISSION);

		$linkParams = $this->_getLinkParams();
		$templateMgr =& TemplateManager::getManager();

		// Preselect tab from keywords 'notes', 'notify', 'history'
		switch ($request->getUserVar('tab')) {
			case 'history':
				$templateMgr->assign('selectedTabIndex', 3);
				break;
			case 'notify':
				$userId = (int) $request->getUserVar('userId');
				if ($userId) {
					$linkParams['userId'] = $userId; // user validated in Listbuilder.
				}
				$templateMgr->assign('selectedTabIndex', 2);
				break;
			case 'notes':
				$templateMgr->assign('selectedTabIndex', 1);
				break;
			// metadata is default
			default:
				$templateMgr->assign('selectedTabIndex', 0);
				break;
		}

		$templateMgr->assign('linkParams', $linkParams);
		parent::setupTemplate($request);
	}

	/**
	 * Get the association ID for this information center view
	 * @return int
	 */
	function _getAssocId() {
		assert(false);
	}

	/**
	 * Get the association type for this information center view
	 * @return int
	 */
	function _getAssocType() {
		assert(false);
	}
}

?>
