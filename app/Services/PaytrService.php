<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class PaytrService
{
    protected string $merchantId;
    protected string $merchantKey;
    protected string $merchantSalt;
    protected int $testMode;

    public function __construct()
    {
        $this->merchantId = config('services.paytr.merchant_id', '');
        $this->merchantKey = config('services.paytr.merchant_key', '');
        $this->merchantSalt = config('services.paytr.merchant_salt', '');
        $this->testMode = (int)config('services.paytr.test_mode', 1);
    }

    /**
     * Request PayTR token to initialize payment.
     * Returns array with status and token or error message.
     */
    public function getToken(array $data): array
    {
        $url = 'https://www.paytr.com/odeme/api/get-token';

        // Validate required fields
        $required = ['merchant_id', 'user_ip', 'merchant_oid', 'email', 'payment_amount', 'paytr_token', 'user_basket'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                Log::error('PayTR getToken: missing required field', ['field' => $field, 'data_keys' => array_keys($data)]);
                return ['status' => 'error', 'error' => "Missing required field: {$field}"];
            }
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_SSL_VERIFYPEER => true, // ✅ SSL doğrulaması aktif
            CURLOPT_SSL_VERIFYHOST => 2, // ✅ Host doğrulaması
            CURLOPT_TIMEOUT => 30, // ✅ Timeout eklendi
            CURLOPT_CONNECTTIMEOUT => 10, // ✅ Connection timeout
            CURLOPT_FOLLOWLOCATION => false, // Güvenlik için
            CURLOPT_MAXREDIRS => 0, // Güvenlik için
        ]);

        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            $err = curl_error($ch);
            $errno = curl_errno($ch);
            curl_close($ch);
            Log::error('PayTR cURL error', [
                'error' => $err,
                'errno' => $errno,
                'url' => $url,
                'data_keys' => array_keys($data), // Hassas verileri loglamıyoruz
            ]);
            return ['status' => 'error', 'error' => $err];
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            Log::error('PayTR HTTP error', [
                'http_code' => $httpCode,
                'response_preview' => substr($result, 0, 200), // İlk 200 karakter
            ]);
            return ['status' => 'error', 'error' => 'HTTP ' . $httpCode];
        }

        $res = json_decode($result, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('PayTR invalid JSON response', [
                'json_error' => json_last_error_msg(),
                'response_preview' => substr($result, 0, 200),
            ]);
            return ['status' => 'error', 'error' => 'Invalid JSON response from PayTR'];
        }

        if (!is_array($res)) {
            Log::error('PayTR response is not array', ['response' => $res]);
            return ['status' => 'error', 'error' => 'Invalid response format from PayTR'];
        }

        return $res;
    }

    /**
     * Build paytr_token according to PayTR spec.
     */
    public function makePaytrToken(
        string $merchant_oid,
        string $email,
        string $user_basket,
        string $payment_amount,
        string $no_installment = '0',
        string $max_installment = '0',
        string $currency = 'TL'
    ): string
    {
        // ✅ PayTR dokümantasyonuna göre doğru sıralama
        $str = $this->merchantId
            . $merchant_oid
            . $payment_amount
            . $email
            . $user_basket
            . $no_installment
            . $max_installment
            . $currency
            . $this->testMode;

        return base64_encode(hash_hmac('sha256', $str, $this->merchantSalt, true));
    }

    /**
     * Verify PayTR callback hash
     */
    public function verifyCallback(array $payload): bool
    {
        // PayTR returns 'hash' in POST payload
        if (empty($payload['merchant_oid'])
            || !isset($payload['status'])
            || !isset($payload['total_amount'])
            || empty($payload['hash'])) {
            Log::warning('PayTR callback missing required fields', ['payload_keys' => array_keys($payload)]);
            return false;
        }

        $merchant_oid = $payload['merchant_oid'];
        $status = $payload['status'];
        $total_amount = $payload['total_amount'];

        // ✅ PayTR dokümantasyonuna göre hash kontrolü
        $hash_str = $merchant_oid . $status . $total_amount;
        $calculated = base64_encode(hash_hmac('sha256', $hash_str, $this->merchantSalt, true));

        $isValid = hash_equals($calculated, $payload['hash']);

        if (!$isValid) {
            Log::warning('PayTR callback hash mismatch', [
                'expected' => $calculated,
                'received' => $payload['hash'],
                'merchant_oid' => $merchant_oid,
            ]);
        }

        return $isValid;
    }
}
