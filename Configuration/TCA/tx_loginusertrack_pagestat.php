<?php
return array(
    'ctrl' => array(
        'title' => 'LLL:EXT:loginusertrack/locallang_db.xml:tx_loginusertrack_pagestat',
        'label' => 'uid',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'sortby' => 'crdate',
        'default_sortby' => ' ORDER BY crdate DESC',
        'hideTable' => true,
        'iconfile' => 'EXT:loginusertrack/ext_icon.gif'
    ),
    'interface' => Array(
        'maxDBListItems' => 60,
    ),
    'columns' => array(
        'fe_user' => array(
            'label' => 'LLL:EXT:loginusertrack/locallang_db.xml:tx_loginusertrack_stat.fe_user',
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
            'label' => 'LLL:EXT:loginusertrack/locallang_db.xml:tx_loginusertrack_stat.session_login',
            'config' => array(
                'type' => 'input',
                'size' => 15,
                'eval' => 'datetime',
            ),
        ),
        'last_page_hit' => array(
            'label' => 'LLL:EXT:loginusertrack/locallang_db.xml:tx_loginusertrack_stat.last_page_hit',
            'config' => array(
                'type' => 'input',
                'size' => 15,
                'eval' => 'datetime',
            ),
        ),
        'session_hit_counter' => array(
            'label' => 'LLL:EXT:loginusertrack/locallang_db.xml:tx_loginusertrack_stat.session_hit_counter',
            'config' => array(
                'type' => 'input',
                'eval' => 'int,required',
            ),
        ),
        'page_id' => array(
            'label' => 'LLL:EXT:loginusertrack/locallang_db.xml:tx_loginusertrack_stat.page_id',
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
            'label' => 'LLL:EXT:loginusertrack/locallang_db.xml:tx_loginusertrack_stat.ip_address',
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


$GLOBALS['TCA']['tx_loginusertrack_pagestat'] = array(
    'ctrl' => $GLOBALS['TCA']['tx_loginusertrack_pagestat']['ctrl'],
    'interface' => Array(
        'maxDBListItems' => 60,
    ),
    'columns' => array(
        'fe_user' => array(
            'label' => 'LLL:EXT:loginusertrack/locallang_db.xml:tx_loginusertrack_stat.fe_user',
            'config' => array(
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'fe_users',
                'minsize' => 1,
                'maxsize' => 1,
                'size' => 1,
            ),
        ),
        'sesstat_uid' => array(
            'label' => 'LLL:EXT:loginusertrack/locallang_db.xml:tx_loginusertrack_pagestat.sesstat_uid',
            'config' => array(
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'tx_loginusertrack_stat',
                'minsize' => 1,
                'maxsize' => 1,
                'size' => 1,
            ),
        ),
        'page_id' => array(
            'label' => 'LLL:EXT:loginusertrack/locallang_db.xml:tx_loginusertrack_pagestat.page_id',
            'config' => array(
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'pages',
                'minsize' => 1,
                'maxsize' => 1,
                'size' => 1,
            ),
        ),
        'hits' => array(
            'label' => 'LLL:EXT:loginusertrack/locallang_db.xml:tx_loginusertrack_pagestat.hits',
            'config' => array(
                'type' => 'input',
                'eval' => 'int,required',
            ),
        ),
        'tstamp' => array(
            'label' => 'LLL:EXT:loginusertrack/locallang_db.xml:tx_loginusertrack_stat.tstamp',
            'config' => array(
                'type' => 'input',
                'size' => 15,
                'eval' => 'datetime',
            ),
        ),
        'crdate' => array(
            'label' => 'LLL:EXT:loginusertrack/locallang_db.xml:tx_loginusertrack_stat.crdate',
            'config' => array(
                'type' => 'input',
                'eval' => 'int,required',
            ),
        ),
    ),
    'types' => array(
        0 => array('showitem' => 'fe_user;;;;1, sesstat_uid, page_id, hits'),
    ),
);