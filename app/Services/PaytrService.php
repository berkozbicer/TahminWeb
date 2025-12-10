<?php

namespace App\Services;

class PaytrService
{
    protected string $merchantId;
    protected string $merchantKey;
    protected string $merchantSalt;
    protected int $testMode;

    public function __construct()
    {
        $this->merchantId = env('PAYTR_MERCHANT_ID', '');
        $this->merchantKey = env('PAYTR_MERCHANT_KEY', '');
        $this->merchantSalt = env('PAYTR_MERCHANT_SALT', '');
        $this->testMode = (int) env('PAYTR_TEST_MODE', 1);
    }

    /**
     * Request PayTR token to initialize payment.
     * Returns array with status and token or error message.
     */
    public function getToken(array $data): array
    {
        $url = 'https://www.paytr.com/odeme/api/get-token';

        $post = $data;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            $err = curl_error($ch);
            curl_close($ch);
            return ['status' => 'error', 'error' => $err];
        }
        curl_close($ch);

        $res = json_decode($result, true);
        if (!is_array($res)) {
            return ['status' => 'error', 'error' => 'Invalid response from PayTR'];
        }

        return $res;
    }

    /**
     * Build paytr_token according to PayTR spec.
     */
    public function makePaytrToken(string $merchant_oid, string $email, string $user_basket, string $payment_amount, string $no_installment = '0', string $max_installment = '0', string $currency = 'TL'): string
    {
        $str = $this->merchantId . $merchant_oid . $payment_amount . $email . $user_basket . $no_installment . $max_installment . $currency . $this->testMode;
        return base64_encode(hash_hmac('sha256', $str, $this->merchantSalt, true));
    }

    /**
     * Verify PayTR callback hash
     */
    public function verifyCallback(array $payload): bool
    {
        // PayTR returns 'hash' in POST payload
        if (empty($payload['merchant_oid']) || !isset($payload['status']) || !isset($payload['total_amount']) || empty($payload['hash'])) {
            return false;
        }

        $merchant_oid = $payload['merchant_oid'];
        $status = $payload['status'];
        $total_amount = $payload['total_amount'];

        $hash_str = $merchant_oid . $status . $total_amount;
        $calculated = base64_encode(hash_hmac('sha256', $hash_str, $this->merchantSalt, true));

        return hash_equals($calculated, $payload['hash']);
    }
}
