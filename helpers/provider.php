<?php
require_once __DIR__ . '/../config/provider.php';

function sendOrderToProvider(array $data)
{
    $payload = [
        'key'      => PROVIDER_API_KEY,
        'action'   => 'add',
        'service'  => $data['service_id'],
        'link'     => $data['link'],
        'quantity' => $data['quantity'],
    ];

    $ch = curl_init(PROVIDER_API_URL);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query($payload),
        CURLOPT_TIMEOUT        => 20,
    ]);

    $response = curl_exec($ch);
    $error    = curl_error($ch);
    curl_close($ch);

    if ($error) {
        return ['success' => false, 'error' => $error];
    }

    $json = json_decode($response, true);

    if (!isset($json['order'])) {
        return ['success' => false, 'error' => $response];
    }

    return [
        'success'      => true,
        'provider_id'  => $json['order'],
        'raw_response' => $response
    ];
}