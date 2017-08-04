<?php

namespace DannyM\Loginusertrack\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Fluid\ViewHelpers\Be\InfoboxViewHelper;

class LoginusertrackController extends \TYPO3\CMS\Backend\Module\BaseScriptClass
{
    protected $pageinfo;

    /**
     * @var string
     */
    protected $moduleName = 'web_txloginusertrackM1';

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->iconFactory = GeneralUtility::makeInstance(IconFactory::class);
        $this->moduleTemplate = GeneralUtility::makeInstance(ModuleTemplate::class);
        $this->getLanguageService()->includeLLFile('EXT:lang/Resources/Private/Language/locallang_mod_web_func.xlf');
        $this->MCONF = [
            'name' => $this->moduleName,
        ];
    }

    /**
     * Injects the request object for the current request or subrequest
     * Then checks for module functions that have hooked in, and renders menu etc.
     *
     * @param ServerRequestInterface $request the current request
     * @param ResponseInterface $response
     * @return ResponseInterface the response with the content
     */
    public function mainAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        $GLOBALS['SOBE'] = $this;
        $this->init();

        // Checking for first level external objects
        $this->checkExtObj();

        // Checking second level external objects
        $this->checkSubExtObj();
        $this->main();

        $this->moduleTemplate->setContent($this->content);

        $response->getBody()->write($this->moduleTemplate->renderContent());
        return $response;
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
                '1' => $this->getLanguageService()->sL('LLL:EXT:loginusertrack/Resources/Private/Language/locallang.xml:function1'),
                '3' => $this->getLanguageService()->sL('LLL:EXT:loginusertrack/Resources/Private/Language/locallang.xml:function3'),
                '4' => $this->getLanguageService()->sL('LLL:EXT:loginusertrack/Resources/Private/Language/locallang.xml:function4'),
                '5' => $this->getLanguageService()->sL('LLL:EXT:loginusertrack/Resources/Private/Language/locallang.xml:function5'),
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

            $this->content .= $this->doc->startPage($this->getLanguageService()->sL('LLL:EXT:loginusertrack/Resources/Private/Language/locallang.xml:title'));
            $this->content .= $this->doc->header($this->getLanguageService()->sL('LLL:EXT:loginusertrack/Resources/Private/Language/locallang.xml:title'));

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
                $this->content .= $this->doc->section('',
                        $this->doc->makeShortcutIcon('id', implode(',', array_keys($this->MOD_MENU)),
                            $this->MCONF['name']));
            }

        } else {
            // If no access or if ID == zero
            $this->content .= $this->doc->startPage($this->getLanguageService()->sL('LLL:EXT:loginusertrack/Resources/Private/Language/locallang.xml:title'));
            $this->content .= $this->doc->header($this->getLanguageService()->sL('LLL:EXT:loginusertrack/Resources/Private/Language/locallang.xml:title'));
        }
        

        $this->content .= $this->doc->endPage();
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
					<td><strong>' . $this->getLanguageService()->sL('LLL:EXT:loginusertrack/Resources/Private/Language/locallang.xml:header_datetime') . '</strong></td>
					<td><strong>' . $this->getLanguageService()->sL('LLL:EXT:loginusertrack/Resources/Private/Language/locallang.xml:header_age') . '</strong></td>
					<td><strong>' . $this->getLanguageService()->sL('LLL:EXT:loginusertrack/Resources/Private/Language/locallang.xml:header_username') . '</strong></td>
					<td><strong>' . $this->getLanguageService()->sL('LLL:EXT:loginusertrack/Resources/Private/Language/locallang.xml:header_name') . '</strong></td>
					<td><strong>' . $this->getLanguageService()->sL('LLL:EXT:loginusertrack/Resources/Private/Language/locallang.xml:header_session_lgd') . '</strong></td>
					<td><strong>' . $this->getLanguageService()->sL('LLL:EXT:loginusertrack/Resources/Private/Language/locallang.xml:header_pagehits') . '</strong></td>
					<td><strong>' . $this->getLanguageService()->sL('LLL:EXT:loginusertrack/Resources/Private/Language/locallang.xml:header_ipaddress') . '</strong></td>
				</tr>
				' . implode('', $list) . '</table>';

                if ($userId > 0) {
                    /* @var $inst tx_loginusertrack_pagestats */
                    $modParam = array('id' => $this->id);
                    if ($sessId) {
                        $modParam['useruid'] = $userId;
                    }
                    $content = '<a href="' . BackendUtility::getModuleUrl($this->MCONF['name'], $modParam) .
                        '"><strong>' . $this->getLanguageService()->sL($sessId ? 'modulecont_listAllSessions' : 'modulecont_listAllUsers') .
                        '</strong></a><br /> <br />' . $content .
                        $this->doc->section($this->getLanguageService()->sL('header_pagestats'),
                            $sessId ?
                                $this->getPageStatsForSession($this->doc, $sessId) :
                                $this->getPageStats($this->doc, $userId)) .
                        '<br /> <br />';
                }

                $this->content .= $this->doc->section($this->getLanguageService()->sL('LLL:EXT:loginusertrack/Resources/Private/Language/locallang.xml:mainheader_log'), $content, 0, 1);
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
					' . $this->getLanguageService()->sL('period') . ' <strong>' . date($GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'],
                            $times[$a + 1]) . ' - ' . date($GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'],
                            $times[$a] - 1) . '</strong><BR>
					<table border="0" cellpadding="1" cellspacing="1" width="100%">
					<tr bgColor="' . $this->doc->bgColor5 . '">
						<td><strong>' . $this->getLanguageService()->sL('LLL:EXT:loginusertrack/Resources/Private/Language/locallang.xml:header_datetime') . '</strong></td>
						<td><strong>' . $this->getLanguageService()->sL('LLL:EXT:loginusertrack/Resources/Private/Language/locallang.xml:header_age') . '</strong></td>
						<td><strong>' . $this->getLanguageService()->sL('LLL:EXT:loginusertrack/Resources/Private/Language/locallang.xml:header_username') . '</strong></td>
						<td><strong>' . $this->getLanguageService()->sL('LLL:EXT:loginusertrack/Resources/Private/Language/locallang.xml:header_name') . '</strong></td>
						<td><strong>' . $this->getLanguageService()->sL('LLL:EXT:loginusertrack/Resources/Private/Language/locallang.xml:header_logins') . '</strong></td>
					</tr>

					' . implode('', $list) . '</table>';
                }

                if ($userId > 0) {
                    $content = '<a href="' . BackendUtility::getModuleUrl($this->MCONF['name'],
                            array('id' => $this->id)) . '"><strong>' . $this->getLanguageService()->sL('modulecont_listAllUsers') . '</strong></a><br>' . $content;
                }

                $this->content .= $this->doc->section($this->getLanguageService()->sL('mainheader_monthly'), $content, 0, 1);
                break;
            case 4:  // Inactive users
                $content = $this->lastLogin($this->id, $this, '');
                break;
            case 5:  // Active users
                $content = $this->lastLogin($this->id, $this, 'active');
                break;
        }
    }


    /**
     * Former class PageStats
     */

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


    var $daySpanBetweenCrAndLogin = 20;
    var $subject = 'Login User Notice!';

    /**
     * Main function for both "Active users" and "Inactive users"
     *
     * @param    integer $id : The current page id of the module. This is where the users are sought for
     * @param    object $pObj : Reference to the parent object of the module ($this)
     * @param    string $mode : "active": Shows active users. Default: Shows in active.
     * @return    void        Sets the content in $pObj->content
     */
    function lastLogin($id, &$pObj, $mode)
    {
        $content = '';

        // Get days back.
        $daysBack = \TYPO3\CMS\Core\Utility\MathUtility::forceIntegerInRange(GeneralUtility::_GP('daysBack'), -1, 1000);

        $content .= '
			' . $this->getLanguageService()->sL('LLL:EXT:loginusertrack/Resources/Private/Language/locallang.xml:lastlogin_main_enterTheDaysSince', '1') . ':<br>
			<input type="text" name="daysBack" value="' . htmlspecialchars($daysBack ? $daysBack : 100) . '">
			<input type="hidden" name="id" value="' . htmlspecialchars($id) . '">
			<input type="submit" name="_" value="' . $this->getLanguageService()->sL('LLL:EXT:loginusertrack/Resources/Private/Language/locallang.xml:lastlogin_main_setDaysSinceLast', '1') . '">
			<br>
		';

        // Total number of users:
        list($row) = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('COUNT(*) AS t', 'fe_users',
            'pid=' . intval($id) . BackendUtility::deleteClause('fe_users'));
        $content .= '<strong>' . $this->getLanguageService()->sL('LLL:EXT:loginusertrack/Resources/Private/Language/locallang.xml:lastlogin_main_totalNumberOfUsers',
                '1') . ':</strong> ' . $row['t'];

        $pObj->content .= $pObj->doc->section($this->getLanguageService()->sL('LLL:EXT:loginusertrack/Resources/Private/Language/locallang.xml:Last_logins'), $content, 0, 1);

        //
        if ($daysBack) {
            switch ($mode) {
                case 'active':
                    $content = $this->showActive($id, $pObj, $daysBack);
                    $pObj->content .= $pObj->doc->section(sprintf($this->getLanguageService()->sL('LLL:EXT:loginusertrack/Resources/Private/Language/locallang.xml:lastlogin_main_activeUsersLoggedIn',
                        '1'), $daysBack), $content, 0, 1);
                    break;
                default:
                    $content = $this->removeOld($id, $pObj, $daysBack);
                    $pObj->content .= $pObj->doc->section(sprintf($this->getLanguageService()->sL('LLL:EXT:loginusertrack/Resources/Private/Language/locallang.xml:lastlogin_removeold_usersWithLastLogin',
                        '1'), $daysBack), $content, 0, 1);
                    break;
            }
        }
    }

    /**
     * Code for the "inactive" function, enabling us to remove users and send them warning emails.
     *
     * @param    integer $id : Page id, see main
     * @param    object $pObj : Reference to the parent object of the module ($this)
     * @param    integer $daysBack : The number of days to use as limit. Coming from input-field.
     * @return    string        HTML content
     */
    function removeOld($id, &$pObj, $daysBack)
    {
        $content = '';

        $tcemain_cmd = array();
        $testUsername = trim(GeneralUtility::_GP('test_username'));
        $emailMsg = trim(GeneralUtility::_GP('email_msg'));
        $action = GeneralUtility::_GP('sendWarningEmail') ? (GeneralUtility::_GP('_DELETE') ? 'delete' : 'email') : '';    // Set to blank, "delete" or "email"
        $this->subject = $this->getLanguageService()->sL('LLL:EXT:loginusertrack/Resources/Private/Language/locallang.xml:lastlogin_removeold_subject');
        $senderName = GeneralUtility::_GP('header_name');
        $senderMail = GeneralUtility::_GP('header_email');

        // old users:
        $query = 'SELECT uid,username,email,name,lastlogin,password FROM fe_users WHERE pid=' . intval($id) .
            ($testUsername ? ' AND username="' . addslashes($testUsername) . '"' : ' AND lastlogin < ' . (time() - $daysBack * 24 * 3600)) .
            BackendUtility::deleteClause('fe_users') .
            ' ORDER BY name';
        $res = $GLOBALS['TYPO3_DB']->sql_query($query);

        $tRows[] = '<tr bgcolor="' . $GLOBALS['TBE_TEMPLATE']->bgColor5 . '">
			<td nowrap><strong>' . $this->getLanguageService()->sL('LLL:EXT:loginusertrack/Resources/Private/Language/locallang.xml:lastlogin_removeold_username', '1') . '</strong></td>
			<td nowrap><strong>' . $this->getLanguageService()->sL('LLL:EXT:loginusertrack/Resources/Private/Language/locallang.xml:lastlogin_removeold_name', '1') . '</strong></td>
			<td nowrap><strong>' . $this->getLanguageService()->sL('LLL:EXT:loginusertrack/Resources/Private/Language/locallang.xml:lastlogin_removeold_email', '1') . '</strong></td>
			<td nowrap><strong>' . $this->getLanguageService()->sL('LLL:EXT:loginusertrack/Resources/Private/Language/locallang.xml:lastlogin_removeold_lastLogin', '1') . '</strong></td>
		</tr>';

        $emailsAcc = array();
        while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))) {
            $tRows[] = '<tr bgcolor="' . $GLOBALS['TBE_TEMPLATE']->bgColor4 . '">
				<td nowrap>' . htmlspecialchars($row['username']) . '</td>
				<td nowrap>' . htmlspecialchars($row['name']) . '</td>
				<td nowrap>' . htmlspecialchars($row['email']) . '</td>
				<td nowrap>' . htmlspecialchars(date($GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'], $row['lastlogin'])) . '</td>
			</tr>';

            if (trim($row['email'])) {
                $emailsAcc[] = preg_replace('/--/', '&#45;&#45;', trim($row['email']));
            }

            if ($action == 'email') {
                $this->sendWarningEmail($row, $emailMsg, $senderMail, $senderName);
            } elseif ($action == 'delete') {
                $tcemain_cmd['fe_users'][$row['uid']]['delete'] = 1;
                if ($emailMsg) {
                    $this->sendWarningEmail($row, $emailMsg, $senderMail, $senderName);
                }
            }
        }
        $num_rows = $GLOBALS['TYPO3_DB']->sql_num_rows($res);

        if ($action == 'delete') {
            $content .= '<span style="color:red;">' . sprintf($this->getLanguageService()->sL('LLL:EXT:loginusertrack/Resources/Private/Language/locallang.xml:lastlogin_removeold_deletedSUsers',
                    '1'), $num_rows) . '</span><br>';
            if ($emailMsg) {
                $content .= '<span style="color:red;">' . sprintf($this->getLanguageService()->sL('LLL:EXT:loginusertrack/Resources/Private/Language/locallang.xml:lastlogin_removeold_sentDeletedEmails',
                        '1'), $num_rows) . '</span><br>';
            }

            $tce = GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\DataHandling\\DataHandler');
            $tce->start(array(), $tcemain_cmd);
            $tce->process_cmdmap();
        } else {
            if ($action == 'email') {
                $content .= '<span style="color:red;">' . sprintf($this->getLanguageService()->sL('LLL:EXT:loginusertrack/Resources/Private/Language/locallang.xml:lastlogin_removeold_sentSWarningEmails',
                        '1'), $num_rows) . '</span><br>';
            }

            $content .= '
			<strong>' . $this->getLanguageService()->sL('LLL:EXT:loginusertrack/Resources/Private/Language/locallang.xml:lastlogin_removeold_numberOfInactiveUsers',
                    '1') . '</strong> ' . $num_rows . '<br>
			(' . sprintf($this->getLanguageService()->sL('LLL:EXT:loginusertrack/Resources/Private/Language/locallang.xml:lastlogin_removeold_didNotLoginDuring', '1'),
                    '<strong>' . $daysBack . '</strong>') . ')<br>
			<table border="0" cellpadding="1" cellspacing="2">' . implode('
			', $tRows) . '</table>';

            $msg = $emailMsg ? $emailMsg : sprintf($this->getLanguageService()->sL('LLL:EXT:loginusertrack/Resources/Private/Language/locallang.xml:lastlogin_removeold_hiNameYouAre', '1'),
                GeneralUtility::getIndpEnv('TYPO3_SITE_URL'), $daysBack, GeneralUtility::getIndpEnv('TYPO3_SITE_URL'));

            $delMsg = sprintf($this->getLanguageService()->sL('LLL:EXT:loginusertrack/Resources/Private/Language/locallang.xml:lastlogin_removeold_hiNameYouWere', '1'),
                GeneralUtility::getIndpEnv('TYPO3_SITE_URL'));

            $content .= '
