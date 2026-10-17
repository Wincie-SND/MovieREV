@extends('layouts.app')

@section('title', 'Settings — MovieREV')

@section('content')
    @include('partials.backdrop')
    @php($profilePhotoUrl = $user->profilePhotoUrl())

<div class="relative min-h-screen text-white [background:linear-gradient(180deg,#0a040f_0%,#13051b_46%,#07040c_100%)]">
    @include('partials.header', ['search' => ''])

    <main class="mx-auto w-full max-w-4xl px-4 pb-16 pt-8 sm:px-6 lg:px-8">
        <div class="mb-8">
            <p class="m-0 text-xs font-bold uppercase tracking-[0.2em] text-red-400">Account</p>
            <h1 class="mt-2 text-3xl font-bold text-white [font-family:'Playfair_Display',Georgia,serif] sm:text-4xl">Settings</h1>
            <p class="mt-2 text-sm text-stone-400">Manage your public profile and account security.</p>
        </div>

        @if (session('status'))
            <p role="status" class="mb-6 rounded-xl border border-green-500/20 bg-green-500/10 px-4 py-3 text-sm text-green-200">{{ session('status') }}</p>
        @endif
        @error('email_delivery')<p role="alert" class="mb-6 rounded-xl border border-red-500/20 bg-red-500/10 px-4 py-3 text-sm text-red-200">{{ $message }}</p>@enderror

        <section aria-labelledby="profile-settings-heading">
            <div class="mb-4">
                <h2 id="profile-settings-heading" class="m-0 text-xl font-bold text-white [font-family:'Playfair_Display',Georgia,serif]">Profile</h2>
                <p class="mt-1 text-sm text-stone-400">Your name, username, and profile picture.</p>
            </div>

            @if ($profilePhotoUrl)
                <form id="remove-profile-photo-form" method="POST" action="{{ route('settings.photo.destroy') }}" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
            @endif

            <form id="profile-settings-form" method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" class="overflow-hidden rounded-xl border border-white/10 bg-[#10020d]/85 shadow-[0_30px_70px_-40px_rgba(0,0,0,0.9)]">
                @csrf
                @method('PUT')

                <div class="flex flex-col gap-7 px-5 pt-5 pb-2 sm:flex-row sm:items-start sm:px-7 sm:pt-7 sm:pb-2">
                    <div class="flex shrink-0 flex-col items-center gap-3 sm:items-start">
                        <label for="profile_photo" class="group relative flex h-28 w-28 cursor-pointer items-center justify-center" aria-label="Choose a profile picture">
                            <span class="relative flex h-full w-full items-center justify-center overflow-hidden rounded-full border-2 border-white/15 bg-gradient-to-br from-fuchsia-800 to-indigo-950 font-bebas text-5xl text-white shadow-lg transition group-hover:border-red-400/70">
                                <img id="profile-photo-preview" @if ($profilePhotoUrl) src="{{ $profilePhotoUrl }}" @endif alt="" class="{{ $profilePhotoUrl ? '' : 'hidden' }} absolute inset-0 h-full w-full object-cover">
                                <span id="profile-photo-initial" class="{{ $profilePhotoUrl ? 'hidden' : '' }}" aria-hidden="true">{{ mb_strtoupper(mb_substr(trim($user->username), 0, 1)) }}</span>
                            </span>
                            <span class="absolute bottom-0 right-0 flex h-9 w-9 items-center justify-center rounded-full border-2 border-[#10020d] bg-red-500 text-white transition group-hover:bg-red-600" aria-hidden="true">
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M10 4a.75.75 0 0 1 .75.75v4.5h4.5a.75.75 0 0 1 0 1.5h-4.5v4.5a.75.75 0 0 1-1.5 0v-4.5h-4.5a.75.75 0 0 1 0-1.5h4.5v-4.5A.75.75 0 0 1 10 4Z" /></svg>
                            </span>
                        </label>
                        <input id="profile_photo" name="profile_photo" type="file" accept="image/jpeg,image/png,image/webp" class="sr-only" aria-describedby="profile-photo-help">
                        <p id="profile-photo-help" class="m-0 text-center text-[11px] text-stone-500 sm:text-left">JPG, PNG, or WebP · up to 4 MB</p>
                        @error('profile_photo')<p role="alert" class="m-0 text-center text-xs text-red-300 sm:text-left">{{ $message }}</p>@enderror

                        <div id="profile-photo-crop" class="hidden w-full max-w-xs rounded-xl border border-white/10 bg-black/20 p-4">
                            <p class="m-0 text-center text-xs font-semibold text-stone-200">Adjust your photo</p>
                            <p class="mt-1 text-center text-[11px] leading-relaxed text-stone-500">Drag the image to choose what appears in your profile circle.</p>
                            <canvas id="profile-photo-canvas" width="240" height="240" tabindex="0" role="img" aria-label="Profile photo crop. Drag to reposition, or use the arrow keys." class="mx-auto mt-3 block h-48 w-48 touch-none cursor-move rounded-full border-2 border-white/20 bg-[#190b1b] shadow-lg focus:outline-none focus:ring-2 focus:ring-red-400"></canvas>
                            <label for="profile-photo-zoom" class="mt-4 flex items-center justify-between text-xs font-semibold text-stone-300">
                                <span>Zoom</span>
                                <output id="profile-photo-zoom-value" for="profile-photo-zoom" class="text-stone-400">100%</output>
                            </label>
                            <input id="profile-photo-zoom" type="range" min="1" max="4" step="0.05" value="1" class="mt-2 w-full accent-red-500" aria-label="Zoom profile photo">
                            <button id="profile-photo-reset" type="button" class="mt-3 block w-full border-0 bg-transparent p-0 text-center text-xs font-semibold text-pink-400 transition-colors hover:text-pink-300">Reset crop</button>
                        </div>
                        <p id="profile-photo-crop-error" class="m-0 hidden text-center text-xs text-red-300" role="alert"></p>
                    </div>

                    <div class="grid min-w-0 flex-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label for="name" class="mb-2 block text-xs font-bold uppercase tracking-[0.12em] text-stone-300">Name</label>
                            <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" autocomplete="name" required class="w-full rounded-lg border border-white/10 bg-black/30 px-4 py-3 text-sm text-white outline-none transition focus:border-red-500/70">
                            @error('name')<p class="mt-2 text-xs text-red-300">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="username" class="mb-2 block text-xs font-bold uppercase tracking-[0.12em] text-stone-300">Username</label>
                            <input id="username" name="username" type="text" value="{{ old('username', $user->username) }}" autocomplete="username" required class="w-full rounded-lg border border-white/10 bg-black/30 px-4 py-3 text-sm text-white outline-none transition focus:border-red-500/70">
                            @error('username')<p class="mt-2 text-xs text-red-300">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-white/10 px-5 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-7">
                    <a href="{{ route('users.show', $user->username) }}" class="text-center text-xs font-semibold text-stone-400 transition-colors hover:text-white">View profile</a>
                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center">
                        @if ($profilePhotoUrl)
                            <button type="submit" form="remove-profile-photo-form" class="border-0 bg-transparent px-2 py-3 text-xs font-semibold text-red-300 transition-colors hover:text-red-200">Remove photo</button>
                        @endif
                        <button type="submit" class="inline-flex items-center justify-center rounded-full border-0 bg-red-500 px-4 py-2 text-xs font-bold uppercase tracking-[0.12em] text-white transition-colors hover:bg-red-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-300 focus-visible:ring-offset-2 focus-visible:ring-offset-[#10020d]">Save profile</button>
                    </div>
                </div>
            </form>
        </section>

        <section class="mt-10" aria-labelledby="security-settings-heading">
            <div class="mb-4">
                <h2 id="security-settings-heading" class="m-0 text-xl font-bold text-white [font-family:'Playfair_Display',Georgia,serif]">Security</h2>
                <p class="mt-1 text-sm text-stone-400">Manage your sign-in details.</p>
            </div>

            <div class="space-y-4">
                <article class="overflow-hidden rounded-xl border border-white/10 bg-[#10020d]/85 shadow-[0_30px_70px_-40px_rgba(0,0,0,0.9)]">
                    <div class="px-5 pt-5 pb-1 sm:px-6">
                        <h3 class="m-0 text-sm font-bold text-white">Password</h3>
                        <p class="mt-1 text-sm text-stone-400">Update the password you use to sign in.</p>
                    </div>
                    <div class="border-t border-white/10 px-5 py-3 sm:px-6">
                        <a href="{{ route('settings.password.edit') }}" class="inline-flex items-center justify-center rounded-full border-0 bg-red-500 px-4 py-2 text-xs font-bold uppercase tracking-[0.12em] text-white transition-colors hover:bg-red-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-300 focus-visible:ring-offset-2 focus-visible:ring-offset-[#10020d]">Change Password</a>
                    </div>
                </article>

                <article class="overflow-hidden rounded-xl border border-white/10 bg-[#10020d]/85 shadow-[0_30px_70px_-40px_rgba(0,0,0,0.9)]">
                    <div class="px-5 pt-5 pb-1 sm:px-6">
                        <h3 class="m-0 text-sm font-bold text-white">Email</h3>
                        <p class="mt-1 text-sm text-stone-400">Update the email address associated with your account.</p>
                    </div>
                    <div class="flex flex-col gap-4 border-t border-white/10 px-5 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                        <div class="flex min-w-0 flex-wrap items-center gap-x-5 gap-y-3">
                            <div class="flex min-w-0 items-center gap-2">
                                <span class="max-w-full truncate text-sm text-stone-200">{{ $user->email }}</span>
                                @if ($user->hasVerifiedEmail())
                                    <span class="inline-flex shrink-0 items-center gap-1.5 text-xs font-semibold text-green-300" aria-label="Email verified">
                                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-green-500/15 text-green-300" aria-hidden="true">✓</span>
                                        <span>Verified</span>
                                    </span>
                                @else
                                    <span class="inline-flex shrink-0 items-center gap-1.5 text-xs font-semibold text-amber-300" aria-label="Email not verified">
                                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-amber-500/15 text-amber-300" aria-hidden="true">!</span>
                                        <span>Not verified</span>
                                    </span>
                                @endif
                            </div>
                            @unless ($user->hasVerifiedEmail())
                            <form method="POST" action="{{ route('verification.send') }}" class="m-0">
                                @csrf
                                <button type="submit" class="inline-flex items-center justify-center rounded-full border-0 bg-red-500 px-4 py-2 text-xs font-bold uppercase tracking-[0.12em] text-white transition-colors hover:bg-red-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-300 focus-visible:ring-offset-2 focus-visible:ring-offset-[#10020d]">Verify Email</button>
                            </form>
                            @endunless
                        </div>
                        <a href="{{ route('settings.email.edit') }}" class="inline-flex items-center justify-center rounded-full border-0 bg-red-500 px-4 py-2 text-xs font-bold uppercase tracking-[0.12em] text-white transition-colors hover:bg-red-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-300 focus-visible:ring-offset-2 focus-visible:ring-offset-[#10020d]">Change Email</a>
                    </div>
                </article>
            </div>
        </section>
    </main>
</div>

<script>
    const profilePhotoInput = document.getElementById('profile_photo');
    const profilePhotoPreview = document.getElementById('profile-photo-preview');
    const profilePhotoInitial = document.getElementById('profile-photo-initial');
    const profilePhotoCanvas = document.getElementById('profile-photo-canvas');
    const profilePhotoCrop = document.getElementById('profile-photo-crop');
    const profilePhotoZoom = document.getElementById('profile-photo-zoom');
    const profilePhotoZoomValue = document.getElementById('profile-photo-zoom-value');
    const profilePhotoCropError = document.getElementById('profile-photo-crop-error');
    const profileSettingsForm = document.getElementById('profile-settings-form');
    const profilePhotoContext = profilePhotoCanvas.getContext('2d');
    const cropSize = profilePhotoCanvas.width;
    let cropImage = null;
    let cropOffsetX = 0;
    let cropOffsetY = 0;
    let activePointer = null;
    let cropPreparing = false;
    let cropSubmissionStarted = false;

    function profilePhotoGeometry() {
        const scale = Math.max(cropSize / cropImage.naturalWidth, cropSize / cropImage.naturalHeight) * Number(profilePhotoZoom.value);
        const imageWidth = cropImage.naturalWidth * scale;
        const imageHeight = cropImage.naturalHeight * scale;
        const centeredX = (cropSize - imageWidth) / 2;
        const centeredY = (cropSize - imageHeight) / 2;
        const imageX = Math.min(0, Math.max(cropSize - imageWidth, centeredX + cropOffsetX));
        const imageY = Math.min(0, Math.max(cropSize - imageHeight, centeredY + cropOffsetY));

        cropOffsetX = imageX - centeredX;
        cropOffsetY = imageY - centeredY;

        return { imageX, imageY, imageWidth, imageHeight };
    }

    function drawProfilePhotoCrop() {
        if (! cropImage) {
            return;
        }

        const { imageX, imageY, imageWidth, imageHeight } = profilePhotoGeometry();

        profilePhotoContext.clearRect(0, 0, cropSize, cropSize);
        profilePhotoContext.drawImage(cropImage, imageX, imageY, imageWidth, imageHeight);
        profilePhotoPreview.src = profilePhotoCanvas.toDataURL('image/jpeg', 0.9);
        profilePhotoPreview.classList.remove('hidden');
        profilePhotoInitial?.classList.add('hidden');
        profilePhotoZoomValue.value = `${Math.round(Number(profilePhotoZoom.value) * 100)}%`;
    }

    profilePhotoInput?.addEventListener('change', () => {
        const [photo] = profilePhotoInput.files ?? [];

        if (! photo) {
            return;
        }

        profilePhotoCropError.classList.add('hidden');
        profilePhotoCropError.textContent = '';

        const imageUrl = URL.createObjectURL(photo);
        const image = new Image();

        image.onload = () => {
            cropImage = image;
            cropOffsetX = 0;
            cropOffsetY = 0;
            profilePhotoZoom.value = '1';
            profilePhotoCrop.classList.remove('hidden');
            drawProfilePhotoCrop();
            URL.revokeObjectURL(imageUrl);
        };

        image.onerror = () => {
            cropImage = null;
            profilePhotoCrop.classList.add('hidden');
            profilePhotoCropError.textContent = 'This image could not be opened. Choose a JPG, PNG, or WebP photo.';
            profilePhotoCropError.classList.remove('hidden');
            URL.revokeObjectURL(imageUrl);
        };

        image.src = imageUrl;
    });

    profilePhotoZoom?.addEventListener('input', drawProfilePhotoCrop);

    document.getElementById('profile-photo-reset')?.addEventListener('click', () => {
        cropOffsetX = 0;
        cropOffsetY = 0;
        profilePhotoZoom.value = '1';
        drawProfilePhotoCrop();
    });

    profilePhotoCanvas?.addEventListener('pointerdown', (event) => {
        if (! cropImage) {
            return;
        }

        activePointer = { id: event.pointerId, x: event.clientX, y: event.clientY };
        profilePhotoCanvas.setPointerCapture(event.pointerId);
        event.preventDefault();
    });

    profilePhotoCanvas?.addEventListener('pointermove', (event) => {
        if (! activePointer || event.pointerId !== activePointer.id) {
            return;
        }

        const bounds = profilePhotoCanvas.getBoundingClientRect();
        cropOffsetX += (event.clientX - activePointer.x) * (cropSize / bounds.width);
        cropOffsetY += (event.clientY - activePointer.y) * (cropSize / bounds.height);
        activePointer.x = event.clientX;
        activePointer.y = event.clientY;
        drawProfilePhotoCrop();
    });

    const stopCropDrag = () => {
        activePointer = null;
    };

    profilePhotoCanvas?.addEventListener('pointerup', stopCropDrag);
    profilePhotoCanvas?.addEventListener('pointercancel', stopCropDrag);
    profilePhotoCanvas?.addEventListener('lostpointercapture', stopCropDrag);
    profilePhotoCanvas?.addEventListener('keydown', (event) => {
        const keySteps = { ArrowLeft: [-10, 0], ArrowRight: [10, 0], ArrowUp: [0, -10], ArrowDown: [0, 10] };

        if (! cropImage || ! keySteps[event.key]) {
            return;
        }

        event.preventDefault();
        const [stepX, stepY] = keySteps[event.key];
        const multiplier = event.shiftKey ? 4 : 1;
        cropOffsetX += stepX * multiplier;
        cropOffsetY += stepY * multiplier;
        drawProfilePhotoCrop();
    });

    profileSettingsForm?.addEventListener('submit', (event) => {
        if (! cropImage || cropPreparing || cropSubmissionStarted) {
            return;
        }

        event.preventDefault();
        cropPreparing = true;

        const outputCanvas = document.createElement('canvas');
        const outputSize = 512;
        const outputContext = outputCanvas.getContext('2d');
        const { imageX, imageY, imageWidth, imageHeight } = profilePhotoGeometry();
        const outputScale = outputSize / cropSize;

        outputCanvas.width = outputSize;
        outputCanvas.height = outputSize;
        outputContext.fillStyle = '#190b1b';
        outputContext.fillRect(0, 0, outputSize, outputSize);
        outputContext.drawImage(cropImage, imageX * outputScale, imageY * outputScale, imageWidth * outputScale, imageHeight * outputScale);
        outputCanvas.toBlob((blob) => {
            if (! blob) {
                cropPreparing = false;
                profilePhotoCropError.textContent = 'The cropped photo could not be prepared. Please try another image.';
                profilePhotoCropError.classList.remove('hidden');
                return;
            }

            const croppedPhoto = new File([blob], 'profile-photo.jpg', { type: 'image/jpeg' });
            const transfer = new DataTransfer();

            transfer.items.add(croppedPhoto);
            profilePhotoInput.files = transfer.files;
            cropPreparing = false;
            cropSubmissionStarted = true;

            if (event.submitter) {
                profileSettingsForm.requestSubmit(event.submitter);
            } else {
                profileSettingsForm.requestSubmit();
            }
        }, 'image/jpeg', 0.92);
    });
</script>
@endsection
