<?php
/**
 * Created by PhpStorm.
 * User: f.barthold
 * Date: 23.06.2021
 * Time: 16:41
 */

namespace App\Http\Controllers;


use App\InterviewOverview;

class InterviewOverviewController extends Controller
{
    /** @var InterviewOverview|null  */
    private $model = null;

    public function __construct()
    {
        $this->model = new InterviewOverview();
        parent::__construct();
    }

    public function all() {
        $rows = $this->model->getAll();
        // throw new \Exception(__LINE__ . ' ' . __FILE__);
        return response()->json([ 'rows' => $rows ]);
    }

    public function allByKid(int $id) {
        $rows = $this->model->getAllByKid($id);
        // throw new \Exception(__LINE__ . ' ' . __FILE__);
        return response()->json([ 'rows' => $rows ]);
    }

    public function get(int $id) {
        $data = $this->model->get($id);
        return response()->json([ 'data' => $data ]);
    }

    public function update(int $id, array $data) {
        $result = InterviewOverview::find($id)->update($data);
        return response()->json($result);
    }
}
