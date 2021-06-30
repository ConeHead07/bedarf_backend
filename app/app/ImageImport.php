<?php
/**
 * Created by PhpStorm.
 * User: f.barthold
 * Date: 22.03.2021
 * Time: 10:57
 */

namespace App;
use App\Utils\ImageCompress;
use Illuminate\Support\Facades\Auth;


class ImageImport
{
    public static $mysqli = null;
    private $blobTestTable = 'ImagesInnoDB';

    private $importRoot = '';
    private $importRootTEST = '';
    private $subDirNew = 'new';
    private $subDirProc = 'processing';
    private $subDirNot = 'notfound';
    private $subDirFin = 'finished';
    private $subDirErr = 'error';
    private $table = 'Import_Objektbuch';
    private $globNewImages = '';
    private $globNewImagesOld = '';
    private $globNewImagesNew = '';
    private $IN = 0;
    private $tlog = '';
    private $listOfNewImages = [];
    private $lastImportError = '';
    /** @var ImageImport  */
    private $imgImportModel = null;

    public static $fileInsertMode = 'PARAM_URLDATA';
    //private $blobTestTable = 'Images';
    //private $blobTestTable = 'ImagesInnoDB';

    public function __construct()
    {
        $this->IN = time();
        $this->importRoot = base_path() . '/importimages/';
        $this->importRootTEST = base_path() . '/importimages/';
        $newDir = realpath($this->importRoot . $this->subDirNew);
        $this->globNewImagesOld = "$newDir/*.{jpg,gif,png}";
        $this->globNewImagesNew = "/application/importimages/new/*";
        $this->globNewImages = $this->globNewImagesOld;

        if (Auth::check()) {
            $this->authUser = Auth::user();
        }
    }

