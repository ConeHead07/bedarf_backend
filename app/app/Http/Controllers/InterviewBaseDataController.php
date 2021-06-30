<?php
/**
 * Created by PhpStorm.
 * User: f.barthold
 * Date: 23.06.2021
 * Time: 16:41
 */

namespace App\Http\Controllers;


use App\BereichMABaseData;

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

    public function update(int $id, array $data) {
        $result = BereichMABaseData::find($id)->update($data);
        return response()->json($result);
    }
}
