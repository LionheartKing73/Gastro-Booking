<?php

namespace App\Http\Controllers;

use Dingo\Api\Routing\Helpers;
use Illuminate\Http\Request;

use App\Http\Requests;

class CustomAuthController extends Controller
{
    use Helpers;

    public function __construct()
    {
        $this->middleware('api.auth');
    }
    public function logout()
    {
        $user = $this->auth->setUser(null);
        return $this->auth->getUser();

    }

    public function getCurrentUser()
    {
        return app('Dingo\Api\Auth\Auth')->getUser();
    }
}