<br>
<strong>' . $this->getLanguageService()->sL('LLL:EXT:loginusertrack/Resources/Private/Language/locallang.xml:lastlogin_removeold_sendingAWarningEmail', '1') . '</strong><br>
<br>
<textarea name="email_msg" rows="20">' . htmlspecialchars($msg) . '</textarea><br>
<strong>' . $this->getLanguageService()->sL('LLL:EXT:loginusertrack/Resources/Private/Language/locallang.xml:lastlogin_removeold_senderName', '1') . '</strong><br>
<input type="text" name="header_name" value="' . htmlspecialchars(GeneralUtility::_GP('header_name') ? GeneralUtility::_GP('header_name') : $GLOBALS['BE_USER']->user['realName']) . '"><br>
<strong>' . $this->getLanguageService()->sL('LLL:EXT:loginusertrack/Resources/Private/Language/locallang.xml:lastlogin_removeold_senderEmail', '1') . '</strong><br>
<input type="text" name="header_email" value="' . htmlspecialchars(GeneralUtility::_GP('header_email') ? GeneralUtility::_GP('header_email') : $GLOBALS['BE_USER']->user['email']) . '"><br>
' . $this->getLanguageService()->sL('LLL:EXT:loginusertrack/Resources/Private/Language/locallang.xml:lastlogin_removeold_sendATestTo',
                    '1') . ' <input type="text" name="test_username" value="' . htmlspecialchars(GeneralUtility::_GP('test_username')) . '"><br>
