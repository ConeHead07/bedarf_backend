<?php

namespace App;

use App\Traits\DbHashUtil;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Inventar
 *
 * @package App
 * @mixin Eloquent
 * @mixin Illuminate\Database\Eloquent\Model
 * @mixin Illuminate\Database\Eloquent\Builder
 */
class Inventar extends BaseModel
{
    use DbHashUtil,
        HasCreatedModUid;

    protected $table = 'Inventar';
    protected $primaryKey = 'ivid';
    protected static $_lastQuery = '';

    protected $fillable = [
        'uuid',
        'mcuuid',
        'mcid',
        'code',
        'rid',
        'ruuid',
        'rid_init',
        'rid_neu',
        'ruuid_init',
        'KST',
        'Anlagennr',
        'Zustand',
        'Seriennr',
        'Geraetnr',
        'Fibunr',
        'Betrag',
        'Baujahr',
        'Datum',
        'jobid',
        'invid',
        'iv_nr',
        'ErsteAufnahmeAm',
        'LetzteAufnahmeAm',
    ];

    protected $rules = [
        'ErsteAufnahmeAm' => 'date',
        'LetzteAufnahmeAm' => 'date',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setNoHashFields([
            'ivid', 'invid', 'uuid', 'hash', 'code', 'raumid', 'jobid',
            'ErsteAufnahme', 'LetzteAufnahmeAm',
            'created_uid', 'created_at', 'modified_uid', 'modified_at'
        ]);
    }

    public function findByGebaeudeId(int $gid) {
        \DB::enableQueryLog();
        $result = $this
            ->select( $this->table . '.*')
            ->join( 'Raeume', $this->table . '.rid', '=', 'Raeume.rid'   )
            ->where( 'Raeume.gid', $gid)
            ->get();

        self::$_lastQuery = \DB::getQueryLog();
        \DB::disableQueryLog();
        return $result;
    }

    public function findByJobid(int $jobid, int $offset = null, int $maxResultSize = null) {
        if (is_null($offset)) {
            $offset = 0;
        }
        if ($maxResultSize === 0 || is_null($maxResultSize)) {
            $maxResultSize = 2 * 1024 * 1024;
        }

        $pdo = \DB::connection()->getPdo();

        $re = (object)[
            'rowsCount' => 0,
            'rowsSize' => 0,
            'offset' => $offset,
            'total' => 0,
            'totalImgSize' => 0,
            'maxResultSize' => $maxResultSize,
            'query' => '',
            'jobid' => $jobid,
            'rows' => [],
        ];

        $sqlCount = 'SELECT COUNT(1) '
            . ' FROM InventurenGebaeude ig '
            . ' JOIN Raeume r ON (ig.gid = r.gid) '
            . ' JOIN Inventar i ON (r.rid = i.rid) '
            .' WHERE ig.jobid = :jobid AND i.for_jobid = :jobid2';

        $sqlRows = 'SELECT i.* '
            . ' FROM InventurenGebaeude ig '
            . ' JOIN Raeume r ON (ig.gid = r.gid) '
            . ' JOIN Inventar i ON (r.rid = i.rid) '
            .' WHERE ig.jobid = :jobid AND i.for_jobid = :jobid2'
            . ' LIMIT :offset, :total';

        $stmtCount = $pdo->prepare($sqlCount);
        $stmtCount->execute([
            'jobid' => $jobid,
            'jobid2' => $jobid
        ]);
        $re->total = $stmtCount->fetchColumn();

        $stmtRows = $pdo->prepare( $sqlRows );
        $stmtRows->execute([
            'jobid' => $jobid,
            'jobid2' => $jobid,
            'offset' => $offset,
            'total' =>  $re->total
        ]);
        $re->query = strtr($stmtRows->queryString, array_map(function($v)use($pdo){return $pdo->quote($v);}, [
            ':jobid' => (int)$jobid,
            ':jobid2' => (int)$jobid,
            ':offset' => (int)$offset,
            ':total' =>  (int)$re->total
        ]));

        $re->loops = 0;
        $re->addedRows = 0;

        while($row = $stmtRows->fetch(\PDO::FETCH_ASSOC)) {

            $re->loops++;
            $rowSize = array_reduce($row, function($carry, $val) { return $carry + (int)strlen((string)$val); }, 0 );
            if (count($re->rows) === 0 || ($re->rowsSize + $rowSize) <= $maxResultSize) {
                $re->rows[] = $row;
                $re->addedRows++;
                $re->rowsSize+= $rowSize;
            } else {
                break;
            }
        }
        $re->rowsCount = count($re->rows);

        return $re;
    }

