DELIMITER ;;
DROP TRIGGER IF EXISTS Inventar.log_inventar_inserts;
DROP TRIGGER IF EXISTS Inventar.log_Inventar_inserts;;
CREATE TRIGGER log_Inventar_inserts AFTER INSERT ON Inventar FOR EACH ROW
BEGIN
	INSERT INTO server_change_log (`type`, `table`, `key`, `id`, `uuid`, jobid, created_uid, created_devid)
	VALUES (1, 'Inventar', 'ivid', NEW.ivid, NEW.uuid, NEW.created_jobid, NEW.created_uid, NEW.created_device_id);
END;;

DROP TRIGGER IF EXISTS Inventar.log_Inventar_updates;
CREATE TRIGGER log_Inventar_updates AFTER UPDATE ON Inventar FOR EACH ROW
BEGIN
	INSERT INTO server_change_log (`type`, `table`, `key`, `id`, `uuid`, jobid, created_uid, created_devid)
	VALUES (2, 'Inventar', 'ivid', NEW.ivid, NEW.uuid, NEW.modified_jobid, NEW.modified_uid, NEW.modified_device_id);
END;;

DROP TRIGGER IF EXISTS Inventar.log_Inventar_deletes;
CREATE TRIGGER log_Inventar_deletes AFTER DELETE ON Inventar FOR EACH ROW
BEGIN
	INSERT INTO server_change_log (`type`, `table`, `key`, `id`, `uuid`, jobid, created_uid, created_devid)
	VALUES (3, 'Inventar', 'ivid', OLD.ivid, OLD.uuid, OLD.created_jobid, OLD.created_uid, OLD.modified_device_id);
END;;


DROP TRIGGER IF EXISTS Raeume.log_Raeume_inserts;;
CREATE TRIGGER log_Raeume_inserts AFTER INSERT ON Raeume FOR EACH ROW
BEGIN
	INSERT INTO server_change_log (`type`, `table`, `key`, `id`, `uuid`, jobid, created_uid, created_devid)
	VALUES (1, 'Raeume', 'rid', NEW.rid, NEW.uuid, NEW.created_jobid, NEW.created_uid, NEW.created_device_id);
END;;

DROP TRIGGER IF EXISTS Raeume.log_Raeume_updates;;
CREATE TRIGGER log_Raeume_updates AFTER UPDATE ON Raeume FOR EACH ROW
BEGIN
	INSERT INTO server_change_log (`type`, `table`, `key`, `id`, `uuid`, jobid, created_uid, created_devid)
	VALUES (2, 'Raeume', 'rid', NEW.rid, NEW.uuid, NEW.modified_jobid, NEW.modified_uid, NEW.modified_device_id);
END;;

DROP TRIGGER IF EXISTS Raeume.log_Raeume_deletes;;
CREATE TRIGGER log_Raeume_deletes AFTER DELETE ON Raeume FOR EACH ROW
BEGIN
	INSERT INTO server_change_log (`type`, `table`, `key`, `id`, `uuid`, jobid, created_uid, created_devid)
	VALUES (3, 'Raeume', 'rid', OLD.rid, OLD.uuid, OLD.created_jobid, OLD.created_uid, OLD.modified_device_id);
END;;


DROP TRIGGER IF EXISTS ObjektKatalogGlobal.log_ObjektKatalogGlobal_inserts;;
CREATE TRIGGER log_ObjektKatalogGlobal_inserts AFTER INSERT ON ObjektKatalogGlobal FOR EACH ROW
BEGIN
	INSERT INTO server_change_log (`type`, `table`, `key`, `id`, `uuid`, jobid, created_uid, created_devid)
	VALUES (1, 'objektKatalogGlobal', 'gcid', NEW.gcid, NEW.uuid, NEW.created_jobid, NEW.created_uid, NEW.created_device_id);
END;;

DROP TRIGGER IF EXISTS ObjektKatalogGlobal.log_ObjektKatalogGlobal_updates;;
CREATE TRIGGER log_ObjektKatalogGlobal_updates AFTER UPDATE ON ObjektKatalogGlobal FOR EACH ROW
BEGIN
	INSERT INTO server_change_log (`type`, `table`, `key`, `id`, `uuid`, jobid, created_uid, created_devid)
	VALUES (2, 'objektKatalogGlobal', 'gcid', NEW.gcid, NEW.uuid, NEW.modified_jobid, NEW.modified_uid, NEW.modified_device_id);
