-- --------------------------------------------------------
-- Host:                         10.30.2.130
-- Server Version:               10.3.11-MariaDB-1:10.3.11+maria~bionic - mariadb.org binary distribution
-- Server Betriebssystem:        debian-linux-gnu
-- HeidiSQL Version:             9.5.0.5196
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


-- Exportiere Datenbank Struktur für mt_lumen_inventory
CREATE DATABASE IF NOT EXISTS `mt_lumen_inventory` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_german2_ci */;
USE `mt_lumen_inventory`;

-- Exportiere Struktur von Tabelle mt_lumen_inventory.ClientChangeLog
DROP TABLE IF EXISTS `ClientChangeLog`;
CREATE TABLE IF NOT EXISTS `ClientChangeLog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_origin` int(11) NOT NULL,
  `timestamp` datetime NOT NULL,
  `table` char(50) COLLATE utf8_german2_ci NOT NULL,
  `type` int(11) NOT NULL COMMENT 'Create = 1, Update = 2,  Delete = 3',
  `key` int(11) NOT NULL,
  `uuid` varchar(50) COLLATE utf8_german2_ci NOT NULL,
  `obj` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `oldObj` longtext COLLATE utf8_german2_ci DEFAULT NULL,
  `mods` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `synced` tinyint(4) NOT NULL DEFAULT 0,
  `error` varchar(100) COLLATE utf8_german2_ci DEFAULT NULL,
  `remods` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  `jobid` int(11) DEFAULT NULL,
  `devid` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=306 DEFAULT CHARSET=utf8 COLLATE=utf8_german2_ci;

-- Daten Export vom Benutzer nicht ausgewählt
-- Exportiere Struktur von Tabelle mt_lumen_inventory.Devices
DROP TABLE IF EXISTS `Devices`;
CREATE TABLE IF NOT EXISTS `Devices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` char(150) COLLATE utf8_german2_ci NOT NULL,
  `user_agent` char(150) COLLATE utf8_german2_ci DEFAULT NULL,
  `created_uid` char(150) COLLATE utf8_german2_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `modified_uid` char(150) COLLATE utf8_german2_ci DEFAULT NULL,
  `modified_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_german2_ci;

-- Daten Export vom Benutzer nicht ausgewählt
-- Exportiere Struktur von Funktion mt_lumen_inventory.dexieInterfaceName
DROP FUNCTION IF EXISTS `dexieInterfaceName`;
DELIMITER //
CREATE DEFINER=`root`@`%` FUNCTION `dexieInterfaceName`(name TEXT) RETURNS text CHARSET utf8 COLLATE utf8_german2_ci
RETURN
(
	SELECT CONCAT( 'DBDI',  UPPER( SUBSTR(name, 1, 1)), SUBSTR( snakeToCamelCase(name), 2))
)//
DELIMITER ;

-- Exportiere Struktur von Funktion mt_lumen_inventory.dexieTable
DROP FUNCTION IF EXISTS `dexieTable`;
DELIMITER //
CREATE DEFINER=`root`@`%` FUNCTION `dexieTable`(name TEXT) RETURNS text CHARSET utf8 COLLATE utf8_german2_ci
RETURN
(
	SELECT CONCAT( LOWER( SUBSTR(name, 1, 1)), SUBSTR( snakeToCamelCase(name), 2))
)//
DELIMITER ;

-- Exportiere Struktur von Tabelle mt_lumen_inventory.Gebaeude
DROP TABLE IF EXISTS `Gebaeude`;
CREATE TABLE IF NOT EXISTS `Gebaeude` (
  `gid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `mid` bigint(20) NOT NULL,
  `mandanten_id` bigint(20) NOT NULL,
  `Gebaeude` char(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Adresse` char(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `modified_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `created_uid` int(11) NOT NULL,
  `modified_uid` int(11) DEFAULT NULL,
  PRIMARY KEY (`gid`),
  UNIQUE KEY `Gebaeude_Adresse` (`mandanten_id`,`Gebaeude`,`Adresse`),
  KEY `mid` (`mid`),
  KEY `gebaeude_gebaeude_index` (`Gebaeude`),
  KEY `Gebaeude` (`Gebaeude`)
) ENGINE=InnoDB AUTO_INCREMENT=95 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Daten Export vom Benutzer nicht ausgewählt
-- Exportiere Struktur von Tabelle mt_lumen_inventory.Hersteller
DROP TABLE IF EXISTS `Hersteller`;
CREATE TABLE IF NOT EXISTS `Hersteller` (
  `hid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `for_jobid` bigint(20) DEFAULT NULL,
  `Hersteller` char(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `created_uid` int(11) DEFAULT NULL,
  `modified_uid` int(11) DEFAULT NULL,
  `created_jobid` int(11) DEFAULT NULL,
  `modified_jobid` int(11) DEFAULT NULL,
  `created_device_id` int(11) DEFAULT NULL,
  `modified_device_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`hid`),
  UNIQUE KEY `hersteller_hersteller_unique` (`Hersteller`),
  UNIQUE KEY `unique_uuid` (`uuid`)
) ENGINE=InnoDB AUTO_INCREMENT=111 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Daten Export vom Benutzer nicht ausgewählt
-- Exportiere Struktur von Tabelle mt_lumen_inventory.Images
DROP TABLE IF EXISTS `Images`;
CREATE TABLE IF NOT EXISTS `Images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` char(50) COLLATE utf8_german2_ci NOT NULL COMMENT 'Globale über mehere Rechner hinweg eindeutige ID, Wichtig für Zusammenführung verteilt erzeugter Daten',
  `for_jobid` bigint(20) DEFAULT NULL,
  `name` char(50) COLLATE utf8_german2_ci NOT NULL,
  `desc` text COLLATE utf8_german2_ci DEFAULT NULL,
  `size` int(11) NOT NULL,
  `width` int(11) NOT NULL,
  `height` int(11) NOT NULL,
  `type` char(50) COLLATE utf8_german2_ci NOT NULL,
  `gcuuid` char(50) COLLATE utf8_german2_ci NOT NULL COMMENT 'uuid des globalen ObjektKatalogs',
  `url` char(150) COLLATE utf8_german2_ci DEFAULT NULL,
  `data_binary` longblob DEFAULT NULL,
  `data_url` longtext COLLATE utf8_german2_ci DEFAULT NULL COMMENT 'Kann in Html einem image als Textblock an src zugewisen werden',
  `revnr` int(11) NOT NULL DEFAULT 1 COMMENT 'Wird bei jeder Änderung hochgezählt',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_uid` int(11) NOT NULL,
  `created_device_id` int(11) NOT NULL DEFAULT 0,
  `created_jobid` int(11) NOT NULL DEFAULT 0,
  `modified_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `modified_uid` int(11) DEFAULT NULL,
  `modified_device_id` int(11) DEFAULT 0,
  `modified_jobid` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_uuid` (`uuid`)
) ENGINE=InnoDB AUTO_INCREMENT=142 DEFAULT CHARSET=utf8 COLLATE=utf8_german2_ci;

-- Daten Export vom Benutzer nicht ausgewählt
-- Exportiere Struktur von Tabelle mt_lumen_inventory.Import_Auswahllisten
DROP TABLE IF EXISTS `Import_Auswahllisten`;
CREATE TABLE IF NOT EXISTS `Import_Auswahllisten` (
  `importKey` varchar(150) COLLATE utf8_german2_ci DEFAULT NULL,
  `uuid` varchar(35) COLLATE utf8_german2_ci DEFAULT NULL,
  `hash` char(32) COLLATE utf8_german2_ci DEFAULT NULL,
  `inventur_id` int(11) DEFAULT NULL,
  `auswid` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `mandant` int(11) DEFAULT NULL,
  `feld` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `wert` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `definition` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `abhaengig` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `von_feld` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `von_wert` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `aend_stamp` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_german2_ci;

-- Daten Export vom Benutzer nicht ausgewählt
-- Exportiere Struktur von Tabelle mt_lumen_inventory.Import_Inventar
DROP TABLE IF EXISTS `Import_Inventar`;
CREATE TABLE IF NOT EXISTS `Import_Inventar` (
  `importKey` varchar(150) COLLATE utf8_german2_ci DEFAULT NULL,
  `AlteObjektId` varchar(50) COLLATE utf8_german2_ci DEFAULT NULL,
  `uuid` varchar(50) COLLATE utf8_german2_ci DEFAULT NULL,
  `hash` char(32) COLLATE utf8_german2_ci DEFAULT NULL,
  `inventur_id` int(11) DEFAULT NULL,
  `invid` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `mandant` int(11) DEFAULT NULL,
  `gebaeude` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `raum_nr` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `raum_bez` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `iv_nr` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `iv_text` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `typ` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `gruppe` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `kst` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `kategorie` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `nutzer` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `lieferant` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `pruefer` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `zustand` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `anlagennr` int(11) DEFAULT NULL,
  `seriennr` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `geraetnr` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `fibunr` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `betrag` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `flaeche` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `gewicht` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `baujahr` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `datum` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `pruef_dat` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `garantdat` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `bild` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `datei1` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `datei2` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `datei3` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `datei4` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `datei5` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `memo1` text COLLATE utf8_german2_ci DEFAULT NULL,
  `memo2` text COLLATE utf8_german2_ci DEFAULT NULL,
  `memo3` text COLLATE utf8_german2_ci DEFAULT NULL,
  `memo4` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `memo5` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `memo6` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `memo7` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `memo8` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `memo9` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `memo` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `indtext1` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `indtext2` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `indtext3` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `indtext4` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `indtext5` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `raum_memo1` text COLLATE utf8_german2_ci DEFAULT NULL,
  `raum_memo2` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `raum_memo3` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `raum_memo4` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `raum_memo5` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `raum_memo6` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `raum_memo7` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `raum_memo8` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `raum_memo9` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `raum_memo` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `raumdatei1` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `raumdatei2` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `raumdatei3` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `raumdatei4` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `raumdatei5` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `raumbild` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `rindtext1` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `rindtext2` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `rindtext3` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `rindtext4` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `rindtext5` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `raum_mit1` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `raum_mit2` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `raum_mit3` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `raum_mit4` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `raum_mit5` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `raum_mit6` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `raum_mit7` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `raum_mit8` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `raum_mit9` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `aend_stamp` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `etage` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `r_flaeche` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `hersteller` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `kauf_dat` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `datei6` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `datei7` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `datei8` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `datei9` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `datei10` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  KEY `inventur_id` (`inventur_id`),
  KEY `invid` (`invid`),
  KEY `iv_nr` (`iv_nr`),
  KEY `mandant` (`mandant`),
  KEY `raum_nr` (`raum_nr`),
  KEY `gebaeude` (`gebaeude`),
  KEY `inv_text` (`iv_text`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_german2_ci;

-- Daten Export vom Benutzer nicht ausgewählt
-- Exportiere Struktur von Tabelle mt_lumen_inventory.Import_Inventur
DROP TABLE IF EXISTS `Import_Inventur`;
CREATE TABLE IF NOT EXISTS `Import_Inventur` (
  `importKey` varchar(150) COLLATE utf8_german2_ci DEFAULT NULL,
  `uuid` varchar(50) COLLATE utf8_german2_ci DEFAULT NULL,
  `hash` char(32) COLLATE utf8_german2_ci DEFAULT NULL,
  `inventur_id` bigint(20) DEFAULT NULL,
  `mandant_id` int(11) DEFAULT NULL,
  `inventur_name` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_german2_ci;

-- Daten Export vom Benutzer nicht ausgewählt
-- Exportiere Struktur von Tabelle mt_lumen_inventory.Import_Kontakte
DROP TABLE IF EXISTS `Import_Kontakte`;
CREATE TABLE IF NOT EXISTS `Import_Kontakte` (
  `importKey` varchar(150) COLLATE utf8_german2_ci DEFAULT NULL,
  `uuid` varchar(50) COLLATE utf8_german2_ci DEFAULT NULL,
  `hash` char(32) COLLATE utf8_german2_ci DEFAULT NULL,
  `inventur_id` int(11) DEFAULT NULL,
  `kontaktid` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `mandant` int(11) DEFAULT NULL,
  `kontakt` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `k_nachname` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `k_vorname` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `k_adresse1` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `k_adresse2` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `k_adresse3` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `k_strasse` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `k_land` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `k_plz` int(11) DEFAULT NULL,
  `k_ort` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `k_telefong` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `k_telefonp` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `k_mobil` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `k_telefax` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `k_email` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `k_internet` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `k_memo1` text COLLATE utf8_german2_ci DEFAULT NULL,
  `k_memo2` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `k_memo3` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `k_memo4` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `k_memo5` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `k_memo6` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `k_memo7` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `k_memo8` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `k_memo9` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `k_memo` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `k_bild` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `k_gruppe` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `k_funktion` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `k_nl` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `k_team` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `k_gebiet` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `k_kst` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `k_eintritt` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `k_austritt` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `k_datei1` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `k_datei2` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `k_datei3` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `k_datei4` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `k_datei5` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `aend_stamp` datetime DEFAULT NULL,
  KEY `inventur_id` (`inventur_id`),
  KEY `kontaktid` (`kontaktid`),
  KEY `mandant` (`mandant`),
  KEY `kontakt` (`kontakt`),
  KEY `k_nachname` (`k_nachname`),
  KEY `k_vorname` (`k_vorname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_german2_ci;

