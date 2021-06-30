<?php
/**
 * Created by PhpStorm.
 * User: f.barthold
 * Date: 04.02.2020
 * Time: 00:31
 */

namespace App;

/**
 * ClientChangeLog
 *
 * @package App
 *
 * @package App
 * @mixin Eloquent
 * @mixin Illuminate\Database\Eloquent\Model
 * @mixin Illuminate\Database\Eloquent\Builder
 */
class ClientChangeLog extends BaseModel
{

    protected $table = 'ClientChangeLog';
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id_origin', 'timestamp', 'table', 'type', 'key', 'obj', 'mods', 'uuid', 'uid', 'jobid', 'devid',
    ];

    protected $attributes = [

    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /*
     * By default, Eloquent expects created_at and updated_at columns to exist on your tables.
     * If you do not wish to have these columns automatically managed by Eloquent,
     * set the $timestamps property on your model to false:
     */

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    public function store() {
        $this->getKey();
    }

    public function importChangeLogs(array &$changes, int $overwriteDevId = -1): int {
        $db = $this->db;
        $iImports = 0;
        $sql = 'SELECT * FROM ' . $this->table
            . ' WHERE  uuid = :uuid '
            . ' AND `timestamp` = :timetamp ';
        foreach($changes as &$item) {
            $change = $item;
            $change['id_origin'] = $item['id'];
            if ($overwriteDevId !== -1) {
                $change['devid'] = $overwriteDevId;
            }
            unset($change['id']);
            if (isset($change['mods']) && is_array($change['mods'])) {
                $change['mods'] = json_encode($change['mods']);
            }
            if (isset($change['obj']) && is_array($change['obj'])) {
                $change['obj'] = json_encode($change['obj']);
            }
            if (isset($change['oldObj']) && is_array($change['oldObj'])) {
                $change['oldObj'] = json_encode($change['oldObj']);
            }
            $change['timestamp'] = date('Y-m-d H:i:s', strtotime($change['timestamp']));

            $dbg = __LINE__;
            $changed = null;
            try {
                $dbg.= ',' . __LINE__;
                if (is_array($change['key'])) {
                    $change['key'] = current($change['key']);
                }
                if (!is_numeric($change['key'])) {
                    $change['key'] = -1;
                }
                $changed = self::create($change);
                $dbg.= ',' . __LINE__;
                $logId = $changed->id;
                $dbg.= ',' . __LINE__;
                $item['logId'] = $logId;
                $dbg.= ',' . __LINE__;
                $iImports++;
            } catch(\Exception $e) {
                throw new \Exception(json_encode([
                    'exception' => $e->getMessage(),
                    'addedMessage' => 'Could not save change',
                    'dbg' => $dbg,
                    'item' => $item,
                    'item[id]'=> $item['id'],
                    'change' => $change,
                    'changed' => $changed,
                    'change[id_origin]' => $change['id_origin']
                    ],JSON_PRETTY_PRINT));
            }
        }
        return $iImports;
    }

    public function synced($syncLogId, array $remods = [], string $error = '') {
        $changeLog = self::find($syncLogId);
        $update = [
            'synced' => 1
        ];
        if ($remods) {
            $update['remods'] = json_encode($remods);
        }
        if ($error) {
            $update['error'] = $error;
            $update['synced'] = 2;
        }
        $changeLog->save();
        \DB::table($this->table )->where( 'id', '=', $syncLogId)->update($update);
    }

    public function numMalformedInvBarcodes(int $iZeitraumMinuten = 0, int $iMinId = 0, int $iMaxId = 0) {
        $db = $this->db;
        if (!$iZeitraumMinuten) {
            $iZeitraumMinuten = 60;
        }

        $sql = 'SELECT COUNT(1) FROM ClientChangeLog cl '
            . ' WHERE `table` = "inventar" AND `type` = 1 ';
            if ($iMinId && $iMaxId) {
                $sql.= ' AND id BETWEEN ' . (int)$iMinId . ' AND ' . $iMaxId . ' ';
            } elseif($iMinId) {
                $sql.= ' AND id > ' . (int)$iMinId . ' ';
            } elseif($iMaxId) {
                $sql.= ' AND id < ' . (int)$iMaxId . ' ';
            }
        $sql.= ' AND TIMESTAMPDIFF(MINUTE, cl.created_at, NOW()) < ' . $iZeitraumMinuten
        . ' AND JSON_UNQUOTE(JSON_EXTRACT(obj, "$.code")) NOT RLIKE \'[1-9][0-9]{9}\'';

        echo $sql . ";\n";
        $sthLogs = $db->query( $sql );
        $iNumMalformedBarcodes = $sthLogs->fetchColumn();
        // echo 'RESULT ' . $iNumMalformedBarcodes . "\n";
        return $iNumMalformedBarcodes;
    }

    public function findMalformedInvBarcodes(int $iZeitraumMinuten = 0, int $iMinId = 0, int $iMaxId = 0, int $limit = 0) {
        $db = $this->db;
        if (!$iZeitraumMinuten) {
            $iZeitraumMinuten = 60;
        }
        if (!$limit) {
            $limit = 100;
        }

        $sql = 'SELECT JSON_UNQUOTE(JSON_EXTRACT(obj, "$.code")) as code, cl.* FROM ClientChangeLog cl '
             . ' WHERE `table` = "inventar" AND `type` = 1 '
             . ' AND IFNULL(error, "") = "" '
             . ' AND id BETWEEN ' . (int)$iMinId . ' AND ' . $iMaxId . ' '
             . ' AND TIMESTAMPDIFF(MINUTE, cl.created_at, NOW()) < ' . $iZeitraumMinuten . ' '
             . ' AND JSON_UNQUOTE(JSON_EXTRACT(obj, "$.code")) NOT RLIKE \'[1-9][0-9]{9}\' '
             . ' ORDER BY id '
             . ' LIMIT ' . $limit
        ;
        echo $sql . ";\n";
        $sthLogs = $db->query( $sql );
        return $sthLogs->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getMaxLogId()
    {
        $db = $this->db;
        $sql = 'SELECT MAX(id) id FROM ClientChangeLog';
        $sthMaxId = $db->query($sql);
        return (int)$sthMaxId->fetchColumn();
    }
}
