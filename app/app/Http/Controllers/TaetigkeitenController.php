<?php
/**
 * Created by PhpStorm.
 * User: f.barthold
 * Date: 23.06.2021
 * Time: 16:41
 */

namespace App\Http\Controllers;


use App\BereichMABaseData;
use App\Taetigkeiten;

class TaetigkeitenController extends Controller
{
    /** @var BereichMABaseData|null  */
    private $model = null;

    public function __construct()
    {
        $this->model = new Taetigkeiten();
        parent::__construct();
    }

    public function all(int $kid) {
        $rows = $this->model->getAllByKid($kid);
        // throw new \Exception(__LINE__ . ' ' . __FILE__);
        return response()->json([ 'rows' => $rows ]);
    }

    public function get(int $id) {
        $data = $this->model->get($id);
        return response()->json([ 'data' => $data ]);
    }

    public function update(int $id, array $data) {
        $result = BereichMABaseData::find($id)->update($data);
        return response()->json($result);
    }
}
