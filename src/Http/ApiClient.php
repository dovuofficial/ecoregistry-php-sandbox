<?php

declare(strict_types=1);

namespace Ecoregistry\Http;

final class ApiClient
{
    private string $baseUrl;
    private ?string $apiSecret;

    public function __construct(string $baseUrl, ?string $apiSecret = null)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->apiSecret = $apiSecret;
    }

    public function request(
        string $method,
        string $path,
        array $query = [],
        ?array $body = null,
        array $headers = []
    ): array {
        $url = $this->baseUrl . '/' . ltrim($path, '/');
        if ($query) {
            $url .= '?' . http_build_query($query);
        }

        $curl = curl_init($url);
        if ($curl == false) {
            throw new \RuntimeException('Unable to initialize cURL.');
        }

        $normalizedMethod = strtoupper($method);
        $payload = $body === null ? null : json_encode($body, JSON_THROW_ON_ERROR);
        $requestHeaders = array_merge([
            'Accept: application/json',
        ], $headers);

        if ($payload !== null) {
            $requestHeaders[] = 'Content-Type: application/json';
            $requestHeaders[] = 'Content-Length: ' . strlen($payload);
        }

        if ($this->apiSecret) {
            $requestHeaders[] = 'Authorization: Bearer ' . $this->apiSecret;
        }

        curl_setopt_array($curl, [
            CURLOPT_CUSTOMREQUEST => $normalizedMethod,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $requestHeaders,
            CURLOPT_TIMEOUT => 30,
        ]);

        if ($payload !== null) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
        }

        $responseBody = curl_exec($curl);
        if ($responseBody === false) {
            $error = curl_error($curl);
            curl_close($curl);
            throw new \RuntimeException('Request failed: ' . $error);
        }

        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        $decoded = json_decode($responseBody, true);
        $data = json_last_error() === JSON_ERROR_NONE ? $decoded : $responseBody;

        return [
            'status' => $status,
            'data' => $data,
        ];
    }
}