    public static function getLastQuery() {
        return self::$_lastQuery;
    }

    public function getInventurInventar(int $jobid) {
        $re = (object)[
            'total' => 0,
            'rows' => [],
        ];

        $pdo = \DB::connection()->getPdo();
        $sql = 'SELECT  '
            . '  i.ivid, i.code, i.mcid, i.rid, i.rid_neu, i.rid_init,'
            . '  h.Hersteller, mk.gcid, gk.hid, gk.Bezeichnung, gk.Produktnr, gk.Typ, gk.Gruppe,'
            . '  gk.Kategorie, gk.Farbe, gk.Groesse, '
            . '  i.jobid, IF (IFNULL(i.jobid, 0) > 0, "Ja", "N") AS Scan, '
            . '  i.KST, i.Anlagennr, i.Datum, i.Zustand, '
            . '  i.created_at, i.modified_at, i.created_uid, i.modified_uid, '
            . '  i.created_device_id, i.modified_device_id, i.created_jobid, '
            . '  r.gid, r.Etage, r.Raum, r.Raumbezeichnung, '
            . '  creator.name AS created_by, modifier.name AS modified_by, '
            . '  IF(img.mcuuid, "Ja", "") AS hatImg '
            . ' FROM ObjektKatalogMandant mk '
            . ' JOIN Inventar i ON (i.mcid = mk.mcid) '
            . ' JOIN ObjektKatalogGlobal gk ON (mk.gcid = gk.gcid) '
            . ' Left JOIN Hersteller h ON (gk.hid = h.hid) '
            . ' JOIN Raeume r ON (i.rid = r.rid) '
            . ' LEFT JOIN users AS creator ON (i.created_uid = creator.id) '
            . ' LEFT JOIN users AS modifier ON (i.modified_uid = modifier.id) '
            . ' LEFT JOIN Images img ON (i.mcuuid = img.mcuuid)'
            . ' WHERE i.for_jobid = :jobid';

        // throw new \Exception( str_replace(':jobid', 1, $sql) );
        $aParams = [
            'jobid' => $jobid,
        ];
        $sqlCount = 'SELECT COUNT(1) FROM (' . $sql . ') AS CountTable';
        $stmtCnt = $pdo->prepare($sqlCount);
        $stmtCnt->execute( $aParams );
        $cntRow = $stmtCnt->fetch(\PDO::FETCH_NUM);
        $re->total = $cntRow[0];

        $stmt = $pdo->prepare(
            $sql
        );

        $stmt->execute( $aParams );

        $re->cols = [];
        $numCols = $stmt->columnCount();
        for($i = 0; $i < $numCols; ++$i) {
            $meta = $stmt->getColumnMeta($i);
            $re->cols[] = $meta['name'];
        }
        $re->rows = $stmt->fetchAll(\PDO::FETCH_NUM);

        return $re;
    }

    public function getInventurInventarItem(int $ivid) {

        $pdo = \DB::connection()->getPdo();
        $sql = 'SELECT  '
            . '  i.ivid, i.mcid, i.rid, i.rid_neu, i.rid_init,'
            . '  h.Hersteller, mk.gcid, gk.hid, gk.Bezeichnung, gk.Produktnr, gk.Typ, gk.Gruppe,'
            . '  gk.Kategorie, gk.Farbe, gk.Groesse, '
            . '  i.KST, i.Anlagennr, i.Datum, i.Zustand, '
            . '  i.created_at, i.modified_at, i.created_uid, i.modified_uid, '
            . '  i.created_device_id, i.modified_device_id, i.created_jobid, '
            . '  r.gid, r.Etage, r.Raum, r.Raumbezeichnung '
            . ' FROM ObjektKatalogMandant mk '
            . ' JOIN Inventar i ON (i.mcid = mk.mcid) '
            . ' JOIN ObjektKatalogGlobal gk ON (mk.gcid = gk.gcid) '
            . ' Left JOIN Hersteller h ON (gk.hid = h.hid) '
            . ' JOIN Raeume r ON (i.rid = r.rid) '
            . ' WHERE i.ivid = :ivid';

        $aParams = [ 'ivid' => $ivid];
        $stmt = $pdo->prepare(
            $sql
        );

        $stmt->execute( $aParams );

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function updateInventar(int $id, array $data) {
        $inv = Inventar::find($id);
        return $inv->update($data);
    }

    public function inventarBarcodes(int $jobid) {

        $re = (object)[ 'success' => true, 'cols' => [], 'rows', [], 'query' => null ];
        $re->query = $this->getInventarBarcodesQuery($jobid);
        // return $re;
        $re->query->limit(0, 10000);

        $sth = $re->query->query();
        $numCols = $sth->columnCount();
        for($i = 0; $i < $numCols; $i++) {
            $col = $sth->getColumnMeta($i);
            $re->cols[] = $col['name'];
        }
        $re->rows = $sth->fetchAll(\PDO::FETCH_NUM);

        return $re;
    }

    public function inventarExtraBarcodes(int $jobid, array $aKategorien = []) {

        if (empty($aKategorien)) {
            $aKategorien = [ 'Kunst'];
        }

        $re = (object)[ 'success' => true, 'cols' => [], 'rows', [], 'query' => null ];
        $this->setKunstInventarBarcodesIfEmpty($jobid, $aKategorien);
        $re->query = $this->getInventarBarcodesQueryByKategorie($jobid, $aKategorien);
        // return $re;
        $re->query->limit(0, 10000);

        try {
            $query = $re->query->asSql();
            $sth = $re->query->query();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage() . "\n" . $query);
        }
        $numCols = $sth->columnCount();
        for($i = 0; $i < $numCols; $i++) {
            $col = $sth->getColumnMeta($i);
            $re->cols[] = $col['name'];
        }
        $re->rows = $sth->fetchAll(\PDO::FETCH_NUM);

        return $re;
    }

