<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	'ADWGOE.' . $_EXTKEY,
	'Runenprojekt',
	'Runenprojekt Frontent Plugin'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
		'ADWGOE.' . $_EXTKEY,
		'RuneS',
		'RuneS Frontent Plugin'
		);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'RuneS Database');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_adwgoerunes_domain_model_example', 'EXT:adw_goe_runes/Resources/Private/Language/locallang_csh_tx_adwgoerunes_domain_model_example.xlf');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_adwgoerunes_domain_model_example');
