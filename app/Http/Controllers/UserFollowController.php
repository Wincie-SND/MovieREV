<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserFollowController extends Controller
{
    public function store(Request $request, User $user): RedirectResponse
    {
        abort_if($request->user()->is($user), 403, 'You cannot follow your own account.');

        $request->user()->following()->syncWithoutDetaching([$user->id]);

        return to_route('users.show', $user->username)->with('status', 'You are now following @'.$user->username.'.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_if($request->user()->is($user), 403, 'You cannot unfollow your own account.');

        $request->user()->following()->detach($user->id);

        return to_route('users.show', $user->username)->with('status', 'You unfollowed @'.$user->username.'.');
    }
}
