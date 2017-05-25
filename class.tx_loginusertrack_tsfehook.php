<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Dmitry Dulepov (dmitry@typo3.org)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Hook to record FE user information
 *
 * @author	Kasper Skaarhoj <kasper@typo3.com>
 * @author	Dmitry Dulepov <dmitry@typo3.org>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 */

/**
 * This class contains a hook to {@link tslib_fe::checkDataSubmission} function.
 * It will check FE user data and record changes. This hook is used instead of
 * {@link tslib_fe::initFEUser} because we need parsed config array to see
 * if tracking is enabled.
 *
 * @author Kasper Skaarhoj (original XCLASS)
 * @author Dmitry Dulepov (this hook + updates for latest best practicies)
 */
class tx_loginusertrack_tsfehook {

	var $uidKey = 'tx_luginusertrack:uid';
	var $pidKey = 'tx_luginusertrack:pid';

	/**
	 * Checks if tracking is enables and adds/updates user data. Do not change function name!
	 *
	 * @return	void
	 */
	public function checkDataSubmission() {
		if ($GLOBALS['TSFE']->config['config']['tx_loginusertrack_enable']) {
			if (is_array($GLOBALS['TSFE']->fe_user->user))	{
				if (t3lib_div::_GP('logintype') == 'login')	{
					$this->ext_addNewEntry();
				} else {
					$this->ext_updateEntry();
				}
			}
		}
	}

	/**
	 * Adds new entry to statictics table
	 *
	 * @return	void
	 */
	private function ext_addNewEntry()	{
		// Add session stats
		$pid = t3lib_div::_GP('pid');	// Storage pid for users!
		$GLOBALS['TSFE']->fe_user->setKey('ses', $this->pidKey, $pid);
		$fields = array(
			'pid' => $pid,
			'fe_user' => intval($GLOBALS['TSFE']->fe_user->user['uid']),
			'session_login' => $GLOBALS['SIM_EXEC_TIME'],
			'last_page_hit' => $GLOBALS['SIM_EXEC_TIME'],
			'page_id' => $GLOBALS['TSFE']->id,	// unused in ext, compatibility value!
			'session_hit_counter' => 1,
			'ip_address' => t3lib_div::getIndpEnv('REMOTE_ADDR'),
		);
		$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_loginusertrack_stat', $fields);
		$sesstat_uid = $GLOBALS['TYPO3_DB']->sql_insert_id();
		$GLOBALS['TSFE']->fe_user->setKey('ses', $this->uidKey, $sesstat_uid);
		$GLOBALS['TSFE']->fe_user->storeSessionData();

		// Add page stats
		$fields = array(
			'pid' => $pid,
			'fe_user' => intval($GLOBALS['TSFE']->fe_user->user['uid']),
			'crdate' => $GLOBALS['SIM_EXEC_TIME'],
			'tstamp' => $GLOBALS['SIM_EXEC_TIME'],
			'page_id' => $GLOBALS['TSFE']->id,
			'sesstat_uid' => $sesstat_uid,
		);
		$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_loginusertrack_pagestat', $fields);
		$pagestat_uid = $GLOBALS['TYPO3_DB']->sql_insert_id();

		$ref = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('\TYPO3\CMS\Core\Database\ReferenceIndex');
		/* @var $ref \TYPO3\CMS\Core\Database\ReferenceIndex */
		$ref->updateRefIndexTable('tx_loginusertrack_stat', $sesstat_uid);
		$ref->updateRefIndexTable('tx_loginusertrack_pagestat', $pagestat_uid);
	}

	/**
	 * Updates statistics table with user data
	 *
	 * @return	void
	 */
	private function ext_updateEntry()	{
		$sesstat_uid = intval($GLOBALS['TSFE']->fe_user->getKey('ses', $this->uidKey));

		if ($sesstat_uid) {
			// Update session hit counter and length
			$fields = array(
				'last_page_hit' => $GLOBALS['SIM_EXEC_TIME'],
				'session_hit_counter' => 'session_hit_counter+1'
			);
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_loginusertrack_stat',
				'uid=' . $sesstat_uid,
				$fields, array('session_hit_counter'));
			if ($GLOBALS['TYPO3_DB']->sql_affected_rows()) {
				// If user exists in database, update current page stats (or insert new page stat record)
				$fields = array(
					'tstamp' => $GLOBALS['SIM_EXEC_TIME'],
					'hits' => 'hits+1',
				);
				$GLOBALS['TYPO3_DB']->exec_UPDATEquery('tx_loginusertrack_pagestat',
					'sesstat_uid=' . $sesstat_uid . ' AND page_id=' . $GLOBALS['TSFE']->id,
					$fields, array('hits'));
				if ($GLOBALS['TYPO3_DB']->sql_affected_rows() == 0) {
					// First visit to this page
					$fields = array(
						'pid' => $GLOBALS['TSFE']->fe_user->getKey('ses', $this->pidKey),
						'fe_user' => intval($GLOBALS['TSFE']->fe_user->user['uid']),
						'crdate' => $GLOBALS['SIM_EXEC_TIME'],
						'tstamp' => $GLOBALS['SIM_EXEC_TIME'],
						'page_id' => $GLOBALS['TSFE']->id,
						'sesstat_uid' => $sesstat_uid,
					);
					$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_loginusertrack_pagestat', $fields);

					$ref = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('\TYPO3\CMS\Core\Database\ReferenceIndex');
					/* @var $ref \TYPO3\CMS\Core\Database\ReferenceIndex */
					$ref->updateRefIndexTable('tx_loginusertrack_pagestat', $GLOBALS['TYPO3_DB']->sql_insert_id());
				}
			}
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/loginusertrack/class.tx_loginusertrack_tsfehook.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/loginusertrack/class.tx_loginusertrack_tsfehook.php']);
}

?>
