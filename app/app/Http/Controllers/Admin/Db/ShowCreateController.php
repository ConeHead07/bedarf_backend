<?php
/**
 * Created by PhpStorm.
 * User: f.barthold
 * Date: 03.06.2020
 * Time: 09:48
 */

namespace App\Http\Controllers\Admin\Db;

use App\Http\Controllers\Controller;


class ShowCreateController extends Controller
{
    public $dbName = '';

    public function __construct()
    {
        parent::__construct();

        $this->dbName = 'mt_lumen_inventory';
    }

    public function getTriggerNameUid(string $tbl) {
        return "trigger_{$tbl}_before_insert_uuid";
    }

    public function getTriggerNameInsert(string $tbl) {
        return "trigger_{$tbl}_after_insert";
    }

    public function getTriggerNameUpdate(string $tbl) {
        return "trigger_{$tbl}_after_update";
    }

    public function getTriggerNameDelete(string $tbl) {
        return "trigger_{$tbl}_after_delete";
    }

    public function showCreateOnInsertCheckUuid(string $tbl, string $triggerName = '')
    {
        if (!$triggerName) {
            $triggerName = $this->getTriggerNameInsert($tbl);
        }

        $creator = "CREATE TRIGGER {$triggerName} BEFORE INSERT ON {$tbl} FOR EACH ROW \n";
        $creator.= "BEGIN\n";
        $creator.= "   IF IFNULL(NEW.uuid, '') = '' THEN \n";
        $creator.= "      SET NEW.uuid = UUID();\n";
        $creator.= "   END IF;\n";
        $creator.= "END";

        return $creator;
    }

    public function showCreateOnInsert(string $tbl, string $key, string $triggerName = '')
    {
        if (!$triggerName) {
            $triggerName = $this->getTriggerNameInsert($tbl);
        }

        $cols = $this->getOrderedTableColNames($tbl);

        $creator = '';

        /**
        proc_exit: BEGIN ' AS col
        UNION SELECT '   IF (SELECT @TRIGGER_DISABLED = TRUE) OR (SELECT @TRIGGER_INSERT_DISABLED = TRUE) THEN ' AS col
        UNION SELECT '   	LEAVE proc_Exit; ' AS col
        UNION SELECT '   END IF; '
         */

        $creator.= "CREATE TRIGGER {$triggerName} AFTER INSERT ON {$tbl} FOR EACH ROW \n";
        if (1) {
            $creator .= "BEGIN \n\n";
        } else {
            $creator .= "proc_exit: BEGIN \n\n";

            $creator .= "   IF (SELECT @TRIGGER_DISABLED = TRUE) OR (SELECT @TRIGGER_UPDATE_DISABLED = TRUE) THEN\n";
            $creator .= "      LEAVE proc_Exit;\n";
            $creator .= "   END IF;\n\n";
        }

        $creator.= "   DECLARE jsonObj LongText;\n";
        $creator.= "   SET jsonObj = JSON_OBJECT();\n";

        foreach($cols as $_col) {
            if (preg_match('/^(modified|updated)_/', $_col)) {
                continue;
            }
            $creator.= "   IF IFNULL(NEW.{$_col}, '') != '' THEN\n";
            $creator.= "      SET jsonObj = JSON_INSERT(jsonObj, '$.{$_col}', NEW.{$_col});\n";
            $creator.= "   END IF;\n\n";
        }

        if (in_array('for_jobid', $cols) && in_array('jobid', $cols)) {
            $exprJobId = 'IFNULL(NEW.jobid, IFNULL(NEW.for_jobid, -1))';
        } else {
            $exprJobId = in_array('for_jobid', $cols) ? 'IFNULL(NEW.for_jobid, -1)' : '-1';
        }
        $exprUId = in_array('created_uid', $cols) ? 'IFNULL(NEW.created_uid, -1)' : '-1';
        $exprDevId = in_array('created_device_id', $cols) ? 'IFNULL(NEW.created_device_id, -1)' : '-1';

        $creator.= "   INSERT INTO server_change_log \n";
        $creator.= "     (`type`, `table`, `key`, `id`, `uuid`, jobid, created_uid, created_devid, obj) \n";
        $creator.= "   VALUES\n";
        $creator.= "     (1, '{$tbl}', '{$key}', NEW.{$key}, NEW.uuid, {$exprJobId}, {$exprUId}, {$exprDevId}, jsonObj);\n\n";
        $creator.= "END \n";

        return $creator;

    }