    public function setKunstInventarBarcodesIfEmpty(int $jobid, array $aKategorien = ['Kunst'], int $incBarcodeOffset = 9000000001) {
        $pdo = \DB::connection()->getPdo();
        if (empty($aKategorien)) {
            $aKategorien = ['Kunst'];
        }
        $qInKategorien = implode(', ', array_map(function($k) use ($pdo) { return $pdo->quote($k); }, $aKategorien));

        $precondition = 'SELECT count(1) FROM Inventar i '
            . ' JOIN ObjektKatalogMandant okm ON (i.mcid = okm.mcid)'
            . ' JOIN ObjektKatalogGlobal okg ON (okm.gcid = okg.gcid)'
            . ' WHERE i.for_jobid = ' . $jobid . ' AND (i.code = "" OR i.code IS NULL) '
            . ' AND okg.Kategorie IN (' . $qInKategorien . ')';
        $sth = $pdo->query($precondition);
        if ( (int)$sth->fetchColumn(0) === 0) {
            // throw new \Exception( json_encode($precondition) );
            return;
        }

        $aSQL = [
            'DROP TEMPORARY TABLE IF EXISTS IncrementBarcodes',
            'CREATE TEMPORARY TABLE IF NOT EXISTS IncrementBarcodes (
                code BIGINT(11) NOT NULL AUTO_INCREMENT,
                ivid BIGINT(11) NOT NULL,
                PRIMARY KEY (code),
                UNIQUE INDEX ivid (ivid)
            )
            AUTO_INCREMENT=' . $incBarcodeOffset,

            'INSERT IGNORE INTO IncrementBarcodes (code, ivid)
            SELECT icode, ivid FROM (
                        SELECT ivid, CAST(i.code AS SIGNED) AS icode, i.code, i.for_jobid
                FROM Inventar i
                JOIN ObjektKatalogMandant okm ON (i.mcuuid = okm.uuid)
                JOIN ObjektKatalogGlobal okg ON (okm.gcuuid = okg.uuid) 
                WHERE
                    i.for_jobid = ' . $jobid . '
                    AND i.code IS NOT NULL
                    AND i.code != ""
                    AND CAST(i.code AS SIGNED) != 0
                    AND okg.Kategorie IN (' . $qInKategorien . ')
            ) AS KunstInventar ORDER BY IF(icode is null , 1, 0), icode',

            'INSERT IGNORE INTO IncrementBarcodes (ivid)
            SELECT ivid FROM (
                        SELECT ivid, i.code, i.for_jobid
                FROM Inventar i
                JOIN ObjektKatalogMandant okm ON (i.mcuuid = okm.uuid)
                JOIN ObjektKatalogGlobal okg ON (okm.gcuuid = okg.uuid) 
                WHERE i.for_jobid = ' . $jobid . ' AND (i.code IS NULL OR i.code = "")  AND okg.Kategorie IN (' . $qInKategorien . ')
            ) AS KunstInventar ORDER BY ivid',

            'SELECT * FROM IncrementBarcodes ORDER BY ivid',

            'UPDATE Inventar i 
            JOIN IncrementBarcodes b USING(ivid) 
            SET i.code = CAST(b.code AS CHAR)
            WHERE i.for_jobid = ' . $jobid . ' AND i.code = "" OR i.code IS NULL'
        ];
        $iNumQueries = count($aSQL);

