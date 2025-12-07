<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Company;
use App\Models\Role;
use App\Models\Invite as Invitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvitationMail;

class InvitationController extends Controller
{
    /**
     * SuperAdmin invites a new Admin to create a new company
     */
    public function inviteAdmin(Request $request)
    {
        // Only SuperAdmin can invite new admins
        if (auth()->user()->role->name !== 'SuperAdmin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $validated = $request->validate([
            'company_name' => 'required|string|max:255|unique:companies,name',
            'company_domain' => 'required|string|max:255|unique:companies,domain',
            'email' => 'required|email',
        ]);

        // Create new company
        $company = Company::create([
            'name' => $validated['company_name'],
            'domain' => $validated['company_domain'],
        ]);

        // Create invitation token and record
        $token = Str::random(64);
        $expires = now()->addDays(7);

        // Hash token before storing
        $tokenHash = hash_hmac('sha256', $token, config('app.key'));

        $invite = Invitation::create([
            'code' => Str::upper(Str::random(8)),
            'token' => null,
            'token_hash' => $tokenHash,
            'email' => $validated['email'],
            'role' => 'Admin',
            'company_id' => $company->id,
            'user_id' => auth()->id(),
            'expires_at' => $expires,
            'used' => false,
        ]);

        // Queue the invitation email
        try {
            Mail::to($invite->email)->queue(new InvitationMail($invite));
        } catch (\Throwable $e) {
            // If mail fails, log and continue — invitation still created
            logger()->error('Failed to queue invitation email: ' . $e->getMessage());
        }

        // If the request expects JSON (API), return JSON, otherwise redirect back with flash
        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Invitation created and email queued',
                'invite' => $invite,
                'registration_link' => url('/register/' . $token),
            ], 201);
        }

        return back()->with('success', 'Invitation created and email queued. Registration link: ' . url('/register/' . $token));
    }

    /**
     * Admin invites a new User (Admin or Member) to their company
     */
    public function inviteUser(Request $request)
    {
        $user = auth()->user();

        // Only Admin (ClientAdmin) can invite users
        if ($user->role->name !== 'Admin' && $user->role->name !== 'ClientAdmin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $validated = $request->validate([
            'email' => 'required|email',
            'role' => 'required|in:Admin,Member',
        ]);

        $token = Str::random(64);
        $expires = now()->addDays(7);
        $tokenHash = hash_hmac('sha256', $token, config('app.key'));

        $invite = Invitation::create([
            'code' => Str::upper(Str::random(8)),
            'token' => null,
            'token_hash' => $tokenHash,
            'email' => $validated['email'],
            'role' => $validated['role'],
            'company_id' => $user->company_id,
            'user_id' => auth()->id(),
            'expires_at' => $expires,
            'used' => false,
        ]);

        // Queue the invitation email
        try {
            Mail::to($invite->email)->queue(new InvitationMail($invite));
        } catch (\Throwable $e) {
            logger()->error('Failed to queue invitation email: ' . $e->getMessage());
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Invitation created and email queued',
                'invite' => $invite,
                'registration_link' => url('/register/' . $token),
            ], 201);
        }

        return back()->with('success', 'Invitation created and email queued. Registration link: ' . url('/register/' . $token));
    }

    /**
     * Show registration form for invitation token
     */
    public function showRegistrationForm($token)
    {
        $tokenHash = hash_hmac('sha256', $token, config('app.key'));

        $invite = Invitation::where('token_hash', $tokenHash)->firstOrFail();

        if ($invite->used) {
            abort(410, 'This invitation has already been used.');
        }

        if ($invite->expires_at && now()->greaterThan($invite->expires_at)) {
            abort(410, 'This invitation has expired.');
        }

        return view('auth.register_invite', ['token' => $token, 'email' => $invite->email]);
    }

    /**
     * Complete registration using token
     */
    public function registerWithToken(Request $request, $token)
    {
        $tokenHash = hash_hmac('sha256', $token, config('app.key'));

        $invite = Invitation::where('token_hash', $tokenHash)->firstOrFail();

        if ($invite->used) {
            return back()->withErrors(['token' => 'Invitation already used']);
        }

        if ($invite->expires_at && now()->greaterThan($invite->expires_at)) {
            return back()->withErrors(['token' => 'Invitation expired']);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Determine role id
        $role = Role::where('name', $invite->role)->first();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $invite->email,
            'password' => Hash::make($validated['password']),
            'role_id' => $role ? $role->id : null,
            'company_id' => $invite->company_id,
        ]);

        // Mark invite used
        $invite->used = true;
        $invite->used_by = $user->id;
        $invite->save();

        // Log the user in
        auth()->login($user);

        return redirect('/dashboard')->with('success', 'Account created successfully');
    }
}
