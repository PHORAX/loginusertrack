<?php
return array(
    'ctrl' => array(
        'title' => 'LLL:EXT:loginusertrack/Resources/Private/Language/locallang_db.xml:tx_loginusertrack_stat',
        'label' => 'uid',
        'tstamp' => 'last_page_hit',
        'crdate' => 'session_login',
        'sortby' => 'last_page_hit',
        'default_sortby' => ' ORDER BY last_page_hit DESC',
        'hideTable' => true,
        'iconfile' => 'EXT:loginusertrack/ext_icon.gif',
    ),
    'interface' => Array(
        'maxDBListItems' => 60,
    ),
    'columns' => array(
        'fe_user' => array(
            'label' => 'LLL:EXT:loginusertrack/Resources/Private/Language/locallang_db.xml:tx_loginusertrack_stat.fe_user',
            'config' => array(
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'fe_users',
                'minsize' => 1,
                'maxsize' => 1,
                'size' => 1,
            ),
        ),
        'session_login' => array(
            'label' => 'LLL:EXT:loginusertrack/Resources/Private/Language/locallang_db.xml:tx_loginusertrack_stat.session_login',
            'config' => array(
                'type' => 'input',
                'size' => 15,
                'eval' => 'datetime',
            ),
        ),
        'last_page_hit' => array(
            'label' => 'LLL:EXT:loginusertrack/Resources/Private/Language/locallang_db.xml:tx_loginusertrack_stat.last_page_hit',
            'config' => array(
                'type' => 'input',
                'size' => 15,
                'eval' => 'datetime',
            ),
        ),
        'session_hit_counter' => array(
            'label' => 'LLL:EXT:loginusertrack/Resources/Private/Language/locallang_db.xml:tx_loginusertrack_stat.session_hit_counter',
            'config' => array(
                'type' => 'input',
                'eval' => 'int,required',
            ),
        ),
        'page_id' => array(
            'label' => 'LLL:EXT:loginusertrack/Resources/Private/Language/locallang_db.xml:tx_loginusertrack_stat.page_id',
            'config' => array(
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'pages',
                'minsize' => 1,
                'maxsize' => 1,
                'size' => 1,
            ),
        ),
        'ip_address' => array(
            'label' => 'LLL:EXT:loginusertrack/Resources/Private/Language/locallang_db.xml:tx_loginusertrack_stat.ip_address',
            'config' => array(
                'type' => 'input',
                'max' => 15,
            ),
        ),
    ),
    'types' => array(
        0 => array('showitem' => 'fe_user;;;;1,session_login,last_page_hit,session_hit_counter'),
    ),
);