END;;

DROP TRIGGER IF EXISTS ObjektKatalogGlobal.log_ObjektKatalogGlobal_deletes;;
CREATE TRIGGER log_ObjektKatalogGlobal_deletes AFTER DELETE ON ObjektKatalogGlobal FOR EACH ROW
BEGIN
	INSERT INTO server_change_log (`type`, `table`, `key`, `id`, `uuid`, jobid, created_uid, created_devid)
	VALUES (3, 'objektKatalogGlobal', 'gcid', OLD.gcid, OLD.uuid, OLD.created_jobid, OLD.created_uid, OLD.modified_device_id);
END;;



DROP TRIGGER IF EXISTS ObjektKatalogMandant.log_ObjektKatalogMandant_inserts;;
CREATE TRIGGER log_ObjektKatalogMandant_inserts AFTER INSERT ON ObjektKatalogMandant FOR EACH ROW
BEGIN
	INSERT INTO server_change_log (`type`, `table`, `key`, `id`, `uuid`, jobid, created_uid, created_devid)
	VALUES (1, 'objektKatalogMandant', 'mcid', NEW.mcid, NEW.uuid, NEW.created_jobid, NEW.created_uid, NEW.created_device_id);
END;;

DROP TRIGGER IF EXISTS ObjektKatalogMandant.log_ObjektKatalogMandant_updates;;
CREATE TRIGGER log_ObjektKatalogMandant_updates AFTER UPDATE ON ObjektKatalogMandant FOR EACH ROW
BEGIN
	INSERT INTO server_change_log (`type`, `table`, `key`, `id`, `uuid`, jobid, created_uid, created_devid)
	VALUES (2, 'objektKatalogMandant', 'mcid', NEW.mcid, NEW.uuid, NEW.modified_jobid, NEW.modified_uid, NEW.modified_device_id);
END;;

DROP TRIGGER IF EXISTS ObjektKatalogMandant.log_ObjektKatalogMandant_deletes;;
CREATE TRIGGER log_ObjektKatalogMandant_deletes AFTER DELETE ON ObjektKatalogMandant FOR EACH ROW
BEGIN
	INSERT INTO server_change_log (`type`, `table`, `key`, `id`, `uuid`, jobid, created_uid, created_devid)
	VALUES (3, 'objektKatalogMandant', 'mcid', OLD.mcid, OLD.uuid, OLD.created_jobid, OLD.created_uid, OLD.modified_device_id);
END;;



DROP TRIGGER IF EXISTS Hersteller.log_Hersteller_inserts;;
CREATE TRIGGER log_Hersteller_inserts AFTER INSERT ON Hersteller FOR EACH ROW
BEGIN
	INSERT INTO server_change_log (`type`, `table`, `key`, `id`, `uuid`, jobid, created_uid, created_devid)
	VALUES (1, 'Hersteller', 'hid', NEW.hid, NEW.uuid, NEW.created_jobid, NEW.created_uid, NEW.created_device_id);
END;;

DROP TRIGGER IF EXISTS Hersteller.log_Hersteller_updates;;
CREATE TRIGGER log_Hersteller_updates AFTER UPDATE ON Hersteller FOR EACH ROW
BEGIN
	INSERT INTO server_change_log (`type`, `table`, `key`, `id`, `uuid`, jobid, created_uid, created_devid)
	VALUES (2, 'Hersteller', 'hid', NEW.hid, NEW.uuid, NEW.modified_jobid, NEW.modified_uid, NEW.modified_device_id);
END;;

DROP TRIGGER IF EXISTS Hersteller.log_Hersteller_deletes;;
CREATE TRIGGER log_Hersteller_deletes AFTER DELETE ON Hersteller FOR EACH ROW
BEGIN
	INSERT INTO server_change_log (`type`, `table`, `key`, `id`, `uuid`, jobid, created_uid, created_devid)
	VALUES (3, 'Hersteller', 'hid', OLD.hid, OLD.uuid, OLD.created_jobid, OLD.created_uid, OLD.modified_device_id);
