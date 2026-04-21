<?php
function apiBaseUrl(): string
{
    $env = getenv('QUOTE_API_BASE');
    return $env ?: 'http://localhost:3000/api/quotes';
}

function apiRequest(string $method, string $path, ?array $payload = null): array
{
    $url = rtrim(apiBaseUrl(), '/') . $path;
    $headers = "Content-Type: application/json\r\n";
    $options = [
        'http' => [
            'method' => $method,
            'header' => $headers,
            'ignore_errors' => true
        ]
    ];

    if ($payload !== null) {
        $options['http']['content'] = json_encode($payload);
    }

    $context = stream_context_create($options);
    $response = @file_get_contents($url, false, $context);
    $statusCode = 0;
    $responseHeaders = [];

    if (function_exists('http_get_last_response_headers')) {
        $responseHeaders = http_get_last_response_headers() ?: [];
    } elseif (isset($http_response_header) && is_array($http_response_header)) {
        // Backward compatibility for older PHP versions.
        $responseHeaders = $http_response_header;
    }

    if (!empty($responseHeaders[0]) && preg_match('/\s(\d{3})\s/', $responseHeaders[0], $matches)) {
        $statusCode = (int)$matches[1];
    }

    $decoded = [];
    if ($response !== false && $response !== '') {
        $parsed = json_decode($response, true);
        if (is_array($parsed)) {
            $decoded = $parsed;
        }
    }

    return ['status' => $statusCode, 'data' => $decoded];
}
?>
