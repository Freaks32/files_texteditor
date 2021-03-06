<?php
/**
 * ownCloud - files_texteditor
 *
 * @author Tom Needham
 * @copyright 2013 Tom Needham tom@owncloud.com
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */
// Check if we are a user
OCP\JSON::checkLoggedIn();
OCP\JSON::callCheck();

// Get paramteres
$filecontents = $_POST['filecontents'];
$path = isset($_POST['path']) ? $_POST['path'] : '';
$mtime = isset($_POST['mtime']) ? $_POST['mtime'] : '';

$l = \OC_L10N('files_texteditor');

if($path != '' && $mtime != '') {
	// Get file mtime
	$filemtime = \OC\Files\Filesystem::filemtime($path);
	if($mtime != $filemtime) {
		// Then the file has changed since opening
		OCP\JSON::error(array('data' => array( 'message' => $l->t('Cannot save file as it has been modified since opening'))));
		OCP\Util::writeLog(
			'files_texteditor',
			"File: ".$path." modified since opening.",
			OCP\Util::ERROR
			);
	} else {
		// File same as when opened, save file
		if(\OC\Files\Filesystem::isUpdatable($path)) {
			$filecontents = iconv(mb_detect_encoding($filecontents), "UTF-8", $filecontents);
			\OC\Files\Filesystem::file_put_contents($path, $filecontents);
			// Clear statcache
			clearstatcache();
			// Get new mtime
			$newmtime = \OC\Files\Filesystem::filemtime($path);
			$newsize = \OC\Files\Filesystem::filesize($path);
			OCP\JSON::success(array('data' => array('mtime' => $newmtime, 'size' => $newsize)));
		} else {
			// Not writeable!
			OCP\JSON::error(array('data' => array( 'message' => $l->t('Insufficient permissions'))));
			OCP\Util::writeLog(
				'files_texteditor',
				"User does not have permission to write to file: ".$path,
				OCP\Util::ERROR
				);
		}
	}
} else if($path == '') {
	OCP\JSON::error(array('data' => array( 'message' => $l->t('File path not supplied'))));
	OCP\Util::writeLog('files_texteditor','No file path supplied', OCP\Util::ERROR);
} else if($mtime == '') {
	OCP\JSON::error(array('data' => array( 'message' => $l->t('File mtime not supplied'))));
	OCP\Util::writeLog('files_texteditor','No file mtime supplied' ,OCP\Util::ERROR);
}
