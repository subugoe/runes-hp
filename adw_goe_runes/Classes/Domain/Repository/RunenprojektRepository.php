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
	 * Gibt die generellen Daten zu einem Fund. Etwa für einen Steckbrief.
	 * @param unknown $findno
	 */
	public function getFindData($findno) {
		return $this->dbHandle->sql_query("SELECT * FROM rp_find WHERE findno = ".$findno);
	}
	
	/**
	 * Gibt die generellen Bibliographie- und Literaturdaten zu einem Fund. 
	 * @param unknown $findno
	 */
	public function getBibData($findno) {
		return $this->dbHandle->sql_query("SELECT b.auth, b.title1, b.title2, b.year, b.picture, b.bibtype FROM rp_bib b, rp_findbib f WHERE f.findno=".$findno." AND b.bibno=f.bibno ORDER BY b.year ASC;");
	}
	
	    		
	/**
	 * Gibt die Deutungen, zu denen es eine Verbindung mit der gegebenen Fundnummer gibt
	 * @param unknown $findno
	 */
	public function getInterpretations($findno) {
		return $this->dbHandle->sql_query(
				 " SELECT i.intprno, i.intpr, i.trslgerm, i.language, i.reading, i.probable, i.comment "
				." FROM rp_intpr i"
				." WHERE EXISTS "
				."   (SELECT * FROM rp_fint f WHERE f.intprno = i.intprno AND f.findno = ".$findno.") "
				." ORDER BY i.intprno;");
	}
	
	/**
	 * Gibt die Literatur zu einer Deutung
	 * @param unknown $findno
	 */
	public function getIntBibData($intprno) {
		return $this->dbHandle->sql_query(
				 " SELECT * FROM rp_bib b "
				." WHERE EXISTS (SELECT * FROM rp_intbib ib WHERE ib.bibno = b.bibno AND ib.intprno = ".$intprno.") "
				." ORDER BY b.year ASC;");
	}		
	
	/**
	 * Gibt alle Bildlinks zu einem bestimmten Fund. Diese kommen aus der RuneS-Datenbank!
	 * @param findno Die Fundnummer des Funds
	 * @return alle Bildlinks zu einem bestimmten Fund
	 */
	public function getImagesByFindno($findno) {
		return $this->dbHandle->sql_query(
				"SELECT * FROM run_abbildung a "
				." WHERE EXISTS (SELECT * FROM run_link_fund_abbildung l WHERE l.findno = $findno AND a.id = l.id)");
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