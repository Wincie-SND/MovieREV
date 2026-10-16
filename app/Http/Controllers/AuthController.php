<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLogin(): View
    {
        return view('login');
    }

    /**
     * Authenticate a user by username or email.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'identifier' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        // Let people log in with whichever they remember: if the input
        // parses as an email we match on `email`, otherwise on `username`.
        $field = filter_var($credentials['identifier'], FILTER_VALIDATE_EMAIL) !== false
            ? 'email'
            : 'username';

        // Honour the "Remember me" checkbox: ticked issues the long-lived
        // recaller cookie, unticked leaves the login session-only.
        $remember = $request->boolean('remember');

        if (! Auth::attempt(
            [$field => $credentials['identifier'], 'password' => $credentials['password']],
            $remember,
        )) {
            throw ValidationException::withMessages([
                'identifier' => 'These credentials do not match our records.',
            ]);
        }

        // An unticked "Remember me" must not inherit a persistent login from an
        // earlier ticked one. The recaller cookie is only cleared by a logout,
        // and a still-valid one silently signs the user back in once this
        // session ends - exactly the "I unchecked it and I am still logged in"
        // report. Tombstone it here so the unchecked login is truly
        // session-only. `Auth::attempt(..., false)` does not do this itself.
        if (! $remember) {
            Cookie::queue(Cookie::forget(Auth::guard('web')->getRecallerName()));
        }

        $request->session()->regenerate();

        if ($remember) {
            $request->session()->forget('session_only_login');
        } else {
            $request->session()->put('session_only_login', true);
        }

        if (Auth::user()->usertype === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->intended('/');
    }

    /**
     * Show the registration form.
     */
    public function showRegister(): View
    {
        return view('register');
    }

    /**
     * Create a new user and sign them in.
     */
    public function register(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'min:3',
                'max:50',
                'regex:/^[A-Za-z0-9._-]+$/',
                'unique:users,username',
            ],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'username.regex' => 'Usernames may only contain letters, numbers, dots, dashes and underscores.',
        ]);

        // The `hashed` cast encrypts the password, and `usertype` falls back
        // to the column default ('user') since we never pass it here.
        $user = User::create($credentials);

        // The register form has no "Remember me" option, so sign the new user
        // in for this session only. Passing `true` here would hand out a
        // persistent recaller cookie they never opted into.
        Auth::login($user);

        $request->session()->regenerate();
        $request->session()->put('session_only_login', true);

        return redirect('/')->with('status', 'Successfully created an account');
    }

    /**
     * Sign the current user out.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