END;;



DROP TRIGGER IF EXISTS Images.log_Images_inserts;;
CREATE TRIGGER log_Images_inserts AFTER INSERT ON Images FOR EACH ROW
BEGIN
	INSERT INTO server_change_log (`type`, `table`, `key`, `id`, `uuid`, jobid, created_uid, created_devid)
	VALUES (1, 'Images', 'id', NEW.id, NEW.uuid, NEW.created_jobid, NEW.created_uid, NEW.created_devid);
END;;

DROP TRIGGER IF EXISTS Images.log_Images_updates;
CREATE TRIGGER log_Images_updates AFTER UPDATE ON Images FOR EACH ROW
BEGIN
	INSERT INTO server_change_log (`type`, `table`, `key`, `id`, `uuid`, jobid, created_uid, created_devid)
	VALUES (2, 'Images', 'id', NEW.id, NEW.uuid, NEW.modified_jobid, NEW.modified_uid, NEW.modified_devid);
END;;

DROP TRIGGER IF EXISTS Images.log_Images_deletes;;
CREATE TRIGGER log_Images_deletes AFTER DELETE ON Images FOR EACH ROW
BEGIN
	INSERT INTO server_change_log (`type`, `table`, `key`, `id`, `uuid`, jobid, created_uid, created_devid)
	VALUES (3, 'Images', 'id', OLD.id, OLD.uuid, OLD.created_jobid, OLD.created_uid, OLD.modified_devid);
END;;
DELIMITER ;

-- FOR Tables AND Key
SET @TR_TBL = 'Hersteller';
SET @TR_KEY = 'hid';

SET @TR_TBL = 'Images';
SET @TR_KEY = 'id';

SET @TR_TBL = 'ObjektKatalogGlobal';
SET @TR_KEY = 'gcid';

SET @TR_TBL = 'ObjektKatalogMandant';
SET @TR_KEY = 'mcid';

SET @TR_TBL = 'Raeume';
SET @TR_KEY = 'rid';

/*
proc_exit: BEGIN

   IF (SELECT @TRIGGER_DISABLED = TRUE) OR (SELECT @TRIGGER_INSERT_DISABLED = TRUE) THEN
   	LEAVE proc_Exit;
   END IF;
*/

SET @TR_TBL = 'Inventar';
SET @TR_KEY = 'ivid';
SELECT CONCAT('/* ', @TR_TBL, ': TRIGGER UPDATES */') AS col
UNION SELECT CONCAT('DROP TRIGGER IF EXISTS ', @TR_TBL, '.log_', @TR_TBL, '_updates;') AS col
UNION SELECT 'DELIMITER ;;' AS col
UNION SELECT CONCAT('CREATE TRIGGER log_', @TR_TBL, '_updates AFTER UPDATE ON ', @TR_TBL, ' FOR EACH ROW ') AS col
UNION SELECT 'proc_exit: BEGIN ' AS col
UNION SELECT '   IF (SELECT @TRIGGER_DISABLED = TRUE) OR (SELECT @TRIGGER_UPDATE_DISABLED = TRUE) THEN ' AS col
UNION SELECT '   	LEAVE proc_Exit; ' AS col
UNION SELECT '   END IF; ' AS col
UNION SELECT '   DECLARE MODS LONGTEXT;' AS col
UNION SELECT '   SET MODS = \'{}\';' AS col
UNION SELECT col FROM (SELECT CONCAT('   IF IFNULL(OLD.', COLUMN_NAME,  ', \'\') != IFNULL(NEW.', COLUMN_NAME, ', \'\') THEN \n      SET MODS = JSON_INSERT(MODS, \'$.', COLUMN_NAME, '\', NEW.', COLUMN_NAME, ');\n   END IF;') AS col
   FROM information_schema.`COLUMNS`
   WHERE TABLE_SCHEMA = 'mt_lumen_inventory' AND TABLE_NAME = @TR_TBL
   ORDER BY ORDINAL_POSITION) AS MODS
