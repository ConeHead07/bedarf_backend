<?php
/**
 * Created by PhpStorm.
 * User: f.barthold
 * Date: 03.06.2020
 * Time: 23:16
 */

namespace App;
use Illuminate\Database\Eloquent\Model;

class ServerChangeRow {
    /** @var int */
    public $revision_id = 0;
    /** @var int  */
    public $type = 0;
    /** @var string  */
    public $table = '';
    /** @var string  */
    public $key = '';
    /** @var int  */
    public $id = 0;
    /** @var string  */
    public $uuid = '';
    /** @var string  */
    public $obj = '{}';
    /** @var string  */
    public $mods = '{}';

    public function __construct(array $row = [])
    {
        foreach($row as $k => $v) {
            $this->{$k} = $v;
        }
        $this->revision_id = (int)$this->revision_id;
        $this->type = (int)$this->type;
        $this->id = (int)$this->id;
    }
}

class ServerChangeLogData {
    /** @var array  */
    public $rows = [];
    /** @var int  */
    public $total = 0;
    /** @var int  */
    public $chunkSize = 0;
    /** @var int  */
    public $firstRevId = 0;
    /** @var int  */
    public $lastRevId = 0;
}

class ServerChangeLog extends BaseModel
{
    use HasCreatedModUid;

    protected $table = 'server_change_log';
    protected $primaryKey = 'revision_id';
    public $lastQuery = '';
    public $lastDebug = [];

    public function getOrCreateInitialRevId(int $jobid, string $table): int {
        $db = \DB::getPdo();
        $sql = 'SELECT MAX(revision_id) max_rev_id FROM ' . $this->table
            . ' WHERE jobid = ' . $jobid
            . ' AND `table` = ' . $db->quote($table);
        $this->lastQuery = $sql;

        $stmt = $db->query( $sql );
        $row = $stmt->fetch(\PDO::FETCH_OBJ);
        $this->lastQuery.= "\n" . json_encode($row);
        $rev_id = $row->max_rev_id;
        if (!is_null($rev_id) && (int)$rev_id > 0) {
            return (int)$rev_id;
        }

        $sql = "INSERT INTO " . $this->table
            . ' (`type`, `table`, `id`, jobid) '
            . ' VALUES (0, ' . $db->quote($table) . ', 0, ' . (int)$jobid . ')';
        $this->lastQuery.= "\n" . $sql;

        $success = $db->exec( $sql );
        if ($success) {
            return $db->lastInsertId();
        }
        return 0;
    }

    public function maxRevisionIdByJobid(int $jobid) {
        $db = \DB::getPdo();
        $this->lastDebug = ['queryies' => [], 'lines' => []];

        /**
         * Problemstellung
         * RevIDs relevante Änderungen in der Tabelle ObjektkatalogGlobal können
         * mit der Jobid nur indirekt über einen Join mit ObjektKatalogMandant
         * ermittelt werden.
         */

        $line = __LINE__;
        $sql = 'SELECT MAX(revision_id) FROM ' . $this->table
            . ' WHERE jobid = ' . (int)$jobid;
        $stmt1 = $db->query( $sql );
        $maxRevIdByJobId = $stmt1->fetchColumn();
        $this->lastDebug['queryies'][] = compact('sql', 'maxRevIdByJobId', 'line');
        $this->lastDebug['queryies']['lines'][] = $line;

        $line = __LINE__;
        $sql2 = 'SELECT MAX(revision_id) FROM ' . $this->table;
        $stmt2 = $db->query( $sql2);
        $maxRevIdOfAllJobs = $stmt2->fetchColumn();
        $this->lastDebug['queryies'][] = compact('sql2', 'maxRevIdOfAllJobs', 'line');
        $this->lastDebug['queryies']['lines'][] = $line;

        if ($maxRevIdByJobId === $maxRevIdOfAllJobs) {
            $this->lastDebug['queryies']['lines'][] = __LINE__;
            return $maxRevIdByJobId;
        }

        $line = __LINE__;
        $called = 'maxRevisionIdByJobidTable';
        $params = [ 'jobid'=> $jobid, 'table' => 'ObjektKatalogGlobal'];
        $maxRevIdOKGByJobid = $this->maxRevisionIdByJobidTable($jobid, 'ObjektKatalogGlobal' );
        $this->lastDebug['queryies'][] = compact('maxRevIdOKGByJobid', 'jobid', 'called', 'params');

        return max($maxRevIdByJobId, $maxRevIdOKGByJobid);
    }

    public function maxRevisionIdByJobidTable(int $jobid, string $table) {
        if ($table !== 'ObjektKatalogGlobal') {
            $db = \DB::getPdo();
            $stmt = $db->query(
                'SELECT MAX(revision_id) FROM ' . $this->table
                . ' WHERE jobid = ' . $jobid
                . ' AND `table` = ' . $db->quote($table)
            );
        } else {
            $db = \DB::getPdo();
            $stmt = $db->query(
                'SELECT MAX(revision_id) '
                . ' FROM ' . $this->table . ' r '
                . ' JOIN ObjektKatalogGlobal g ON (r.uuid = g.uuid) '
                . ' JOIN ObjektKatalogMandant m ON (g.uuid = m.gcuuid) '
                . ' WHERE `table` = ' . $db->quote($table)
                . ' AND m.for_jobid = ' . (int)$jobid
            );
        }
        return $stmt->fetchColumn();
    }


