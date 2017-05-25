<?php
if (!defined ('TYPO3_MODE')) {
	die ('Access denied.');
}

if (TYPO3_MODE === 'BE')	{
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule("web","txloginusertrackM1","before:info",\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY)."mod1/");
}

$TCA['tx_loginusertrack_stat'] = array(
	'ctrl' => array (
		'title' => 'LLL:EXT:loginusertrack/locallang_db.xml:tx_loginusertrack_stat',
		'label' => 'uid',
		'tstamp' => 'last_page_hit',
		'crdate' => 'session_login',
		'sortby' => 'last_page_hit',
		'default_sortby' => ' ORDER BY last_page_hit DESC',
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'hideTable' => true,
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY).'ext_icon.gif',
	)
);
t3lib_extMgm::allowTableOnStandardPages('tx_loginusertrack_stat');

$TCA['tx_loginusertrack_pagestat'] = array(
	'ctrl' => array (
		'title' => 'LLL:EXT:loginusertrack/locallang_db.xml:tx_loginusertrack_pagestat',
		'label' => 'uid',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'sortby' => 'crdate',
		'default_sortby' => ' ORDER BY crdate DESC',
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'hideTable' => true,
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY).'ext_icon.gif',
	)
);
t3lib_extMgm::allowTableOnStandardPages('tx_loginusertrack_pagestat');
