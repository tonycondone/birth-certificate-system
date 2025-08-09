<?php

namespace App\Services;

use Exception;

class RefundService
{
    /**
     * Attempt a refund with the recorded gateway
     *
     * @param array $payment expects keys: id, amount, currency, gateway, reference
     * @param string $reason
     * @return array [success=>bool, message=>string, gateway_refund_id=>string|null]
     */
    public function refund(array $payment, string $reason): array
    {
        $gateway = strtolower($payment['gateway'] ?? '');
        if ($gateway === 'paystack') {
            return $this->refundWithPaystack($payment, $reason);
        }
        // Unsupported gateway
        return [
            'success' => false,
            'message' => 'Unsupported payment gateway for refund',
            'gateway_refund_id' => null,
        ];
    }

    private function refundWithPaystack(array $payment, string $reason): array
    {
        $secret = $_ENV['PAYSTACK_SECRET_KEY'] ?? getenv('PAYSTACK_SECRET_KEY') ?: '';
        if (empty($secret)) {
            return ['success' => false, 'message' => 'Missing PAYSTACK_SECRET_KEY', 'gateway_refund_id' => null];
        }

        $endpoint = 'https://api.paystack.co/refund';
        $payload = [
            // Paystack accepts transaction reference or id; we'll send reference if available
            'transaction' => $payment['reference'] ?? $payment['id'],
            'customer_note' => $reason,
        ];

        try {
            $resp = $this->httpPostJson($endpoint, $payload, [
                'Authorization: Bearer ' . $secret,
                'Content-Type: application/json'
            ]);
            if (!$resp['success']) {
                return ['success' => false, 'message' => $resp['message'], 'gateway_refund_id' => null];
            }
            $body = $resp['body'];
            $refundId = $body['data']['reference'] ?? ($body['data']['id'] ?? null);
            $status = strtolower($body['data']['status'] ?? '');
            if ($refundId && in_array($status, ['success','processed','completed','pending'])) {
                return ['success' => true, 'message' => 'Refund initiated', 'gateway_refund_id' => (string)$refundId];
            }
            return ['success' => false, 'message' => 'Refund not accepted by gateway', 'gateway_refund_id' => null];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage(), 'gateway_refund_id' => null];
        }
    }

    /**
     * Minimal HTTP POST JSON with cURL fallback to streams
     */
    private function httpPostJson(string $url, array $data, array $headers): array
    {
        $json = json_encode($data);
        // Try cURL if available
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($headers, ['Content-Length: ' . strlen($json)]));
            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $err = curl_error($ch);
            curl_close($ch);
            if ($result === false) {
                return ['success' => false, 'message' => 'HTTP error: ' . $err];
            }
            $body = json_decode($result, true);
            return ['success' => $httpCode >= 200 && $httpCode < 300, 'message' => 'ok', 'body' => $body];
        }
        // Fallback to stream
        $opts = [
            'http' => [
                'method' => 'POST',
                'header' => implode("\r\n", array_merge($headers, ['Content-Length: ' . strlen($json)]) ),
                'content' => $json,
                'ignore_errors' => true
            ]
        ];
        $context = stream_context_create($opts);
        $result = file_get_contents($url, false, $context);
        if ($result === false) {
            return ['success' => false, 'message' => 'HTTP request failed'];
        }
        $statusLine = $http_response_header[0] ?? 'HTTP/1.1 500';
        preg_match('#\s(\d{3})\s#', $statusLine, $m);
        $code = isset($m[1]) ? (int)$m[1] : 500;
        $body = json_decode($result, true);
        return ['success' => $code >= 200 && $code < 300, 'message' => 'ok', 'body' => $body];
    }
} 