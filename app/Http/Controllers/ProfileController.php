<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Profile\DeleteAccountRequest;
use App\Http\Requests\ProfileUpdateRequest;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Throwable;

class ProfileController extends Controller
{
    public function __construct(
        protected UserService $userService
    )
    {
    }

    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        try {
            /** @var \App\Models\User $user */
            $user = $request->user();

            $this->userService->updateProfile($user, $request->validated());

            return Redirect::route('profile.edit')->with('status', 'profile-updated');

        } catch (Throwable $e) {
            \Log::error('Profil güncelleme hatası: ' . $e->getMessage(), ['user_id' => $request->user()?->id]);

            return Redirect::route('profile.edit')
                ->with('error', 'Profil güncellenirken bir hata oluştu.');
        }
    }

    public function destroy(DeleteAccountRequest $request): RedirectResponse
    {

        try {
            /** @var \App\Models\User $user */
            $user = $request->user();

            Auth::logout();

            $this->userService->deleteAccount($user);

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return Redirect::to('/');

        } catch (Throwable $e) {
            \Log::error('Hesap silme hatası: ' . $e->getMessage(), ['exception' => $e]);

            return Redirect::route('profile.edit')
                ->with('error', 'Hesap silinirken bir sorun oluştu.');
        }
    }
}
