<?php

// Make instance:
$SOBE = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('DannyM\\Loginusertrack\\UserTrack');
$SOBE->init();
$SOBE->main();
$SOBE->printContent();
