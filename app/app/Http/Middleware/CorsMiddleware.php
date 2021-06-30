<?php namespace App\Http\Middleware;

use Illuminate\Http\Request;

class CorsMiddleware {

    public function handle(Request $request, \Closure $next)
    {
        $origin = $request->header('Origin');
        $headers = [
            'Access-Control-Allow-Origin'      => $origin,
            'Access-Control-Allow-Methods'     => 'POST, GET, OPTIONS, PUT, DELETE',
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Max-Age'           => '86400',
            'Access-Control-Allow-Headers'     => 'Content-Type, Client-App-Version, Authorization, X-Requested-With, Client-Device-Id'
        ];
        $headers['Access-Control-Allow-Headers'].= ',Accept,Access-Control-Allow-Origin'
            . ',Referer,sec-ch-ua,sec-ch-ua-mobile,User-Agent';

        if ($request->isMethod('OPTIONS'))
        {
            // $debug = json_encode(['line'=>__LINE__, 'file'=>__FILE__, 'origin'=>$origin, 'headers'=>$headers]);
            // return response()->('{"method":"OPTIONS"}', 200, $headers);
            // return response('OK' . "\n" . $debug)->withHeaders($headers);
            return response('OK')->withHeaders($headers);
        }

        $response = $next($request);
        foreach($headers as $key => $value)
        {
            $response->header($key, $value);
        }

        return $response;
    }
}
