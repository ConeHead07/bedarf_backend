<?php
/**
 * Created by PhpStorm.
 * User: f.barthold
 * Date: 23.06.2021
 * Time: 16:41
 */

namespace App\Http\Controllers;


use App\BereichMABaseData;
use Illuminate\Http\Request;

class InterviewBaseDataController extends Controller
{
    /** @var BereichMABaseData|null  */
    private $model = null;

    public function __construct()
    {
        $this->model = new BereichMABaseData();
        parent::__construct();
    }

    public function all(int $id) {
        $rows = $this->model->getAllByKid($id);
        // throw new \Exception(__LINE__ . ' ' . __FILE__);
        return response()->json([ 'rows' => $rows ]);
    }

    public function get(int $id) {
        $data = $this->model->get($id);
        return response()->json([ 'data' => $data ]);
    }

    public function update(Request $request, int $id) {
        $data = $request->post();
        $row = BereichMABaseData::find($id);
        if (!$row) {
            return response()->json([
                'success' => false,
                'message' => 'Datensatz mit der ID ' . $id . ' wurde nicht gefunden!',
                'data' => [],
            ]);
        }
        $result = $row->update($data);
        if ($result) {
            return response()->json([
                'success' => true,
                'message' => 'Datensatz wurde aktualisiert!',
                'data' => $row
            ]);
        }
        return response()->json([
            'success' => false,
            'message' => 'Unknown Error!',
        ]);
    }

    public function insert(Request $request) {
        $data = $request->post();
        try {
            $row = BereichMABaseData::create($data);
            if ($row) {
                return response()->json([
                    'success' => true,
                    'message' => 'Neuer Datensatzt wurde angelegt!',
                    'id' => $row->bid,
                    'data' => $row
                ]);
            }
        } catch( \Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'id' => 0,
                'data' => []
            ]);
        }
    }

    public function delete(int $id) {
        $row = BereichMABaseData::find($id);
        // throw new \Exception('DELETE ROW BY ID ' . $id . ', Find ROW ' . var_export($row, 1));
        if ($row) {
            $deleted = $row->delete();
            if ($deleted) {
                /* throw new \Exception('DELETE ROW BY ID ' . $id . ', Find ROW ' . var_export(
                    compact('row', 'deleted'), 1)
                );
                */
                return response()->json([
                    'success' => true,
                    'id' => $id,
                    'message' => 'Datensatz wurde gelöscht!',
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'id' => $id,
                    'message' => 'Beim Löschen ist ein Fehler aufgetreten!',
                ]);
            }
        }

        return response()->json([
            'success' => false,
            'id' => $id,
            'message' => 'Datensatz wurde nicht gefunden!',
        ]);
    }
}