    public function getChangeLogs(int $jobid, int $devid, int $offsetRevId, int $maxChunkSize = 1024000): ServerChangeLogData {
        $re = new ServerChangeLogData();

        $db = \DB::getPdo();
        $sql =
//            'WITH OKG AS ('
//            . 'SELECT g.uuid '
//            . ' FROM ' . $this->table . ' r '
//            . ' JOIN ObjektKatalogGlobal g ON ( r.uuid = g.uuid ) '
//            . ' JOIN ObjektKatalogMandant m ON ( m.for_jobid = ' . $jobid . ' AND g.uuid = m.gcuuid ) '
//            . ' WHERE r.table = ' . $db->quote('ObjektKatalogGlobal') . ' AND revision_id > ' . $offsetRevId
//            . ' ORDER BY g.uuid '
//            . ') '
            ' SELECT r.revision_id, r.`type`, r.`table` , r.`key`, r.id, r.uuid, r.obj, r.mods '
            . ' FROM ' . $this->table . ' r '
            . ' LEFT JOIN ('
            . '     SELECT g.uuid '
            . '     FROM ' . $this->table . ' r '
            . '     JOIN ObjektKatalogGlobal g ON ( r.uuid = g.uuid ) '
            . '     JOIN ObjektKatalogMandant m ON ( m.for_jobid = ' . $jobid . ' AND g.uuid = m.gcuuid ) '
            . '     WHERE r.table = ' . $db->quote('ObjektKatalogGlobal') . ' AND revision_id > ' . $offsetRevId
            . '     ORDER BY g.uuid '
            . ') AS k ON (r.table = "ObjektkatalogGlobal" AND r.uuid = k.uuid) '
            . ' WHERE '
            . ' revision_id > ' . $offsetRevId
            . ' AND created_devid != ' . $devid
            . ' AND '
            . ' ('
            . '    jobid = ' . $jobid
            . '    OR r.uuid = k.uuid '
            . ' )'
            . ' ORDER BY revision_id';
        $this->lastQuery = $sql;

        $stmtCount = $db->query('SELECT COUNT(1) FROM (' . $sql . ') AS JobChanges');
        $re->total = (int)$stmtCount->fetchColumn();
        $re->rows = [];
        $re->chunkSize = 0;
        $re->maxChunkSize = $maxChunkSize;

        $stmt = $db->query( $sql, \PDO::FETCH_ASSOC );
        $jsonColnamesLength = 0;

        while( $row = $stmt->fetch(\PDO::FETCH_ASSOC) ) {

            if ($re->chunkSize >= $maxChunkSize) {
                break;
            }

            if (0 === $jsonColnamesLength) {
                $jsonColnamesLength = strlen( '"' . implode('": "', array_keys($row)) . '": ');
            }

            $rowSize = $jsonColnamesLength;
            foreach($row as $v) {
                if (is_null($v)) {
                    $rowSize+= 6;
                }
                elseif ($v === '') {
                    $rowSize+= 4;
                }
                elseif (is_int($v) || is_float($v) ) {
                    $rowSize+= 2 + strlen( (string)$v);
                }
                else {
                    $rowSize+= 3 + strlen( (string)$v);
                }
            }

            $offset = count($re->rows);
            $revId = $row['revision_id'];

            if ($offset === 0) {
                $re->firstRevId = $revId;
            }

            if ($offset === 0 || ($re->chunkSize + $rowSize) <= $maxChunkSize) {
                $re->rows[] = $row;
                $re->lastRevId = $revId;
                $re->chunkSize+= $rowSize;
            }
        }

        return $re;
    }

    public function getJobChangesInfos(int $jobid, int $offsetRevId, int $devid = 0) {
        $db = \DB::getPdo();

        $query =
//            'WITH OKG AS ('
//            . 'SELECT g.uuid '
//            . ' FROM ' . $this->table . ' r '
//            . ' JOIN ObjektKatalogGlobal g ON ( r.uuid = g.uuid ) '
//            . ' JOIN ObjektKatalogMandant m ON ( m.for_jobid = 2 AND g.uuid = m.gcuuid ) '
//            . ' WHERE r.table = ' . $db->quote('ObjektKatalogGlobal') . ' AND revision_id > ' . $offsetRevId
//            . ' ORDER BY g.uuid '
//            . ') '
            'SELECT COUNT(1) '
            . ' FROM ' . $this->table . ' r '
            . ' LEFT JOIN ('
            . '     SELECT g.uuid '
            . '     FROM ' . $this->table . ' r '
            . '     JOIN ObjektKatalogGlobal g ON ( r.uuid = g.uuid ) '
            . '     JOIN ObjektKatalogMandant m ON ( m.for_jobid = ' . $jobid . ' AND g.uuid = m.gcuuid ) '
            . '     WHERE r.table = ' . $db->quote('ObjektKatalogGlobal') . ' AND revision_id > ' . $offsetRevId
            . '     ORDER BY g.uuid '
            . ') AS k ON (r.table = "ObjektkatalogGlobal" AND r.uuid = k.uuid) '
            . ' WHERE '
            . ' revision_id > ' . $offsetRevId
            . ( $devid ? ' AND created_devid != ' . $devid : '' )
            . ' AND '
            . ' ('
            . '    jobid = ' . $jobid
            . '    OR r.uuid = k.uuid '
            . ' )';

        $stmt = $db->query( $query );

        return [
            'success' => true,
            'NumChanges' => $stmt->fetchColumn(),
            'MaxRevisionId' => $this->maxRevisionIdByJobid( $jobid ),
            'debug' => $this->lastDebug,
            'query' => $query,
        ];
    }

}
