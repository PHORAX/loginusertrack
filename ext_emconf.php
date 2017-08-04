<?php

$EM_CONF[$_EXTKEY] = array(
    'title' => 'Login User Tracking',
    'description' => 'Logs in a separate table each time a frontend user logs in and further the timespan of the session and viewed pages. Backend module provides statistics over the data.',
    'category' => 'module',
    'version' => '2.1.0',
    'TYPO3_version' => '6.2.0-6.2.99',
    'state' => 'stable',
    'uploadfolder' => 1,
    'createDirs' => '',
    'modify_tables' => '',
    'clearcacheonload' => 1,
    'author' => 'Daniel Minder',
    'author_email' => 'typo3@minder.de',
);