UNION SELECT '   INSERT INTO server_change_log (`type`, `table`, `key`, `id`, `uuid`, jobid, mods, created_uid, created_devid) ' AS col
UNION SELECT CONCAT('   VALUES (2, \'', @TR_TBL, '\', \'', @TR_KEY, '\', NEW.', @TR_KEY, ', NEW.uuid, IFNULL(NEW.created_jobid, -1), MODS, IFNULL(NEW.modified_uid, -1), IFNULL(NEW.modified_device_id, -1) ); ') AS col
UNION SELECT 'END;' AS col
UNION SELECT 'DELIMITER ;' AS col
;

SELECT CONCAT('/* ', @TR_TBL, ': TRIGGER INSERTS */') AS col
UNION SELECT CONCAT('DROP TRIGGER IF EXISTS ', @TR_TBL, '.log_', @TR_TBL, '_inserts;') AS col
UNION SELECT 'DELIMITER ;;' AS col
UNION SELECT CONCAT('CREATE TRIGGER log_', @TR_TBL, '_inserts AFTER INSERT ON ', @TR_TBL, ' FOR EACH ROW ') AS col
UNION SELECT 'proc_exit: BEGIN ' AS col
UNION SELECT '   IF (SELECT @TRIGGER_DISABLED = TRUE) OR (SELECT @TRIGGER_INSERT_DISABLED = TRUE) THEN ' AS col
UNION SELECT '   	LEAVE proc_Exit; ' AS col
UNION SELECT '   END IF; \n' AS col
UNION SELECT '   DECLARE OBJ LONGTEXT;' AS col
UNION SELECT '   SET OBJ = \'{}\';' AS col
UNION SELECT col FROM (SELECT CONCAT('   IF IFNULL(NEW.', COLUMN_NAME,  ', \'\') != \'\' THEN \n      SET OBJ = JSON_INSERT(OBJ, \'$.', COLUMN_NAME, '\', NEW.', COLUMN_NAME, ');\n   END IF;') AS col
   FROM information_schema.`COLUMNS`
   WHERE TABLE_SCHEMA = 'mt_lumen_inventory' AND TABLE_NAME = @TR_TBL
   ORDER BY ORDINAL_POSITION) AS OBJ
UNION SELECT '   INSERT INTO server_change_log (`type`, `table`, `key`, `id`, `uuid`, jobid, obj, created_uid, created_devid) ' AS col
UNION SELECT CONCAT('   VALUES (1, \'', @TR_TBL, '\', \'', @TR_KEY, '\', NEW.', @TR_KEY, ', NEW.uuid, IFNULL(NEW.created_jobid, -1), OBJ, IFNULL(NEW.created_uid, -1), IFNULL(NEW.created_device_id, -1)); ') AS col
UNION SELECT 'END;' AS col
UNION SELECT 'DELIMITER ;' AS col
;

SELECT CONCAT('/* ', @TR_TBL, ': TRIGGER DELETES */') AS col
UNION SELECT CONCAT('DROP TRIGGER IF EXISTS ', @TR_TBL, '.log_', @TR_TBL, '_deletes;') AS col
UNION SELECT 'DELIMITER ;;' AS col
UNION SELECT CONCAT('CREATE TRIGGER log_', @TR_TBL, '_deletes AFTER DELETE ON ', @TR_TBL, ' FOR EACH ROW ') as col
UNION SELECT 'proc_exit: BEGIN ' AS col
UNION SELECT '   IF (SELECT @TRIGGER_DISABLED = TRUE) OR (SELECT @TRIGGER_DELETE_DISABLED = TRUE) THEN ' AS col
UNION SELECT '   	LEAVE proc_Exit; ' AS col
UNION SELECT '   END IF; \n' AS col
UNION SELECT '   INSERT INTO server_change_log (`type`, `table`, `key`, `id`, `uuid`, jobid, created_uid, created_devid) ' AS col
UNION SELECT CONCAT('   VALUES (3, \'', @TR_TBL, '\', \'', @TR_KEY, '\', OLD.', @TR_KEY, ', OLD.uuid, IFNULL(OLD.created_jobid, -1), INFULL(OLD.created_uid, -1), -1); ') AS col
UNION SELECT 'END;;' AS col
UNION SELECT 'DELIMITER ;' AS col
;

