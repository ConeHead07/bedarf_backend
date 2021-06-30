<?php
/**
 * Created by PhpStorm.
 * User: f.barthold
 * Date: 31.08.2020
 * Time: 18:00
 */

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Providers\CookieAuthService;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('adminAuth', ['except' => ['login', 'auth', 'logout']]);
    }

    public function login(Request $request)
    {
        $tplVars = [];

        return view('admin.login.login', compact('tplVars'));
    }

    public function logout(Request $request) {
        $token = $request->cookie('Access-Token', '');
        !empty($token) && CookieAuthService::deleteByToken($token);

        setcookie('Access-Token', '', 0, '/api/admin');
        setcookie('Token-Type', '', 0, '/api/admin');

        $tplVars = [];
        return view('admin.login.login', compact('tplVars'));
    }

    public function authDebug(Request $request)
    {
        return response()->json(['auth' => 'debug']);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function auth(Request $request)
    {
        $json = $request->getContent();
        $authParams = json_decode($json);
        $credentials = [
            'email' => null,
            'password' => null,
        ];

        if (!empty($authParams->email) ){
            $credentials['email'] = $authParams->email;
        }
        if (!empty($authParams->password) ) {
            $credentials['password'] = $authParams->password;
        }

        $lifetime = 3600;
        $authResult = CookieAuthService::authByCredentials($credentials['email'], $credentials['password'], $lifetime);

        if (!$authResult['success']) {
            return response()->json([
                'error' => 'Unauthorized'
            ], 401);
        }
        $token = $authResult['token'];

        setcookie('Access-Token', $token, time() + $lifetime, '/api/admin');
        setcookie('Token-Type', 'bearer', time() + $lifetime, '/api/admin');

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $lifetime,
            'authResult' => $authResult,
        ]);
    }


}
