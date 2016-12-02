<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
		'ADWGOE.' . $_EXTKEY,
		'RuneS',
		array(
				'Runes' => 'alphabetListe, steckbrief, errorReport',
		),
		// non-cacheable actions
		array(
				'Runes' => 'alphabetListe, steckbrief, errorReport',
		)
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'ADWGOE.' . $_EXTKEY,
	'Runenprojekt',
	array(
		'Runenprojekt' => 'alphabetListe, steckbrief, errorReport',
	),
	// non-cacheable actions
	array(
		'Runenprojekt' => 'alphabetListe, steckbrief, errorReport',
	)
);