    public function showCreateOnUpdate(string $tbl, string $key, string $triggerName = '')
    {
        if (!$triggerName) {
            $triggerName = $this->getTriggerNameInsert($tbl);
        }

        $cols = $this->getOrderedTableColNames($tbl);
        $creator = '';

        $creator.= "CREATE TRIGGER {$triggerName} AFTER UPDATE ON {$tbl} FOR EACH ROW \n";
        if (1) {
            $creator .= "BEGIN \n\n";
        } else {
            $creator.= "proc_exit: BEGIN \n";
            $creator.= "   IF (SELECT @TRIGGER_DISABLED = TRUE) OR (SELECT @TRIGGER_UPDATE_DISABLED = TRUE) THEN \n";
            $creator.= "   	LEAVE proc_Exit; \n";
            $creator.= "   END IF; \n\n";
        }

        $creator.= "   DECLARE jsonObj LongText;\n";
        $creator.= "   SET jsonObj = JSON_OBJECT();\n";

        foreach($cols as $_col) {
            if (preg_match('/^created_/', $_col)) {
                continue;
            }
            $creator.= "   IF IFNULL(NEW.{$_col}, '') != IFNULL(OLD.{$_col}, '') THEN \n";
            $creator.= "      SET jsonObj = JSON_INSERT(jsonObj, '$.{$_col}', NEW.{$_col});\n";
            $creator.= "   END IF;\n\n";
        }

        if (in_array('for_jobid', $cols) && in_array('jobid', $cols)) {
            $exprJobId = 'IFNULL(NEW.jobid, IFNULL(NEW.for_jobid, -1))';
        } elseif (in_array('for_jobid', $cols)) {
            $exprJobId = 'IFNULL(NEW.for_jobid, -1)';
        } elseif (in_array('modified_jobid', $cols)) {
            $exprJobId = 'IFNULL(NEW.modified_jobid, -1)';
        } else {
            $exprJobId = '-1';
        }

        $exprUId = in_array('modified_uid', $cols) ? 'IFNULL(NEW.modified_uid, -1)' : '-1';
        $exprDevId = in_array('modified_device_id', $cols) ? 'IFNULL(NEW.modified_device_id, -1)' : '-1';

        $creator.= "   INSERT INTO server_change_log \n";
        $creator.= "     (`type`, `table`, `key`, `id`, `uuid`, jobid, created_uid, created_devid, mods) \n";
        $creator.= "   VALUES\n";
        $creator.= "     (2, '{$tbl}', '{$key}', NEW.{$key}, NEW.uuid, {$exprJobId}, {$exprUId}, {$exprDevId}, jsonObj);\n\n";
        $creator.= "END \n";

        return $creator;

    }

    public function showCreateOnDelete(string $tbl, string $key, string $triggerName = '')
    {
        if (!$triggerName) {
            $triggerName = $this->getTriggerNameInsert($tbl);
        }

        $creator = "CREATE TRIGGER {$triggerName} AFTER DELETE ON {$tbl} FOR EACH ROW \n";
        if (1) {
            $creator .= "BEGIN \n\n";
        } else {
            $creator .= "proc_exit: BEGIN \n";
            $creator .= "   IF (SELECT @TRIGGER_DISABLED = TRUE) OR (SELECT @TRIGGER_DELETE_DISABLED = TRUE) THEN \n";
            $creator .= "   	LEAVE proc_Exit; \n";
            $creator .= "   END IF; \n\n";
        }

        $creator.= "   INSERT INTO server_change_log (`type`, `table`, `key`, `id`, `uuid`, jobid, created_uid, created_devid) \n";
        $creator.= "   VALUES (3, '{$tbl}', '{$key}', OLD.{$key}, OLD.uuid, IFNULL(OLD.created_jobid, -1), -1, -1); \n";
        $creator.= "END \n";

        return $creator;
    }