-- Daten Export vom Benutzer nicht ausgewählt
-- Exportiere Struktur von Tabelle mt_lumen_inventory.Import_Mandanten
DROP TABLE IF EXISTS `Import_Mandanten`;
CREATE TABLE IF NOT EXISTS `Import_Mandanten` (
  `mid` int(11) DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  `name` varchar(50) COLLATE utf8_german2_ci DEFAULT NULL,
  `invMandator` int(11) DEFAULT NULL,
  `useRFID` tinyint(4) DEFAULT NULL,
  `csvVersion` varchar(10) COLLATE utf8_german2_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_german2_ci;

-- Daten Export vom Benutzer nicht ausgewählt
-- Exportiere Struktur von Tabelle mt_lumen_inventory.Import_Nutzer
DROP TABLE IF EXISTS `Import_Nutzer`;
CREATE TABLE IF NOT EXISTS `Import_Nutzer` (
  `importKey` varchar(150) COLLATE utf8_german2_ci DEFAULT NULL,
  `uuid` varchar(50) COLLATE utf8_german2_ci DEFAULT NULL,
  `hash` char(32) COLLATE utf8_german2_ci DEFAULT NULL,
  `inventur_id` bigint(20) DEFAULT NULL,
  `nutzerid` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `mandant` int(11) DEFAULT NULL,
  `nutzer` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `nachname` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `vorname` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `strasse` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `plz` int(11) DEFAULT NULL,
  `ort` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `tel_p` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `tel_g` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `fax` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `tel_mobil` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `n_funktion` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `n_nl` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `n_team` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `n_gebiet` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `n_kst` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `n_gruppe` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `n_memo1` text COLLATE utf8_german2_ci DEFAULT NULL,
  `n_memo2` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `n_memo3` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `n_memo4` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `n_memo5` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `n_memo6` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `n_memo7` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `n_memo8` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `n_memo9` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `n_memo` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `n_eintritt` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `n_austritt` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `aend_stamp` datetime DEFAULT NULL,
  KEY `inventur_id` (`inventur_id`),
  KEY `nutzerid` (`nutzerid`),
  KEY `mandant` (`mandant`),
  KEY `nutzer` (`nutzer`),
  KEY `nachname` (`nachname`),
  KEY `vorname` (`vorname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_german2_ci;

-- Daten Export vom Benutzer nicht ausgewählt
-- Exportiere Struktur von Tabelle mt_lumen_inventory.Import_Objektbuch
DROP TABLE IF EXISTS `Import_Objektbuch`;
CREATE TABLE IF NOT EXISTS `Import_Objektbuch` (
  `oid` bigint(20) NOT NULL AUTO_INCREMENT,
  `jobid` bigint(20) NOT NULL,
  `Gruppe` char(50) COLLATE utf8_german2_ci NOT NULL,
  `Code128` char(50) COLLATE utf8_german2_ci NOT NULL,
  `md5` char(64) COLLATE utf8_german2_ci NOT NULL,
  `ID` char(50) COLLATE utf8_german2_ci NOT NULL,
  `Wert` varchar(500) COLLATE utf8_german2_ci NOT NULL,
  `Bild` varchar(250) COLLATE utf8_german2_ci DEFAULT NULL,
  PRIMARY KEY (`oid`),
  KEY `ID` (`ID`),
  KEY `jobid` (`jobid`)
) ENGINE=InnoDB AUTO_INCREMENT=6293 DEFAULT CHARSET=utf8 COLLATE=utf8_german2_ci;

-- Daten Export vom Benutzer nicht ausgewählt
-- Exportiere Struktur von Tabelle mt_lumen_inventory.Import_Raeume
DROP TABLE IF EXISTS `Import_Raeume`;
CREATE TABLE IF NOT EXISTS `Import_Raeume` (
  `importKey` varchar(150) COLLATE utf8_german2_ci DEFAULT NULL,
  `uuid` varchar(50) COLLATE utf8_german2_ci DEFAULT NULL,
  `hash` char(32) COLLATE utf8_german2_ci DEFAULT NULL,
  `inventur_id` bigint(20) DEFAULT NULL,
  `raumid` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `mandant` int(11) DEFAULT NULL,
  `raum_nr` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `raum_bez` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `gebaeude` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `mit1` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `mit2` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `mit3` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `mit4` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `mit5` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `mit6` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `mit7` datetime DEFAULT NULL,
  `mit8` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `mit9` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `raum_memo1` text COLLATE utf8_german2_ci DEFAULT NULL,
  `raum_memo2` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `raum_memo3` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `raum_memo4` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `raum_memo5` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `raum_memo6` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `raum_memo7` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `raum_memo8` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `raum_memo9` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `raum_memo` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `datei1` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `datei2` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `datei3` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `datei4` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `datei5` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `bild` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `indtext1` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `indtext2` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `indtext3` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `indtext4` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `indtext5` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `aend_stamp` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `etage` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  `r_flaeche` varchar(255) COLLATE utf8_german2_ci DEFAULT NULL,
  KEY `inventur_id` (`inventur_id`),
  KEY `raumid` (`raumid`),
  KEY `mandant` (`mandant`),
  KEY `raum_nr` (`raum_nr`),
  KEY `gebaeude` (`gebaeude`),
  KEY `raum_bez` (`raum_bez`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_german2_ci;

-- Daten Export vom Benutzer nicht ausgewählt
-- Exportiere Struktur von Tabelle mt_lumen_inventory.Inventar
DROP TABLE IF EXISTS `Inventar`;
CREATE TABLE IF NOT EXISTS `Inventar` (
  `ivid` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'System-Interne-Inventarisierungs-ID',
  `mcid` bigint(20) NOT NULL COMMENT 'Elementen-ID aus Mandanten-Katalog',
  `uuid` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Wird via Trigger Before Insert hinzugefügt, falls UUID nicht gesetzt ist',
  `import_uuid` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'UUID aus Tabelle Import_Inventar wird zum sicheren Tracking mit import',
  `for_jobid` bigint(20) DEFAULT NULL,
  `mcuuid` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'uuid of ObjektKatalogMandant',
  `hash` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rid` bigint(20) NOT NULL COMMENT 'Raum-ID',
  `ruuid` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Bezeichnung` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Typ` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Kategorie` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Farbe` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Groesse` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Zustand` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Seriennr` char(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Raum-ID',
  `jobid` bigint(20) NOT NULL COMMENT 'Jobid der letzten zugrundeliegenden Inventur, s. Tabelle Inventuren',
  `invid` char(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `iv_nr` char(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ErsteAufnahmeAm` datetime DEFAULT NULL,
  `LetzteAufnahmeAm` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `modified_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `created_uid` int(11) NOT NULL,
  `modified_uid` int(11) DEFAULT NULL,
  `created_jobid` int(11) DEFAULT NULL,
  `modified_jobid` int(11) DEFAULT NULL,
  `created_device_id` int(11) DEFAULT NULL,
  `modified_device_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`ivid`),
  UNIQUE KEY `unique_uuid` (`uuid`),
  KEY `mcid` (`mcid`),
  KEY `rid` (`rid`),
  KEY `jobid` (`jobid`)
) ENGINE=InnoDB AUTO_INCREMENT=53761 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Daten Export vom Benutzer nicht ausgewählt
-- Exportiere Struktur von Tabelle mt_lumen_inventory.Inventar2
DROP TABLE IF EXISTS `Inventar2`;
CREATE TABLE IF NOT EXISTS `Inventar2` (
  `ivid` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'System-Interne-Inventarisierungs-ID',
  `mcid` bigint(20) NOT NULL COMMENT 'Elementen-ID aus Mandanten-Katalog',
  `uuid` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `for_jobid` bigint(20) DEFAULT NULL,
  `mcuuid` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'uuid of ObjektKatalogMandant',
  `hash` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rid` bigint(20) NOT NULL COMMENT 'Raum-ID',
  `ruuid` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Bezeichnung` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Typ` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Kategorie` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Farbe` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Groesse` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Zustand` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Seriennr` char(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Raum-ID',
  `jobid` bigint(20) NOT NULL COMMENT 'Jobid der letzten zugrundeliegenden Inventur, s. Tabelle Inventuren',
  `invid` char(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `iv_nr` char(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ErsteAufnahmeAm` datetime DEFAULT NULL,
  `LetzteAufnahmeAm` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `modified_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `created_uid` int(11) NOT NULL,
  `modified_uid` int(11) DEFAULT NULL,
  `created_jobid` int(11) DEFAULT NULL,
  `modified_jobid` int(11) DEFAULT NULL,
  `created_device_id` int(11) DEFAULT NULL,
  `modified_device_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`ivid`),
  KEY `mcid` (`mcid`),
  KEY `rid` (`rid`),
  KEY `jobid` (`jobid`)
) ENGINE=InnoDB AUTO_INCREMENT=61762 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Daten Export vom Benutzer nicht ausgewählt
-- Exportiere Struktur von Tabelle mt_lumen_inventory.Inventuren
DROP TABLE IF EXISTS `Inventuren`;
CREATE TABLE IF NOT EXISTS `Inventuren` (
  `jobid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `mid` bigint(20) DEFAULT NULL,
  `gid` bigint(20) DEFAULT NULL,
  `loaded` tinyint(4) DEFAULT 0,
  `Titel` char(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Start` date NOT NULL DEFAULT current_timestamp(),
  `aktiviert` tinyint(1) NOT NULL DEFAULT 1,
  `AbgeschlossenAm` datetime DEFAULT NULL,
  `upload_jobid` bigint(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `modified_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `created_uid` int(11) NOT NULL,
  `modified_uid` int(11) DEFAULT NULL,
  PRIMARY KEY (`jobid`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Daten Export vom Benutzer nicht ausgewählt
-- Exportiere Struktur von Tabelle mt_lumen_inventory.InventurenGebaeude
DROP TABLE IF EXISTS `InventurenGebaeude`;
CREATE TABLE IF NOT EXISTS `InventurenGebaeude` (
  `jobid` int(11) NOT NULL,
  `gid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_german2_ci;

-- Daten Export vom Benutzer nicht ausgewählt
-- Exportiere Struktur von Tabelle mt_lumen_inventory.InventurenUser
DROP TABLE IF EXISTS `InventurenUser`;
CREATE TABLE IF NOT EXISTS `InventurenUser` (
  `jobid` bigint(20) NOT NULL,
  `uid` bigint(20) NOT NULL,
  UNIQUE KEY `jobid_uid` (`jobid`,`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_german2_ci;

-- Daten Export vom Benutzer nicht ausgewählt
-- Exportiere Struktur von Tabelle mt_lumen_inventory.Lieferant
DROP TABLE IF EXISTS `Lieferant`;
CREATE TABLE IF NOT EXISTS `Lieferant` (
  `hid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `Lieferant` char(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`hid`),
  UNIQUE KEY `lieferant_lieferant_unique` (`Lieferant`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Daten Export vom Benutzer nicht ausgewählt
-- Exportiere Struktur von Tabelle mt_lumen_inventory.log_inventar
DROP TABLE IF EXISTS `log_inventar`;
CREATE TABLE IF NOT EXISTS `log_inventar` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `crud_action` enum('insert','update','delete') COLLATE utf8mb4_unicode_ci NOT NULL,
  `ivid` bigint(20) NOT NULL,
  `mcid` bigint(20) NOT NULL,
  `rid` bigint(20) NOT NULL,
  `mid` bigint(20) NOT NULL,
  `jobid` bigint(20) NOT NULL,
  `Bezeichnung` char(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `log_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `log_user` char(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `log_devid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `log_inventar_ivid_index` (`ivid`),
  KEY `log_inventar_jobid_index` (`jobid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Daten Export vom Benutzer nicht ausgewählt
-- Exportiere Struktur von Tabelle mt_lumen_inventory.log_inventarisierung
DROP TABLE IF EXISTS `log_inventarisierung`;
CREATE TABLE IF NOT EXISTS `log_inventarisierung` (
  `id` int(11) DEFAULT NULL,
  `jobid` int(11) DEFAULT NULL,
  `table` enum('Inventar','Raeume','Hersteller','Lieferant','ObjektKatalogGlobal','ObjektKatalogMandant') COLLATE utf8_german2_ci DEFAULT NULL,
  `table_id` int(11) DEFAULT NULL,
  `aktion` enum('insert','update','delete') COLLATE utf8_german2_ci DEFAULT NULL,
  `data_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `data_before` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  `deviceid` int(11) DEFAULT NULL,
  `aktion_date` datetime DEFAULT NULL,
  `log_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_german2_ci;

-- Daten Export vom Benutzer nicht ausgewählt
-- Exportiere Struktur von Tabelle mt_lumen_inventory.log_login
DROP TABLE IF EXISTS `log_login`;
CREATE TABLE IF NOT EXISTS `log_login` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user` varchar(150) COLLATE utf8_german2_ci DEFAULT NULL,
  `url` varchar(300) COLLATE utf8_german2_ci DEFAULT NULL,
  `user_agent` varchar(300) COLLATE utf8_german2_ci DEFAULT NULL,
  `error` text COLLATE utf8_german2_ci DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `devid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_german2_ci;

-- Daten Export vom Benutzer nicht ausgewählt
-- Exportiere Struktur von Tabelle mt_lumen_inventory.log_objekt_global
DROP TABLE IF EXISTS `log_objekt_global`;
CREATE TABLE IF NOT EXISTS `log_objekt_global` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `crud_action` enum('insert','update','delete') COLLATE utf8mb4_unicode_ci NOT NULL,
  `gcid` bigint(20) NOT NULL,
  `Bezeichnung` char(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `log_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `log_user` char(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `log_objekt_global_gcid_index` (`gcid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Daten Export vom Benutzer nicht ausgewählt
-- Exportiere Struktur von Tabelle mt_lumen_inventory.log_objekt_mandant
DROP TABLE IF EXISTS `log_objekt_mandant`;
CREATE TABLE IF NOT EXISTS `log_objekt_mandant` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `crud_action` enum('insert','update','delete') COLLATE utf8mb4_unicode_ci NOT NULL,
  `gcid` bigint(20) NOT NULL,
  `mcid` bigint(20) NOT NULL,
  `mid` bigint(20) NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `log_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `log_user` char(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `log_objekt_mandant_mcid_index` (`mcid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Daten Export vom Benutzer nicht ausgewählt
-- Exportiere Struktur von Tabelle mt_lumen_inventory.log_raeume
DROP TABLE IF EXISTS `log_raeume`;
CREATE TABLE IF NOT EXISTS `log_raeume` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `crud_action` enum('insert','update','delete') COLLATE utf8mb4_unicode_ci NOT NULL,
  `rid` bigint(20) NOT NULL,
  `gid` bigint(20) NOT NULL,
  `mid` bigint(20) NOT NULL,
  `Raum` char(80) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Raumkennung: Name, Nummer, Beschreibung',
  `Raumbezeichnung` char(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Etage` char(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `log_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `log_user` char(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Daten Export vom Benutzer nicht ausgewählt
-- Exportiere Struktur von Tabelle mt_lumen_inventory.Mandanten
DROP TABLE IF EXISTS `Mandanten`;
CREATE TABLE IF NOT EXISTS `Mandanten` (
  `mid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `Mandant` char(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `modified_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `created_uid` int(11) NOT NULL,
  `modified_uid` int(11) DEFAULT NULL,
  PRIMARY KEY (`mid`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Daten Export vom Benutzer nicht ausgewählt
-- Exportiere Struktur von Tabelle mt_lumen_inventory.migrations
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Daten Export vom Benutzer nicht ausgewählt
-- Exportiere Struktur von Tabelle mt_lumen_inventory.ObjektbuchBarcodesLookup
DROP TABLE IF EXISTS `ObjektbuchBarcodesLookup`;
CREATE TABLE IF NOT EXISTS `ObjektbuchBarcodesLookup` (
  `code` varchar(32) COLLATE utf8_german2_ci NOT NULL,
  `table` varchar(32) COLLATE utf8_german2_ci NOT NULL,
  `key` varchar(32) COLLATE utf8_german2_ci NOT NULL,
  `for_jobid` bigint(20) DEFAULT NULL,
  `id` int(11) NOT NULL,
  `uuid` varchar(36) COLLATE utf8_german2_ci NOT NULL,
  `updateHelper` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`code`),
  UNIQUE KEY `uuid` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_german2_ci;

-- Daten Export vom Benutzer nicht ausgewählt
-- Exportiere Struktur von Tabelle mt_lumen_inventory.ObjektKatalogGlobal
DROP TABLE IF EXISTS `ObjektKatalogGlobal`;
CREATE TABLE IF NOT EXISTS `ObjektKatalogGlobal` (
  `gcid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `hash` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT '0',
  `code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '0',
  `hid` bigint(20) DEFAULT NULL COMMENT 'Hersteller-ID',
  `huuid` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lid` bigint(20) DEFAULT NULL COMMENT 'Lieferanten-ID',
  `Bezeichnung` char(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Produktnr` char(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Typ` char(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Gruppe` char(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Kategorie` char(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Farbe` char(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Groesse` char(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Bild` char(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `AnlagenNr` char(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `GeraetNr` char(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `FibuNr` char(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Flaeche` char(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Gewicht` char(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Baujahr` char(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Kst` char(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `modified_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `created_uid` int(11) NOT NULL,
  `modified_uid` int(11) DEFAULT NULL,
  `created_jobid` int(11) DEFAULT NULL,
  `modified_jobid` int(11) DEFAULT NULL,
  `created_device_id` int(11) DEFAULT NULL,
  `modified_device_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`gcid`),
  UNIQUE KEY `unique_uuid` (`uuid`),
  UNIQUE KEY `UniqueCheck` (`Bezeichnung`,`hid`,`Typ`,`Gruppe`,`Kategorie`,`Farbe`,`Groesse`),
  KEY `Bezeichnung` (`Bezeichnung`),
  KEY `objektkatalogglobal_hid_index` (`hid`)
) ENGINE=InnoDB AUTO_INCREMENT=2497 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Globaler Mandanten-Übergreifender Objektkatalog, alle Elemente eines Mandanten-Katalogs müssen mit einem globalen Katalogeintrag verknüpft sein.';

-- Daten Export vom Benutzer nicht ausgewählt
-- Exportiere Struktur von Tabelle mt_lumen_inventory.ObjektKatalogMandant
DROP TABLE IF EXISTS `ObjektKatalogMandant`;
CREATE TABLE IF NOT EXISTS `ObjektKatalogMandant` (
  `mcid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `for_jobid` bigint(20) DEFAULT NULL,
  `gcid` bigint(20) NOT NULL,
  `gcuuid` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mid` bigint(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `modified_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `created_uid` int(11) NOT NULL,
  `modified_uid` int(11) DEFAULT NULL,
  `created_jobid` int(11) DEFAULT NULL,
  `modified_jobid` int(11) DEFAULT NULL,
  `created_device_id` int(11) DEFAULT NULL,
  `modified_device_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`mcid`),
  UNIQUE KEY `unique_uuid` (`uuid`),
  UNIQUE KEY `uniqe_gcid_jobid_mid` (`gcid`,`for_jobid`,`mid`),
  KEY `gcid` (`gcid`),
  KEY `for_jobid` (`for_jobid`)
) ENGINE=InnoDB AUTO_INCREMENT=2497 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Mandanten-Katalog ist Teilmenge von Globalem Katalog. Jedes Inventar muss damit verknüft sein.';

-- Daten Export vom Benutzer nicht ausgewählt
-- Exportiere Struktur von Tabelle mt_lumen_inventory.Raeume
DROP TABLE IF EXISTS `Raeume`;
CREATE TABLE IF NOT EXISTS `Raeume` (
  `rid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `gid` bigint(20) NOT NULL,
  `uuid` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `hash` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `for_jobid` bigint(20) DEFAULT NULL,
  `raumid` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Raum` char(80) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Raumkennung: Name, Nummer, Beschreibung',
  `Raumbezeichnung` char(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Etage` char(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `current_jobid` int(11) NOT NULL DEFAULT 0,
  `current_jobstatus` int(11) NOT NULL DEFAULT 0 COMMENT '0=Init, 1=Started, 2, Closed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT '0=Init, 1=In Bearbeitung, 2=Aufnahme angeschlossen',
  `modified_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `created_uid` int(11) NOT NULL,
  `modified_uid` int(11) DEFAULT NULL,
  `created_jobid` int(11) DEFAULT NULL,
  `modified_jobid` int(11) DEFAULT NULL,
  `created_device_id` int(11) DEFAULT NULL,
  `modified_device_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`rid`),
  UNIQUE KEY `unique_uuid` (`uuid`),
  KEY `gid` (`gid`),
  KEY `Raum` (`Raum`)
) ENGINE=InnoDB AUTO_INCREMENT=2163 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Daten Export vom Benutzer nicht ausgewählt
-- Exportiere Struktur von Tabelle mt_lumen_inventory.server_change_log
DROP TABLE IF EXISTS `server_change_log`;
CREATE TABLE IF NOT EXISTS `server_change_log` (
  `revision_id` int(11) NOT NULL AUTO_INCREMENT,
  `type` int(11) NOT NULL DEFAULT 0 COMMENT '1=create, 2=update, 3=delete',
  `table` varchar(50) COLLATE utf8_german2_ci NOT NULL DEFAULT '0',
  `key` varchar(50) COLLATE utf8_german2_ci DEFAULT '0',
  `id` bigint(20) NOT NULL DEFAULT 0,
  `uuid` varchar(50) COLLATE utf8_german2_ci DEFAULT NULL,
  `jobid` int(11) NOT NULL DEFAULT 0,
  `obj` longtext COLLATE utf8_german2_ci DEFAULT NULL,
  `mods` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `created_uid` int(11) NOT NULL DEFAULT 0,
  `created_devid` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`revision_id`)
) ENGINE=InnoDB AUTO_INCREMENT=29810 DEFAULT CHARSET=utf8 COLLATE=utf8_german2_ci;

-- Daten Export vom Benutzer nicht ausgewählt
-- Exportiere Struktur von Funktion mt_lumen_inventory.snakeToCamelCase
DROP FUNCTION IF EXISTS `snakeToCamelCase`;
DELIMITER //
CREATE DEFINER=`root`@`%` FUNCTION `snakeToCamelCase`(name TEXT) RETURNS text CHARSET utf8 COLLATE utf8_german2_ci
RETURN
(
	SELECT
		REPLACE(
			REPLACE(
				REPLACE(
					REPLACE(
						REPLACE(
							REPLACE(
								REPLACE(name,
									'_i', 'I'),
									'_l', 'L'),
									'_o', 'O'),
									'_g', 'G'),
									'_m', 'M'),
									'_r', 'R'),
									'_u', 'U')
)//
DELIMITER ;

-- Exportiere Struktur von Funktion mt_lumen_inventory.TEST
DROP FUNCTION IF EXISTS `TEST`;
DELIMITER //
CREATE DEFINER=`root`@`%` FUNCTION `TEST`(val Text ) RETURNS text CHARSET utf8 COLLATE utf8_german2_ci
BEGIN

   IF (SELECT @TRIGGER_DISABLE = TRUE) THEN
   	return 'Nothing';
   END IF;

	return val;

END//
DELIMITER ;

-- Exportiere Struktur von Tabelle mt_lumen_inventory.uploads
DROP TABLE IF EXISTS `uploads`;
CREATE TABLE IF NOT EXISTS `uploads` (
  `jobid` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(50) COLLATE utf8_german2_ci DEFAULT '0',
  `mid` int(11) NOT NULL,
  `standort` varchar(150) COLLATE utf8_german2_ci DEFAULT NULL,
  `importkey` varchar(150) COLLATE utf8_german2_ci DEFAULT NULL,
  `filename` varchar(200) COLLATE utf8_german2_ci DEFAULT NULL,
  `filesize` int(11) DEFAULT NULL,
  `checksum` varchar(64) COLLATE utf8_german2_ci DEFAULT NULL,
  `content` longblob DEFAULT NULL,
  `objektbuch` longblob DEFAULT NULL,
  `stat` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `errors` text COLLATE utf8_german2_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_uid` int(11) DEFAULT NULL,
  `modified_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `modified_uid` int(11) DEFAULT NULL,
  PRIMARY KEY (`jobid`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_german2_ci COMMENT='''INSERT INTO uploads (mid, standort, importkey, filename, filesize, checksum, content, stat, errors) '' . "\\n"\r\n            . '' VALUES(:mid, :gid, :importkey, :filename, :filesize, :checksum, :content, :stat, :errors)''\r\n\r\n''SELECT id, mid, standort, importkey, filename, filesize, checksum FROM uploads WHERE id = '' . $dbh->quote($id);\r\n\r\nSELECT * FROM uploads WHERE checksum LIKE :checksum OR importkey LIKE :importkey OR filename LIKE :filename \r\n\r\n';

-- Daten Export vom Benutzer nicht ausgewählt
-- Exportiere Struktur von Tabelle mt_lumen_inventory.users
DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Daten Export vom Benutzer nicht ausgewählt
-- Exportiere Struktur von Trigger mt_lumen_inventory.trigger_Hersteller_after_delete
DROP TRIGGER IF EXISTS `trigger_Hersteller_after_delete`;
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trigger_Hersteller_after_delete` AFTER DELETE ON `Hersteller` FOR EACH ROW BEGIN

   INSERT INTO server_change_log (`type`, `table`, `key`, `id`, `uuid`, jobid, created_uid, created_devid)
   VALUES (3, 'Hersteller', 'hid', OLD.hid, OLD.uuid, IFNULL(OLD.created_jobid, -1), -1, -1);
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Exportiere Struktur von Trigger mt_lumen_inventory.trigger_Hersteller_after_insert
DROP TRIGGER IF EXISTS `trigger_Hersteller_after_insert`;
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trigger_Hersteller_after_insert` AFTER INSERT ON `Hersteller` FOR EACH ROW BEGIN

   DECLARE jsonObj LongText;
   SET jsonObj = JSON_OBJECT();
   IF IFNULL(NEW.hid, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.hid', NEW.hid);
   END IF;

   IF IFNULL(NEW.uuid, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.uuid', NEW.uuid);
   END IF;

   IF IFNULL(NEW.for_jobid, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.for_jobid', NEW.for_jobid);
   END IF;

   IF IFNULL(NEW.Hersteller, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.Hersteller', NEW.Hersteller);
   END IF;

   IF IFNULL(NEW.created_at, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.created_at', NEW.created_at);
   END IF;

   IF IFNULL(NEW.created_uid, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.created_uid', NEW.created_uid);
   END IF;

   IF IFNULL(NEW.created_jobid, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.created_jobid', NEW.created_jobid);
   END IF;

   IF IFNULL(NEW.created_device_id, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.created_device_id', NEW.created_device_id);
   END IF;

   INSERT INTO server_change_log
     (`type`, `table`, `key`, `id`, `uuid`, jobid, created_uid, created_devid, obj)
   VALUES
     (1, 'Hersteller', 'hid', NEW.hid, NEW.uuid, IFNULL(NEW.created_jobid, -1), -1, -1, jsonObj);

END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Exportiere Struktur von Trigger mt_lumen_inventory.trigger_Hersteller_after_update
DROP TRIGGER IF EXISTS `trigger_Hersteller_after_update`;
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trigger_Hersteller_after_update` AFTER UPDATE ON `Hersteller` FOR EACH ROW BEGIN

   DECLARE jsonObj LongText;
   SET jsonObj = JSON_OBJECT();
   IF IFNULL(NEW.hid, '') != IFNULL(OLD.hid, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.hid', NEW.hid);
   END IF;

   IF IFNULL(NEW.uuid, '') != IFNULL(OLD.uuid, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.uuid', NEW.uuid);
   END IF;

   IF IFNULL(NEW.for_jobid, '') != IFNULL(OLD.for_jobid, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.for_jobid', NEW.for_jobid);
   END IF;

   IF IFNULL(NEW.Hersteller, '') != IFNULL(OLD.Hersteller, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.Hersteller', NEW.Hersteller);
   END IF;

   IF IFNULL(NEW.updated_at, '') != IFNULL(OLD.updated_at, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.updated_at', NEW.updated_at);
   END IF;

   IF IFNULL(NEW.modified_uid, '') != IFNULL(OLD.modified_uid, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.modified_uid', NEW.modified_uid);
   END IF;

   IF IFNULL(NEW.modified_jobid, '') != IFNULL(OLD.modified_jobid, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.modified_jobid', NEW.modified_jobid);
   END IF;

   IF IFNULL(NEW.modified_device_id, '') != IFNULL(OLD.modified_device_id, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.modified_device_id', NEW.modified_device_id);
   END IF;

   INSERT INTO server_change_log
     (`type`, `table`, `key`, `id`, `uuid`, jobid, created_uid, created_devid, mods)
   VALUES
     (2, 'Hersteller', 'hid', NEW.hid, NEW.uuid, IFNULL(NEW.created_jobid, -1), -1, -1, jsonObj);

END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Exportiere Struktur von Trigger mt_lumen_inventory.trigger_Hersteller_before_insert_uuid
DROP TRIGGER IF EXISTS `trigger_Hersteller_before_insert_uuid`;
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trigger_Hersteller_before_insert_uuid` BEFORE INSERT ON `Hersteller` FOR EACH ROW BEGIN
   IF IFNULL(NEW.uuid, '') = '' THEN
      SET NEW.uuid = UUID();
   END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Exportiere Struktur von Trigger mt_lumen_inventory.trigger_Images_after_delete
DROP TRIGGER IF EXISTS `trigger_Images_after_delete`;
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trigger_Images_after_delete` AFTER DELETE ON `Images` FOR EACH ROW BEGIN

   INSERT INTO server_change_log (`type`, `table`, `key`, `id`, `uuid`, jobid, created_uid, created_devid)
   VALUES (3, 'Images', 'id', OLD.id, OLD.uuid, IFNULL(OLD.created_jobid, -1), -1, -1);
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Exportiere Struktur von Trigger mt_lumen_inventory.trigger_Images_after_insert
DROP TRIGGER IF EXISTS `trigger_Images_after_insert`;
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trigger_Images_after_insert` AFTER INSERT ON `Images` FOR EACH ROW BEGIN

   DECLARE jsonObj LongText;
   SET jsonObj = JSON_OBJECT();
   IF IFNULL(NEW.id, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.id', NEW.id);
   END IF;

   IF IFNULL(NEW.uuid, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.uuid', NEW.uuid);
   END IF;

   IF IFNULL(NEW.for_jobid, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.for_jobid', NEW.for_jobid);
   END IF;

   IF IFNULL(NEW.name, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.name', NEW.name);
   END IF;

   IF IFNULL(NEW.size, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.size', NEW.size);
   END IF;

   IF IFNULL(NEW.width, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.width', NEW.width);
   END IF;

   IF IFNULL(NEW.height, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.height', NEW.height);
   END IF;

   IF IFNULL(NEW.type, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.type', NEW.type);
   END IF;

   IF IFNULL(NEW.gcuuid, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.gcuuid', NEW.gcuuid);
   END IF;

   IF IFNULL(NEW.url, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.url', NEW.url);
   END IF;

   IF IFNULL(NEW.data_binary, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.data_binary', NEW.data_binary);
   END IF;

   IF IFNULL(NEW.data_url, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.data_url', NEW.data_url);
   END IF;

   IF IFNULL(NEW.revnr, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.revnr', NEW.revnr);
   END IF;

   IF IFNULL(NEW.created_at, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.created_at', NEW.created_at);
   END IF;

   IF IFNULL(NEW.created_uid, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.created_uid', NEW.created_uid);
   END IF;

   IF IFNULL(NEW.created_device_id, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.created_device_id', NEW.created_device_id);
   END IF;

   IF IFNULL(NEW.created_jobid, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.created_jobid', NEW.created_jobid);
   END IF;

   INSERT INTO server_change_log
     (`type`, `table`, `key`, `id`, `uuid`, jobid, created_uid, created_devid, obj)
   VALUES
     (1, 'Images', 'id', NEW.id, NEW.uuid, IFNULL(NEW.created_jobid, -1), -1, -1, jsonObj);

END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Exportiere Struktur von Trigger mt_lumen_inventory.trigger_Images_after_update
DROP TRIGGER IF EXISTS `trigger_Images_after_update`;
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trigger_Images_after_update` AFTER UPDATE ON `Images` FOR EACH ROW BEGIN

   DECLARE jsonObj LongText;
   SET jsonObj = JSON_OBJECT();
   IF IFNULL(NEW.id, '') != IFNULL(OLD.id, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.id', NEW.id);
   END IF;

   IF IFNULL(NEW.uuid, '') != IFNULL(OLD.uuid, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.uuid', NEW.uuid);
   END IF;

   IF IFNULL(NEW.for_jobid, '') != IFNULL(OLD.for_jobid, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.for_jobid', NEW.for_jobid);
   END IF;

   IF IFNULL(NEW.name, '') != IFNULL(OLD.name, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.name', NEW.name);
   END IF;

   IF IFNULL(NEW.size, '') != IFNULL(OLD.size, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.size', NEW.size);
   END IF;

   IF IFNULL(NEW.width, '') != IFNULL(OLD.width, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.width', NEW.width);
   END IF;

   IF IFNULL(NEW.height, '') != IFNULL(OLD.height, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.height', NEW.height);
   END IF;

   IF IFNULL(NEW.type, '') != IFNULL(OLD.type, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.type', NEW.type);
   END IF;

   IF IFNULL(NEW.gcuuid, '') != IFNULL(OLD.gcuuid, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.gcuuid', NEW.gcuuid);
   END IF;

   IF IFNULL(NEW.url, '') != IFNULL(OLD.url, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.url', NEW.url);
   END IF;

   IF IFNULL(NEW.data_binary, '') != IFNULL(OLD.data_binary, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.data_binary', NEW.data_binary);
   END IF;

   IF IFNULL(NEW.data_url, '') != IFNULL(OLD.data_url, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.data_url', NEW.data_url);
   END IF;

   IF IFNULL(NEW.revnr, '') != IFNULL(OLD.revnr, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.revnr', NEW.revnr);
   END IF;

   IF IFNULL(NEW.modified_at, '') != IFNULL(OLD.modified_at, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.modified_at', NEW.modified_at);
   END IF;

   IF IFNULL(NEW.modified_uid, '') != IFNULL(OLD.modified_uid, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.modified_uid', NEW.modified_uid);
   END IF;

   IF IFNULL(NEW.modified_device_id, '') != IFNULL(OLD.modified_device_id, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.modified_device_id', NEW.modified_device_id);
   END IF;

   IF IFNULL(NEW.modified_jobid, '') != IFNULL(OLD.modified_jobid, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.modified_jobid', NEW.modified_jobid);
   END IF;

   INSERT INTO server_change_log
     (`type`, `table`, `key`, `id`, `uuid`, jobid, created_uid, created_devid, mods)
   VALUES
     (2, 'Images', 'id', NEW.id, NEW.uuid, IFNULL(NEW.created_jobid, -1), -1, -1, jsonObj);

END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Exportiere Struktur von Trigger mt_lumen_inventory.trigger_Images_before_insert_uuid
DROP TRIGGER IF EXISTS `trigger_Images_before_insert_uuid`;
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trigger_Images_before_insert_uuid` BEFORE INSERT ON `Images` FOR EACH ROW BEGIN
   IF IFNULL(NEW.uuid, '') = '' THEN
      SET NEW.uuid = UUID();
   END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Exportiere Struktur von Trigger mt_lumen_inventory.trigger_Inventar_after_delete
DROP TRIGGER IF EXISTS `trigger_Inventar_after_delete`;
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trigger_Inventar_after_delete` AFTER DELETE ON `Inventar` FOR EACH ROW BEGIN

   INSERT INTO server_change_log (`type`, `table`, `key`, `id`, `uuid`, jobid, created_uid, created_devid)
   VALUES (3, 'Inventar', 'ivid', OLD.ivid, OLD.uuid, IFNULL(OLD.created_jobid, -1), -1, -1);
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Exportiere Struktur von Trigger mt_lumen_inventory.trigger_Inventar_after_insert
DROP TRIGGER IF EXISTS `trigger_Inventar_after_insert`;
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trigger_Inventar_after_insert` AFTER INSERT ON `Inventar` FOR EACH ROW BEGIN

   DECLARE jsonObj LongText;
   SET jsonObj = JSON_OBJECT();
   IF IFNULL(NEW.ivid, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.ivid', NEW.ivid);
   END IF;

   IF IFNULL(NEW.mcid, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.mcid', NEW.mcid);
   END IF;

   IF IFNULL(NEW.uuid, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.uuid', NEW.uuid);
   END IF;

   IF IFNULL(NEW.import_uuid, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.import_uuid', NEW.import_uuid);
   END IF;

   IF IFNULL(NEW.for_jobid, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.for_jobid', NEW.for_jobid);
   END IF;

   IF IFNULL(NEW.mcuuid, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.mcuuid', NEW.mcuuid);
   END IF;

   IF IFNULL(NEW.hash, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.hash', NEW.hash);
   END IF;

   IF IFNULL(NEW.code, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.code', NEW.code);
   END IF;

   IF IFNULL(NEW.rid, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.rid', NEW.rid);
   END IF;

   IF IFNULL(NEW.ruuid, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.ruuid', NEW.ruuid);
   END IF;

   IF IFNULL(NEW.Bezeichnung, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.Bezeichnung', NEW.Bezeichnung);
   END IF;

   IF IFNULL(NEW.Typ, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.Typ', NEW.Typ);
   END IF;

   IF IFNULL(NEW.Kategorie, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.Kategorie', NEW.Kategorie);
   END IF;

   IF IFNULL(NEW.Farbe, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.Farbe', NEW.Farbe);
   END IF;

   IF IFNULL(NEW.Groesse, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.Groesse', NEW.Groesse);
   END IF;

   IF IFNULL(NEW.Zustand, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.Zustand', NEW.Zustand);
   END IF;

   IF IFNULL(NEW.Seriennr, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.Seriennr', NEW.Seriennr);
   END IF;

   IF IFNULL(NEW.jobid, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.jobid', NEW.jobid);
   END IF;

   IF IFNULL(NEW.invid, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.invid', NEW.invid);
   END IF;

   IF IFNULL(NEW.iv_nr, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.iv_nr', NEW.iv_nr);
   END IF;

   IF IFNULL(NEW.ErsteAufnahmeAm, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.ErsteAufnahmeAm', NEW.ErsteAufnahmeAm);
   END IF;

   IF IFNULL(NEW.LetzteAufnahmeAm, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.LetzteAufnahmeAm', NEW.LetzteAufnahmeAm);
   END IF;

   IF IFNULL(NEW.created_at, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.created_at', NEW.created_at);
   END IF;

   IF IFNULL(NEW.created_uid, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.created_uid', NEW.created_uid);
   END IF;

   IF IFNULL(NEW.created_jobid, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.created_jobid', NEW.created_jobid);
   END IF;

   IF IFNULL(NEW.created_device_id, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.created_device_id', NEW.created_device_id);
   END IF;

   INSERT INTO server_change_log
     (`type`, `table`, `key`, `id`, `uuid`, jobid, created_uid, created_devid, obj)
   VALUES
     (1, 'Inventar', 'ivid', NEW.ivid, NEW.uuid, IFNULL(NEW.created_jobid, -1), -1, -1, jsonObj);

END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Exportiere Struktur von Trigger mt_lumen_inventory.trigger_Inventar_after_update
DROP TRIGGER IF EXISTS `trigger_Inventar_after_update`;
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trigger_Inventar_after_update` AFTER UPDATE ON `Inventar` FOR EACH ROW BEGIN

   DECLARE jsonObj LongText;
   SET jsonObj = JSON_OBJECT();
   IF IFNULL(NEW.ivid, '') != IFNULL(OLD.ivid, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.ivid', NEW.ivid);
   END IF;

   IF IFNULL(NEW.mcid, '') != IFNULL(OLD.mcid, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.mcid', NEW.mcid);
   END IF;

   IF IFNULL(NEW.uuid, '') != IFNULL(OLD.uuid, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.uuid', NEW.uuid);
   END IF;

   IF IFNULL(NEW.import_uuid, '') != IFNULL(OLD.import_uuid, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.import_uuid', NEW.import_uuid);
   END IF;

   IF IFNULL(NEW.for_jobid, '') != IFNULL(OLD.for_jobid, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.for_jobid', NEW.for_jobid);
   END IF;

   IF IFNULL(NEW.mcuuid, '') != IFNULL(OLD.mcuuid, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.mcuuid', NEW.mcuuid);
   END IF;

   IF IFNULL(NEW.hash, '') != IFNULL(OLD.hash, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.hash', NEW.hash);
   END IF;

   IF IFNULL(NEW.code, '') != IFNULL(OLD.code, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.code', NEW.code);
   END IF;

   IF IFNULL(NEW.rid, '') != IFNULL(OLD.rid, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.rid', NEW.rid);
   END IF;

   IF IFNULL(NEW.ruuid, '') != IFNULL(OLD.ruuid, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.ruuid', NEW.ruuid);
   END IF;

   IF IFNULL(NEW.Bezeichnung, '') != IFNULL(OLD.Bezeichnung, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.Bezeichnung', NEW.Bezeichnung);
   END IF;

   IF IFNULL(NEW.Typ, '') != IFNULL(OLD.Typ, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.Typ', NEW.Typ);
   END IF;

   IF IFNULL(NEW.Kategorie, '') != IFNULL(OLD.Kategorie, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.Kategorie', NEW.Kategorie);
   END IF;

   IF IFNULL(NEW.Farbe, '') != IFNULL(OLD.Farbe, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.Farbe', NEW.Farbe);
   END IF;

   IF IFNULL(NEW.Groesse, '') != IFNULL(OLD.Groesse, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.Groesse', NEW.Groesse);
   END IF;

   IF IFNULL(NEW.Zustand, '') != IFNULL(OLD.Zustand, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.Zustand', NEW.Zustand);
   END IF;

   IF IFNULL(NEW.Seriennr, '') != IFNULL(OLD.Seriennr, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.Seriennr', NEW.Seriennr);
   END IF;

   IF IFNULL(NEW.jobid, '') != IFNULL(OLD.jobid, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.jobid', NEW.jobid);
   END IF;

   IF IFNULL(NEW.invid, '') != IFNULL(OLD.invid, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.invid', NEW.invid);
   END IF;

   IF IFNULL(NEW.iv_nr, '') != IFNULL(OLD.iv_nr, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.iv_nr', NEW.iv_nr);
   END IF;

   IF IFNULL(NEW.ErsteAufnahmeAm, '') != IFNULL(OLD.ErsteAufnahmeAm, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.ErsteAufnahmeAm', NEW.ErsteAufnahmeAm);
   END IF;

   IF IFNULL(NEW.LetzteAufnahmeAm, '') != IFNULL(OLD.LetzteAufnahmeAm, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.LetzteAufnahmeAm', NEW.LetzteAufnahmeAm);
   END IF;

   IF IFNULL(NEW.modified_at, '') != IFNULL(OLD.modified_at, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.modified_at', NEW.modified_at);
   END IF;

   IF IFNULL(NEW.modified_uid, '') != IFNULL(OLD.modified_uid, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.modified_uid', NEW.modified_uid);
   END IF;

   IF IFNULL(NEW.modified_jobid, '') != IFNULL(OLD.modified_jobid, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.modified_jobid', NEW.modified_jobid);
   END IF;

   IF IFNULL(NEW.modified_device_id, '') != IFNULL(OLD.modified_device_id, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.modified_device_id', NEW.modified_device_id);
   END IF;

   INSERT INTO server_change_log
     (`type`, `table`, `key`, `id`, `uuid`, jobid, created_uid, created_devid, mods)
   VALUES
     (2, 'Inventar', 'ivid', NEW.ivid, NEW.uuid, IFNULL(NEW.created_jobid, -1), -1, -1, jsonObj);

END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Exportiere Struktur von Trigger mt_lumen_inventory.trigger_Inventar_before_insert_uuid
DROP TRIGGER IF EXISTS `trigger_Inventar_before_insert_uuid`;
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trigger_Inventar_before_insert_uuid` BEFORE INSERT ON `Inventar` FOR EACH ROW BEGIN
   IF IFNULL(NEW.uuid, '') = '' THEN
      SET NEW.uuid = UUID();
   END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Exportiere Struktur von Trigger mt_lumen_inventory.trigger_ObjektKatalogGlobal_after_delete
DROP TRIGGER IF EXISTS `trigger_ObjektKatalogGlobal_after_delete`;
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trigger_ObjektKatalogGlobal_after_delete` AFTER DELETE ON `ObjektKatalogGlobal` FOR EACH ROW BEGIN

   INSERT INTO server_change_log (`type`, `table`, `key`, `id`, `uuid`, jobid, created_uid, created_devid)
   VALUES (3, 'ObjektKatalogGlobal', 'gcid', OLD.gcid, OLD.uuid, IFNULL(OLD.created_jobid, -1), -1, -1);
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Exportiere Struktur von Trigger mt_lumen_inventory.trigger_ObjektKatalogGlobal_after_insert
DROP TRIGGER IF EXISTS `trigger_ObjektKatalogGlobal_after_insert`;
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trigger_ObjektKatalogGlobal_after_insert` AFTER INSERT ON `ObjektKatalogGlobal` FOR EACH ROW BEGIN

   DECLARE jsonObj LongText;
   SET jsonObj = JSON_OBJECT();
   IF IFNULL(NEW.gcid, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.gcid', NEW.gcid);
   END IF;

   IF IFNULL(NEW.uuid, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.uuid', NEW.uuid);
   END IF;

   IF IFNULL(NEW.hash, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.hash', NEW.hash);
   END IF;

   IF IFNULL(NEW.code, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.code', NEW.code);
   END IF;

   IF IFNULL(NEW.hid, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.hid', NEW.hid);
   END IF;

   IF IFNULL(NEW.huuid, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.huuid', NEW.huuid);
   END IF;

   IF IFNULL(NEW.lid, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.lid', NEW.lid);
   END IF;

   IF IFNULL(NEW.Bezeichnung, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.Bezeichnung', NEW.Bezeichnung);
   END IF;

   IF IFNULL(NEW.Produktnr, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.Produktnr', NEW.Produktnr);
   END IF;

   IF IFNULL(NEW.Typ, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.Typ', NEW.Typ);
   END IF;

   IF IFNULL(NEW.Gruppe, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.Gruppe', NEW.Gruppe);
   END IF;

   IF IFNULL(NEW.Kategorie, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.Kategorie', NEW.Kategorie);
   END IF;

   IF IFNULL(NEW.Farbe, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.Farbe', NEW.Farbe);
   END IF;

   IF IFNULL(NEW.Groesse, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.Groesse', NEW.Groesse);
   END IF;

   IF IFNULL(NEW.Bild, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.Bild', NEW.Bild);
   END IF;

   IF IFNULL(NEW.AnlagenNr, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.AnlagenNr', NEW.AnlagenNr);
   END IF;

   IF IFNULL(NEW.GeraetNr, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.GeraetNr', NEW.GeraetNr);
   END IF;

   IF IFNULL(NEW.FibuNr, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.FibuNr', NEW.FibuNr);
   END IF;

   IF IFNULL(NEW.Flaeche, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.Flaeche', NEW.Flaeche);
   END IF;

   IF IFNULL(NEW.Gewicht, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.Gewicht', NEW.Gewicht);
   END IF;

   IF IFNULL(NEW.Baujahr, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.Baujahr', NEW.Baujahr);
   END IF;

   IF IFNULL(NEW.Kst, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.Kst', NEW.Kst);
   END IF;

   IF IFNULL(NEW.created_at, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.created_at', NEW.created_at);
   END IF;

   IF IFNULL(NEW.created_uid, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.created_uid', NEW.created_uid);
   END IF;

   IF IFNULL(NEW.created_jobid, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.created_jobid', NEW.created_jobid);
   END IF;

   IF IFNULL(NEW.created_device_id, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.created_device_id', NEW.created_device_id);
   END IF;

   INSERT INTO server_change_log
     (`type`, `table`, `key`, `id`, `uuid`, jobid, created_uid, created_devid, obj)
   VALUES
     (1, 'ObjektKatalogGlobal', 'gcid', NEW.gcid, NEW.uuid, IFNULL(NEW.created_jobid, -1), -1, -1, jsonObj);

END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Exportiere Struktur von Trigger mt_lumen_inventory.trigger_ObjektKatalogGlobal_after_update
DROP TRIGGER IF EXISTS `trigger_ObjektKatalogGlobal_after_update`;
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trigger_ObjektKatalogGlobal_after_update` AFTER UPDATE ON `ObjektKatalogGlobal` FOR EACH ROW BEGIN

   DECLARE jsonObj LongText;
   SET jsonObj = JSON_OBJECT();
   IF IFNULL(NEW.gcid, '') != IFNULL(OLD.gcid, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.gcid', NEW.gcid);
   END IF;

   IF IFNULL(NEW.uuid, '') != IFNULL(OLD.uuid, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.uuid', NEW.uuid);
   END IF;

   IF IFNULL(NEW.hash, '') != IFNULL(OLD.hash, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.hash', NEW.hash);
   END IF;

   IF IFNULL(NEW.code, '') != IFNULL(OLD.code, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.code', NEW.code);
   END IF;

   IF IFNULL(NEW.hid, '') != IFNULL(OLD.hid, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.hid', NEW.hid);
   END IF;

   IF IFNULL(NEW.huuid, '') != IFNULL(OLD.huuid, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.huuid', NEW.huuid);
   END IF;

   IF IFNULL(NEW.lid, '') != IFNULL(OLD.lid, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.lid', NEW.lid);
   END IF;

   IF IFNULL(NEW.Bezeichnung, '') != IFNULL(OLD.Bezeichnung, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.Bezeichnung', NEW.Bezeichnung);
   END IF;

   IF IFNULL(NEW.Produktnr, '') != IFNULL(OLD.Produktnr, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.Produktnr', NEW.Produktnr);
   END IF;

   IF IFNULL(NEW.Typ, '') != IFNULL(OLD.Typ, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.Typ', NEW.Typ);
   END IF;

   IF IFNULL(NEW.Gruppe, '') != IFNULL(OLD.Gruppe, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.Gruppe', NEW.Gruppe);
   END IF;

   IF IFNULL(NEW.Kategorie, '') != IFNULL(OLD.Kategorie, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.Kategorie', NEW.Kategorie);
   END IF;

   IF IFNULL(NEW.Farbe, '') != IFNULL(OLD.Farbe, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.Farbe', NEW.Farbe);
   END IF;

   IF IFNULL(NEW.Groesse, '') != IFNULL(OLD.Groesse, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.Groesse', NEW.Groesse);
   END IF;

   IF IFNULL(NEW.Bild, '') != IFNULL(OLD.Bild, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.Bild', NEW.Bild);
   END IF;

   IF IFNULL(NEW.AnlagenNr, '') != IFNULL(OLD.AnlagenNr, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.AnlagenNr', NEW.AnlagenNr);
   END IF;

   IF IFNULL(NEW.GeraetNr, '') != IFNULL(OLD.GeraetNr, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.GeraetNr', NEW.GeraetNr);
   END IF;

   IF IFNULL(NEW.FibuNr, '') != IFNULL(OLD.FibuNr, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.FibuNr', NEW.FibuNr);
   END IF;

   IF IFNULL(NEW.Flaeche, '') != IFNULL(OLD.Flaeche, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.Flaeche', NEW.Flaeche);
   END IF;

   IF IFNULL(NEW.Gewicht, '') != IFNULL(OLD.Gewicht, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.Gewicht', NEW.Gewicht);
   END IF;

   IF IFNULL(NEW.Baujahr, '') != IFNULL(OLD.Baujahr, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.Baujahr', NEW.Baujahr);
   END IF;

   IF IFNULL(NEW.Kst, '') != IFNULL(OLD.Kst, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.Kst', NEW.Kst);
   END IF;

   IF IFNULL(NEW.modified_at, '') != IFNULL(OLD.modified_at, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.modified_at', NEW.modified_at);
   END IF;

   IF IFNULL(NEW.modified_uid, '') != IFNULL(OLD.modified_uid, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.modified_uid', NEW.modified_uid);
   END IF;

   IF IFNULL(NEW.modified_jobid, '') != IFNULL(OLD.modified_jobid, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.modified_jobid', NEW.modified_jobid);
   END IF;

   IF IFNULL(NEW.modified_device_id, '') != IFNULL(OLD.modified_device_id, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.modified_device_id', NEW.modified_device_id);
   END IF;

   INSERT INTO server_change_log
     (`type`, `table`, `key`, `id`, `uuid`, jobid, created_uid, created_devid, mods)
   VALUES
     (2, 'ObjektKatalogGlobal', 'gcid', NEW.gcid, NEW.uuid, IFNULL(NEW.created_jobid, -1), -1, -1, jsonObj);

END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Exportiere Struktur von Trigger mt_lumen_inventory.trigger_ObjektKatalogGlobal_before_insert_uuid
DROP TRIGGER IF EXISTS `trigger_ObjektKatalogGlobal_before_insert_uuid`;
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trigger_ObjektKatalogGlobal_before_insert_uuid` BEFORE INSERT ON `ObjektKatalogGlobal` FOR EACH ROW BEGIN
   IF IFNULL(NEW.uuid, '') = '' THEN
      SET NEW.uuid = UUID();
   END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Exportiere Struktur von Trigger mt_lumen_inventory.trigger_ObjektKatalogMandant_after_delete
DROP TRIGGER IF EXISTS `trigger_ObjektKatalogMandant_after_delete`;
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trigger_ObjektKatalogMandant_after_delete` AFTER DELETE ON `ObjektKatalogMandant` FOR EACH ROW BEGIN

   INSERT INTO server_change_log (`type`, `table`, `key`, `id`, `uuid`, jobid, created_uid, created_devid)
   VALUES (3, 'ObjektKatalogMandant', 'mcid', OLD.mcid, OLD.uuid, IFNULL(OLD.created_jobid, -1), -1, -1);
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Exportiere Struktur von Trigger mt_lumen_inventory.trigger_ObjektKatalogMandant_after_insert
DROP TRIGGER IF EXISTS `trigger_ObjektKatalogMandant_after_insert`;
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trigger_ObjektKatalogMandant_after_insert` AFTER INSERT ON `ObjektKatalogMandant` FOR EACH ROW BEGIN

   DECLARE jsonObj LongText;
   SET jsonObj = JSON_OBJECT();
   IF IFNULL(NEW.mcid, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.mcid', NEW.mcid);
   END IF;

   IF IFNULL(NEW.uuid, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.uuid', NEW.uuid);
   END IF;

   IF IFNULL(NEW.for_jobid, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.for_jobid', NEW.for_jobid);
   END IF;

   IF IFNULL(NEW.gcid, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.gcid', NEW.gcid);
   END IF;

   IF IFNULL(NEW.gcuuid, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.gcuuid', NEW.gcuuid);
   END IF;

   IF IFNULL(NEW.code, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.code', NEW.code);
   END IF;

   IF IFNULL(NEW.mid, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.mid', NEW.mid);
   END IF;

   IF IFNULL(NEW.created_at, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.created_at', NEW.created_at);
   END IF;

   IF IFNULL(NEW.created_uid, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.created_uid', NEW.created_uid);
   END IF;

   IF IFNULL(NEW.created_jobid, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.created_jobid', NEW.created_jobid);
   END IF;

   IF IFNULL(NEW.created_device_id, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.created_device_id', NEW.created_device_id);
   END IF;

   INSERT INTO server_change_log
     (`type`, `table`, `key`, `id`, `uuid`, jobid, created_uid, created_devid, obj)
   VALUES
     (1, 'ObjektKatalogMandant', 'mcid', NEW.mcid, NEW.uuid, IFNULL(NEW.created_jobid, -1), -1, -1, jsonObj);

END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Exportiere Struktur von Trigger mt_lumen_inventory.trigger_ObjektKatalogMandant_after_update
DROP TRIGGER IF EXISTS `trigger_ObjektKatalogMandant_after_update`;
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trigger_ObjektKatalogMandant_after_update` AFTER UPDATE ON `ObjektKatalogMandant` FOR EACH ROW BEGIN

   DECLARE jsonObj LongText;
   SET jsonObj = JSON_OBJECT();
   IF IFNULL(NEW.mcid, '') != IFNULL(OLD.mcid, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.mcid', NEW.mcid);
   END IF;

   IF IFNULL(NEW.uuid, '') != IFNULL(OLD.uuid, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.uuid', NEW.uuid);
   END IF;

   IF IFNULL(NEW.for_jobid, '') != IFNULL(OLD.for_jobid, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.for_jobid', NEW.for_jobid);
   END IF;

   IF IFNULL(NEW.gcid, '') != IFNULL(OLD.gcid, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.gcid', NEW.gcid);
   END IF;

   IF IFNULL(NEW.gcuuid, '') != IFNULL(OLD.gcuuid, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.gcuuid', NEW.gcuuid);
   END IF;

   IF IFNULL(NEW.code, '') != IFNULL(OLD.code, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.code', NEW.code);
   END IF;

   IF IFNULL(NEW.mid, '') != IFNULL(OLD.mid, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.mid', NEW.mid);
   END IF;

   IF IFNULL(NEW.modified_at, '') != IFNULL(OLD.modified_at, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.modified_at', NEW.modified_at);
   END IF;

   IF IFNULL(NEW.modified_uid, '') != IFNULL(OLD.modified_uid, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.modified_uid', NEW.modified_uid);
   END IF;

   IF IFNULL(NEW.modified_jobid, '') != IFNULL(OLD.modified_jobid, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.modified_jobid', NEW.modified_jobid);
   END IF;

   IF IFNULL(NEW.modified_device_id, '') != IFNULL(OLD.modified_device_id, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.modified_device_id', NEW.modified_device_id);
   END IF;

   INSERT INTO server_change_log
     (`type`, `table`, `key`, `id`, `uuid`, jobid, created_uid, created_devid, mods)
   VALUES
     (2, 'ObjektKatalogMandant', 'mcid', NEW.mcid, NEW.uuid, IFNULL(NEW.created_jobid, -1), -1, -1, jsonObj);

END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Exportiere Struktur von Trigger mt_lumen_inventory.trigger_ObjektKatalogMandant_before_insert_uuid
DROP TRIGGER IF EXISTS `trigger_ObjektKatalogMandant_before_insert_uuid`;
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trigger_ObjektKatalogMandant_before_insert_uuid` BEFORE INSERT ON `ObjektKatalogMandant` FOR EACH ROW BEGIN
   IF IFNULL(NEW.uuid, '') = '' THEN
      SET NEW.uuid = UUID();
   END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Exportiere Struktur von Trigger mt_lumen_inventory.trigger_Raeume_after_delete
DROP TRIGGER IF EXISTS `trigger_Raeume_after_delete`;
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trigger_Raeume_after_delete` AFTER DELETE ON `Raeume` FOR EACH ROW BEGIN

   INSERT INTO server_change_log (`type`, `table`, `key`, `id`, `uuid`, jobid, created_uid, created_devid)
   VALUES (3, 'Raeume', 'rid', OLD.rid, OLD.uuid, IFNULL(OLD.created_jobid, -1), -1, -1);
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Exportiere Struktur von Trigger mt_lumen_inventory.trigger_Raeume_after_insert
DROP TRIGGER IF EXISTS `trigger_Raeume_after_insert`;
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trigger_Raeume_after_insert` AFTER INSERT ON `Raeume` FOR EACH ROW BEGIN

   DECLARE jsonObj LongText;
   SET jsonObj = JSON_OBJECT();
   IF IFNULL(NEW.rid, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.rid', NEW.rid);
   END IF;

   IF IFNULL(NEW.gid, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.gid', NEW.gid);
   END IF;

   IF IFNULL(NEW.uuid, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.uuid', NEW.uuid);
   END IF;

   IF IFNULL(NEW.hash, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.hash', NEW.hash);
   END IF;

   IF IFNULL(NEW.code, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.code', NEW.code);
   END IF;

   IF IFNULL(NEW.for_jobid, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.for_jobid', NEW.for_jobid);
   END IF;

   IF IFNULL(NEW.raumid, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.raumid', NEW.raumid);
   END IF;

   IF IFNULL(NEW.Raum, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.Raum', NEW.Raum);
   END IF;

   IF IFNULL(NEW.Raumbezeichnung, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.Raumbezeichnung', NEW.Raumbezeichnung);
   END IF;

   IF IFNULL(NEW.Etage, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.Etage', NEW.Etage);
   END IF;

   IF IFNULL(NEW.current_jobid, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.current_jobid', NEW.current_jobid);
   END IF;

   IF IFNULL(NEW.current_jobstatus, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.current_jobstatus', NEW.current_jobstatus);
   END IF;

   IF IFNULL(NEW.created_at, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.created_at', NEW.created_at);
   END IF;

   IF IFNULL(NEW.created_uid, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.created_uid', NEW.created_uid);
   END IF;

   IF IFNULL(NEW.created_jobid, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.created_jobid', NEW.created_jobid);
   END IF;

   IF IFNULL(NEW.created_device_id, '') != '' THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.created_device_id', NEW.created_device_id);
   END IF;

   INSERT INTO server_change_log
     (`type`, `table`, `key`, `id`, `uuid`, jobid, created_uid, created_devid, obj)
   VALUES
     (1, 'Raeume', 'rid', NEW.rid, NEW.uuid, IFNULL(NEW.created_jobid, -1), -1, -1, jsonObj);

END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Exportiere Struktur von Trigger mt_lumen_inventory.trigger_Raeume_after_update
DROP TRIGGER IF EXISTS `trigger_Raeume_after_update`;
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trigger_Raeume_after_update` AFTER UPDATE ON `Raeume` FOR EACH ROW BEGIN

   DECLARE jsonObj LongText;
   SET jsonObj = JSON_OBJECT();
   IF IFNULL(NEW.rid, '') != IFNULL(OLD.rid, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.rid', NEW.rid);
   END IF;

   IF IFNULL(NEW.gid, '') != IFNULL(OLD.gid, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.gid', NEW.gid);
   END IF;

   IF IFNULL(NEW.uuid, '') != IFNULL(OLD.uuid, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.uuid', NEW.uuid);
   END IF;

   IF IFNULL(NEW.hash, '') != IFNULL(OLD.hash, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.hash', NEW.hash);
   END IF;

   IF IFNULL(NEW.code, '') != IFNULL(OLD.code, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.code', NEW.code);
   END IF;

   IF IFNULL(NEW.for_jobid, '') != IFNULL(OLD.for_jobid, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.for_jobid', NEW.for_jobid);
   END IF;

   IF IFNULL(NEW.raumid, '') != IFNULL(OLD.raumid, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.raumid', NEW.raumid);
   END IF;

   IF IFNULL(NEW.Raum, '') != IFNULL(OLD.Raum, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.Raum', NEW.Raum);
   END IF;

   IF IFNULL(NEW.Raumbezeichnung, '') != IFNULL(OLD.Raumbezeichnung, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.Raumbezeichnung', NEW.Raumbezeichnung);
   END IF;

   IF IFNULL(NEW.Etage, '') != IFNULL(OLD.Etage, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.Etage', NEW.Etage);
   END IF;

   IF IFNULL(NEW.current_jobid, '') != IFNULL(OLD.current_jobid, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.current_jobid', NEW.current_jobid);
   END IF;

   IF IFNULL(NEW.current_jobstatus, '') != IFNULL(OLD.current_jobstatus, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.current_jobstatus', NEW.current_jobstatus);
   END IF;

   IF IFNULL(NEW.modified_at, '') != IFNULL(OLD.modified_at, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.modified_at', NEW.modified_at);
   END IF;

   IF IFNULL(NEW.modified_uid, '') != IFNULL(OLD.modified_uid, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.modified_uid', NEW.modified_uid);
   END IF;

   IF IFNULL(NEW.modified_jobid, '') != IFNULL(OLD.modified_jobid, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.modified_jobid', NEW.modified_jobid);
   END IF;

   IF IFNULL(NEW.modified_device_id, '') != IFNULL(OLD.modified_device_id, '') THEN
      SET jsonObj = JSON_INSERT(jsonObj, '$.modified_device_id', NEW.modified_device_id);
   END IF;

   INSERT INTO server_change_log
     (`type`, `table`, `key`, `id`, `uuid`, jobid, created_uid, created_devid, mods)
   VALUES
     (2, 'Raeume', 'rid', NEW.rid, NEW.uuid, IFNULL(NEW.created_jobid, -1), -1, -1, jsonObj);

END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Exportiere Struktur von Trigger mt_lumen_inventory.trigger_Raeume_before_insert_uuid
DROP TRIGGER IF EXISTS `trigger_Raeume_before_insert_uuid`;
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trigger_Raeume_before_insert_uuid` BEFORE INSERT ON `Raeume` FOR EACH ROW BEGIN
   IF IFNULL(NEW.uuid, '') = '' THEN
      SET NEW.uuid = UUID();
   END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
