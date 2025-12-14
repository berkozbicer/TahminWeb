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

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1); // ✅ SSL doğrulaması aktif
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // ✅ Host doğrulaması
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); // ✅ Timeout eklendi
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // ✅ Connection timeout

        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            $err = curl_error($ch);
            curl_close($ch);
            Log::error('PayTR cURL error', ['error' => $err, 'data' => $data]);
            return ['status' => 'error', 'error' => $err];
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            Log::error('PayTR HTTP error', ['http_code' => $httpCode, 'response' => $result]);
            return ['status' => 'error', 'error' => 'HTTP ' . $httpCode];
        }

        $res = json_decode($result, true);

        if (!is_array($res)) {
            Log::error('PayTR invalid JSON response', ['response' => $result]);
            return ['status' => 'error', 'error' => 'Invalid response from PayTR'];
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
