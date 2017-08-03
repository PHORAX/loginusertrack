<?php

namespace DannyM\Loginusertrack;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2002 Kasper Skaarhoj (kasper@typo3.com)
 *  All rights reserved
 *
 *  This script is part of the Typo3 project. The Typo3 project is
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
 * Main backend module for the 'loginusertrack' extension.
 *
 * @author    Kasper Skaarhoj <kasper@typo3.com>
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;

class UserTrack extends \TYPO3\CMS\Backend\Module\BaseScriptClass
{
    protected $pageinfo;

    /**
     * Constructor
     */
    public function __construct()
    {
        $GLOBALS['LANG']->includeLLFile('EXT:loginusertrack/mod1/locallang.php');
        $GLOBALS['BE_USER']->modAccess($GLOBALS['MCONF'], true);
        parent::init();
    }

    /**
     * Adds items to the ->MOD_MENU array. Used for the function menu selector.
     *
     * @return    void
     */
    function menuConfig()
    {
        $this->MOD_MENU = Array(
            'function' => Array(
                '1' => $GLOBALS['LANG']->getLL('function1'),
                #			'2' => $GLOBALS['LANG']->getLL('function2'),
                '3' => $GLOBALS['LANG']->getLL('function3'),
                '4' => $GLOBALS['LANG']->getLL('function4'),
                '5' => $GLOBALS['LANG']->getLL('function5'),
            )
        );
        parent::menuConfig();
    }

