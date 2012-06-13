<?php

/**
 * @file classes/monograph/ArtworkFileDAODelegate.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ArtworkFileDAODelegate
 * @ingroup monograph
 * @see ArtworkFile
 *
 * @brief Operations for retrieving and modifying ArtworkFile objects.
 *
 * The SubmissionFileDAO will delegate to this class if it wishes
 * to access ArtworkFile classes.
 */


import('classes.monograph.ArtworkFile');
import('classes.monograph.MonographFileDAODelegate');

class ArtworkFileDAODelegate extends MonographFileDAODelegate {
	/**
	 * Constructor
	 */
	function ArtworkFileDAODelegate() {
		parent::MonographFileDAODelegate();
	}


	//
	// Public methods
	//
	/**
	 * @see SubmissionFileDAODelegate::insert()
	 * @param $artworkFile ArtworkFile
	 * @return ArtworkFile
	 */
	function &insertObject(&$artworkFile, $sourceFile, $isUpload = false) {
		// First insert the data for the super-class.
		$artworkFile =& parent::insertObject($artworkFile, $sourceFile, $isUpload);
		if (is_null($artworkFile)) return $artworkFile;

		// Now insert the artwork-specific data.
		$this->update(
			'INSERT INTO monograph_artwork_files
				(file_id, revision, caption, chapter_id, contact_author, copyright_owner, copyright_owner_contact, credit, permission_file_id, permission_terms)
			VALUES
				(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
			array(
				$artworkFile->getFileId(),
				$artworkFile->getRevision(),
				$artworkFile->getCaption(),
				$artworkFile->getChapterId(),
				$artworkFile->getContactAuthor(),
				$artworkFile->getCopyrightOwner(),
				$artworkFile->getCopyrightOwnerContactDetails(),
				$artworkFile->getCredit(),
				$artworkFile->getPermissionFileId(),
				$artworkFile->getPermissionTerms()
			)
		);

		return $artworkFile;
	}

	/**
	 * @see SubmissionFileDAODelegate::update()
	 * @param $artworkFile ArtworkFile
	 * @param $previousFile ArtworkFile
	 */
	function updateObject(&$artworkFile, &$previousFile) {
		// Update the parent class table first.
		if (!parent::updateObject($artworkFile, $previousFile)) return false;

		// Now update the artwork file table.
		$this->update(
			'UPDATE monograph_artwork_files
				SET
					file_id = ?,
					revision = ?,
					caption = ?,
					chapter_id = ?,
					contact_author = ?,
					copyright_owner = ?,
					copyright_owner_contact = ?,
					credit = ?,
					permission_file_id = ?,
					permission_terms = ?
				WHERE file_id = ? and revision = ?',
			array(
				(int)$artworkFile->getFileId(),
				(int)$artworkFile->getRevision(),
				$artworkFile->getCaption(),
				is_null($artworkFile->getChapterId()) ? null : (int)$artworkFile->getChapterId(),
				$artworkFile->getContactAuthor(),
				$artworkFile->getCopyrightOwner(),
				$artworkFile->getCopyrightOwnerContactDetails(),
				$artworkFile->getCredit(),
				is_null($artworkFile->getPermissionFileId()) ? null : (int)$artworkFile->getPermissionFileId(),
				$artworkFile->getPermissionTerms(),
				(int)$previousFile->getFileId(),
				(int)$previousFile->getRevision()
			)
		);
		return true;
	}

	/**
	 * @see SubmissionFileDAODelegate::deleteObject()
	 */
	function deleteObject(&$submissionFile) {
		// First delete the monograph file entry.
		if (!parent::deleteObject($submissionFile)) return false;

		// Delete the artwork file entry.
		return $this->update(
			'DELETE FROM monograph_artwork_files
			 WHERE file_id = ? AND revision = ?',
			array(
				(int)$submissionFile->getFileId(),
				(int)$submissionFile->getRevision()
			));
	}

	/**
	 * @see SubmissionFileDAODelegate::fromRow()
	 * @return ArtworkFile
	 */
	function &fromRow(&$row) {
		$artworkFile =& parent::fromRow($row);
		$artworkFile->setCredit($row['credit']);
		$artworkFile->setCaption($row['caption']);
		$artworkFile->setChapterId(is_null($row['chapter_id']) ? null : (int)$row['chapter_id']);
		$artworkFile->setContactAuthor($row['contact_author']);
		$artworkFile->setCopyrightOwner($row['copyright_owner']);
		$artworkFile->setPermissionTerms($row['permission_terms']);
		$artworkFile->setPermissionFileId(is_null($row['permission_file_id']) ? null : (int)$row['permission_file_id']);
		$artworkFile->setCopyrightOwnerContactDetails($row['copyright_owner_contact']);

		return $artworkFile;
	}

	/**
	 * @see SubmissionFileDAODelegate::newDataObject()
	 * @return MonographFile
	 */
	function newDataObject() {
		return new ArtworkFile();
	}
}

?>
