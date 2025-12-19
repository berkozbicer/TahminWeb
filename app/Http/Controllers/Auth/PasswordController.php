<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Throwable;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        try {
            // 'updatePassword' hata çantası (error bag) ismidir.
            // Blade tarafında $errors->updatePassword->get(...) diye yakalanır.
            $validated = $request->validateWithBag('updatePassword', [
                'current_password' => ['required', 'current_password'],
                'password' => ['required', Password::defaults(), 'confirmed'],
            ]);

            $request->user()->update([
                'password' => Hash::make($validated['password']),
            ]);

            return back()->with('status', 'password-updated');

        } catch (Throwable $e) {
            \Log::error('PasswordController::update error: ' . $e->getMessage(), [
                'user_id' => $request->user()->id,
                'exception' => $e
            ]);

            return back()->with('error', 'Şifre güncellenirken teknik bir hata oluştu.');
        }
    }
}
