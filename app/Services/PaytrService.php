<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaytrService
{
    protected string $merchantId;
    protected string $merchantKey;
    protected string $merchantSalt;
    protected bool $testMode;
    protected bool $debugOn;

    public function __construct()
    {
        $this->merchantId = config('services.paytr.merchant_id');
        $this->merchantKey = config('services.paytr.merchant_key');
        $this->merchantSalt = config('services.paytr.merchant_salt');

        // Boşsa varsayılanları ata
        $this->testMode = (bool)config('services.paytr.test_mode', true);
        $this->debugOn = (bool)config('services.paytr.debug', false);
    }

    /**
     * PayTR'dan iframe token alır.
     */
    public function requestToken(array $data): array
    {
        // --- BYPASS BAŞLANGIÇ (TEST İÇİN) ---
        // Eğer config dosyasında anahtarlar yoksa (veya 'test' ise) sahte token dön
        // Bu sayede sistem "Token Alınamadı" hatası verip patlamaz, en azından akışı test edersiniz.
        if (empty($this->merchantId) || $this->merchantId === 'test_merchant_id') {
            Log::info('PayTR Bypass: Anahtarlar eksik olduğu için sahte token dönüldü.');
            return [
                'token' => 'test_token_bypass_' . uniqid(),
                'status' => 'success'
            ];
        }
        // --- BYPASS BİTİŞ ---

        $this->validateTokenParams($data);

        $timeoutLimit = "30";
        $noInstallment = "0"; // Taksit yok
        $maxInstallment = "0";
        $currency = "TL";

        $testModeStr = $this->testMode ? "1" : "0";
        $debugOnStr = $this->debugOn ? "1" : "0";

        // Hash oluşturma sırası çok önemlidir, dokümantasyona sadık kalınmalı.
        $hashStr = $this->merchantId .
            $data['user_ip'] .
            $data['merchant_oid'] .
            $data['email'] .
            $data['payment_amount'] .
            $data['user_basket'] .
            $noInstallment .
            $maxInstallment .
            $currency .
            $testModeStr;

        $paytrToken = base64_encode(hash_hmac('sha256', $hashStr . $this->merchantSalt, $this->merchantKey, true));

        $postData = [
            'merchant_id' => $this->merchantId,
            'user_ip' => $data['user_ip'],
            'merchant_oid' => $data['merchant_oid'],
            'email' => $data['email'],
            'payment_amount' => $data['payment_amount'],
            'paytr_token' => $paytrToken,
            'user_basket' => $data['user_basket'],
            'debug_on' => $debugOnStr,
            'no_installment' => $noInstallment,
            'max_installment' => $maxInstallment,
            'user_name' => $data['user_name'] ?? '',
            'user_address' => $data['user_address'] ?? '',
            'user_phone' => $data['user_phone'] ?? '',
            'merchant_ok_url' => route('subscriptions.index'), // Başarılı dönüş URL
            'merchant_fail_url' => route('subscriptions.index'), // Hatalı dönüş URL
            'timeout_limit' => $timeoutLimit,
            'currency' => $currency,
            'test_mode' => $testModeStr,
        ];

        try {
            $response = Http::asForm()->post('https://www.paytr.com/odeme/api/get-token', $postData);
            $result = $response->json();

            if (!isset($result['status']) || $result['status'] === 'failed') {
                Log::error('PayTR Token Hatası', ['response' => $result, 'oid' => $data['merchant_oid']]);
                throw new Exception('PayTR Servis Hatası: ' . ($result['reason'] ?? 'Bilinmeyen Hata'));
            }

            return [
                'token' => $result['token'],
                'status' => 'success'
            ];

        } catch (\Throwable $e) {
            Log::error('PayTR Bağlantı Hatası: ' . $e->getMessage());
            throw new Exception('Ödeme servisine bağlanılamadı.');
        }
    }

    public function verifyCallback(array $params): bool
    {
        if (!isset($params['merchant_oid'], $params['status'], $params['total_amount'], $params['hash'])) {
            return false;
        }

        $hashStr = $params['merchant_oid'] . $this->merchantSalt . $params['status'] . $params['total_amount'];
        $generatedHash = base64_encode(hash_hmac('sha256', $hashStr, $this->merchantKey, true));

        return hash_equals($generatedHash, $params['hash']);
    }

    private function validateTokenParams(array $data): void
    {
        $required = ['merchant_oid', 'email', 'payment_amount', 'user_basket', 'user_ip'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("PayTR için zorunlu alan eksik: {$field}");
            }
        }
    }
}
