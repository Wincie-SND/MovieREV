<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AccountVerificationCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserSettingsController extends Controller
{
    public function show(Request $request): View
    {
        return view('users.settings', ['user' => $request->user()]);
    }

    public function showEmailForm(Request $request, AccountVerificationCodeService $codes): View
    {
        return view('users.settings.email', [
            'user' => $request->user(),
            'pendingEmail' => $codes->pendingDestination($request->user(), AccountVerificationCodeService::EMAIL_CHANGE),
        ]);
    }

    public function showPasswordForm(Request $request, AccountVerificationCodeService $codes): View
    {
        return view('users.settings.password', [
            'user' => $request->user(),
            'passwordCodePending' => $codes->hasPendingCode($request->user(), AccountVerificationCodeService::PASSWORD_CHANGE),
        ]);
    }

    public function sendVerificationEmail(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return to_route('settings')->with('status', 'Your email address is already verified.');
        }

        try {
            $user->sendEmailVerificationNotification();
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withErrors(['email_delivery' => 'The verification email could not be sent. Check the Gmail API configuration and try again.']);
        }

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

    public function sendEmailChangeCode(Request $request, AccountVerificationCodeService $codes): RedirectResponse
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

        if (strcasecmp($attributes['email'], $user->email) === 0) {
            throw ValidationException::withMessages([
                'email' => 'Enter a different email address from your current one.',
            ]);
        }

        try {
            $codes->send(
                $user,
                AccountVerificationCodeService::EMAIL_CHANGE,
                $attributes['email'],
                $attributes['email'],
            );
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withInput()->withErrors(['email_delivery' => 'The verification code could not be sent. Check the Gmail API configuration and try again.']);
        }

        return to_route('settings.email.edit')->with('status', 'A verification code was sent to '.$attributes['email'].'.');
    }

    public function updateEmail(Request $request, AccountVerificationCodeService $codes): RedirectResponse
    {
        $attributes = $request->validate([
            'verification_code' => ['required', 'digits:6'],
        ]);

        $user = $request->user();
        $newEmail = $codes->verify($user, AccountVerificationCodeService::EMAIL_CHANGE, $attributes['verification_code']);

        if (! is_string($newEmail)) {
            throw ValidationException::withMessages([
                'verification_code' => 'Request a new email verification code and try again.',
            ]);
        }

        if (User::query()->where('email', $newEmail)->whereKeyNot($user->getKey())->exists()) {
            throw ValidationException::withMessages([
                'verification_code' => 'That email address is already associated with another account. Request a new code for a different address.',
            ]);
        }

        $user->email = $newEmail;
        $user->email_verified_at = now();
        $user->save();

        return to_route('settings')->with('status', 'Email address updated and verified.');
    }

    public function sendPasswordChangeCode(Request $request, AccountVerificationCodeService $codes): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
        ], [
            'current_password.current_password' => 'The current password is incorrect.',
        ]);

        $user = $request->user();
        try {
            $codes->send($user, AccountVerificationCodeService::PASSWORD_CHANGE, $user->email);
        } catch (\Throwable $exception) {
            report($exception);

            return back()->withInput()->withErrors(['email_delivery' => 'The verification code could not be sent. Check the Gmail API configuration and try again.']);
        }

        return to_route('settings.password.edit')->with('status', 'A verification code was sent to '.$user->email.'.');
    }

    public function updatePassword(Request $request, AccountVerificationCodeService $codes): RedirectResponse
    {
        $attributes = $request->validate([
            'verification_code' => ['required', 'digits:6'],
            'password' => ['required', 'string', Password::min(8)->symbols(), 'regex:/[A-Z]/', 'confirmed'],
        ], [
            'password.regex' => 'The new password must include at least one capital letter.',
            'password.symbols' => 'The new password must include at least one special character.',
        ]);

        $codes->verify($request->user(), AccountVerificationCodeService::PASSWORD_CHANGE, $attributes['verification_code']);
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
