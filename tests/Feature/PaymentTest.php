<?php

namespace Tests\Feature;

use App\Models\PaymentLog;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_initialize_payment(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $plan = SubscriptionPlan::factory()->create([
            'is_active' => true,
            'price' => 100.00,
        ]);

        $response = $this->actingAs($user)
            ->post(route('paytr.initialize', $plan));

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('payment_logs', [
            'user_id' => $user->id,
            'status' => PaymentLog::STATUS_PENDING,
            'amount' => $plan->price,
        ]);
    }

    public function test_unverified_user_cannot_initialize_payment(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $plan = SubscriptionPlan::factory()->create([
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)
            ->post(route('paytr.initialize', $plan));

        $response->assertRedirect(route('verification.notice'));
    }

    public function test_inactive_plan_cannot_be_purchased(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $plan = SubscriptionPlan::factory()->create([
            'is_active' => false,
        ]);

        $response = $this->actingAs($user)
            ->post(route('paytr.initialize', $plan));

        $response->assertRedirect(route('subscriptions.index'));
        $response->assertSessionHas('error');
    }
}


