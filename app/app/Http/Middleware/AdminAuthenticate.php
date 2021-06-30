<?php

namespace App\Http\Middleware;

use App\Providers\CookieAuthService;
use App\User;
use Closure;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;


class AdminAuthenticate
{

    /**
     * Create a new middleware instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $method = __METHOD__;
        $token = $request->cookie('Access-Token', '');
        $tokenType = $request->cookie('Token-Type', '');

        $authResult = CookieAuthService::authByToken($token);
        // throw new \Exception('#38 AdminAuthenticate authResult: ' . print_r($authResult, 1));
        if ($tokenType !== 'bearer' || empty($authResult['success'])) {
            $expectedContentType = $request->header('Accept', 'text/html');
            $aExpectedTypes = explode(',', $expectedContentType);

            $response = new Response();

            $response->setStatusCode(401, 'Unauthorized'); // utf8_decode('Sie müssen sich neu einloggen'));
            $line = __LINE__;
            // return response()->json( compact('line', 'method', 'token', 'tokenType', 'authResult' ));

            if (in_array('application/json', $aExpectedTypes) || in_array('text/javascript', $aExpectedTypes)) {
                return response()
                    ->json([
                        'success' => false,
                        'statusCode' => 401,
                        'statusText' => 'Unauthorized',
                        'message' => 'Sie müssen sich neu einloggen. Ihre Sitzung ist abgelaufen.',
                        'redirect' => $request->url()
                    ], 401,
                        [
                            'Content-Type' => 'application/json; charset=UTF-8'
                        ])
                    ->setStatusCode(401, 'Unauthorized');
            }
            if (1 || in_array('text/html', $aExpectedTypes)) {
                $tplVars = [
                    'redirectUrl' => $request->url(),
                ];
                return view('admin.login.login', compact('tplVars'));
            }
            // return response()->json( compact('expectedContentType'));
            return redirect('api/admin/login', 302);
        }
        $uid = $authResult['data']->id;
        $user = User::find($uid);
        Auth::login($user);

        return $next($request);
    }
}
