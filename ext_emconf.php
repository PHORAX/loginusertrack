<?php

$EM_CONF[$_EXTKEY] = array(
    'title' => 'Login User Tracking',
    'description' => 'Logs in a separate table each time a frontend user logs in and further the timespan of the session and viewed pages. Backend module provides statistics over the data.',
    'category' => 'module',
    'version' => '3.0.0',
    'TYPO3_version' => '7.6.0-8.7.999',
    'state' => 'stable',
    'uploadfolder' => 1,
    'createDirs' => '',
    'modify_tables' => '',
    'clearcacheonload' => 1,
    'author' => 'Daniel Minder, Felix Kopp',
    'author_email' => 'typo3@minder.de, hello@phorax.com',
);