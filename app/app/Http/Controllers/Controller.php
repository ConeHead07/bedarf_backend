<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

class Controller extends BaseController
{
    protected $authUser = null;

    public function __construct()
    {
        if (Auth::check()) {
            $this->authUser = Auth::user();
        }
    }

    /**
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function getAuthUser(): \App\User {
        return $this->authUser;
    }

    public function getAuthId(): int {
        return (int)($this->authUser['id'] ?? 0);
    }

    protected function jsonError(string $message, $data = []): JsonResponse {
        $success = false;
        return response()->json(compact('success', 'message', 'data'));
    }

    protected function jsonSuccess($data = [], string $message = null): JsonResponse {
        $success = true;
        if (is_null($message)) {
            $message = '';
        }
        return response()->json(compact('success', 'message', 'data'));
    }
    //
}
