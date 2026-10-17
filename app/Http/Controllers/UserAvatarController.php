<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class UserAvatarController extends Controller
{
    public function __invoke(User $user): BinaryFileResponse
    {
        $path = $user->profilePhotoPath();

        abort_if($path === null, 404);

        return response()->file(Storage::disk('public')->path($path), [
            'Cache-Control' => 'no-store',
        ]);
    }
}
