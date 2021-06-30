<?php
/**
 * Created by PhpStorm.
 * User: f.barthold
 * Date: 28.08.2020
 * Time: 14:54
 */

namespace App\Http\Controllers\Admin;

use App\BaseModel;
use App\Gebaeude;
use App\Hersteller;
use App\Images;
use App\Inventuren;
use App\InventurenUser;
use App\Mandanten;
use App\ObjektKatalogGlobal;
use App\ObjektKatalogImages;
use App\ObjektKatalogMandant;
use App\Raeume;
use App\Inventar;
use App\User;
use Illuminate\Http\Request;

function fileSizeReadable(int $fsize, $unit='auto', $withUnit = true) {
    if (!$unit) {
        $unit = 'auto';
    }
    if ( ($fsize < 1024 && $unit === 'auto') || in_array($unit, ['Bytes', 'B'])) {
        return $fsize . ($withUnit ? 'B' : '');
    } else if (($fsize < (1024 * 1024) && $unit === 'auto') || $unit === 'KB') {
        return str_replace('.', ',', round($fsize / 1024, 3)) . ($withUnit ? 'KB' : '');
    }
    return str_replace('.', ',', round($fsize / (1024 * 1024), 3)) . ($withUnit ? 'MB' : '');
}


class InventurenController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        // $this->getAuthUser();
    }

    public function index(Request $request)
    {
        $inventurenModel = new Inventuren();

        $tplVars = new \stdClass();

        $tplVars->aRows = $inventurenModel->getAllInventuren();

        return view('admin.inventuren.index', compact('tplVars') );
    }

    public function get(Request $request, $jobid) {
        $tplVars = new \stdClass();
        $mandantenModel = new Mandanten();
        $userModel = new User();
        $inventurUserModel = new InventurenUser();

        $tplVars->jobid = $jobid;
        $tplVars->aMandanten = $mandantenModel->getList();
        $tplVars->aUsers = $userModel->getList();
        $tplVars->aInvUsers = $inventurUserModel->getList((int)$jobid);
        $tplVars->authUser = $this->getAuthUser()->toArray();

        $inv = Inventuren::where('jobid', $jobid)->first();
        $tplVars->inventur = $inv->toArray();
        $tplVars->inventurCreatedBy = $inv->createdBy;
        // return response()->json($tplVars);

        return view('admin.inventuren.get', compact('tplVars') );
    }

    public function setUser(Request $request, int $jobid) {
        $aUids = $request->post('mitarbeiter', []);
        $aIntIds = array_map(function($id){ return (int)$id;}, $aUids);


        $inventurUserModel = new InventurenUser();
        $inventurUserModel->setUsers($jobid, $aIntIds);

        return response()->json([ 'success' => false ]);
    }

    public function setJobUsers(Request $request) {
        $jobid = $request->post('jobid', 0);
        $aUids = $request->post('mitarbeiter', []);
        $aIntIds = array_map(function($id){ return (int)$id;}, $aUids);

        $inventurUserModel = new InventurenUser();
        $result = $inventurUserModel->setUsers($jobid, $aIntIds);

        return response()->json($result);
    }

    public function addUser(Request $request, int $uid) {
        return response()->json([ 'success' => false ]);
    }

    public function removeUser(Request $request, int $uid) {
        return response()->json([ 'success' => false ]);
    }

    public function getGebaeudeList(Request $request, int $jobid) {
        $gebaeude = new Gebaeude();
        $rows = $gebaeude->getGebaeudeListByJobId($jobid);
        $re = new \stdClass();
        $re->success = true;
        $re->total = count($rows);
        $re->rows = $rows;
        return response()->json( $re );
    }

    public function getHerstellerList(Request $request, int $jobid) {
        $hstModel = new Hersteller();
        $rows = $hstModel->getHerstellerListByJobId($jobid);
        $re = new \stdClass();
        $re->success = true;
        $re->total = count($rows);
        $re->rows = $rows;
        return response()->json( $re );
    }

    public function getHerstellerArtikelList(Request $request, $id, string $hst) {
        $hstModel = new Hersteller();
        $hst = rawurldecode( $hst );
        $rows = $hstModel->getHerstellerArtikelListByJobId( (int)$id, $hst);
        $lastQuery = $hstModel::getLastQuery();
        $re = new \stdClass();
        $re->success = true;
        $re->total = count($rows);
        $re->rows = $rows;

        $re->lastQuery = $lastQuery;
        $re->jobid = $id;
        $re->hst = $hst;
        return response()->json( $re );
    }

    public function getEtagenGroupedByGid(Request $request, int $jobid) {
        $raeume = new Raeume();
        $aEtagenByGid = $raeume->getInventurEtagenGroupedByGid($jobid);
        $re = new \stdClass();
        $re->success = true;
        $re->rows = $aEtagenByGid;
        return response()->json( $re );
    }

    public function getRaeume(Request $request, int $jobid) {
        $raeume = new Raeume();
        $re = $raeume->getInventurRaeume($jobid);
        $re->success = true;
        return response()->json( $re );
    }

    public function getRaeumeSelect(Request $request, int $jobid) {
        $raeume = new Raeume();
        $re = $raeume->getInventurRaeumeSelect($jobid);
        $re->success = true;
        return response()->json( $re );
    }

    public function getArtikel(Request $request, int $jobid) {
        $katalog = new ObjektKatalogMandant();
        $re = $katalog->getInventurArtikel($jobid);
        $re->success = true;
        return response()->json( $re );
    }

    public function getArtikelSelect(Request $request, int $jobid) {
        $katalog = new ObjektKatalogMandant();
        $re = $katalog->getInventurArtikelSelect($jobid);
        $re->success = true;
        return response()->json( $re );
    }

    public function getInventar(Request $request, int $jobid) {
        $inventar = new Inventar();
        $re = $inventar->getInventurInventar($jobid);
        $re->success = true;
        return response()->json( $re );
    }

    public function getKunstSettings(int $jobid) {
        $inventurenModel = new Inventuren();
        $settings = $inventurenModel->getKunstSettings($jobid);

        return response()->json([
            'success' => true,
            'settings' => $settings,
        ]);
    }

    public function setKunstSettings(Request $request, int $jobid) {
        $inventurenModel = new Inventuren();
        $data = [
            'EnthaeltKunst' => $request->input('EnthaeltKunst', 0),
            'KunstKategorien' => $request->input('KunstKategorien', []),
        ];

        $inventarModel = new Inventar();

        try {
            $success = $inventurenModel->setKunstSettings($jobid, $data);
            $settings = $inventurenModel->getKunstSettings($jobid);
            if ($settings['EnthaeltKunst'] && count($settings['KunstKategorien']) > 0) {
                $inventarModel->setKunstInventarBarcodesIfEmpty($jobid, $settings['KunstKategorien']);
            }

            $message = $success ? '' : $inventurenModel->lastError;
        } catch(\Exception $e) {
            $success = false;
            $message = $e->getMessage();
        }

        return response()->json(compact('success', 'message'));
    }

    public function getKategorien(int $jobid) {
        $katalogModel = new ObjektKatalogMandant();
        $aKategorien = $katalogModel->getKategorien($jobid);

        return response()->json([
            'success' => true,
            'kategorien' => $aKategorien
        ]);
    }

    public function fileSizeReadable(int $fsize, $unit='auto', $withUnit = true) {
        if (!$unit) {
            $unit = 'auto';
        }
        if ( ($fsize < 1024 && $unit === 'auto') || in_array($unit, ['Bytes', 'B'])) {
            return $fsize . ($withUnit ? 'B' : '');
        } else if (($fsize < (1024 * 1024) && $unit === 'auto') || $unit === 'KB') {
            return str_replace('.', ',', round($fsize / 1024, 1)) . ($withUnit ? 'KB' : '');
        }
        return str_replace('.', ',', round($fsize / (1024 * 1024), 1)) . ($withUnit ? 'MB' : '');
    }

    public function importClientChangeLogs(int $jobid) {
        global $lastChgId;
        global $aMemLog;
        global $iLoop;

        $iMemoryUsage = memory_get_usage();
        $sMemoryLimit = ini_get('memory_limit');
        $iMemoryLimit = ((int)$sMemoryLimit) * 1024 * 1024;
        $rMemoryLimit = $this->fileSizeReadable($iMemoryLimit);
        $iMemoryAlert = 1024 * 1024 * 5;

        if (0) return response()->json(compact(
            'sMemoryLimit',
            'iMemoryLimit',
            'rMemoryLimit',
            'iMemoryUsage',
            'iMemoryAlert'
        ));

        $iMemoryUsageBefore = $iMemoryLimit;
        $aMemLog = [];
        $db = \DB::connection()->getPdo();
        // $db->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
        $queries = [];
        $aUnknownTables = [];
        $aProcessedChanges = [];
        $aEmptyChanges = [];
        $aExistingInserts = [];
        $errorFile = '';
        $errorLine = '';
        $errorCode = '';
        $errorMsg = '';
        $errorStack = [];
        $lastQuery = '';
        $lastChgId = 0;
        $lastTable = '';
        $lastType = 0;
        $iLoop = 0;

        $isMemUsageTooHigh = function($line = '') use(&$iMemoryAlert, &$lastChgId, &$lastTable, &$lastType, &$iLoop) {
            $iMemoryUsage = memory_get_usage();
            echo '#' . $line . ' iLoop: ' . $iLoop . ' ' . $lastTable . '(' . $lastType . ') lastChgId: ' . $lastChgId . ' iMemoryUsage: '
                . fileSizeReadable($iMemoryUsage)
                . ', iMemoryAlert: '
                . fileSizeReadable($iMemoryAlert)
                . "<br>\n";

            flush();
            return $iMemoryUsage >= $iMemoryAlert;
        };

        $fMemLog = function($line) use(
            &$iMemoryLimit,
            &$iMemoryUsageBefore,
            &$iMemoryAlert,
            &$aMemLog
        ) {
            while (count($aMemLog) > 50) {
                array_pop($aMemLog);
            }
            $iMemoryUsage = memory_get_usage();
            array_unshift($aMemLog, [
                '#' . $line,
                $this->fileSizeReadable($iMemoryUsage),
                ($iMemoryUsage * 100 / $iMemoryLimit) . '%',
                $this->fileSizeReadable($iMemoryUsage - $iMemoryUsageBefore),
                $this->fileSizeReadable($iMemoryUsageBefore)
            ]);
            $iMemoryUsageBefore = $iMemoryUsage;
            return $iMemoryUsage >= $iMemoryAlert;
        };

        $fMemExit = function($line = '') use(
            &$iLoop,
            &$lastQuery,
            &$lastChgId,
            &$iMemoryLimit,
            &$iMemoryAlert,
            &$aMemLog,
            &$aUnknownTables,
            &$aProcessedChanges,
            &$aEmptyChanges,
            &$aExistingInserts
        ) {
            $iMemoryUsage = memory_get_usage();
            $iMemoryPeakUsage = memory_get_peak_usage();
            $sMemoryUsage = $this->fileSizeReadable($iMemoryUsage);
            $sMemoryPeakUsage = $this->fileSizeReadable($iMemoryPeakUsage);
            $iMemoryRUsage = memory_get_usage(true);
            $iMemoryRPeakUsage = memory_get_peak_usage(true);
            $sMemoryRUsage = $this->fileSizeReadable($iMemoryRUsage);
            $sMemoryRPeakUsage = $this->fileSizeReadable($iMemoryRPeakUsage);

            return compact(
                'line',
                'iLoop',
                'lastQuery',
                'lastChgId',
                'iMemoryAlert',
                'iMemoryLimit',
                'aMemLog',
                'aProcessedChanges',
                'aExistingInserts',
                'aEmptyChanges',
                'aUnknownTables',
                'iMemoryUsage', 'sMemoryUsage', 'iMemoryPeakUsage', 'sMemoryPeakUsage',
                'iMemoryRUsage', 'sMemoryRUsage', 'iMemoryRPeakUsage', 'sMemoryRPeakUsage'
            );
        };

        /** @var BaseModel[]  $models */
        $models = [
            'Hersteller' => new Hersteller(),
            'Images' => new Images(),
            'Inventar' => new Inventar(),
            'Raeume' => new Raeume(),
            'ObjektKatalogGlobal' => new ObjektKatalogGlobal(),
            'ObjektKatalogMandant' => new ObjektKatalogMandant(),
            'ObjektKatalogImages' => new ObjektKatalogImages(),
        ];

        $aTblColumns = [];
        // 2021-05-06 09:18:49
        // BETWEEN '2021-05-05 15:12:00' AND '2021-05-06 10:30:00'
        // BETWEEN '2021-05-06 09:10:00' AND '2021-05-06 10:30:00'
        $sqlOffset = <<<EOT
            SELECT id
            FROM ClientChangeLog cl 
            WHERE cl.timestamp > '2021-05-05 15:12:00' OR cl.id IN(32630,32631)
            ORDER BY cl.id ASC
            LIMIT 1
EOT;

        $sth = $db->query($sqlOffset);
        $_currId = ( (int)$sth->fetchColumn()) - 1;
//        $_currId = 34800;
        try {
            do {
                $sql = <<<EOT
                    SELECT * 
                    FROM ClientChangeLog cl 
                    WHERE
                     cl.id > $_currId AND (cl.timestamp > '2021-05-05 15:12:00' OR cl.id IN(32630,32631))
                    ORDER BY cl.id ASC
                    LIMIT 1
EOT;
                $fMemLog(__LINE__);
                if ($isMemUsageTooHigh(__LINE__)) return $fMemExit(__LINE__);
                $lastQuery = $sql;
                $sthQL = $db->query($sql);
                $row = $sthQL->fetch(\PDO::FETCH_ASSOC);
                if (!$row) {
                    break;
                }
                $_currId = $row['id'];
                ++$iLoop;
                $_chid = $row['id'];
                $_timestamp = $row['timestamp'];
                $_table = ucfirst($row['table']);
                $_uuid = $row['uuid'];
                $_type = (int)$row['type'];
                $_obj = $row['obj'];
                $_mods = $row['mods'];
                $_devid = (int)$row['devid'];
                $_data = json_decode(($_type === 1) ? $_obj : ($_type === 2 ? $_mods : '[]'), JSON_OBJECT_AS_ARRAY);
                $lastTable = $_table;
                $lastChgId = $_chid;
                $lastType = $_type;

                if ($_type === 1 && $_uuid && in_array($_table, [
                        'Hersteller', 'Images','Inventar','ObjektKatalogGlobal', 'ObjektKatalogImages', 'ObjektKatalogMandant'
                    ])
                ) {
                    $_existsSql = 'SELECT uuid FROM ' . $_table . ' WHERE uuid = ' . $db->quote($_uuid) . ' LIMIT 1';
                    $lastQuery = $_existsSql;
                    $_sthExists = $db->query($_existsSql);
                    if ($_sthExists->fetch()) {
                        if (!isset($aExistingInserts[$_table])) {
                            $aExistingInserts[$_table] = 1;
                        } else {
                            $aExistingInserts[$_table]++;
                        }
                        continue;
                    }
                }
                if ($isMemUsageTooHigh(__LINE__)) return $fMemExit(__LINE__);

                if ($_type < 3) {
                    if (!$_data || !is_array($_data) || count($_data) === 0) {
                        if (!isset($aEmptyChanges[$_table])) {
                            $aEmptyChanges[$_table] = 1;
                        } else {
                            $aEmptyChanges[$_table]++;
                        }
                        continue;
                    }
                    if ($models[$_table]) {
                        /** @var BaseModel $_model */
                        $_model = $models[$_table];
                        if (empty($aTblColumns[$_table])) {
                            $aTblColumns[$_table] = $_model->getColumnNames();
                        }
                        $_data = array_intersect_key($_data, $aTblColumns[$_table]);
                    }

                    if ($_table === 'Inventar') {
                        if (!empty($_data['mcuuid'])) {
                            $lastQuery = 'SELECT mcid FROM ObjektKatalogMandant WHERE uuid = ' . $db->quote($_data['mcuuid']);
                            $_sth1 = $db->query($lastQuery);
                            $_rslt = $_sth1->fetchColumn();
                            if ($_rslt) {
                                $_data['mcid'] = $_rslt;
                            }
                        }
                        if (!empty($_data['ruuid'])) {
                            $lastQuery = 'SELECT rid FROM Raeume WHERE uuid = ' . $db->quote($_data['ruuid']);
                            $_sth2 = $db->query($lastQuery);
                            $_rslt = $_sth2->fetchColumn();
                            if ($_rslt) {
                                $_data['rid'] = $_rslt;
                            }
                        }
                    } elseif ($_table === 'ObjektKatalogGlobal') {
                        if (!empty($_data['huuid'])) {
                            $lastQuery = 'SELECT hid FROM Hersteller WHERE uuid = ' . $db->quote($_data['huuid']);
                            $_sth3 = $db->query($lastQuery);
                            $_rslt = $_sth3->fetchColumn();
                            if ($_rslt) {
                                $_data['hid'] = $_rslt;
                            }
                        }
                        $_data = array_filter($_data, function($key){ return !in_array($key, ['lid']); }, ARRAY_FILTER_USE_KEY);
                    } elseif ($_table === 'ObjektKatalogMandant') {
                        if (!empty($_data['gcuuid'])) {
                            $lastQuery = 'SELECT gcid FROM ObjektKatalogGlobal WHERE uuid = ' . $db->quote($_data['gcuuid']);
                            $_sth4 = $db->query($lastQuery);
                            $_rslt = $_sth4->fetchColumn();
                            if ($_rslt) {
                                $_data['gcid'] = $_rslt;
                            }
                        }
                    }
                    if ($isMemUsageTooHigh(__LINE__)) return $fMemExit(__LINE__);
                }

                switch($_type) {
                    case 1:
                        $_data['created_at'] = $row['timestamp'];
                        if (!empty($_devid) && $_devid > 0) {
                            $_data['created_device_id'] = $_devid;
                        }
                        $_data = array_filter(
                            $_data,
                            function($key){ return !in_array($key, [
                                'modified_at', 'modified_uid', 'modified_jobid'
                            ]);},
                            ARRAY_FILTER_USE_KEY);
                        $_cols = array_keys($_data);
                        $_vals = array_map(function($val) use($db) { return $db->quote($val); }, array_values($_data));
                        if ($isMemUsageTooHigh(__LINE__)) return $fMemExit(__LINE__);

                        switch ($_table) {
                            case 'Hersteller':
                            case 'Images':
                            case 'Inventar':
                            case 'Raeume':
                            case 'ObjektKatalogGlobal':
                            case 'ObjektKatalogMandant':
                            case 'ObjektKatalogImages':
                                $_insert = 'INSERT INTO ' . $_table . '( `' . implode('`, `', $_cols) . '`) VALUES (' . implode(', ', $_vals) . ')';
                                $lastQuery = $_insert;
                                if ($isMemUsageTooHigh(__LINE__)) return $fMemExit(__LINE__);

                                $db->query($_insert);

                                if (!isset($aProcessedChanges[$_table])) {
                                    $aProcessedChanges[$_table] = [
                                        'total' => 1,
                                        'insert' => 1,
                                        'update' => 0,
                                        'delete' => 0,
                                    ];
                                } else {
                                    $aProcessedChanges[$_table]['total']++;
                                    $aProcessedChanges[$_table]['insert']++;
                                }
                                break;

                            default:
                                if (!isset($aUnknownTables[$_table])) {
                                    $aUnknownTables[$_table]['total'] = 1;
                                    $aUnknownTables[$_table]['insert'] = 1;
                                    $aUnknownTables[$_table]['update'] = 0;
                                    $aUnknownTables[$_table]['delete'] = 0;
                                } else {
                                    $aUnknownTables[$_table]['total']++;
                                    $aUnknownTables[$_table]['insert']++;
                                }
                        }
                        break;

                    case 2:
                        $_data['modified_at'] = $row['timestamp'];
                        if ($_devid > 0) {
                            $_data['modified_device_id'] = $_devid;
                        }
                        switch ($_table) {
                            case 'Images':
                            case 'Inventar':
                            case 'Raeume':
                            case 'ObjektKatalogGlobal':
                            case 'ObjektKatalogMandant':
                            case 'ObjektKatalogImages':
                                $_update = 'UPDATE ' . $_table . ' SET ';
                                $_fLoop = 0;
                                foreach($_data as $_col => $_val) {
                                    $_update.= ($_fLoop ? ',' : '') . "\n `" . $_col . '` = ' . (is_numeric($_val) ? $_val : $db->quote($_val));
                                    ++$_fLoop;
                                }
                                $_update.= "\n WHERE uuid = " . $db->quote($row['uuid']);
                                $lastQuery = $_update;
                                if ($isMemUsageTooHigh(__LINE__)) return $fMemExit(__LINE__);

                                $db->query($_update);
                                if (!isset($aProcessedChanges[$_table])) {
                                    $aProcessedChanges[$_table] = [
                                        'total' => 1,
                                        'insert' => 0,
                                        'update' => 1,
                                        'delete' => 0,
                                    ];
                                } else {
                                    $aProcessedChanges[$_table]['total']++;
                                    $aProcessedChanges[$_table]['update']++;
                                }
                                break;

                            default:
                                if (!isset($aUnknownTables[$_table])) {
                                    $aUnknownTables[$_table]['total'] = 1;
                                    $aUnknownTables[$_table]['insert'] = 0;
                                    $aUnknownTables[$_table]['update'] = 1;
                                    $aUnknownTables[$_table]['delete'] = 0;
                                } else {
                                    $aUnknownTables[$_table]['total']++;
                                    $aUnknownTables[$_table]['update']++;
                                }

                        }
                        break;

                    case 3:
                        switch ($_table) {
                            case 'Images':
                            case 'Inventar':
                            case 'Raeume':
                            case 'ObjektKatalogGlobal':
                            case 'ObjektKatalogMandant':
                            case 'ObjektKatalogImages':
                                $_delete = 'DELETE FROM ' . $_table . ' WHERE uuid = ' . $db->quote($row['uuid']);
                                $lastQuery = $_delete;

                                $db->query($_delete);
                                if (!isset($aProcessedChanges[$_table])) {
                                    $aProcessedChanges[$_table] = [
                                        'total' => 1,
                                        'insert' => 0,
                                        'update' => 0,
                                        'delete' => 1,
                                    ];
                                } else {
                                    $aProcessedChanges[$_table]['total']++;
                                    $aProcessedChanges[$_table]['delete']++;
                                }
                                break;

                            default:
                                if (!isset($aUnknownTables[$_table])) {
                                    $aUnknownTables[$_table]['total'] = 1;
                                    $aUnknownTables[$_table]['insert'] = 0;
                                    $aUnknownTables[$_table]['update'] = 0;
                                    $aUnknownTables[$_table]['delete'] = 1;
                                } else {
                                    $aUnknownTables[$_table]['total']++;
                                    $aUnknownTables[$_table]['delete']++;
                                }
                        }
                        if ($isMemUsageTooHigh(__LINE__)) return $fMemExit(__LINE__);
                        break;
                }
                if ($isMemUsageTooHigh(__LINE__) || $iLoop > 1000) return $fMemExit(__LINE__);
            }
            while($row);
        } catch(\Exception $e) {
            $errorMsg = $e->getMessage();
            $errorLine = $e->getLine();
            $errorFile = $e->getFile();
            $errorStack = $e->getTrace();
            $errorCode = $e->getCode();
        }
        $iMemoryUsage = memory_get_usage();
        $iMemoryPeakUsage = memory_get_peak_usage();
        $sMemoryUsage = $this->fileSizeReadable($iMemoryUsage);
        $sMemoryPeakUsage = $this->fileSizeReadable($iMemoryPeakUsage);
        $iMemoryRUsage = memory_get_usage(true);
        $iMemoryRPeakUsage = memory_get_peak_usage(true);
        $sMemoryRUsage = $this->fileSizeReadable($iMemoryRUsage);
        $sMemoryRPeakUsage = $this->fileSizeReadable($iMemoryRPeakUsage);
        $line = __LINE__;

        return response()->json(
            compact(
                'line',
                'sql',
                'lastQuery',
                'lastChgId',
                'iLoop',
                'aProcessedChanges',
                'aEmptyChanges',
                'aUnknownTables',
                'aExistingInserts',
                'errorMsg',
                'errorFile',
                'errorLine',
                'errorCode',
                'errorStack',
                'iMemoryUsage', 'sMemoryUsage', 'iMemoryPeakUsage', 'sMemoryPeakUsage',
                'iMemoryRUsage', 'sMemoryRUsage', 'iMemoryRPeakUsage', 'sMemoryRPeakUsage'
            )
        );
    }
}
