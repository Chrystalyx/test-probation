<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    public function index()
    {
        return view('auth.login');
    }
}