    /**
     * Main function of the module.
     * Write the content to $this->content
     *
     * @return    void
     */
    function main()
    {
        // Access check!
        // The page will show only if there is a valid page and if this page may be viewed by the user
        $this->pageinfo = BackendUtility::readPageAccess($this->id, $this->perms_clause);
        $access = is_array($this->pageinfo) ? 1 : 0;

        // start the doc
        $this->doc = GeneralUtility::makeInstance(\TYPO3\CMS\Backend\Template\DocumentTemplate::class);
        $this->doc->backPath = $GLOBALS['BACK_PATH'];

        // TODO: does this make sense?
        if (($this->id && $access) || ($GLOBALS['BE_USER']->isAdmin() && !$this->id)) {
            // Form for the input field in a sub-module
            $this->doc->form = '<form action="' . BackendUtility::getModuleUrl($this->MCONF['name']) . '" method="post">';

            // Draw the header
            $headerSection = $this->doc->getHeader('pages',
                    $this->pageinfo,
                    $this->pageinfo['_thePath']) . '<br>' . $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.path') . ': ' . GeneralUtility::fixed_lgd_cs($this->pageinfo['_thePath'],
                    -50);

            $this->content .= $this->doc->startPage($GLOBALS['LANG']->getLL('title'));
            $this->content .= $this->doc->header($GLOBALS['LANG']->getLL('title'));
            $this->content .= $this->doc->spacer(5);
            $this->content .= $this->doc->section('',
                $this->doc->funcMenu($headerSection,
                    BackendUtility::getFuncMenu($this->id, 'SET[function]', $this->MOD_SETTINGS['function'],
                        $this->MOD_MENU['function']))
            );
            $this->content .= $this->doc->divider(5);

            // Render content:
            $this->moduleContent();

            // ShortCut
            if ($GLOBALS['BE_USER']->mayMakeShortcut()) {
                $this->content .= $this->doc->spacer(20) . $this->doc->section('',
                        $this->doc->makeShortcutIcon('id', implode(',', array_keys($this->MOD_MENU)),
                            $this->MCONF['name']));
            }

        } else {
            // If no access or if ID == zero
            $this->content .= $this->doc->startPage($GLOBALS['LANG']->getLL('title'));
            $this->content .= $this->doc->header($GLOBALS['LANG']->getLL('title'));
            $this->content .= $this->doc->spacer(5);
        }
        $this->content .= $this->doc->spacer(10);
    }

    /**
     * Prints out the module HTML
     *
     * @return    void
     */
    function printContent()
    {
        $this->content .= $this->doc->endPage();
        echo $this->content;
    }

    /**
     * Generates the module content with a switch-construct
     *
     * @return    void
     */
    function moduleContent()
    {
        $userId = intval(GeneralUtility::_GP('useruid'));
        $sessId = intval(GeneralUtility::_GP('sessid'));
        switch ((string)$this->MOD_SETTINGS['function']) {
            case 1:  // Login log
                $list = array();
                $query = 'SELECT tx_loginusertrack_stat.*,fe_users.username,fe_users.name,fe_users.uid AS user_uid FROM tx_loginusertrack_stat,fe_users WHERE fe_users.uid=tx_loginusertrack_stat.fe_user' .
                    ($userId ? ' AND fe_users.uid=' . intval($userId) : '') .
                    ($sessId ? ' AND tx_loginusertrack_stat.uid=' . $sessId : '') .
                    ' AND fe_users.pid = ' . intval($this->id) .
                    BackendUtility::deleteClause('fe_users') .
                    ' ORDER BY session_login DESC LIMIT 200';
                $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))) {
                    $modParam = array('id' => $this->id, 'useruid' => $row['user_uid']);
                    if ($userId) {
                        $modParam['sessid'] = $row['uid'];
                    }
                    $list[] = '<tr bgcolor="' . $this->doc->bgColor4 . '">
						<td nowrap>' . date($GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'] . ' ' . $GLOBALS['TYPO3_CONF_VARS']['SYS']['hhmm'],
                            $row['session_login']) . '</td>
						<td nowrap>' . BackendUtility::calcAge(time() - $row['session_login'],
                            $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.minutesHoursDaysYears')) . '</td>
						<td nowrap><a href="' . BackendUtility::getModuleUrl($this->MCONF['name'],
                            $modParam) . '">' . $row['username'] . '</a></td>
						<td nowrap>' . $row['name'] . '</td>
						<td nowrap>' . BackendUtility::calcAge($row['last_page_hit'] - $row['session_login'],
                            $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.minutesHoursDaysYears')) . '</td>
						<td>' . $row['session_hit_counter'] . '</td>
                        <td>' . $row['ip_address'] . '</td>
					</tr>
					';
                }
                $GLOBALS['TYPO3_DB']->sql_free_result($res);

                $content = '<table border="0" cellpadding="1" cellspacing="1" width="100%">
				<tr bgcolor="' . $this->doc->bgColor5 . '">
					<td><strong>' . $GLOBALS['LANG']->getLL('header_datetime') . '</strong></td>
					<td><strong>' . $GLOBALS['LANG']->getLL('header_age') . '</strong></td>
					<td><strong>' . $GLOBALS['LANG']->getLL('header_username') . '</strong></td>
					<td><strong>' . $GLOBALS['LANG']->getLL('header_name') . '</strong></td>
					<td><strong>' . $GLOBALS['LANG']->getLL('header_session_lgd') . '</strong></td>
					<td><strong>' . $GLOBALS['LANG']->getLL('header_pagehits') . '</strong></td>
					<td><strong>' . $GLOBALS['LANG']->getLL('header_ipaddress') . '</strong></td>
				</tr>
				' . implode('', $list) . '</table>';

                if ($userId > 0) {
                    $inst = GeneralUtility::makeInstance('DannyM\\Loginusertrack\\PageStats');
                    /* @var $inst tx_loginusertrack_pagestats */
                    $modParam = array('id' => $this->id);
                    if ($sessId) {
                        $modParam['useruid'] = $userId;
                    }
                    $content = '<a href="' . BackendUtility::getModuleUrl($this->MCONF['name'], $modParam) .
                        '"><strong>' . $GLOBALS['LANG']->getLL($sessId ? 'modulecont_listAllSessions' : 'modulecont_listAllUsers') .
                        '</strong></a><br /> <br />' . $content .
                        $this->doc->section($GLOBALS['LANG']->getLL('header_pagestats'),
                            $sessId ?
                                $inst->getPageStatsForSession($this->doc, $sessId) :
                                $inst->getPageStats($this->doc, $userId)) .
                        '<br /> <br />';
                }

                $this->content .= $this->doc->section($GLOBALS['LANG']->getLL('mainheader_log'), $content, 0, 1);
                break;
            case 3:  // Monthly view
                $times = array();
                $times[0] = time();
                for ($a = 1; $a <= 12; $a++) {
                    $times[$a] = mktime(0, 0, 0, date('m') + 1 - $a, 1);
                }

                $content = '';
                for ($a = 0; $a < 12; $a++) {
                    $list = array();
                    $query = 'SELECT tx_loginusertrack_stat.*,fe_users.username,fe_users.name,fe_users.uid AS user_uid, count(*) AS counter, max(session_login) AS last_login FROM tx_loginusertrack_stat,fe_users WHERE fe_users.uid=tx_loginusertrack_stat.fe_user' .
                        ($userId > 0 ? ' AND fe_users.uid=' . intval($userId) : '') .
                        ' AND fe_users.pid = ' . intval($this->id) .
                        ' AND session_login<' . intval($times[$a]) . ' AND session_login>=' . intval($times[$a + 1]) .
                        BackendUtility::deleteClause('fe_users') .
                        ' GROUP BY fe_users.uid ORDER BY counter DESC LIMIT 200';
                    $res = $GLOBALS['TYPO3_DB']->sql_query($query);
                    while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))) {
                        $list[] = '<tr bgcolor="' . $this->doc->bgColor4 . '">
							<td nowrap>' . date($GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'] . ' ' . $GLOBALS['TYPO3_CONF_VARS']['SYS']['hhmm'],
                                $row['last_login']) . '</td>
							<td nowrap>' . BackendUtility::calcAge(time() - $row['last_login'],
                                $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.minutesHoursDaysYears')) . '</td>
							<td nowrap><a href="' . BackendUtility::getModuleUrl($this->MCONF['name'],
                                array('id' => $this->id, 'useruid' => $row['user_uid'])) . '">' . $row['username'] . '</a></td>
							<td nowrap>' . $row['name'] . '</td>
							<td>' . $row['counter'] . '</td>
						</tr>
						';
                    }
                    $GLOBALS['TYPO3_DB']->sql_free_result($res);

                    $content .= '
					<BR>
					' . $GLOBALS['LANG']->getLL('period') . ' <strong>' . date($GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'],
                            $times[$a + 1]) . ' - ' . date($GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'],
                            $times[$a] - 1) . '</strong><BR>
					<table border="0" cellpadding="1" cellspacing="1" width="100%">
					<tr bgColor="' . $this->doc->bgColor5 . '">
						<td><strong>' . $GLOBALS['LANG']->getLL('header_datetime') . '</strong></td>
						<td><strong>' . $GLOBALS['LANG']->getLL('header_age') . '</strong></td>
						<td><strong>' . $GLOBALS['LANG']->getLL('header_username') . '</strong></td>
						<td><strong>' . $GLOBALS['LANG']->getLL('header_name') . '</strong></td>
						<td><strong>' . $GLOBALS['LANG']->getLL('header_logins') . '</strong></td>
					</tr>

					' . implode('', $list) . '</table>';
                }

                if ($userId > 0) {
                    $content = '<a href="' . BackendUtility::getModuleUrl($this->MCONF['name'],
                            array('id' => $this->id)) . '"><strong>' . $GLOBALS['LANG']->getLL('modulecont_listAllUsers') . '</strong></a><br>' . $content;
                }

                $this->content .= $this->doc->section($GLOBALS['LANG']->getLL('mainheader_monthly'), $content, 0, 1);
                break;
            case 4:  // Inactive users
                $inst = GeneralUtility::makeInstance('DannyM\\Loginusertrack\\LastLogin');
                /* @var $inst tx_loginusertrack_lastlogin */
                $content = $inst->main($this->id, $this, '');
                break;
            case 5:  // Active users
                $inst = GeneralUtility::makeInstance('DannyM\\Loginusertrack\\LastLogin');
                /* @var $inst tx_loginusertrack_lastlogin */
                $content = $inst->main($this->id, $this, 'active');
                break;
        }
    }
}
