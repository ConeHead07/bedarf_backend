<?php
/**
 * Created by PhpStorm.
 * User: f.barthold
 * Date: 16.09.2020
 * Time: 13:39
 */

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\InventurenUser;
use App\Mandanten;
use App\User;
use Illuminate\Validation\ValidationException;
use Validator;
use Illuminate\Http\Request;

class MandantenController extends Controller
{

    public function get(Request $request, $mid)
    {
        $mid = +$mid;
        $tplVars = (object)[];
        $tplVars->mandant = Mandanten::find($mid);

        return view('admin.mandanten.get', compact('tplVars'));
    }

    public function index(Request $request)
    {
        $mandantenModel = new Mandanten();
        $tplVars = new \stdClass();
        $tplVars->aRows = $mandantenModel->getListWithInvGebStat();

        return view('admin.mandanten.index', compact('tplVars'));
    }

    public function checkMandant(Request $request) {
        try {
            $aResults = $this->validate($request, [
                'Mandant' => 'required|string|unique:Mandanten',
            ]);
        } catch (ValidationException $e) {
            $message = $e->getMessage() . "\n";
            $errors = $e->errors();
            if (is_array($errors) && count($errors) > 0) {
                foreach($errors as $_fld => $_err) {
                     $message.= "$_fld: " . implode(' ', $_err) . "\n";
                }
            }

            return response()->json([
                'success' => false,
                'errorFields' => 'Mandant',
                'message' => trim($message),
                'errors' => $e->errors(),
                'errorsType' => gettype($e->errors()),
                'isValid' => false,
            ]);
        }
        return response()->json([
            'success' => true,
            'isValid' => true,
        ]);
    }

    public function create(Request $request) {

        //validate incoming request
        $validator = $this->validate($request, [
            'Mandant' => 'required|string|unique:Mandanten',
        ]);


        $userData = [];
        $userData['Mandant'] = $request->post('Mandant', '');
        $mandantenModel = new Mandanten();
        $result = $mandantenModel->create($userData);
        return response()->json($result);
    }

    public function update(Request $request, int $id) {
        $data = $request->only('Mandant');

        $v = Validator::make($data, [
            'Mandant' => 'sometimes|string|unique:Mandanten,' . $id,
        ]);

        $mandantenModel = Mandanten::find($id);
        $success = $mandantenModel->update($data);
        $savedData = $mandantenModel->toArray();
        return response()->json([
            'success' => $success,
            'data' => $savedData,
            'id' => $id
        ]);
    }

    public function delete(Request $request, int $id) {
        $mandantenModel = new Mandanten();
        $iNumInventuren = $mandantenModel->getNumInventuren($id);
        if ($iNumInventuren === 0) {
            return response()->json(['success' => true]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Mandant kann nicht gelöscht werden! Es bestehen ' . $iNumInventuren . ' Inventuren.']);
        }
        // $result = $mandantenModel->delete($id);
        // return response()->json($result);
    }

    public function getInventurenList(Request $request, int $id) {
        /** @var  Mandanten $mandant */
        $mandant = Mandanten::find($id);

        $rows = $mandant->getInventurenList();
        return response()->json([
            'success' => true,
            'rows' => $rows,
        ]);
    }

    public function getGebaeudeList(Request $request, int $id) {
        /** @var  Mandanten $mandant */
        $mandant = Mandanten::find($id);

        $rows = $mandant->getGebaeudeList();
        return response()->json([
            'success' => true,
            'rows' => $rows,
        ]);
    }
}
