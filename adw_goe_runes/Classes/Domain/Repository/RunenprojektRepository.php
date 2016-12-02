<?php 
namespace ADWGOE\AdwGoeRunes\Domain\Repository;

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
 * Diese Klasse funktioniert anders als die normalen Extbase Repositories, da sie direkt auf der DB arbeitet
 * und MySQL-Anfragen verwendet. Hier werden also die Anfragen an die Runenprojekt-Datenbank formuliert.
 * @package adw_goe_runes
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class RunenprojektRepository extends \TYPO3\CMS\Extbase\Persistence\Repository  {
	private
		$dbHost = 'jens-bahr.com',
		$db = 'jensbahr_runes',
		$dbUsername = 'jensbahr_extbase',
		$dbPassword = 'extbase1234';
	
	/**
	 * Die Datenbankverbindung.
	 */
	private $dbHandle;
	
	/**
	 * Wird aufgerufen, wenn dieses Repository instantiiert wird; verbindet mit der DB
	 */
	public function initializeObject() {
		$this->connectDatabase();
	}
	
	/**
	 * Verbindet dieses Repository mit der Datenbank
	 */
	private function connectDatabase() {
		$this->dbHandle = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Database\\DatabaseConnection');
		$this->dbHandle->setDatabaseHost($this->dbHost);
		$this->dbHandle->setDatabaseUsername($this->dbUsername);
		$this->dbHandle->setDatabasePassword($this->dbPassword);
		$this->dbHandle->setDatabaseName($this->db);
		$this->dbHandle->sql_pconnect();
		$this->dbHandle->sql_select_db();
	}
	
	/**
	 * Gibt eine Liste aller Funde in der Runenprojekt-Datenbank
	 * @return Liste aller Funde in der Runenprojekt-Datenbank
	 */
	public function getFindList() {
		return $this->dbHandle->sql_query("SELECT * FROM rp_find ORDER BY findno");
	}
		
	/**
	 * Gibt eine Lister aller Wörter oder german. Stämme, welche mit einem bestimmten Buchstaben beginnen. Wird für
	 * Anfrage 16, "Wurzel", und Anfrage 17, "Germanischer Stamm", verwendet
	 * @param mode Entweder 'gmc' oder 'root', je nachdem wonach gefragt wird (germanischer Stamm / Wurzel)
	 * @param AFB Anfangsbuchstabe
	 * @return Lister aller Wörter oder german. Stämme, welche mit einem bestimmten Buchstaben beginnen
	 */
	public function getWordsOrderedStartingLetter($AFB, $mode) {
		return $this->dbHandle->sql_query(
				"SELECT word.".$mode.", word.wordno, word.word "
				." FROM rp_word word "
				." WHERE word.".$mode." NOT LIKE '...%' AND word.".$mode." NOT LIKE '/%'  "
				." AND word.".$mode." <> ' ' AND (word.word NOT LIKE '*%') AND (word.probable NOT LIKE '%-%' OR word.probable IS NULL)  "
				." AND word.".$mode." COLLATE utf8_bin LIKE '".$AFB."%' "
				." GROUP BY word.".$mode.", word.wordno, word.word ORDER BY word.".$mode.", word.word");
	}
	
	/**
	 * Gibt eine Liste aller Funde mit einem bestimmten Anfangsbuchstaben, georndet nach den übergebenen Parametern
	 * @return Liste aller Funde mit einem bestimmten Anfangsbuchstaben
	 */
	public function getFindListOrderedStartingLetter($AFB, $field, $field2) {
		// Hier muss collate to utf8_bin verwendet werden, damit z.B. A und Ä unterschieden werden
		return $this->dbHandle->sql_query("SELECT * FROM rp_find WHERE $field COLLATE utf8_bin LIKE '$AFB%' AND genuity NOT LIKE '???' ORDER BY $field, $field2");
	}
}

?>