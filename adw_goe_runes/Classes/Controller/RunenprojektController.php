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
 * Der Runenprojekt Controller nutzt die RunenprojektRepository, um die Daten aus der Datenbank
 * an die Templates weiterzugeben. 
 * 
 * Es gibt nur einen Controller für das ganze Runenprojekt, und es wird immer dieselbe Action 
 * von allen Seiten aufgerufen. Für eine Seite wird im Template unter Setup angegeben, welche
 * Funktionen des Plugins auf dieser Seite angezeigt werden sollen.
 */
class RunenprojektController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
	/**
	 * Das Repository
	 */
	protected $rpRepository = NULL;
	
	/**
	 * Initialisierung des Controllers
	 * @return void
	 */
	public function initializeAction() {
		$objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
		$rpRepository = $objectManager->get('ADWGOE\\AdwGoeRunes\\Domain\\Repository\\RunenprojektRepository');
		$rpRepository->initializeObject();
		
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
     * Action AlphabetListe
     * Einige Anfragen starten mit einer Liste, in welcher ein Anfangsbuchstabe ausgewählt wird. Diese Anfragen
     * nutzen diese Action: 1, 2, 8, 10, 15, 16, 17
     * @return void
     */
    public function alphabetListeAction() {
    	// Setup der Verbindung
    	$objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Extbase\\Object\\ObjectManager');
    	$rpRepository = $objectManager->get('ADWGOE\\AdwGoeRunes\\Domain\\Repository\\RunenprojektRepository');
    	$rpRepository->initializeObject();
    	    	
    	// Zuweisung der Anfangsbuchstaben, die je nach Anfragenart angezeigt werden sollen.
    	// Die pageid kommt aus dem Typoscript
    	$letterArray= array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'Ä', 'Å', 'Ö', 'Ø');
    	if($this->settings['pageid'] == 'germstamm') {
    		$letterArray= array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 'þ', 'ƀ', 'ǥ', 'ō', '?');
    	} else if($this->settings['pageid'] ==  'wurzel') {
    		$letterArray= array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 'ā', 'ī', 'þ', 'ƀ', 'ǥ', 'ō', '?');
    	}    	
    	$this->view->assign('letters', $letterArray);
    	
    	// Anfangsbuchstaben zuweisen und wenn nötig bestimmen
    	$AFB = $this->request->hasArgument('AFB') ? $this->request->getArgument('AFB') : $letterArray[0];
    	$this->view->assign('AFB', $AFB); 
    	
    	// Die Anfrage nach Funden mit diesem Anfangsbuchstaben ausführen
    	switch($this->settings['pageid']) {
    		case 'wurzel':
    			$this->view->assign('resultmode', 'wurzel');
    			$this->view->assign('findings', $rpRepository->getWordsOrderedStartingLetter($AFB, 'root'));
    			break;   			
    		
    		case 'germstamm':
    			$this->view->assign('resultmode', 'germstamm');
    			$this->view->assign('findings', $rpRepository->getWordsOrderedStartingLetter($AFB, 'gmc'));
    			break;
    			
    		default:
    			$this->view->assign('resultmode', 'default');
    			$this->view->assign('findings', $rpRepository->getFindListOrderedStartingLetter($AFB, "findspt", "object"));
    			break;
    	}
       
    }
    
    /**
     * Sendet einen Error Report dieser Runenprojekt-Seite per Email an uns.
     * Hierfür muss die Typo3-Emailfunktion eingerichtet sein!
     * @return void
     */
    public function errorReportAction() {
    	$arguments = $this->request->getArguments();
    
    	$mail = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Mail\\MailMessage');
    	$mail->setSubject('Runenprojekt: Website-Fehlerreport');
    	$mail->setFrom(array('noreply@jens-bahr.com' => 'Runenprojekt System'));
    	$mail->setTo(array('ju.bahr@isfas.uni-kiel.de', 'jens_bahr+fnd9ksygquikqfqc1ohn@boards.trello.com'));
    	$mail->setBody('Ein Fehler wurde auf der Runenprojekt-Website gemeldet.<br /><br />'.$arguments['internalMessage'].'<br /><br />'
    			.'Person Name: '.$arguments['personName'].'<br />Person Mail: '.$arguments['personMail'].'<br />Person Message: '.$arguments['personMessage'], 'text/html', 'utf-8');
    	$mail->send();
    }
    

}