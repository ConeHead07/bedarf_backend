<?php

namespace App\Http\Controllers;

use App\Devices;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     * Test 1.2
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api', ['except' => ['login', 'register', 'heartbeat']]);
    }

    public function register(Request $request)
    {
        //validate incoming request
        $this->validate($request, [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => app('hash')->make($request->password, ['rounds' => 12]),
        ]);

        $token = Auth::login($user);

        return $this->respondWithToken($token);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->only(['email', 'password']);
        if (!$token = Auth::attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $clientDeviceId = (int)$request->input('clientDeviceId', '0');

        if (!$clientDeviceId) {
            $clientDeviceId = Devices::createClientIdentity('', $request->header('User-Agent'));
        }

        return $this->respondWithToken($token, $clientDeviceId);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable|null $user */
        $user = Auth::user();
        if ($user) {
            $userId = $user->getAuthIdentifier();
        }

        return response()->json(Auth::user());
    }

    /**
     * Get simple Ping.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function heartbeat()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable|null $user */
        $user = Auth::user();
        $isConnected = !!$user;

        return response()->json([
            'status' => 'OK',
            'connected' => $isConnected,
        ]);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        Auth::logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token, $clientDeviceId = 0)
    {

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60,
//            'included_files' => get_included_files(),
            'auth_identifier' => Auth::user()->getAuthIdentifier(),
            'auth_identifiername' => Auth::user()->getAuthIdentifierName(),
            'clientDeviceId' => $clientDeviceId,
        ]);
    }
}