<input type="submit" name="sendWarningEmail" value="' . $this->getLanguageService()->sL('LLL:EXT:loginusertrack/Resources/Private/Language/locallang.xml:lastlogin_removeold_sendWarningEmail',
                    '1') . '"> - <input type="submit" name="_" value="' . $this->getLanguageService()->sL('LLL:EXT:loginusertrack/Resources/Private/Language/locallang.xml:lastlogin_removeold_sendUpdate',
                    '1') . '"><br>

<!--

' . implode(', ', $emailsAcc) . '

-->

<div style="background-color: red; color:white; padding-left: 5px;">
<input type="checkbox" name="_DELETE" value="" onclick="
	document.forms[0].sendWarningEmail.value=this.checked?\'' . $this->getLanguageService()->sL('LLL:EXT:loginusertrack/Resources/Private/Language/locallang.xml:lastlogin_removeold_deleteUsers',
                    '1') . '\':\'' . $this->getLanguageService()->sL('LLL:EXT:loginusertrack/Resources/Private/Language/locallang.xml:lastlogin_removeold_sendWarningEmail', '1') . '\';
	if (this.checked)	{
		this.value = document.forms[0].email_msg.value;
		document.forms[0].email_msg.value=unescape(\'' . rawurlencode(trim($delMsg)) . '\');
	} else {
		document.forms[0].email_msg.value = this.value;
	}
	"> <strong>' . $this->getLanguageService()->sL('LLL:EXT:loginusertrack/Resources/Private/Language/locallang.xml:lastlogin_removeold_delete', '1') . '</strong><br>
' . $this->getLanguageService()->sL('LLL:EXT:loginusertrack/Resources/Private/Language/locallang.xml:lastlogin_showactive_ifYouCheckThis') . '
</div>


		';
        }

        $GLOBALS['TYPO3_DB']->sql_free_result($res);
        return $content;
    }

    /**
     * Shows active users in a table
     *
     * @param    integer $id : Page id, see main function
     * @param    object $pObj : Reference to the parent object of the module ($this)
     * @param    integer $daysBack : Number of days to use as limit for active users. See description in the output in the module.
     * @return    string        HTML output
     */
    function showActive($id, &$pObj, $daysBack)
    {

        // Total number of 'active' users were created more than XX days ago and having login within the last XX days
        $orderBy = GeneralUtility::_GP('orderby');
        $query = 'SELECT uid,username,email,name,lastlogin FROM fe_users WHERE pid=' . intval($id) .
            ' AND lastlogin > ' . (time() - $daysBack * 24 * 3600) .
            ' AND crdate < ' . (time() - $daysBack * 24 * 3600) .
            ' AND lastlogin-crdate > ' . (24 * 3600 * $this->daySpanBetweenCrAndLogin) .
            BackendUtility::deleteClause('fe_users') .
            ' ORDER BY ' . (GeneralUtility::inList('username,name,email,lastlogin',
                $orderBy) ? $orderBy . ($orderBy == 'lastlogin' ? ' DESC' : '') : 'name');
        $res = $GLOBALS['TYPO3_DB']->sql_query($query);
        $tRows = array();

        $tRows[] = '<tr bgcolor="' . $GLOBALS['TBE_TEMPLATE']->bgColor5 . '">
			<td nowrap><strong><a href="' . BackendUtility::getModuleUrl($pObj->MCONF['name'], array(
                'id' => $id,
                'daysBack' => $daysBack,
                'orderby' => 'username'
            )) . '">' . $this->getLanguageService()->sL('LLL:EXT:loginusertrack/Resources/Private/Language/locallang.xml:lastlogin_showactive_username', '1') . '</a></strong></td>
			<td nowrap><strong><a href="' . BackendUtility::getModuleUrl($pObj->MCONF['name'], array(
                'id' => $id,
                'daysBack' => $daysBack,
                'orderby' => 'name'
            )) . '">' . $this->getLanguageService()->sL('LLL:EXT:loginusertrack/Resources/Private/Language/locallang.xml:lastlogin_showactive_name', '1') . '</a></strong></td>
			<td nowrap><strong><a href="' . BackendUtility::getModuleUrl($pObj->MCONF['name'], array(
                'id' => $id,
                'daysBack' => $daysBack,
                'orderby' => 'email'
            )) . '">' . $this->getLanguageService()->sL('LLL:EXT:loginusertrack/Resources/Private/Language/locallang.xml:lastlogin_showactive_email', '1') . '</a></strong></td>
			<td nowrap><strong><a href="' . BackendUtility::getModuleUrl($pObj->MCONF['name'], array(
                'id' => $id,
                'daysBack' => $daysBack,
                'orderby' => 'lastlogin'
            )) . '">' . $this->getLanguageService()->sL('LLL:EXT:loginusertrack/Resources/Private/Language/locallang.xml:lastlogin_showactive_lastLogin', '1') . '</a></strong></td>
		</tr>';

        $emailsAcc = array();
        while (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))) {
            if (trim($row['email'])) {
                $emailsAcc[] = preg_replace('/--/', '&#45;&#45;', trim($row['email']));
            }

            $tRows[] = '<tr bgcolor="' . $GLOBALS['TBE_TEMPLATE']->bgColor4 . '">
				<td nowrap>' . htmlspecialchars($row['username']) . '</td>
				<td nowrap>' . htmlspecialchars($row['name']) . '</td>
				<td nowrap>' . htmlspecialchars($row['email']) . '</td>
				<td nowrap>' . htmlspecialchars(date($GLOBALS['TYPO3_CONF_VARS']['SYS']['ddmmyy'], $row['lastlogin'])) . '</td>
			</tr>';
        }
        $content .= '
		<strong>' . $this->getLanguageService()->sL('LLL:EXT:loginusertrack/Resources/Private/Language/locallang.xml:lastlogin_showactive_numberOfActiveUsers',
                '1') . '</strong> ' . $GLOBALS['TYPO3_DB']->sql_num_rows($res) . '<br>
