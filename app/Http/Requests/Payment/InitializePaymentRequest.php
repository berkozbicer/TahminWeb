<?php

declare(strict_types=1);

namespace App\Http\Requests\Payment;

use App\Models\SubscriptionPlan;
use Illuminate\Foundation\Http\FormRequest;

class InitializePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (!$user || !$user->hasVerifiedEmail()) {
            return false;
        }

        return true;
    }

    public function rules(): array
    {
        return [
        ];
    }

    protected function failedAuthorization()
    {
        parent::failedAuthorization();
    }
}
