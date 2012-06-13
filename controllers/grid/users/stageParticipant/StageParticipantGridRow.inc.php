<?php

/**
 * @file controllers/grid/users/stageParticipant/StageParticipantGridRow.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class StageParticipantGridRow
 * @ingroup controllers_grid_users_stageParticipant
 *
 * @brief StageParticipant grid row definition
 */

import('lib.pkp.classes.controllers.grid.GridRow');

class StageParticipantGridRow extends GridRow {
	/** @var $_monograph Monograph */
	var $_monograph;

	/** @var $_stageId int */
	var $_stageId;

	/** @var $canAdminister boolean Whether the user can admin this row */
	var $_canAdminister;

	/**
	 * Constructor
	 */
	function StageParticipantGridRow(&$monograph, $stageId, $canAdminister = false) {
		$this->_monograph =& $monograph;
		$this->_stageId =& $stageId;
		$this->_canAdminister = $canAdminister;

		parent::GridRow();
	}


	//
	// Overridden methods from GridRow
	//
	/**
	 * @see GridRow::initialize()
	 * @param $request PKPRequest
	 */
	function initialize(&$request) {
		// Do the default initialization
		parent::initialize($request);

		// Is this a new row or an existing row?
		$rowId = $this->getId();
		if (!empty($rowId) && is_numeric($rowId)) {
			// Only add row actions if this is an existing row.
			$router =& $request->getRouter();

			import('lib.pkp.classes.linkAction.request.RemoteActionConfirmationModal');
			if ($this->_canAdminister) $this->addAction(
				new LinkAction(
					'delete',
					new RemoteActionConfirmationModal(
						__('editor.monograph.removeStageParticipant.description'),
						__('editor.monograph.removeStageParticipant'),
						$router->url($request, null, null, 'deleteParticipant', null, $this->getRequestArgs())
						),
					__('grid.action.remove'),
					'delete'
				)
			);

			import('controllers.informationCenter.linkAction.NotifyLinkAction');
			$monograph =& $this->getMonograph();
			$stageId = $this->getStageId();
			$stageAssignment =& $this->getData();
			$userId = $stageAssignment->getUserId();
			$this->addAction(new NotifyLinkAction($request, $monograph, $stageId, $userId));

			// Set a non-default template that supports row actions
			$this->setTemplate('controllers/grid/gridRowWithActions.tpl');
		}
	}

	//
	// Getters/Setters
	//
	/**
	 * Get the monograph for this row (already authorized)
	 * @return Monograph
	 */
	function &getMonograph() {
		return $this->_monograph;
	}

	/**
	 * Get the stage id for this row
	 * @return int
	 */
	function getStageId() {
		return $this->_stageId;
	}

	/**
	 * Get the grid request parameters.
	 * @see GridHandler::getRequestArgs()
	 * @return array
	 */
	function getRequestArgs() {
		return array(
			'monographId' => $this->getMonograph()->getId(),
			'stageId' => $this->_stageId,
			'assignmentId' => $this->getId()
		);
	}
}

?>
