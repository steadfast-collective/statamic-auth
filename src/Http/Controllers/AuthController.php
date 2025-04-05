<?php

namespace SteadfastCollective\StatamicAuth\Http\Controllers;

use Statamic\Auth\User;
use Statamic\View\View;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use SteadfastCollective\StatamicAuth\Http\Requests\LoginRequest;

class AuthController extends Controller
{
    public $layout;
    
    public function __construct() {
        $this->layout = config('statamic-auth.layout', 'layout');
    }
}