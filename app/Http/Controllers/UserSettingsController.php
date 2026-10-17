<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserSettingsController extends Controller
{
    public function show(Request $request): View
    {
        return view('users.settings', ['user' => $request->user()]);
    }

    public function showEmailForm(Request $request): View
    {
        return view('users.settings.email', ['user' => $request->user()]);
    }

    public function showPasswordForm(Request $request): View
    {
        return view('users.settings.password', ['user' => $request->user()]);
    }

    public function sendVerificationEmail(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return to_route('settings')->with('status', 'Your email address is already verified.');
        }

        $user->sendEmailVerificationNotification();

        return to_route('settings')->with('status', 'A verification link was sent to '.$user->email.'.');
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $attributes = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'min:3',
                'max:50',
                'regex:/^[A-Za-z0-9._-]+$/',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ], [
            'username.regex' => 'Usernames may only contain letters, numbers, dots, dashes and underscores.',
            'profile_photo.image' => 'Choose a valid image file.',
            'profile_photo.mimes' => 'Profile photos must be JPG, PNG, or WebP images.',
            'profile_photo.max' => 'Profile photos must be 4 MB or smaller.',
        ]);

        if ($request->hasFile('profile_photo')) {
            $photo = $request->file('profile_photo');
            $extension = match ($photo->getMimeType()) {
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
                default => null,
            };

            if ($extension === null) {
                return back()->withErrors(['profile_photo' => 'Choose a valid JPG, PNG, or WebP image.'])->withInput();
            }

            if (! $photo->storePubliclyAs('profile-photos', $user->id.'.'.$extension, 'public')) {
                return back()->withErrors(['profile_photo' => 'The profile photo could not be saved.'])->withInput();
            }

            foreach (['jpg', 'jpeg', 'png', 'webp'] as $oldExtension) {
                if ($oldExtension !== $extension) {
                    Storage::disk('public')->delete('profile-photos/'.$user->id.'.'.$oldExtension);
                }
            }
        }

        $user->update(Arr::only($attributes, ['name', 'username']));

        return to_route('settings')->with('status', 'Profile updated.');
    }

    public function updateEmail(Request $request): RedirectResponse
    {
        $user = $request->user();

        $attributes = $request->validate([
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'current_password_email' => ['required', 'current_password'],
        ], [
            'current_password_email.current_password' => 'The current password is incorrect.',
        ]);

        $user->email = $attributes['email'];
        $user->email_verified_at = null;
        $user->save();

        return to_route('settings')->with('status', 'Email address updated.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $attributes = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', Password::min(8)->symbols(), 'regex:/[A-Z]/', 'confirmed'],
        ], [
            'current_password.current_password' => 'The current password is incorrect.',
            'password.regex' => 'The new password must include at least one capital letter.',
            'password.symbols' => 'The new password must include at least one special character.',
        ]);

        $request->user()->update(['password' => $attributes['password']]);
        $request->session()->regenerate();

        return to_route('settings')->with('status', 'Password updated.');
    }

    public function destroyPhoto(Request $request): RedirectResponse
    {
        $user = $request->user();
        $disk = Storage::disk('public');

        foreach (['jpg', 'jpeg', 'png', 'webp'] as $extension) {
            $path = 'profile-photos/'.$user->id.'.'.$extension;

            if ($disk->exists($path) && ! $disk->delete($path)) {
                return back()->withErrors(['profile_photo' => 'The profile photo could not be removed.']);
            }
        }

        return to_route('settings')->with('status', 'Profile photo removed.');
    }
}
