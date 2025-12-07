<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Invite;


class InviteController extends Controller
{
    public function __construct()
{
    $this->middleware('auth');
}

    public function index()
    {
        $invites = Invite::orderBy('created_at', 'desc')->get();
        return view('invites.index', compact('invites'));
    }

    public function generate(Request $request)
{
    $invite = Invite::create([
        'code' => Str::upper(Str::random(8)),
        'user_id' => auth()->id(), // optional: if logged-in user sends invite
    ]);

    return redirect()->route('invites.index')
                     ->with('success', 'Invite code generated: ' . $invite->code);
}
}
