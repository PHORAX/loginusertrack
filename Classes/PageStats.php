<?php

namespace DannyM\Loginusertrack;

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
 * Print detailed session statistics
 *
 * @author    Kasper Skaarhoj <kasper@typo3.com>
 * @author    Dmitry Dulepov <dmitry@typo3.org>
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;

class PageStats
{
    /**
     * Makes report about visited pages.
     *
     * @param    mediumDoc $doc Document (like mediumDoc)
     * @param    int $user User ID
     * @param    int $periodStart Start period
     * @param    int $periodEnd End period
     * @return    string    Generated HTML
     */
    function getPageStatsForSession(&$doc, $session_id)
    {
        /* @var $doc \TYPO3\CMS\Backend\Template\MediumDocumentTemplate */
        $content = '<table width="100%" border="0" cellpadding="1" cellspacing="1">' .
            '<tr bgcolor="' . $doc->bgColor5 . '">' .
            '<td><strong>' . $GLOBALS['LANG']->getLL('header_pid') . '</strong></td>' .
            '<td><strong>' . $GLOBALS['LANG']->getLL('header_pagetitle') . '</strong></td>' .
            '<td><strong>' . $GLOBALS['LANG']->getLL('header_pagehits') . '</strong></td>' .
            '<td><strong>' . $GLOBALS['LANG']->getLL('header_firsthit') . '</strong></td>' .
            '<td><strong>' . $GLOBALS['LANG']->getLL('header_lasthit') . '</strong></td>' .
            '</tr>';
        // Get records
        $res = $GLOBALS['TYPO3_DB']->sql_query('SELECT t1.page_id,t2.title,t1.hits,t1.crdate, t1.tstamp ' .
            'FROM tx_loginusertrack_pagestat t1 LEFT JOIN pages t2 ON ' .
            't1.page_id=t2.uid WHERE sesstat_uid=' . intval($session_id) .
            BackendUtility::deleteClause('pages', 't2') .
            ' ORDER BY t1.hits DESC');
        $num = 0;
        $numResults = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
        while ($num < 64 && false != ($ar = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))) {
            $content .= '<tr bgcolor="' . $doc->bgColor4 . '"><td>' .
                $ar['page_id'] . '</td><td>' .
                '<a target="_blank" href="' . GeneralUtility::getIndpEnv('TYPO3_REQUEST_HOST') . '/index.php?id=' . $ar['page_id'] . '">' .
                htmlspecialchars($ar['title']) . '</a></td><td>' .
                $ar['hits'] . '</td><td>' .
                date($GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'] . ' ' . $GLOBALS['TYPO3_CONF_VARS']['SYS']['hhmm'],
                    $ar['crdate']) . '</td><td>' .
                date($GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'] . ' ' . $GLOBALS['TYPO3_CONF_VARS']['SYS']['hhmm'],
                    $ar['tstamp']) . '</td></tr>';
            $num++;
        }
        $GLOBALS['TYPO3_DB']->sql_free_result($res);
        if ($num < $numResults) {
            $content .= '<tr><td colspan="4">' .
                sprintf($GLOBALS['LANG']->getLL('message_moreresults'), $numResults - $num) .
                '</td></tr>';
        }
        $content .= '</table>';
        return $content;
    }

    /**
     * Makes report about visited pages.
     *
     * @param    mediumDoc $doc Document (like mediumDoc)
     * @param    int $user User ID
     * @param    int $periodStart Start period
     * @param    int $periodEnd End period
     * @return    string    Generated HTML
     */
    function getPageStats(&$doc, $user, $periodStart = 0, $periodEnd = 0)
    {
        /* @var $doc \TYPO3\CMS\Backend\Template\MediumDocumentTemplate */
        $content = '<table width="100%" border="0" cellpadding="1" cellspacing="1">' .
            '<tr bgcolor="' . $doc->bgColor5 . '">' .
            '<td><strong>' . $GLOBALS['LANG']->getLL('header_pid') . '</strong></td>' .
            '<td><strong>' . $GLOBALS['LANG']->getLL('header_pagetitle') . '</strong></td>' .
            '<td><strong>' . $GLOBALS['LANG']->getLL('header_numsessions') . '</strong></td>' .
            '<td><strong>' . $GLOBALS['LANG']->getLL('header_pagehits') . '</strong></td>' .
            '<td><strong>' . $GLOBALS['LANG']->getLL('header_firsthit') . '</strong></td>' .
            '<td><strong>' . $GLOBALS['LANG']->getLL('header_lasthit') . '</strong></td>' .
            '</tr>';
        // Get records
        $res = $GLOBALS['TYPO3_DB']->sql_query(
            'SELECT COUNT(page_id) AS num_sessions, SUM(hits) AS num_hits, ' .
            'MIN(t1.crdate) AS crdate, MAX(t1.tstamp) AS tstamp, page_id, title FROM ' .
            'tx_loginusertrack_pagestat t1 LEFT JOIN pages t2 ON ' .
            't1.page_id=t2.uid WHERE fe_user=' . intval($user) .
            BackendUtility::deleteClause('pages', 't2') .
            ' GROUP BY page_id ORDER BY hits DESC'
        );
        $num = 0;
        $numResults = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
        while ($num < 64 && false != ($ar = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))) {
            $content .= '<tr bgcolor="' . $doc->bgColor4 . '"><td>' .
                $ar['page_id'] . '</td><td>' .
                '<a target="_blank" href="' . GeneralUtility::getIndpEnv('TYPO3_REQUEST_HOST') . '/index.php?id=' . $ar['page_id'] . '">' .
                htmlspecialchars($ar['title']) . '</a></td><td>' .
                $ar['num_sessions'] . '</td><td>' .
                $ar['num_hits'] . '</td><td>' .
                date($GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'] . ' ' . $GLOBALS['TYPO3_CONF_VARS']['SYS']['hhmm'],
                    $ar['crdate']) . '</td><td>' .
                date($GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'] . ' ' . $GLOBALS['TYPO3_CONF_VARS']['SYS']['hhmm'],
                    $ar['tstamp']) . '</td></tr>';
            $num++;
        }
        $GLOBALS['TYPO3_DB']->sql_free_result($res);
        if ($num < $numResults) {
            $content .= '<tr><td colspan="4">' .
                sprintf($GLOBALS['LANG']->getLL('message_moreresults'), $numResults - $num) .
                '</td></tr>';
        }
        $content .= '</table>';
        return $content;
    }

}