    /**
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function getAuthUser(): \App\User {
        return $this->authUser;
    }

    public function getAuthId(): int {
        return (int)($this->authUser['id'] ?? 0);
    }

    public function getMyqlConnector(): \mysqli {
        if (self::$mysqli === null) {
            /*
                DB_HOST=sql208.your-server.de
                DB_DATABASE=mertens_inventory
                DB_USERNAME=mrinventr_w
                DB_PASSWORD=lumen:User
                DB_USERNAME=mrinventr
                DB_PASSWORD=lumen:Mastrix
             */
            $dbHost = env('DB_HOST');
            $dbPort = env('DB_PORT');
            $dbName = env('DB_DATABASE');
            $dbUser = env('DB_USERNAME');
            $dbPass = env('DB_PASSWORD');
            self::$mysqli = new \mysqli($dbHost, $dbUser, $dbPass, $dbName, $dbPort);
            mysqli_set_charset(self::$mysqli, 'utf8mb4');
        }
        return self::$mysqli;
    }

    public function importImageByPath(array $data, int $jobid) {
        $db = \DB::getPdo();

        $this->lastImportError = '';

        $gcuuid = $data['gcuuid'];
        $mcuuid = $data['mcuuid'];
        $text = $data['Wert'] ?? $data['wert'];
        $path = $data['img'];
        $name = basename($path);
        if (!file_exists($path) || !is_readable($path)) {
            $this->lastImportError = 'File not found or is not readable: ' . $path;
            return null;
        }
        $uid = $this->getAuthId();

        $imgCompress = new ImageCompress($path);

        if (!$imgCompress->isValid()) {
            $this->lastImportError = $imgCompress->getError();
            return null;
        }
        $compressedFile = $imgCompress->getCompressedFile();


        $size = $imgCompress->size();
        $width = $imgCompress->width();
        $height = $imgCompress->height();
        $type = $imgCompress->type();
        $mime = $imgCompress->mime();

        $fh = fopen($compressedFile, 'rb');
        $sth = $db->prepare(
            'INSERT INTO Images SET '
            . ' uuid = UUID(), '
            . ' for_jobid = :jobid, '
            . ' `name` = :iname, '
            . ' `desc` = :idesc, '
            . ' `size` = :isize, '
            . ' width  = :iwidth, '
            . ' height = :iheight, '
            . ' `type` = :smimemtype, '
            . ' gcuuid = :gcuuid, '
            . ' mcuuid = :mcuuid, '
            . ' data_binary = :binaryData, '
            . ' data_url = :urlData, '
            . ' revnr = 1, '
            . ' created_uid = :created_uid, '
            . ' created_jobid = :created_jobid, '
            . ' created_device_id = -1 '
        );

        $binData = null;
        $urlData = null;
        $sth->bindParam(':jobid', $jobid);
        $sth->bindParam(':iname', $name);
        $sth->bindParam(':idesc', $text);
        $sth->bindParam(':isize', $size);
        $sth->bindParam(':iwidth', $width);
        $sth->bindParam(':iheight', $height);
        $sth->bindParam(':smimemtype', $mime);
        $sth->bindParam(':gcuuid', $gcuuid);
        $sth->bindParam(':mcuuid', $mcuuid);
        $sth->bindParam(':binaryData',  $binData); //$fhO, \PDO::PARAM_LOB); //
        $sth->bindParam(':urlData', $urlData); //  $fhImgB64, \PDO::PARAM_LOB); //
        $sth->bindParam(':created_uid', $uid);
        $sth->bindParam(':created_jobid', $jobid);
        $success = $sth->execute();

        // throw new \Exception(json_encode(compact('success','compressedFile', 'size', 'width', 'height', 'type', 'mime')));
        if ($success) {
            $id = (int)$db->lastInsertId();

            $sth1 = $db->prepare(
                'UPDATE Images SET data_binary = :binaryData1' . "\n" . 'WHERE id = :id'
            );
            $sth1->bindParam(':id', $id);
            $sth1->bindParam(':binaryData1', $fh, \PDO::PARAM_LOB); //  $binData); //
            $success1 = $sth1->execute();


            if ($success1) {
                $imgUrlDataStart = 'data:' . $mime . ';base64,';
                $sth2 = $db->prepare(
                    'UPDATE Images SET data_url = REPLACE(CONCAT(:dataStart, TO_BASE64(data_binary)), "\n", "") WHERE id = :id'
                );
                $sth2->bindParam(':id', $id);
                $sth2->bindParam(':dataStart', $imgUrlDataStart); //, \PDO::PARAM_LOB); // $urlData); //
                $success2 = $sth2->execute();
//                throw new \Exception(json_encode(compact('success2', 'success1', 'success','compressedFile', 'size', 'width', 'height', 'type', 'mime')));
            }

            $stmt = $db->query('SELECT id, uuid, name, size, width, height, type, gcuuid FROM Images WHERE id = ' . (int)$id);
            return $stmt->fetchObject();
        } else {
            $this->lastImportError = 'Bild konnte nicht in DB gespeichert werden!';
            throw new \Exception(json_encode(compact('success','compressedFile', 'size', 'width', 'height', 'type', 'mime')));
            return null;
        }
        if ($path && file_exists($path) && is_writable($path)) {
            @unlink($path);
        }
        if ($compressedFile && file_exists($compressedFile) && is_writable($compressedFile)) {
            @unlink($compressedFile);
        }
        $pathStillExists = file_exists($path);
        $compressedFileStillExists = filex_exists($compressedFile);
        throw new \Exception(json_encode(compact('pathStillExists', 'compressedFileStillExists', 'success2', 'success1', 'success','compressedFile', 'size', 'width', 'height', 'type', 'mime')));

        return null;
    }

    public function getNewImgKatIds(int $jobid)
    {
        $matches = $this->getObjektBuchMatches((int)$jobid);

        $helperTable = 'MapWerte_' . time();
        $this->createTempWerteTable($helperTable, $jobid);

        $mapImages = (object)[
            'matches' => $matches,
            'found' => [],
            'notfound' => [],
            'queries' => [],
        ];

        $db = \DB::getPdo();

        foreach($matches->found as $_itm) {
            $_objektBuchWert = $_itm->Wert . '::';
            $_sql = 'SELECT * FROM ' . $helperTable . ' WHERE Wert LIKE ' . $db->quote($_objektBuchWert);
            $stmt = $db->query($_sql);
            $mapImages->queries[] = $_sql;
            $_gKatIds = $stmt->fetch(\PDO::FETCH_OBJ );

            if ($_gKatIds) {
                $gcid = $_gKatIds->gcid;
                $gcuuid = $_gKatIds->uuid;
                $mcid = $_gKatIds->mcid;
                $mcuuid = $_gKatIds->mcuuid;
                $img = $_itm->path;
                $iid = $_itm->filename;
                $oid = $_itm->oid;
                $wert = $_itm->Wert;
                array_push($mapImages->found, compact('oid', 'gcid', 'gcuuid', 'mcid', 'mcuuid', 'img', 'iid', 'wert'));
            } else {
                array_push($mapImages->notfound, compact('_itm', '_objektBuchWert'));
            }
        }
        return $mapImages;
    }

    public function createTempWerteTable(string $name, int $jobid)
    {
        $db = \DB::getPdo();

        // TEMPORARY
        $sql = "CREATE TEMPORARY TABLE $name
            SELECT
                g.gcid,
                g.uuid,
                m.mcid,
                m.uuid AS mcuuid,
                CONCAT(
                    IFNULL(g.Typ, ''),
                    IF ( TRIM(IFNULL(g.Typ, '')) = '', '', '::'),
                    IFNULL(g.Bezeichnung, ''),
                    IF ( TRIM(IFNULL(g.Bezeichnung, '')) = '', '', '::'),
                    IFNULL(h.Hersteller, ''),
                    IF ( TRIM(IFNULL(h.Hersteller, '')) = '', '', '::'),
                    IFNULL(g.Farbe, ''),
                    IF ( TRIM(IFNULL(g.Farbe, '')) = '', '', '::'),
                    IFNULL( g.Groesse, ''),
                    IF ( TRIM(IFNULL(g.Groesse, '')) = '', '', '::')
                ) As Wert
            FROM ObjektKatalogMandant m
            JOIN ObjektKatalogGlobal g ON (m.gcid = g.gcid)
            LEFT JOIN Hersteller h ON (g.huuid = h.uuid)
            WHERE m.for_jobid = " . $jobid;
        return $db->query($sql);
    }

    public function createTempNewImgTable(array $listOfNewImages, $name) {

        $db = \DB::getPdo();

        $sql = "CREATE TEMPORARY TABLE $name (
          filename char(50) COLLATE 'utf8_german2_ci',
          path char(200) COLLATE 'utf8_german2_ci',
          PRIMARY KEY(filename)
        )";
        $re = $db->query($sql);


        $inserts = '';
        foreach($listOfNewImages as $img) {
            $imgInfo = pathinfo($img);
            $fileID = $imgInfo['filename'];
            if ($inserts){
                $inserts.= ",\n";
            }
            $inserts.= '(' . $db->quote($fileID) . ', ' . $db->quote($img) . ')';
        }

        if ($inserts) {
            $sql = "INSERT IGNORE INTO $name (filename, path) VALUES $inserts";
            // echo $sql;
            $re = $db->query($sql);
        }

        return $re;
    }

    public function getObjektBuchMatches(int $jobid) {
        $this->tlog.= '#125 ' . (time() - $this->IN) . "s\n";
        $listOfNewImages = $this->listOfNewImages;
        $tmpTable = 'NewImages_' . time();
        $this->createTempNewImgTable($listOfNewImages, $tmpTable);
        $this->tlog.= '#127 ' . (time() - $this->IN) . "s\n";
        $db = \DB::getPdo();

        $matches = (object)[
            'found' => [],
            'notfound' => [],
            'listOfNewImages' => $listOfNewImages,
            'query' => '',
        ];

        $sql = 'SELECT * FROM ' . $tmpTable . ' i '
            . ' JOIN ' . $this->table . ' ob ON (i.filename = ob.ID AND ob.jobid = ' . $jobid . ')';
        $stmt = $db->query($sql);
        $rows = $stmt->fetchAll(\PDO::FETCH_OBJ);
        $matches->query = $sql;

        foreach($rows as $row) {

            if ($row->jobid) {
                array_push($matches->found, $row);
            } else {
                array_push($matches->notfound, $row);
            }
        }

        return $matches;
    }

    public function getListOfNewImages(): array
    {
        $listOfNewImages = glob($this->globNewImages, GLOB_BRACE);
        return $listOfNewImages;
    }

    public function getListOfNewImagesByJobid(int $jobid): array
    {
        $newJobDir = $this->importRoot . $this->subDirNew . '/' . $jobid;
        $globNewImages = "$newJobDir/*.{jpg,gif,png}";

        $listOfNewImages = glob($globNewImages, GLOB_BRACE);
        if (!$listOfNewImages) {
            if (!file_exists($newJobDir)) {
                $importRoot = $this->importRoot;
                $importRootExists = file_exists($importRoot);
                $newJobDirExists = file_exists($newJobDir);
                throw new \Exception(json_encode(compact(
                    'jobid', 'newJobDir', 'newJobDirExists', 'importRoot',
                    'importRootExists', 'globNewImages')));
            }
            return [];
        }
        return $listOfNewImages;
    }

    public function blobTest(Request $request, int $jobid) {
        $db = \DB::getPdo();
        $uid = (int)$this->getAuthId();
        $insertMode = $request->input('insertMode', 'PARAM_LOB');
        $useTable = $request->input('table', '');
        if ($useTable) {
            $this->blobTestTable = $useTable;
        }
        $table = $this->blobTestTable;
        $aValidInsertModes = [
            'MYSQLI_BLOB',
            'PARAM_LOB',
            'FILECONTENT',
            'STRING',
            'PARAM_URLDATA',
            'PARAM_URLDATA_FH'
        ];

        if (!in_array($insertMode, $aValidInsertModes)) {
            echo 'Invalid ?insertMode=' . $insertMode . "<br>\n";
            echo 'Valid Values: ' . implode(', ', $aValidInsertModes);
            return '';
        }

        $imgDir = "/application/importimages/new/$jobid";
        $imgGlob = $imgDir . '/*.jpg';
        $imgFiles = glob($imgGlob);
        sort($imgFiles);
        $numImgFiles = count($imgFiles);

        if (!$numImgFiles) {
            echo 'ERROR: No Jpeg-Files Found In Folder ' . $imgDir . "<br>\n";
            return '';
        }

        $imgFiles = array_slice($imgFiles, 0, 10);


        $sth = $db->query('DELETE FROM ' . $table . ' WHERE for_jobid=' . $jobid);
        $sth->execute();

        $sth = $db->prepare(
            'INSERT INTO ' . $table . ' SET '
            . ' uuid = UUID(), '
            . ' for_jobid = :jobid, '
            . ' `name` = :iname, '
            . ' `desc` = :idesc, '
            . ' `size` = :isize, '
            . ' width  = :iwidth, '
            . ' height = :iheight, '
            . ' `type` = :smimemtype, '
            . ' gcuuid = :gcuuid, '
            . ' mcuuid = :mcuuid, '
            . ' data_binary = :binaryData, '
            . ' data_url = :urlData, '
            . ' revnr = 1, '
            . ' created_uid = :created_uid, '
            . ' created_jobid = :created_jobid, '
            . ' created_device_id = -1 '
        );

        $binData = null;
        $urlData = null;
        $gcuuid = 'nogcuid-abc-123';
        $mcuuid = null;
        $iCurr = 0;

        foreach ($imgFiles as $file) {
            ++$iCurr;
            $name = basename($file);
            $text = $file;
            $imgInfo = getimagesize($file);
            $size = filesize($file);
            $width = $imgInfo[0];
            $height = $imgInfo[1];
            $type = $imgInfo[2];
            $mime = image_type_to_mime_type($type );

            $sth->bindParam(':jobid', $jobid);
            $sth->bindParam(':iname', $name);
            $sth->bindParam(':idesc', $text);
            $sth->bindParam(':isize', $size);
            $sth->bindParam(':iwidth', $width);
            $sth->bindParam(':iheight', $height);
            $sth->bindParam(':smimemtype', $mime);
            $sth->bindParam(':gcuuid', $gcuuid);
            $sth->bindParam(':mcuuid', $mcuuid);
            $sth->bindParam(':binaryData',  $binData); //$fhO, \PDO::PARAM_LOB); //
            $sth->bindParam(':urlData', $urlData); //  $fhImgB64, \PDO::PARAM_LOB); //
            $sth->bindParam(':created_uid', $uid);
            $sth->bindParam(':created_jobid', $jobid);
            $success = $sth->execute();
            if (!$success) {
                echo 'ERROR: Cannot save Image-Metadata for ' . $file . "<br>\n";
                continue;
            }

            $id = (int)$db->lastInsertId();
            echo "$iCurr / $numImgFiles IMPORT $file size " . $size. " (InsertMode $insertMode): ";

            try {
                $success = $this->blobTestImport($id, $file, $insertMode);
                echo ($success ? "OK" : "FAILURE") . "<br>\n";
            } catch (\Exception $e) {
                echo $e->getMessage() . "<br>\n";
            }
        }
        return '';
    }

    public function blobTestImport(int $id, string $file, $insertMode = 'MYSQLI_BLOB')
    {
        $db = \DB::getPdo();
        $table = $this->blobTestTable;
        $success = false;

        switch ($insertMode) {
            case 'MYSQLI_BLOB':
                $mysqli = $this->getMyqlConnector();
                $stmt1 = $mysqli->prepare("UPDATE $table SET data_binary = ? WHERE id=?");
                $null = null;
                $stmt1->bind_param("bi", $null, $id);
                $fp = fopen($file, 'rb');
                while (!feof($fp)) {
                    $stmt1->send_long_data(0, fread($fp, 8192));
                }
                $stmt1->execute();
                break;

            case 'PARAM_URLDATA':
            case 'PARAM_URLDATA_FH':
                $asFH = $insertMode === 'PARAM_URLDATA_FH';
                $temp_file = tempnam(sys_get_temp_dir(), 'ImgB64');
                file_put_contents(
                    $temp_file,
                    'data:jpeg;base64,' . base64_encode(file_get_contents($file))
                );

                $sth = $db->prepare(
                    'UPDATE ' . $table . 'SET data_url = :urlData WHERE id = :id'
                );
                $sth->bindParam(':id', $id);
                if ($asFH) {
                    $fhImgB64 = fopen($temp_file, 'rb');
                    $sth->bindParam(':urlData', $fhImgB64, \PDO::PARAM_LOB);
                } else {
                    $imgB64Data4 = file_get_contents($temp_file);
                    // $qImgB64Data4 = $db->quote(file_get_contents($temp_file));
                    $sth->bindParam(':urlData', $imgB64Data4); //  $binData); //
                }
                $success = $sth->execute();

                if ($success) {
                    $sth2 = $db->prepare(
                        'UPDATE ' . $table . ' '
                        . ' SET data_binary = FROM_BASE64(SUBSTR(data_url, LOCATE(",", data_url) + 1)) '
                        . ' WHERE id = :id'
                    );
                    $sth2->bindParam(':id', $id);
                    $success = $sth2->execute();
                }
                return $success;
                break;

            case 'PARAM_LOB':
                $fh = fopen($file, 'rb');
                $sth3 = $db->prepare(
                    'UPDATE ' . $table . ' SET data_binary = :binaryData1 WHERE id = :id'
                );
                $sth3->bindParam(':id', $id);
                $sth3->bindParam(':binaryData1', $fh, \PDO::PARAM_LOB); //  $binData); //
                $success = $sth3->execute();
                break;

            case 'FILECONTENT':
                $mysqli = $this->getMyqlConnector();
                $fileContent = '0x' . bin2hex(file_get_contents($file));
                $sql = 'UPDATE ' . $table . ' SET `data_binary` = (' . $fileContent . ")\n" . ' WHERE `id` = ' . $id;
                $sql2 = 'UPDATE `' . $table . '` SET `data_binary` = 0xffd8ffe000104a46494600010100000100010000ffdb00430006040506050406060506070706080a100a0a09090a140e0f0c1017141818171416161a1d251f1a1b231c1616202c20232627292a29191f2d302d283025282928ffdb0043010707070a080a130a0a13281a161a2828282828282828282828282828282828282828282828282828282828282828282828282828282828282828282828282828ffc000110800ec00c703012200021101031101ffc4001c0000000701010000000000000000000000000102030405060708ffc400561000010302030501080b0a0b0705000000010002110304052131060712415161132232718191b1b314151724527293a1c1d1d3082333344262637383a3252635434445535455a2b416273775b2f0f13665828494ffc4001b01010101010101010100000000000000000001020304050706ffc40030110101000200050203040b00000000000000010211031221314104130551a11461a2e206071517525462649192d1ffda000c03010002110311003f00f43621715d956af0d6a80079c838f552db5aa100f747f9cae29b43bdf387ed3e2d6156c28d6a567795a83cb1e5af86d42d9cf29c9758c0efe96258459dedb9268dc526d4613ac10bc96d8d696c2ad4f86ef3a57757fc3779d32d29614dd41d5ab5033c370f2a8edaf560cd57ebf08a7aa66c2a297364b5a7be9cc468b36d12a9d6a87f9c7e9d4a3eeb527c3779d3148e70a3e2189d9e1ece3bdb9a7447471ccf886aa4ca8b1eeaff0086ef3a3359cd6173ea16b46a4ba00580c576fe9b65985db979fed2b643ccb21896357d89ba6f2e5ef6f264c3479025e26964763663562f30dc46813a7e142954ef2954f02e69bbe2d407e95c10bc472487548d32f129eed395e846bdcef05e4f88a81b4942e6ef67b12b7b5af5e85c54b6a8da75693cb1ec77098208cc195c0ea5e5c539ee75eab3e2bc84fe1b8ce274de78711bb03a77571fa56bdce869d0f7098fdde3dbb7b2ab88dd57b9beb7ab52deb55ad50bdee2d765c44e64c10ba2713be11f3ae13816215f03a3529611505a53aaf351eca6d0039c7524755754f6d31a66b74d77c6a60abeec34ebbc4ef847ce8713be11f3ae594b6f7166f86db5a9e3611e82a553de1dd8fc258dbbbe2b9c15f76269d2439dd4f9d0e27753e7580a7bc567f3b8711f12afd614ba5bc1c3ddf84b4b9678a0ad7b93e669b3e377c23e74389df08f9d65a9edce0cf3df3ebd3f1d3fa94aa7b5d81d4fe9cd6fc76387d0af3cf99a5ff1bbe11f3a2e277c23e75574b68308a9e06256a7c6f8f4a974b10b3ab1dcaf2d9fe2aadfad362651738d56cb89f2a08511f7c082eb8764785b782e23789b5dc273f6d2efd7397a337538e319bafc2aa1a5715aa50a0e6c3193c45ae390cd79af7855037799b580f3c56f07ef9ebb66e36edf5360a8b1a73a55aa37e79fa5673c76ddbd1bb1b796a70a75cd422deacf08a6f043819e60abcd98da5b5c628bde2bdb800c0efe0f941596c6adb0dbaa2c7e2cca2784c8e3d4fd6aa4ed0d8e1dc4dc26ce9f11cb88b035be61aaf3f2f2ddda6e59d9d80be93984f75a511af18594c6b6af0ac35ee68adec9ac3f22867e7768b97e278ddee207df35c96f2637bd68f20554ea8573cb39e0d3658aedce23752db40db4a67e066f8f8c7e8598ad72fad50beabdcf79d5ce325403533d511a8b96f6a99dd7b50ee87aa861f96a941eaec56ed063afb278b7b4e135889738e7c13a65d79e7d9acaa03b41899fe95fbb6fd491b446719b83f17fe90ab97cce2f1b3e7baafd97e07f03f418fa0e1679f0b1cb2cb196db25bbb37e564ec771176b73fe46fd48d98ee24cf06e63ff00837ea55882e7eee7fc57fcbebfec7f87ff002f87fae3ff0016e3693161a5dfeed9f529169b5788d17cd7753b86922439a1a40e70447cf2a81049c6e24ebcd58e27c0fe1dc4c6e3781875f96327d675759b6b91716f4ab3010da8c0f00eb044a77ba155784bff0082acc74a2cff00a4297c792fb38f592bf0cf5184e1f1b3c31ed2d9f548ee8500f4c07a30f855c529a7aa32428ddd024beb00338002090e7b6354de1cff006562d674a98ef1d59809ebdf05575ee3bb4b5b219e9565b255683b6b708b47d468ad56b35cda739900893e25bc71b6a3d1d4ff000a1048a601aed21d033ef7aa0bd787666bc17b7c29bb7a9b50dad57b9d338c5dcba263efcfe4b6bb17b5165b3d833f0fb1c4e1afa86a3aa54a45a64c69d064b07bcfb6aa3795b5550005bedb5d9fdf3967d9538485329cdd36bd9dd1b8ed9de3b8bdb0a359e799aa09f9d4915dae12d735de232b811798d52a9dd55a4669d57b4fe6b885c2f03ef36ef0f79e49b73faae2d4b1ec4a8fe0ef6e00f8e4fa54da3b618b5389b9e3f8ed0566f03236eb46a2497ae694b6eaf99f84a541fe4214da5b7834ad6607c47fd6162f03336de87a507ac850db6c3de0774a55d9e40ef4153696d5613523df5c1f1da42cfb794f0bb35b456ef6de9af99a7500ce3420447ccaa96a28e3387d603b95e5074fe78525b59af1de39ae1d86578f3f47cd95cb7a7f75f0efd37cfd27a6c3d3e7c1e6e59adf36ba4edd396f863905b46b93ec7ac7d8beff00a3dbfbc0fedff1fe56111b5a5ee0d682e71300012495d01af84a0f4fb17f57d0bfac0e9d3d3fe3fca55807d2b1b6a75043d94dad23a10148e30a387a3e35ee9d269f9e717897899e59def6ecf87414ae351b8d26ad614d85ef74346aab9a4beb35ad25c400352abab5d1b9700dca98e5d540ad74eb97731481c9bd7b4abcd94c0aef1fc45b6b68d86f8552a1f069b7a9fa974c71d84db59dc57b0bebba34e68d9d1756a8f3e08804813d4acd6e3aad6c637ab697576e2ea9c0e7483e088c80ec5de76fb0eb5d9fdd56316d64ce1636d9fc4e3abdd0732b88fdccb47ba6de3ea47e0eddc57a38735b4af58da08b96e6e39693d88216a7df0dff00be4505d31eccbc1dbd4b88de26d4871c862d7423f6ce5917d5e1120131c82d16f4b846f2b6b6733edbddfae7acc714e6926976d9ecbec4629b49838c42c2a5a86f1b99dcea3cb5d23c909cbcddbed55bc9185babb473a351affa56eb72979893b646e2861b6d4ae1946f49ac2407f0b9add27c45749bbc669d9d1b46d2c3eb8739d0f1c04f08ed8e53cd79b897892de56f192ceaf2f5eecfe33653ecbc2afa976ba8ba3cf0aadfde121e0b4f476457ba2c6dac6e2830b6ec31ce130e303e751b1dd94c36f680f65370facd035a8c692567dfe24ef8a6a3c3bc59205fa659af5063dbbdd9bee0facec0ad5b4da25d5cbcd103c40112b94625b37b1d52bbdb6d718b5bc12039ac0f61f14e70ba63ea25ef1395cdb8d1f7423495b4adb1585d49f616d1d21d05cd0733d0a155d85bf98b4bec32ea74ee77001f315d271b1a9cb599155c343e44ed3bdacc8e17911d0c2188d85c61988bec6f69865d322581c1da8919842eb0cbfb41efab2baa3cfef949cdf485bdcd26936df68310a1e05d571fb427d2acedf6cf13a47beadc63f39a0aca03062528153971a3796fb7d7032ad428be3c6d5696db796cefc35b39bf16a03e95cbc1cba2534e6a5e0e17c1b760a3b6185d4804d661ed64fa15852c7f0da8070de5313c9d2df4ae24d770b949a551c0f7af23cab1f67c4e676ef6c2d8d2351b5e9bda3e0b815515eedf79525d2da6d3deb67d2b09b395deec4035c44709e416fb66f0bbbc6b10a56387d2352bd47791a3993d005cf2e172d5ded6db3383dde3b8952b2b167154719738f82c6f371ec5e8ad99c06d367b0c6da59b64eb52a11df547753f528bb1db356bb31858b7b78a95df06b562337bbea1c82bf95bc669583df955ee5bb2c68f5a5c3e7cbe95c8bee57a3c5b498ad68f02dc09f195d3bee87addcb76388e7e1398dff30582fb9468cbb1dafd0319f3ad61e4be1e8eb4fc619e5f42093667df2cf2fa105d31ec95e0fde7dbd23bc8dab7410ef6daecc831fcf3963ab8e17c71133cc995b0de6bcfba56d58ffddaefd73963ae6441392be51b1ddced45cecfd4ba6d1b97d16552d7168301c44ebe75d3b0dde2def742f6d2b4b89cc9d1c7c641fa1705c34cd623b1593643bbdc8f51925b3c8f49e1bbcda521b758740fcda9f5856d79bc0c1c61f52e28d2ac2bb0654cb467e5192f32dbde5f528ee7715401c899f4a9ecc67102c730bd9df0d7802c598d3ab63b61b577389bbbae2f70e651d69dad33af93e92b9a6237352f2ebbab78a9b5b931ad3e08522a30d4a85f55cea8e3ab9c64945dc87453b2e88a188df50102b97b7e0d46877a5496e2ee2e06b585bbfa9612c29934fa8cd24d204c80a6cd29efee056c7056e034e632266328d575bb4deadf52b7a749f716b59ad601c371488d075cd720c5406622d235812accdb87dbd290def9b2ae5863949b25b3b3a7bb6fb08bfcb13d97c1ae81d5d4f801f9c04d547eeeafdbc573b3b7d624fe5db1747f9490b990c3438135010d1a007c24ba6cbba07def73569b47269c87914f6b0f0bcd5a4db4c2b632d7057dd6ce6257aebd151ad16b7035693999201c9580dd7d3bab7656c2b69f0cafc4d0ee0a80b48246922561715babea96819755454a4d78325a27cea53712c81ab86dbbf2f09b2d27ccb5c975a9537f38d15d6eb369e8f7d428da5db791a172d33e430b2d89e1f7985de3adb11b6ab6b5c6aca8d83e3ed0a6331b349a7d8efc4acdfc8d2b8240f21502fb1dbec5cb29e217556e7b883c0eaa0481e3493397a96cf0b1d9b24e22d11278480bd9dbb8d8fa3b2f81d2fbdf1621714db52e2a9d4139f08e802f176ccd7f63e274aa983c043a0e86082bd2f86efb1ae03d97863a3ad0aa1c3cc615cb0b95e892e9d98c8d4142735ce6d77c38155cab36ea89391e3a53e8957d63bc3d9bbdc9b895bb5cee4f3c27e7859b858d6e319f74d56ee7bb97b67c3b8a6dff0034fd0a9bee54a3c3b3b8c5723c3aed6f98143ee9ec6ac2eb622ce8da56a155f56ed8e1dcde0c001c792b2fb97a9d366efee2a39d0fa976ef3003eb4c66a52bb3d9e772cf2fa0a09568d6fb2585ae9cce51ca105ac7b25784f792c1ee91b5640feb6bbf5cf58dc45b0d98d0adb6f241f747daaff9addfae72cae2541dec673dd940984df515f8619bca6d1ce42d2b28860d24f52b3186bc32fedddc83c2d8b9dc4643404c8861ad81a14a0c24e590ed4e86071ccc0e894181d98e4b0d1aee7260040530350a5400d88cd24b5a06435504734e44930a66138751c41f5693f10b6b4ae23b98ac603e751af8937c33ca5355e953a8385d4daecf9894199da46b69df318d6b4707130bd8e90f20c4f62bac3e8975a52ee8d99602d13d9cd54ed3d26d275bc00351015c60a26c2819fc95aceeb14c66e9f16ee332e33da92ea0f8d079d4e6c64810bcfcf5be58cf63741edc3ea39c4402341da9fa74668b09120b47a14ac6d81f60f67c2213b6e01b7a7d8d017499dd272f541341a72cbccab6fa98a59b4449857d558d6b09390026552e2ae069b4fe72de196d8b34461aefbeb8756c27859398658f7b4fe6951b0d23d90d8e70b405a272192e96eaa48814ee310a3953bbaa239133e95269e3989d21df8a3547e73227cc9c2c11a243a902330933a72ab31bc4eadfd5a46ad06d1e011de939ad5ec9ed9d5c0b0ea56d42f6f6da25c7b91ef4cf62c8e32031d4801aca9765438ed699206616a65e4d3d0fb97de2dee33b7385e135714f6451afdd659529c3cf0d27bb231d882c07dcf56ed66f8b67de069ec8ff004f510577b35a52ef0e8b5bbc2da7711ae2b747f7ce596c499c76f500d78485aede209de0ed3f4f6d2ebd6b966ee99c549d92e55a62e91e1a8c77420adcb04898d42c2d505af73633060add5977f6945c0e65809f32d65d921c63037582528cf625f076994877358683967aa36c73c900234461b397350011c9186819ce6831a403f3a30225419ddaf68ee56ee0dfca2253d82ded0658d0a755c69b80805c201cfaa1b5ac9c3e9bba541e84e6094595b06a3c6d0ed467e32b5719963d537aab563dae6cb5c1c3a828c93cd55bb0aa0e712c0ea7f10908bdac20436eae00e9c6b1ed4f9b5cc937ddf320e41302fade8530d75405df05b994918453719a8fa8ff008ce254ba3616f423829b655e49e53990b8abdeb877869d10660ea7c6a0e34d0da6c03e17d0b4659025ab3db426383492e2b5352c919a878519baa63b42d4068d792cbe0f9df5211f94b58448e9d8b7977210465119a22d93109ce14a397292b2acded1657149b3f9255ae1ec8b2a320c70aa7da33efe60e7c0b436b4c8b7a42330d1e857c24740dc1348dede05fb7ff4f5104bdc183eeb5811fd7fa8a882d63d8acf6f0fff005fed313fe2975eb5cb3ef05cd23a85a2de08fe3fed34ff0089dd7ad72a011060959aac2e22de0bcac34efa56bb04a9c585db11f0609f12cd63ac0cc41fda255e6ccbf8b0a68f82f70fa55ef12775bcceb978916919884091390f9d02d3063359518049c8129c6b7ce835a40c92c121bdf11d54a130d1acca33922912246489ef6b4689a151b5801c24e505af694d6ce3b8b0aa62720e70f9d2f681c6a6175c09c803f3a63665ff00c1d1d1e56a4e88b7c80d51b44a01c260663a25c00246aa6945c45a8c3b212513b3d500204929a0b770f2ce7a2cd6d264e60fce2b46d127a059bda63f7c678ca4ef0a8f8109c4690d35f42d599ca26165b6784e22cf11f42d53c75572483cbff281ea4a0d1af24441ce4a8acb63ddf62c1a3a342d435a5ac03480b2f888e3c7f87f3da16b23c6b57b246f370bff0016702fdbfa8a8823dc2c7bace05fb7f5151056159dde11fe3fed28e5ed9dd7ad72a01274f4ad0ef05bfc7eda6d3f94ee75fd6b950111e25956536a19c3774ddaf102a66c93b8ad6bb3e0be63c893b5ac9a74aa0eb09bd8f74d6b96722d0e56764f2d2004e9a7893b0672f22046794a566342b2a369c883976a2d4648089e689c6066632404f706b73512ad62fc80108547f13b530901bce335a910c5c536d7a6ea751b2c76a3aa2b3b5a76cce0a121b3309f830667ca9c6b3398540683a9825183119256820045a725009339a54f5465a7874c9260823d080c380d743c82cd6d1e75d83b4ad18227459bda23efa6eba953cc05b383dfe0f2e12b55049ca0742166366c1f663e3930fa42d2335399056aa42f3190ea86bae7d52493328b36f359699acaa6d3c465dd07cc16ab21966b2b61f7cda5a87a39c56a0ccf8510ad891d0770c1beead8191fa7f5151048dc29ff007b38167fdbfa8a882d62567f7843876fb694cff59dcfad72a02497667257bbc4693b7db4a44ff29dd69fad72a5a6cf84b34546d2512fc2dee3ab482aa3649e1b8a969d1f4c85a6c5a99ab875c8e7c2563f679e198cdb13cc96f9c14837ad1d103d74f122190d3241ce0076a8a4b9d91e4a257793ce52aad6e20442431b949cd5910900c67925e7a648164c103b52c3447fde6a826b7d29d2d2d6e7aa0dd2623b128bc099503597e50284084a043918a45d9ce45504dcb9127b50ef891213bc01adcf549073cc4a884966590f22ca6d1c8ba6cf6ad6b9f27482b25b4a47b342790fecb80eb8ac4cc70723dab400800f3e8a8765467719819057f001fa16aa939ce688981a4c25b9b0663c89261446770227db8b8a9d38be72b485fc620e60734d8653692e0c6f11e719a2913001945743dc167bdac0e047e1fd454410dc1f0fbad60307fb7ff4f5105a9114fbc06c6deed29d49c4ee7d6b951683357fb7e7f8f9b4b3fe2573eb5ca8c16912607458532f1dd29b99a82233580b43dc316a272965603e75d0c341199f32e7f8c33d8f8bd703f26a48f4ab0ae82e7403d3aa8f50f14c8000442a775a4d74e440289bdf122621240d86e73a8f4a70033cb34a765a651f3a0003a998080b8648e9aa744819468806019119f66889c497204900f882220684ebd1380754330098060a041000f1205c5a3447aea5170c9d11061c4cc9438837413da8a4c401e446e00819e4812624b8ebc964f69bf1f03b16b4b7210642c8ed308c420f44f227eca37ef55cf5202bf6b7ca02a4d966fbd6a9eae03e657ac96e634e4a843811123349d72397893b565daa418689e68a61cd24f54901c13e1bc3323cc9040397241bddc108dee603ff00d89fff003d4412b704d8ded6047f5fea2a20ac4546f064edeed272fe13b9f5ae5481b965aabcde0c7fb7db49ff0033b9f5ae544496c46456541c406c0399ebaac3ed4d3e1c4dce8f0d80adab9d3cc4e99aa5c7f09a97c693e83d9c6d1041e615d6849c3de6b61f6c46734c49f229e0343609e6a2e1b6ceb4b3a545c7c1113d54b05ba4204804f7bda9c1de88024754a6019e5994932441072404412601c9172c8e89c004e68ddc3a011da884f0e9d502d21b99d52a0e9274c91384f2f3a06de4441f3c202729109c9ca0b72ec40e9c2244762046519ea8889339251d721e5452403944f24d023969f32c76d3ff0029c73e10b6b039f258cda9fe553198e10a0b6d966fbc1c4f379f42bc0660aaad97118603ccb8956e441ca0ab422388f284cd5199cd3ce22083911926f23004ca04e64c728492c399d3b52da0139f3ea8dadefbbe3de8e9a20de6e0d846f5f013cbeffea2a209ddc3c7babe0600cbeffea2a20ac145bc1701b79b4797f59dc8fdeb967c9327e657dbc01fc7dda5ce3f84ee7d6b95181119ebd54537035810529ad820e811b4cea13a406ba46bda81244b7206537c10741329e925e7a0406609023aa026b8806024190e93d53bc39e901261a1dd8882777c66721aa0c2c20c8cbe74794907208f84e9a0ec40870e23deff00e1003bdccf9528822401978900322632e4a843a03607328cb75194a5b8738ed4900cc4793a204640e59f8919673e473809669f7dd4049765a67279201c2e9cb30b17b5238716703f042db783944ac56d5cfb70e919f037d0a0d16ccb07b55467ab8fceacdf008d0055f80b7830ab68d4b663ca54fe286e93d1034f238a0663aa4b413a1ecf12538c93919ece880e169cc735419646408308883196606710945d1a73e493a9f1a2b7bb8733bd7c08febfd454412b70e00deb6071fa7f5151055141b7e48dbdda5cbfaceeb3fdab9520899689eaaef78196de6d2cf3c4ee72fdab951b1dc801da028a5074691db010ccba4c93d11b5a64c8cc6708ce998409c84733a2112e0639f54ae1390d404b0d30735109104941c002425861e024414d8249911972400b48f1fa501df4004884a2389bdea26f7ad05bf3aa070927339442220011994a2788ccb75cf2444708977fe501180dea63924b750eedc91f0e72844679191e640791767aa06339d51004090734973c9004840012d260c0589da827db87c9cf85ab6e4b413df411d561f69e0e32fe1f82df420d56152dc36d9bfa31e65249d6341c93387802d6dc67c229b478f2520f0cb48d3526745421a4c67e52933c3a44271c272041e6913339482816d01cdce33d51ba272d354400e1cf5840e59100b541bfdc416fbaae08073eefea2a2086e23fe2a609fb78cbf415105467b7839ede6d240d312b9f5ae548d1c591123942f51e31b92d9cc5717bdc46e2f7176d7bbaf52e1eda756986873dc5c626993127aa8a370bb303fac31b8fd752fb35079a4e604f97344d3c51c2320bd2e3709b2e0c8bfc6be5a97d9a0370bb2e3fa76359fe9a97d9a2bcd4c2e11965ce12c100c95e931b86d98d3d9f8d7cb52fb340ee1f6627f1ec6b2fd352fb34d23cdff91a73488f32f4a9dc46cc911ecfc6a3f5d4becd10dc4eccc9f7fe339fe9a97d9a0f34c13c2d10119644709e6bd2bee0fb31fdff001af96a5f6680dc46cc0fe9d8cfcb52fb341e6a0241811283838900af4b1dc46cc123dfd8ce5fa6a5f668bdc23663fbfe35f2d4becd079a5ad899cd260f08e11e45e986ee236604fbff001af96a5f6687b83ecc714fb3f1af96a5f66a8f338d401331d51161033033e4bd30370db300922fb1904fe9a97d9a1ee11b304e77f8d78bbb52fb341e65aac04c1e5cd53e278153bcba15c54731f9022241017ac7dc1765e0fbff001acff4d4becd17b826cbe5effc6be5a97d9a0f31329f0530dd1a11064933a92bd3e770bb2e4fe3f8d7cb52fb345ee09b2ffdff001af96a5f6683cca1b04123940ed46d608ed5e9a76e1765c8fc7b1ad67f0d4becd01b85d9707f1fc6be5a97d9a0f33f73ccf3e689de102750bd32770bb2e7fa7e353d7bb52fb346770db2e441bec64c7e9a97d9a83926e28ffbd2c1075eef1f215105dc765374980ecc6d05ae2f617789d4b9b6e3e0656a94cb0f131cd320301d1c79a083ffd9, `modified_at` = NULL WHERE `ImagesInnoDB`.`id` = ' . $id . ';';
                $success = $mysqli->query(
                    $sql
                );
                echo $sql . "<br>\n";
                if ($mysqli->error) {
                    throw new \Exception( $mysqli->error);
                }
                return $success;
                break;

            case 'STRING':
                $sth = $db->prepare(
                    'UPDATE ImagesInnoDB SET data_binary = FROM_BASE64(:binaryData) WHERE id = :id'
                );
                $fileContent = base64_encode(file_get_contents($file));
                $sth->bindParam(':id', $id);
                $sth->bindParam(':binaryData', $fileContent); //, \PDO::PARAM_LOB); //  $binData); //
                $success = $sth->execute();
                break;
        }
        return $success;
    }

    public function updateObjektbuchOkmMcuids(int $jobid): int {
        $mysqli = $this->getMyqlConnector();
        $sql = "UPDATE Import_Objektbuch ob
             JOIN (
                         SELECT 
                            okm.mcid, okm.for_jobid, okm.uuid AS mcuuid, 
                            okm.gcid, okg.uuid AS gcuuid,
                            CONCAT(
                                 IFNULL(okg.Typ, ''),
                                 IF ( TRIM(IFNULL(okg.Typ, '')) = '', '', '::'),
                                 IFNULL(okg.Bezeichnung, ''),
                                 IF ( TRIM(IFNULL(h.Hersteller, '')) = '', '', '::'),
                                 IFNULL(h.Hersteller, ''),
                                 IF ( TRIM(IFNULL(okg.Farbe, '')) = '', '', '::'),
                                 IFNULL(okg.Farbe, ''),
                                 IF ( TRIM(IFNULL(okg.Groesse, '')) = '', '', '::'),
                                 IFNULL( okg.Groesse, '')
                            ) As Wert,
                            okg.Typ, okg.Bezeichnung, h.Hersteller, okg.Farbe, okg.Groesse, okg.Kategorie, okg.Gruppe
                         FROM ObjektKatalogMandant okm 
                         JOIN ObjektKatalogGlobal okg ON (okm.gcid = okg.gcid)
                         LEFT JOIN Hersteller h ON (okg.hid = h.hid)
                         WHERE okm.for_jobid = $jobid
                ) AS t ON (ob.Wert = t.Wert AND ob.jobid = t.for_jobid)
            	SET ob.mcuuid = t.mcuuid
                WHERE ob.jobid = $jobid AND ob.Gruppe LIKE \"Ty%\"";
        $success = $mysqli->query($sql);

        $db = \DB::getPdo();
        $checkNum = 'SELECT COUNT(1) FROM Import_Objektbuch WHERE jobid = ' . $jobid . ' AND IFNULL(mcuuid, "") != "" AND Gruppe LIKE "Ty%"';
        $sth = $db->query($checkNum);
        return $sth->fetchColumn();
    }

    public function getExistingObImagesByList(int $jobid, array $aImgFilenameList) {
        $aExistingObImages = [];
        $aFound = [];
        $db = \DB::getPdo();

        $aBasenames = array_map(function($path) use($db) {
            return implode('.', array_slice(explode('.', basename($path)), 0, -1));
        }, $aImgFilenameList);

        $sql = 'SELECT ob.*, okm.gcuuid ' . "\n"
            . ' FROM Import_Objektbuch ob '  . "\n"
            . ' JOIN ObjektKatalogMandant okm ON(mcuuid = okm.uuid)'  . "\n"
            . ' WHERE jobid = ' . $jobid . "\n"
            . ' AND IFNULL(ob.mcuuid, "") != "" AND Gruppe LIKE "Ty%" ' . "\n"
            . ' AND ID IN (' . implode(",\n", array_map(function($f) use ($db) { return $db->quote($f);}, $aBasenames)) . ')';
        try {
            $sth = $db->query($sql);
        } catch(\Exception $e) {
            throw new \Exception($sql . "\n" . $e->getMessage());
        }
        $numFiles = count($aImgFilenameList);

        $aIDsToFiles = array_combine($aBasenames, $aImgFilenameList);

        while($row = $sth->fetch(\PDO::FETCH_ASSOC)) {
            $_id = $row['ID'];
            for($i = 0; $i < $numFiles; $i++ ) {
                if (!empty($aIDsToFiles[ $_id ])) {
                    $row['img'] =  $aIDsToFiles[ $_id ];
                }
            }
            if (!empty($row['img'])) {
                $aExistingObImages[] = $row;
                $aFound[] = $aIDsToFiles[$_id];
            }
        }

        $re = new \stdClass();
        $re->query = $sql;
        $re->found = $aExistingObImages;
        $re->notfound = array_diff($aImgFilenameList, $aFound);

        return $re;

    }

    public function getValidObjektbuchImagesIds(int $jobid) {
        $db  = \DB::getPdo();
        $sql = 'SELECT ID, Wert, mcuuid FROM Import_Objektbuch '
            . ' WHERE jobid = ? AND mcuuid IS NOT NULL AND mcuuid != ""';
        $sth = $db->prepare($sql);
        $sth->execute([ $jobid ]);
        return $sth->fetchAll(\PDO::FETCH_ASSOC);

    }

    public function getObjektbuchMcuuidByFilename(int $jobid, string $fileName) {
        $fileName = basename($fileName);
        if (preg_match('/#(.*)?\.(jpg|jpeg|png|gif)$/', $fileName, $m)) {
            $fileName = $m[1];
        }
        $db  = \DB::getPdo();
        $sql = 'SELECT ID, mcuuid FROM Import_Objektbuch WHERE jobid = :jobid AND ID LIKE :fileName LIMIT 1';
        $sth = $db->prepare($sql);
        $sth->execute([ ':jobid' => $jobid, ':fileName' => $fileName ]);
        return $sth->fetchColumn();
    }
}