    private function showDropByName(string $triggerName) {
        return "DROP TRIGGER IF EXISTS {$triggerName}";
    }

    private function getOrderedTableColNames(string $tbl) {
        $db = \DB::getPdo();

        $sql = "SELECT COLUMN_NAME FROM information_schema.`COLUMNS` \n"
                ."  WHERE TABLE_SCHEMA = " . $db->quote($this->dbName) . " AND TABLE_NAME = " . $db->quote($tbl) . "\n"
                ."  ORDER BY ORDINAL_POSITION";
        $stmt = $db->query( $sql );
        return array_map(function($cols) { return $cols[0]; }, $stmt->fetchAll());
    }

    public function triggers()
    {
        $db = \DB::getPdo();

        $aChangeLogTrigger = [
            [ 'TBL' => 'Hersteller', 'KEY' => 'hid'],
            [ 'TBL' => 'Images', 'KEY' => 'id'],
            [ 'TBL' => 'Inventar', 'KEY' => 'ivid'],
            [ 'TBL' => 'ObjektKatalogGlobal', 'KEY' => 'gcid'],
            [ 'TBL' => 'ObjektKatalogMandant', 'KEY' => 'mcid'],
            [ 'TBL' => 'ObjektKatalogImages', 'KEY' => 'id'],
            [ 'TBL' => 'Raeume', 'KEY' => 'rid']
        ];

        $list = '';

        foreach($aChangeLogTrigger as $_props) {
            $_tbl = $_props['TBL'];
            $_key = $_props['KEY'];

            $uidName = $this->getTriggerNameUid($_tbl);
            $insName = $this->getTriggerNameInsert($_tbl);
            $updName = $this->getTriggerNameUpdate($_tbl);
            $delName = $this->getTriggerNameDelete($_tbl);

            $list.= '-- TRIGGER FOR ' . $_tbl . "\n\n";

            $list.= "-- :: BEFORE INSERT: $uidName\n";
            $uidDrop = $this->showDropByName($uidName);
            $list.= $uidDrop . ";\n";
            $uidCreate = $this->showCreateOnInsertCheckUuid($_tbl, $uidName);
            $list.= $uidCreate . "\n\n";

            $list.= "-- :: AFTER INSERT: $insName \n";
            $insDrop = $this->showDropByName($insName);
            $list.= $insDrop . ";\n";
            $insCreate = $this->showCreateOnInsert($_tbl, $_key, $insName);
            $list.= $insCreate . ";\n\n";

            $list.= "-- :: AFTER UPDATE: $updName \n";
            $updDrop = $this->showDropByName($updName);
            $list.= $updDrop . ";\n";
            $updCreate = $this->showCreateOnUpdate($_tbl, $_key, $updName) . ";\n\n";
            $list.= $updCreate . ";\n\n ";

            $list.= "-- :: AFTER DELETE: $delName \n";
            $delDrop = $this->showDropByName($delName);
            $list.= $delDrop . ";\n";
            $delCreate = $this->showCreateOnDelete($_tbl, $_key, $delName) . ";\n\n";
            $list.= $delCreate . ";\n\n ";

            // if (1 && $db->beginTransaction() ) {
                // $db->exec('DELIMITER ;');

            try {
                $db->exec($uidDrop);
                $db->exec($uidCreate);
                $db->exec($insDrop);
                $db->exec($insCreate);
                $db->exec($updDrop);
                $db->exec($updCreate);
                $db->exec($delDrop);
                $db->exec($delCreate);
                // $db->exec('DELIMITER ;');

                $list.= 'Successful executed!';
            }
            catch(\Exception $e) {
                $list.= 'Could Not be executed!';
                $list.= $e->getMessage();
                $list.= '<pre>' . $e->getTraceAsString() . '</pre>';
                return $list;
            }
        }

        return $list;

    }

