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
use App\User;
use Validator;
use Illuminate\Http\Request;

class UserController extends Controller
{

    public function index(Request $request, $uid)
    {
        $uid = +$uid;
        $userModel = new User();
        $tplVars = (object)[];
        $tplVars->user = User::find($uid);
        $tplVars->inventurenListen = $userModel->getActiveInventurenByUID(+$uid);

        // return response()->json( ['uid' => $uid, 'inventurenListen' => $inventurenListen]);


        return view('admin.user.index', compact('tplVars'));
    }

    public function listUsers(Request $request)
    {
        $tplVars = new \stdClass();
        $tplVars->aRows = User::all()->toArray();

        $viewEngine = $request->input('viewEngine', 'blade');

        if ($viewEngine === 'blade') {
            return view('admin.user.listUsers-blade', compact('tplVars'));
        }
        return view('admin.user.listUsers', compact('tplVars'));
    }

    public function checkUser(Request $request) {
        $name = $request->post('name', '');
        $email = $request->post('email', '');

        if (0) return response()->json([
            'success' => true,
            'isValid' => true,
        ]);

        $userModel = new User();
        $validation = $userModel->checkAddUserEmail($name, $email, 0);

        return response()->json($validation);
    }

    public function create(Request $request) {

        //validate incoming request
        $validator = $this->validate($request, [
            'name' => 'required|string|unique:users',
            'email' => 'required|email|unique:users',
            'pw' => 'required',
        ]);


        $userData = [];
        $userData['name'] = $request->post('name', '');
        $userData['email'] = $request->post('email', '');
        $userData['password'] = $request->post('pw', '');
        $userData['IstAdmin'] = $request->post('IstAdmin', 0);
        $userModel = new User();
        $result = $userModel->createUser($userData);
        return response()->json($result);
    }

    public function update(Request $request, int $id) {
        $data = $request->only('id', 'name', 'IstAdmin', 'email', 'password');
        $v = Validator::make($data, [
            'name' => 'sometimes|string|unique:users,' . $id,
            'email' => 'sometimes|email|unique:users,' . $id,
        ]);
        $userModel = new User();
        $result = $userModel->updateUser($id, $data);
        return response()->json($result);
    }

    public function delete(Request $request, int $id) {
        $userModel = new User();
        $result = $userModel->deleteUser($id);
        return response()->json($result);
    }
}