        $aAffected = [];
        for($i = 0; $i < $iNumQueries; $i++) {
            try {
                $sth = $pdo->query($aSQL[$i]);
                $aAffected[$i] = is_object($sth) ? $sth->rowCount() : null;
            } catch (\Exception $e) {
                throw new \Exception(
                    'Error in aSQL[' . $i . '] ' . $e->getMessage() . "<br>\n" . json_encode($aSQL) );
            }
        }

        // throw new \Exception( json_encode( compact('aSQL', 'aAffected')) );
    }

    public function getInventarBarcodesQueryByKategorie(int $jobid, array $aKategorien = []): \App\SqlQuery {
        $pdo = \DB::connection()->getPdo();

        if (empty($aKategorien) || 0 === count($aKategorien)) {
            return '';
        }
        if (count($aKategorien)) {
            $aQKategorien = array_map(function($k) use($pdo) { return $pdo->quote($k); }, $aKategorien);
            $qInKategorien = implode(', ', $aQKategorien);
        }
        $query = $this->getQuery();
        $query
            ->select([
                'i.ivid',
                'g.Gebaeude',
                'r.rid',
                'r.code AS raumBarcode',
                'r.Raum',
                'r.Raumbezeichnung',
                'r.Etage',
                'CONCAT_WS(":", g.Gebaeude, r.Etage, r.Raum, r.Raumbezeichnung) RaumText',
                'CONCAT_WS(":", okg.Bezeichnung, okg.Gruppe, okg.Kategorie, okg.Typ, okg.Farbe, okg.Groesse) ArtikelText',
                'i.code AS inventarBarcode',
                'okm.code artikelBarcode',
                'okg.Bezeichnung',
                'okg.Gruppe',
                'okg.Kategorie',
                'okg.Typ',
                'okg.Farbe',
                'okg.Groesse'
            ])
            ->from('Inventar i')
            ->join([
                'JOIN ObjektKatalogMandant okm ON (i.mcid = okm.mcid)',
                'JOIN ObjektKatalogGlobal okg ON (okm.gcid = okg.gcid)',
                'LEFT JOIN Raeume r ON (i.rid = r.rid)',
                'LEFT JOIN Gebaeude g ON (r.gid = g.gid)',
            ])
            ->where([ 'i.for_jobid = ' . $jobid, 'i.code !="" AND i.code IS NOT NULL', 'okg.Kategorie IN (' . $qInKategorien . ')' ])
            ->order(['r.gid, r.Raum'])
            ->limit(500);
        return $query;
    }

    public function getInventarBarcodesQuery(int $jobid): \App\SqlQuery {
        $query = $this->getQuery();
        $query
            ->select([
                'i.ivid',
                'g.Gebaeude',
                'r.rid',
                'r.code AS raumBarcode',
                'r.Raum',
                'r.Raumbezeichnung',
                'r.Etage',
                'CONCAT_WS(":", g.Gebaeude, r.Etage, r.Raum, r.Raumbezeichnung) RaumText',
                'CONCAT_WS(":", okg.Bezeichnung, okg.Gruppe, okg.Kategorie, okg.Typ, okg.Farbe, okg.Groesse) ArtikelText',
                'i.code AS inventarBarcode',
                'okm.code artikelBarcode',
                'okg.Bezeichnung',
                'okg.Gruppe',
                'okg.Kategorie',
                'okg.Typ',
                'okg.Farbe',
                'okg.Groesse'
            ])
            ->from('InventurenGebaeude ig')
            ->join([
                'JOIN Gebaeude g ON (ig.gid = g.gid)',
                'JOIN Raeume r ON (ig.gid = r.gid AND ig.jobid = r.for_jobid)',
                'JOIN Inventar i ON (r.rid = i.rid AND r.for_jobid = i.for_jobid)',
                'JOIN ObjektKatalogMandant okm ON (i.mcid = okm.mcid)',
                'JOIN ObjektKatalogGlobal okg ON (okm.gcid = okg.gcid)'
            ])
            ->where([ 'ig.jobid = ' . $jobid ])
            ->order(['r.gid, r.Raum'])
            ->limit(500);
        return $query;
    }
}