' . sprintf($this->getLanguageService()->sL('LLL:EXT:loginusertrack/Resources/Private/Language/locallang.xml:lastlogin_sendwarnin_usersAreShownHere'), $daysBack, $daysBack,
                $this->daySpanBetweenCrAndLogin) . '<br>

<!--

' . implode(', ', $emailsAcc) . '

-->


		<table border="0" cellpadding="1" cellspacing="1" width="100%">' . implode('
		', $tRows) . '</table>';

        $GLOBALS['TYPO3_DB']->sql_free_result($res);

        return $content;
    }

    /**
     * Sends a warning email to the fe_users record being input /
     *
     * @param    array $row : A fe_users row with fields like uid, username, email, name, password
     * @param    string $emailMsg : The message to send. Can contain markers, ###USERNAME###, ###NAME###, ###PASSWORD###
     * @param    string $senderMail : The mail address of the sender
     * @param    string $senderName : The name of the sender
     * @return    void
     */
    function sendWarningEmail($row, $emailMsg, $senderMail, $senderName)
    {
        $recipient = trim($row['email']);
        if ($recipient) {
            $markers = array('###USERNAME###', '###NAME###');
            $subst = array($row['username'], $row['name']);
            $message = str_replace($markers, $subst, $emailMsg);

            $mail = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Mail\\MailMessage');
            $mail->setFrom(array($senderMail => $senderName))
                ->setTo(array($recipient => $row['name']))
                ->setSubject($this->subject)
                ->setBody($message)
                ->send();
        }
    }

}
