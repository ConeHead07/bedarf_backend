<?php
/**
 * Created by PhpStorm.
 * User: f.barthold
 * Date: 17.02.2021
 * Time: 17:08
 */

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class BaseController extends Controller
{
    /** @var \Illuminate\Contracts\Auth\Authenticatable|null  */
    protected $authUser = null;
    protected $authUserId = null;

    public function __construct()
    {
        parent::__construct();

        $this->authUser = Auth::user();
        if (!is_null($this->authUser)) {
            $this->authUserId = $this->authUser->getAuthIdentifier();
        }
        $this->getAuthUser();
    }
}
