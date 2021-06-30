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

-- Exportiere Struktur von Trigger mt_lumen_inventory.log_Hersteller_after_update
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `log_Hersteller_after_update` AFTER UPDATE ON `Hersteller` FOR EACH ROW BEGIN

	DECLARE jsonMods LongText;
	SET jsonMods = JSON_OBJECT();

	IF NEW.Hersteller != OLD.Hersteller THEN
		SET jsonMods = JSON_INSERT(jsonMods, '$.Hersteller', NEW.Hersteller);
	END IF;

	IF NEW.for_jobid != OLD.for_jobid THEN
		SET jsonMods = JSON_INSERT(jsonMods, '$.for_jobid', NEW.for_jobid);
	END IF;

	SET jsonMods = JSON_INSERT(jsonMods, '$.updated_at', NEW.updated_at);
	SET jsonMods = JSON_INSERT(jsonMods, '$.modified_uid', NEW.modified_uid);
	SET jsonMods = JSON_INSERT(jsonMods, '$.modified_jobid', NEW.modified_jobid);
	SET jsonMods = JSON_INSERT(jsonMods, '$.modified_device_id', NEW.modified_device_id);

	INSERT INTO server_change_log
	(`type`, `table`, `key`, `id`, `uuid`, jobid, created_uid, created_devid, mods)
   VALUES
	(2, 'Hersteller', 'hid', NEW.hid, NEW.uuid, IFNULL(NEW.created_jobid, -1), -1, -1,
		jsonMods
	);
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Exportiere Struktur von Trigger mt_lumen_inventory.log_Hersteller_deletes
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `log_Hersteller_deletes` AFTER DELETE ON `Hersteller` FOR EACH ROW proc_exit: BEGIN
   IF (SELECT @TRIGGER_DISABLED = TRUE) OR (SELECT @TRIGGER_DELETE_DISABLED = TRUE) THEN
   	LEAVE proc_Exit;
   END IF;
   INSERT INTO server_change_log (`type`, `table`, `key`, `id`, `uuid`, jobid, created_uid, created_devid)
   VALUES (3, 'Hersteller', 'hid', OLD.hid, OLD.uuid, IFNULL(OLD.created_jobid, -1), -1, -1);
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Exportiere Struktur von Trigger mt_lumen_inventory.log_Hersteller_inserts
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `log_Hersteller_inserts` AFTER INSERT ON `Hersteller` FOR EACH ROW BEGIN

	INSERT INTO server_change_log
	(`type`, `table`, `key`, `id`, `uuid`, jobid, created_uid, created_devid, obj)
   VALUES
	(1, 'Hersteller', 'hid', NEW.hid, NEW.uuid, IFNULL(NEW.created_jobid, -1), -1, -1,
		JSON_OBJECT(
			'hid', NEW.hid,
			'uuid', NEW.uuid,
			'for_jobid', NEW.for_jobid,
			'Hersteller', NEW.Hersteller,
			'created_at', NEW.created_at,
			'created_uid', NEW.created_uid,
			'created_jobid', NEW.created_jobid,
			'created_device_id', NEW.created_device_id
		)
	);
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Exportiere Struktur von Trigger mt_lumen_inventory.log_Hersteller_inserts_uuid
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `log_Hersteller_inserts_uuid` BEFORE INSERT ON `Hersteller` FOR EACH ROW BEGIN
   IF IFNULL(NEW.uuid, '') = '' THEN
      SET NEW.uuid = UUID();
   END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Exportiere Struktur von Trigger mt_lumen_inventory.log_Images_after_insert
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `log_Images_after_insert` AFTER INSERT ON `Images` FOR EACH ROW BEGIN

	INSERT INTO server_change_log
	(`type`, `table`, `key`, `id`, `uuid`, jobid, created_uid, created_devid, obj)
   VALUES
	(1, 'Images', 'id', NEW.id, NEW.uuid, IFNULL(NEW.created_jobid, -1), -1, -1,
		JSON_OBJECT(
			'id', NEW.id,
			'uuid', NEW.uuid,
			'for_jobid', NEW.for_jobid,
			'name', NEW.name,
			'size', NEW.size,
			'width', NEW.width,
			'height', NEW.height,
			'type', NEW.type,
			'gcuuid', NEW.gcuuid,
			'url', NEW.url,
			'data_url', NEW.data_url,
			'revnr', NEW.revnr,
			'created_at', NEW.created_at,
			'created_uid', NEW.created_uid,
			'created_jobid', NEW.created_jobid,
			'created_device_id', NEW.created_device_id
		)
	);
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Exportiere Struktur von Trigger mt_lumen_inventory.log_Images_deletes
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `log_Images_deletes` AFTER DELETE ON `Images` FOR EACH ROW proc_exit: BEGIN
   IF (SELECT @TRIGGER_DISABLED = TRUE) OR (SELECT @TRIGGER_DELETE_DISABLED = TRUE) THEN
   	LEAVE proc_Exit;
   END IF;
   INSERT INTO server_change_log (`type`, `table`, `key`, `id`, `uuid`, jobid, created_uid, created_devid)
   VALUES (3, 'Images', 'id', OLD.id, OLD.uuid, IFNULL(OLD.created_jobid, -1), -1, -1);
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Exportiere Struktur von Trigger mt_lumen_inventory.log_Images_inserts_uuid
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `log_Images_inserts_uuid` BEFORE INSERT ON `Images` FOR EACH ROW BEGIN
   IF IFNULL(NEW.uuid, '') = '' THEN
      SET NEW.uuid = UUID();
   END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Exportiere Struktur von Trigger mt_lumen_inventory.log_Inventar_after_insert
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `log_Inventar_after_insert` AFTER INSERT ON `Inventar` FOR EACH ROW BEGIN

	INSERT INTO server_change_log
	  (`type`, `table`, `key`, `id`, `uuid`, jobid, created_uid, created_devid, obj)
   VALUES
	  (1, 'Images', 'ivid', NEW.ivid, NEW.uuid, IFNULL(NEW.created_jobid, -1), -1, -1,
		JSON_OBJECT(
			'ivid', NEW.ivid,
			'mcid', NEW.mcid,
			'uuid', NEW.uuid,
			'import_uuid', NEW.import_uuid,
			'for_jobid', NEW.for_jobid,
			'mcuuid', NEW.mcuuid,
			'hash', NEW.hash,
			'code', NEW.code,
			'rid', NEW.rid,
			'ruuid', NEW.ruuid,
			'Bezeichnung', NEW.Bezeichnung,
			'Typ', NEW.Typ,
			'Kategorie', NEW.Kategorie,
			'Farbe', NEW.Farbe,
			'Groesse', NEW.Groesse,
			'Zustand', NEW.Zustand,
			'Seriennr', NEW.Seriennr,
			'jobid', NEW.jobid,
			'invid', NEW.invid,
			'iv_nr', NEW.iv_nr,
			'ErsteAufnahmeAm', NEW.ErsteAufnahmeAm,
			'LetzteAufnahmeAm', NEW.LetzteAufnahmeAm,

			'created_at', NEW.created_at,
			'created_uid', NEW.created_uid,
			'created_jobid', NEW.created_jobid,
			'created_device_id', NEW.created_device_id
		)
	);
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Exportiere Struktur von Trigger mt_lumen_inventory.log_Inventar_deletes
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `log_Inventar_deletes` AFTER DELETE ON `Inventar` FOR EACH ROW proc_exit: BEGIN
   IF (SELECT @TRIGGER_DISABLED = TRUE) OR (SELECT @TRIGGER_DELETE_DISABLED = TRUE) THEN
   	LEAVE proc_Exit;
   END IF;
   INSERT INTO server_change_log (`type`, `table`, `key`, `id`, `uuid`, jobid, created_uid, created_devid)
   VALUES (3, 'Inventar', 'ivid', OLD.ivid, OLD.uuid, IFNULL(OLD.created_jobid, -1), -1, -1);
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Exportiere Struktur von Trigger mt_lumen_inventory.log_Inventar_inserts_uuid
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `log_Inventar_inserts_uuid` BEFORE INSERT ON `Inventar` FOR EACH ROW BEGIN
   IF IFNULL(NEW.uuid, '') = '' THEN
      SET NEW.uuid = UUID();
   END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Exportiere Struktur von Trigger mt_lumen_inventory.log_ObjektKatalogGlobal_after_insert
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `log_ObjektKatalogGlobal_after_insert` AFTER INSERT ON `ObjektKatalogGlobal` FOR EACH ROW BEGIN

	INSERT INTO server_change_log
	(`type`, `table`, `key`, `id`, `uuid`, jobid, created_uid, created_devid, obj)
   VALUES
	(1, 'ObjektKatalogGlobal', 'gcid', NEW.gcid, NEW.uuid, IFNULL(NEW.created_jobid, -1), -1, -1,
		JSON_OBJECT(
			'gcid', NEW.gcid,
			'uuid', NEW.uuid,
			'hash', NEW.hash,
			'code', NEW.code,
			'hid', NEW.hid,
			'huuid', NEW.huuid,
			'lid', NEW.lid,
			'Bezeichnung', NEW.Bezeichnung,
			'Produktnr', NEW.Produktnr,
			'Typ', NEW.Typ,
			'Gruppe', NEW.Gruppe,
			'Kategorie', NEW.Kategorie,
			'Farbe', NEW.Farbe,
			'Groesse', NEW.Groesse,
			'Bild', NEW.Bild,
			'AnlagenNr', NEW.AnlagenNr,
			'GeraetNr', NEW.GeraetNr,
			'FibuNr', NEW.FibuNr,
			'Flaeche', NEW.Flaeche,
			'Gewicht', NEW.Gewicht,
			'Baujahr', NEW.Baujahr,
			'Kst', NEW.Kst,

			'created_at', NEW.created_at,
			'created_uid', NEW.created_uid,
			'created_jobid', NEW.created_jobid,
			'created_device_id', NEW.created_device_id
		)
	);
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Exportiere Struktur von Trigger mt_lumen_inventory.log_ObjektKatalogGlobal_deletes
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `log_ObjektKatalogGlobal_deletes` AFTER DELETE ON `ObjektKatalogGlobal` FOR EACH ROW proc_exit: BEGIN
   IF (SELECT @TRIGGER_DISABLED = TRUE) OR (SELECT @TRIGGER_DELETE_DISABLED = TRUE) THEN
   	LEAVE proc_Exit;
   END IF;
   INSERT INTO server_change_log (`type`, `table`, `key`, `id`, `uuid`, jobid, created_uid, created_devid)
   VALUES (3, 'ObjektKatalogGlobal', 'gcid', OLD.gcid, OLD.uuid, IFNULL(OLD.created_jobid, -1), -1, -1);
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Exportiere Struktur von Trigger mt_lumen_inventory.log_ObjektKatalogGlobal_inserts_uuid
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `log_ObjektKatalogGlobal_inserts_uuid` BEFORE INSERT ON `ObjektKatalogGlobal` FOR EACH ROW BEGIN
   IF IFNULL(NEW.uuid, '') = '' THEN
      SET NEW.uuid = UUID();
   END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Exportiere Struktur von Trigger mt_lumen_inventory.log_ObjektKatalogMandant_after_insert
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `log_ObjektKatalogMandant_after_insert` AFTER INSERT ON `ObjektKatalogMandant` FOR EACH ROW BEGIN

	INSERT INTO server_change_log
	(`type`, `table`, `key`, `id`, `uuid`, jobid, created_uid, created_devid, obj)
   VALUES
	(1, 'ObjektKatalogMandant', 'mcid', NEW.mcid, NEW.uuid, IFNULL(NEW.created_jobid, -1), -1, -1,
		JSON_OBJECT(
			'mcid', NEW.mcid,
			'uuid', NEW.uuid,
			'for_jobid', NEW.for_jobid,
			'gcid', NEW.gcid,
			'gcuuid', NEW.gcuuid,
			'code', NEW.code,
			'mid', NEW.mid,

			'created_at', NEW.created_at,
			'created_uid', NEW.created_uid,
			'created_jobid', NEW.created_jobid,
			'created_device_id', NEW.created_device_id
		)
	);
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Exportiere Struktur von Trigger mt_lumen_inventory.log_ObjektKatalogMandant_deletes
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `log_ObjektKatalogMandant_deletes` AFTER DELETE ON `ObjektKatalogMandant` FOR EACH ROW proc_exit: BEGIN
   IF (SELECT @TRIGGER_DISABLED = TRUE) OR (SELECT @TRIGGER_DELETE_DISABLED = TRUE) THEN
   	LEAVE proc_Exit;
   END IF;
   INSERT INTO server_change_log (`type`, `table`, `key`, `id`, `uuid`, jobid, created_uid, created_devid)
   VALUES (3, 'ObjektKatalogMandant', 'mcid', OLD.mcid, OLD.uuid, IFNULL(OLD.created_jobid, -1), -1, -1);
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Exportiere Struktur von Trigger mt_lumen_inventory.log_ObjektKatalogMandant_inserts_uuid
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `log_ObjektKatalogMandant_inserts_uuid` BEFORE INSERT ON `ObjektKatalogMandant` FOR EACH ROW BEGIN
   IF IFNULL(NEW.uuid, '') = '' THEN
      SET NEW.uuid = UUID();
   END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Exportiere Struktur von Trigger mt_lumen_inventory.log_Raeume_after_insert
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `log_Raeume_after_insert` AFTER INSERT ON `Raeume` FOR EACH ROW BEGIN

	INSERT INTO server_change_log
	(`type`, `table`, `key`, `id`, `uuid`, jobid, created_uid, created_devid, obj)
   VALUES
	(1, 'Raeume', 'rid', NEW.rid, NEW.uuid, IFNULL(NEW.created_jobid, -1), -1, -1,
		JSON_OBJECT(
			'rid', NEW.rid,
			'gid', NEW.gid,
			'uuid', NEW.uuid,
			'hash', NEW.hash,
			'code', NEW.code,
			'for_jobid', NEW.for_jobid,
			'raumid', NEW.raumid,
			'Raum', NEW.Raum,
			'Raumbezeichnung', NEW.Raumbezeichnung,
			'Etage', NEW.Etage,
			'current_jobid', NEW.current_jobid,
			'current_jobstatus', NEW.current_jobstatus,

			'created_at', NEW.created_at,
			'created_uid', NEW.created_uid,
			'created_jobid', NEW.created_jobid,
			'created_device_id', NEW.created_device_id
		)
	);
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Exportiere Struktur von Trigger mt_lumen_inventory.log_Raeume_deletes
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `log_Raeume_deletes` AFTER DELETE ON `Raeume` FOR EACH ROW proc_exit: BEGIN
   IF (SELECT @TRIGGER_DISABLED = TRUE) OR (SELECT @TRIGGER_DELETE_DISABLED = TRUE) THEN
   	LEAVE proc_Exit;
   END IF;
   INSERT INTO server_change_log (`type`, `table`, `key`, `id`, `uuid`, jobid, created_uid, created_devid)
   VALUES (3, 'Raeume', 'rid', OLD.rid, OLD.uuid, IFNULL(OLD.created_jobid, -1), -1, -1);
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Exportiere Struktur von Trigger mt_lumen_inventory.log_Raeume_inserts_uuid
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `log_Raeume_inserts_uuid` BEFORE INSERT ON `Raeume` FOR EACH ROW BEGIN
   IF IFNULL(NEW.uuid, '') = '' THEN
      SET NEW.uuid = UUID();
   END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
