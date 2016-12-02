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
 * und MySQL-Anfragen verwendet. Hier werden also die Anfragen an die RuneS-Datenbank formuliert.
 * @package adw_goe_runes
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class RunesRepository extends \TYPO3\CMS\Extbase\Persistence\Repository  {
	private
		$dbHost = 'jens-bahr.com',
		$db = 'jensbahr_runes',
		$dbUsername = 'jensbahr_extbase',
		$dbPassword = 'extbase1234';
	
	/**
	 * Die Datenbank-Verbindung
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
	 * Gibt eine Liste aller Funde mit einem bestimmten Anfangsbuchstaben, georndet nach dem Fundort.
	 * Wird für die alphabetische Übersichtsliste verwendet
	 * @return Liste aller Funde mit einem bestimmten Anfangsbuchstaben
	 */
	public function getFindListOrderedStartingLetter($AFB) {
		// Anfangsbuchstaben "Alle" funktioniert in SQL mit %
		$AFB = ($AFB == 'Alle' ? '%' : $AFB);
		
		// Hier muss collate to utf8_bin verwendet werden, damit z.B. A und Ä unterschieden werden
		return $this->dbHandle->sql_query(
				"SELECT p.findno, p.beschreibung_de, t.traeger_typ, t.suffix_de "
				." FROM run_position p, run_traeger t "
				." WHERE p.beschreibung_de COLLATE utf8_bin LIKE '$AFB%' AND t.findno = p.findno "
				." ORDER BY p.beschreibung_de, t.traeger_typ, t.suffix_de");
	}
	
	/**
	 * Gibt alle Siglen zu einem bestimmten Fund
	 * @param findno Die Fundnummer des Funds
	 * @return alle Siglen zu einem bestimmten Fund
	 */
	public function getSigilsByFindno($findno) {
		return $this->dbHandle->sql_query("SELECT * FROM run_sigle WHERE findno = $findno ORDER BY ist_hauptsigle DESC");
	}
	
	/**
	 * Gibt alle Bildlinks zu einem bestimmten Fund
	 * @param findno Die Fundnummer des Funds
	 * @return alle Bildlinks zu einem bestimmten Fund
	 */
	public function getImagesByFindno($findno) {
		return $this->dbHandle->sql_query(
				"SELECT * FROM run_abbildung a "
				." WHERE EXISTS (SELECT * FROM run_link_fund_abbildung l WHERE l.findno = $findno AND a.id = l.id)");
	}
	
	/**
	 * Gibt die Museumsdaten zu einem bestimmten Fund, falls vorhanden.
	 * @param findno Die Fundnummer des Funds
	 * @return alle Museumsdaten zu einem bestimmten Fund
	 */
	public function getMuseumForFindno($findno) {
		return $this->dbHandle->sql_query(
				"SELECT * FROM run_museum a "
				." WHERE EXISTS (SELECT * FROM run_aufenthaltsort l WHERE l.findno = $findno AND a.id = l.museum_id)");
	}
	
	/**
	 * Gibt die grundlegenden Daten für einen bestimmten Fund, um den Steckbrief anzuzeigen
	 * @param findno Die Fundnummer des Funds
	 * @return die grundlegenden Daten für einen bestimmten Fund
	 */
	public function getFindDataByFindno($findno) {
		return $this->dbHandle->sql_query("SELECT f.findno, f.fundjahr, f.kommentar_de, "
				." p.g_lat, p.g_long, p.beschreibung_de, "
				." t.traeger_typ, t.suffix_de, t.suffix_en, t.abmessungen, t.zustand as obj_zustand_de, t.vollstaendig as obj_vollst_de, t.material_spezial, "
				." t.dat_extern_von, t.dat_extern_bis, t.dat_art, "
				." m.bez_de as mat_de, m.bez_en as mat_en, m.materialklasse as matclass_de, mk.bez_en as matclass_en,  "
				." g.gemeinde, b.bezirk, lan.landschaft, lan.land, "
				." o.bez_de as obj_typ_de, o.bez_en as obj_typ_en, ok.bez_de as obj_class_de, ok.bez_en as obj_class_en, "
				." i.runenreihe as ins_runenreihe, i.zustand as ins_zustand, i.vollstaendig as ins_vollstaendig, i.hat_beizeichen, "
				." i.inschrift_charakter, i.kontext, i.translit, i.translit_graph, i.translit_gen, i.transcript, i.uebersetzung_de, i.uebersetzung_en, "
				." auf.modus as aufb_modus, auf.kommentar as aufb_kommentar, auf.g_lat as auf_g_lat, auf.g_long as auf_g_long, auf.inventarnummer "
				." FROM run_fund f, run_position p, run_gemeinde g, run_bezirk b, run_landschaft lan, run_traeger t, run_material m, "
				." run_materialklasse mk, run_objekttyp o, run_objektklasse ok, run_inschrift i, run_aufenthaltsort	auf "
				." WHERE f.findno = $findno AND p.findno = f.findno AND t.findno = f.findno AND g.id = p.gemeinde_id AND b.id = g.bezirk_id AND lan.id = b.landschaft_id "
				." AND m.id = t.material AND m.materialklasse = mk.bez_de AND o.id = t.objekttyp AND ok.bez_de = o.objektklasse AND i.findno = f.findno "
				." AND auf.findno = f.findno");
	}
}

?>