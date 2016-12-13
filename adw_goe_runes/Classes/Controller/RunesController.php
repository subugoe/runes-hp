<?php
namespace ADWGOE\AdwGoeRunes\Controller;


/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2016 Jens Bahr <ju.bahr@isfas.uni-kiel.de>, University of Kiel
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Der RuneS Controller nutzt die RunesRepository, um die Daten aus der Datenbank
 * an die Templates weiterzugeben. 
 * 
 * Es gibt nur einen Controller für das ganze RuneS-Projekt, und es wird immer dieselbe Action 
 * von allen Seiten aufgerufen. Für eine Seite wird im Template unter Setup angegeben, welche
 * Funktionen des Plugins auf dieser Seite angezeigt werden sollen.
 */
class RunesController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {
	/**
	 * Repository für RuneS
	 */
	protected $rsRepository = NULL;
	
	/**
	 * Initialisierung des Controllers
	 * @return void
	 */
	public function initializeAction() 	{
		$objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
		$rsRepository = $objectManager->get('ADWGOE\\AdwGoeRunes\\Domain\\Repository\\RunesRepository');
		$rsRepository->initializeObject();
		
		// Anfrage der Einstellungen im Typoscript
		if(TYPO3_MODE === 'BE') {
			$configurationManager = t3lib_div::makeInstance('Tx_Extbase_Configuration_BackendConfigurationManager');
			$this->settings = $configurationManager->getConfiguration(
					$this->request->getControllerExtensionName(),
					$this->request->getPluginName()
			);
		}
	}
	
	/**
	 * Diese Haupt-Action für RuneS ruft die anderen Actions auf, je nachdem,
	 * was im TypoScript-Template eingestellt wurde.
	 * @return void
	 */
	public function alphabetListeAction() {
		// Setup der Verbindung
		$objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
		$rsRepository = $objectManager->get('ADWGOE\\AdwGoeRunes\\Domain\\Repository\\RunesRepository');
		$rsRepository->initializeObject();
		
		// Template einstellen
		$this->view->setTemplatePathAndFilename('typo3conf/ext/adw_goe_runes/Resources/Private/Templates/Runes/AlphabetListe.html');
		
		$letterArray= array('Alle', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'Ä', 'Å', 'Ö', 'Ø');
		$AFB = $this->request->hasArgument('AFB') ? $this->request->getArgument('AFB') : 'A';
		$this->view->assign('letters', $letterArray);
		$this->view->assign('AFB', $AFB);
		$this->view->assign('findings', $rsRepository->getFindListOrderedStartingLetter($AFB));
	}
	
    /**
     * Action zum Anzeigen eines Steckbriefs mit bestimmter Fundnummer
     * @return void
     */
    public function steckbriefAction() {
    	// Template einstellen
    	$this->view->setTemplatePathAndFilename('typo3conf/ext/adw_goe_runes/Resources/Private/Templates/Runes/Steckbrief.html');
    	
    	// Verbindung aufbauen
    	$objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
    	$rsRepository = $objectManager->get('ADWGOE\\AdwGoeRunes\\Domain\\Repository\\RunesRepository');
    	$rsRepository->initializeObject();
    	    	    	
    	// Fundnummer einstellen oder, wenn noch keine übergeben wurde, festlegen (auf 1)
    	$findno = $this->request->hasArgument('findno') ? $this->request->getArgument('findno') : 1;
    	$this->view->assign('findno', $findno); 
    	$lang = $GLOBALS['TSFE']->sys_language_uid;
    	
    	// Daten abfragen und an den View übergeben
    	$this->view->assign('sigils', $rsRepository->getSigilsByFindno($findno));
    	$this->view->assign('images', $rsRepository->getImagesByFindno($findno));
    	$this->view->assign('museum', $rsRepository->getMuseumForFindno($findno));
    	$this->view->assign('findresults', $rsRepository->getFindDataByFindno($findno, $lang == 3 ? "en" : "de"));      
    
    }

    /**
     * Sendet einen Error Report dieser RuneS-Seite per Email an uns.
     * Hierfür muss die Typo3-Emailfunktion eingerichtet sein!
     * @return void
     */
	public function errorReportAction() {
		$arguments = $this->request->getArguments();

		$mail = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Mail\\MailMessage');
		$mail->setSubject('RuneS: Website-Fehlerreport');
		$mail->setFrom(array('noreply@jens-bahr.com' => 'RuneS System'));
		$mail->setTo(array('c.zimmermann@isfas.uni-kiel.de', 'u.zimmermann@isfas.uni-kiel.de', 'ju.bahr@isfas.uni-kiel.de', 'jens_bahr+fnd9ksygquikqfqc1ohn@boards.trello.com'));
		$mail->setBody('Ein Fehler wurde auf der RuneS-Website gemeldet.<br /><br />'.$arguments['internalMessage'].'<br /><br />'
				.'Person Name: '.$arguments['personName'].'<br />Person Mail: '.$arguments['personMail'].'<br />Person Message: '.$arguments['personMessage'], 'text/html', 'utf-8');
		$mail->send();
	}
    
}