    public function triggers2()
    {
        $db = \DB::getPdo();

        $aChangeLogTrigger = [
            [ 'TR_TBL' => 'Hersteller', 'TR_KEY' => 'hid'],
            [ 'TR_TBL' => 'Images', 'TR_KEY' => 'id'],
            [ 'TR_TBL' => 'Inventar', 'TR_KEY' => 'ivid'],
            [ 'TR_TBL' => 'ObjektKatalogGlobal', 'TR_KEY' => 'gcid'],
            [ 'TR_TBL' => 'ObjektKatalogMandant', 'TR_KEY' => 'mcid'],
            [ 'TR_TBL' => 'Raeume', 'TR_KEY' => 'rid'],
        ];

        // SET @TR_TBL = 'Inventar';
        // SET @TR_KEY = 'ivid';

        $showCreateUuidTrigger = <<<EOT
SELECT CONCAT('/* ', @TR_TBL, ': TRIGGER INSERTS */') AS col
UNION SELECT CONCAT('DROP TRIGGER IF EXISTS log_', @TR_TBL, '_inserts_uuid;') AS col
UNION SELECT 'DELIMITER ;;' AS col
UNION SELECT CONCAT('CREATE TRIGGER log_', @TR_TBL, '_inserts_uuid BEFORE INSERT ON ', @TR_TBL, ' FOR EACH ROW ') AS col
UNION SELECT 'BEGIN ' AS col
UNION SELECT '   IF IFNULL(NEW.uuid, \'\') = \'\' THEN \n      SET NEW.uuid = UUID();\n   END IF;' AS col
UNION SELECT 'END;;' AS col
UNION SELECT 'DELIMITER ;' AS col
EOT;
        $showCreateInsertTrigger = <<<EOT
SELECT CONCAT('/* ', @TR_TBL, ': TRIGGER INSERTS */') AS col
UNION SELECT CONCAT('DROP TRIGGER IF EXISTS log_', @TR_TBL, '_inserts;') AS col
UNION SELECT 'DELIMITER ;;' AS col
UNION SELECT CONCAT('CREATE TRIGGER log_', @TR_TBL, '_inserts AFTER INSERT ON ', @TR_TBL, ' FOR EACH ROW ') AS col
UNION SELECT 'proc_exit: BEGIN ' AS col
UNION SELECT '   IF (SELECT @TRIGGER_DISABLED = TRUE) OR (SELECT @TRIGGER_INSERT_DISABLED = TRUE) THEN ' AS col
UNION SELECT '   	LEAVE proc_Exit; ' AS col
UNION SELECT '   END IF; ' AS col
UNION SELECT '   DECLARE OBJ LONGTEXT;' AS col
UNION SELECT '   SET OBJ = \'{}\';' AS col
UNION SELECT col FROM (SELECT CONCAT('   IF IFNULL(NEW.', COLUMN_NAME,  ', \'\') != \'\' THEN \n      SET OBJ = JSON_INSERT(OBJ, \'$.', COLUMN_NAME, '\', NEW.', COLUMN_NAME, ');\n   END IF;') AS col
   FROM information_schema.`COLUMNS`
   WHERE TABLE_SCHEMA = 'mt_lumen_inventory' AND TABLE_NAME = @TR_TBL
   ORDER BY ORDINAL_POSITION) AS OBJ
UNION SELECT '   INSERT INTO server_change_log (`type`, `table`, `key`, `id`, `uuid`, jobid, obj, created_uid, created_devid) ' AS col
UNION SELECT CONCAT('   VALUES (1, \'', @TR_TBL, '\', \'', @TR_KEY, '\', NEW.', @TR_KEY, ', NEW.uuid, IFNULL(NEW.created_jobid, -1), OBJ, IFNULL(NEW.created_uid, -1), IFNULL(NEW.created_device_id, -1)); ') AS col
UNION SELECT 'END;;' AS col
UNION SELECT 'DELIMITER ;' AS col
EOT;

        $showCreateUpdateTrigger = <<<EOT
SELECT CONCAT('/* ', @TR_TBL, ': TRIGGER UPDATES */') AS col
UNION SELECT CONCAT('DROP TRIGGER IF EXISTS log_', @TR_TBL, '_updates;') AS col
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
UNION SELECT 'END;;' AS col
UNION SELECT 'DELIMITER ;' AS col
EOT;

        $showCreateDeleteTrigger = <<<EOT
SELECT CONCAT('/* ', @TR_TBL, ': TRIGGER DELETES */') AS col
UNION SELECT CONCAT('DROP TRIGGER IF EXISTS log_', @TR_TBL, '_deletes;') AS col
UNION SELECT 'DELIMITER ;;' AS col
UNION SELECT CONCAT('CREATE TRIGGER log_', @TR_TBL, '_deletes AFTER DELETE ON ', @TR_TBL, ' FOR EACH ROW ') as col
UNION SELECT 'proc_exit: BEGIN ' AS col
UNION SELECT '   IF (SELECT @TRIGGER_DISABLED = TRUE) OR (SELECT @TRIGGER_DELETE_DISABLED = TRUE) THEN ' AS col
UNION SELECT '   	LEAVE proc_Exit; ' AS col
UNION SELECT '   END IF; ' AS col
UNION SELECT '   INSERT INTO server_change_log (`type`, `table`, `key`, `id`, `uuid`, jobid, created_uid, created_devid) ' AS col
UNION SELECT CONCAT('   VALUES (3, \'', @TR_TBL, '\', \'', @TR_KEY, '\', OLD.', @TR_KEY, ', OLD.uuid, IFNULL(OLD.created_jobid, -1), -1, -1); ') AS col
UNION SELECT 'END;;' AS col
UNION SELECT 'DELIMITER ;' AS col
EOT;

        foreach($aChangeLogTrigger as $_vars) {
            foreach($_vars as $_k => $_v) {
                $db->exec("SET @{$_k} = '{$_v}';");
            }

            $stmt = $db->query($showCreateUuidTrigger);
            $rows = $stmt->fetchAll(\PDO::FETCH_NUM);
            $cols = array_map(function($row) { return $row[0]; }, $rows);
            $sqlAutoUiid = implode("\n", $cols);

            $stmt = $db->query($showCreateInsertTrigger);
            $rows = $stmt->fetchAll(\PDO::FETCH_NUM);
            $cols = array_map(function($row) { return $row[0]; }, $rows);
            $sqlInsertTrigger = implode("\n", $cols);


            $stmt = $db->query($showCreateUpdateTrigger);
            $rows = $stmt->fetchAll(\PDO::FETCH_NUM);
            $cols = array_map(function($row) { return $row[0]; }, $rows);
            $sqlUpdateTrigger = implode("\n", $cols);


            $stmt = $db->query($showCreateDeleteTrigger);
            $rows = $stmt->fetchAll(\PDO::FETCH_NUM);
            $cols = array_map(function($row) { return $row[0]; }, $rows);
            $sqlDeleteTrigger = implode("\n", $cols);

            echo "<pre>\n$sqlAutoUiid\n\n$sqlInsertTrigger\n\n$sqlUpdateTrigger\n\n$sqlDeleteTrigger\n</pre>";
            @ob_end_flush();
        }

    }
}
