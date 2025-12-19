<?php

namespace Tests\Unit\Services;

use App\Services\PaytrService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaytrServiceTest extends TestCase
{
    public function test_make_paytr_token_generates_valid_hash(): void
    {
        config([
            'services.paytr.merchant_id' => 'test_merchant',
            'services.paytr.merchant_salt' => 'test_salt',
            'services.paytr.test_mode' => 1,
        ]);

        $service = new PaytrService();
        
        $token = $service->makePaytrToken(
            'ORD123',
            'test@example.com',
            base64_encode(json_encode([['Product', 100.00, 1]])),
            '10000'
        );

        $this->assertNotEmpty($token);
        $this->assertIsString($token);
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9+\/]+=*$/', $token); // Base64 format
    }

    public function test_verify_callback_validates_correct_signature(): void
    {
        config([
            'services.paytr.merchant_salt' => 'test_salt',
        ]);

        $service = new PaytrService();
        
        $merchantOid = 'ORD123';
        $status = 'success';
        $totalAmount = '10000';
        
        $hashStr = $merchantOid . $status . $totalAmount;
        $calculatedHash = base64_encode(hash_hmac('sha256', $hashStr, 'test_salt', true));

        $payload = [
            'merchant_oid' => $merchantOid,
            'status' => $status,
            'total_amount' => $totalAmount,
            'hash' => $calculatedHash,
        ];

        $result = $service->verifyCallback($payload);
        
        $this->assertTrue($result);
    }

    public function test_verify_callback_rejects_invalid_signature(): void
    {
        config([
            'services.paytr.merchant_salt' => 'test_salt',
        ]);

        $service = new PaytrService();
        
        $payload = [
            'merchant_oid' => 'ORD123',
            'status' => 'success',
            'total_amount' => '10000',
            'hash' => 'invalid_hash',
        ];

        $result = $service->verifyCallback($payload);
        
        $this->assertFalse($result);
    }
}


