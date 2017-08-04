<?php
if (!defined('TYPO3_MODE')) {
    die ('Access denied.');
}

if (TYPO3_MODE === 'BE') {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule(
        'web',
        'txloginusertrackM1',
        'before:info',
        '',
        [
            'routeTarget' => \DannyM\Loginusertrack\Controller\LoginusertrackController::class . '::mainAction',
            'access' => 'group,user',
            'name' => 'web_txloginusertrackM1',
            'icon' => 'EXT:loginusertrack/Resources/Public/Icons/moduleicon.gif',
            'labels' => 'LLL:EXT:loginusertrack/Resources/Private/Language/locallang_mod.xml'
        ]
    );
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_loginusertrack_stat');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_loginusertrack_pagestat');