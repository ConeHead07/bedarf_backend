<?php

namespace App\Http\Controllers;

class PingController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api', ['except' => [
            'get',
        ]]);
    }

    public function get()
    {
        return response()->json(['status' => 'OK']);
    }
}
