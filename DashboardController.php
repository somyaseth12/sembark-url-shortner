<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
   public function index()
    {
        $user = Auth::user();

        if ($user->role->name === 'SuperAdmin') {
            return redirect()->route('superadmin.dashboard');
        }

        if ($user->role->name === 'ClientAdmin') {
            return redirect()->route('clientadmin.dashboard');
        }

        if ($user->role->name === 'Member') {
            return redirect()->route('member.dashboard');
        }

        return abort(403, 'Unauthorized User Role');
    }
}
