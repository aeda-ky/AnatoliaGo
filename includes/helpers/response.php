<?php
// includes/helpers/response.php

// Check if we're in production or development
define('IS_PRODUCTION', !in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1', 'localhost:80', 'localhost:8080']));

function respond_json($statusCode, $payload)
{
    if (ob_get_length() !== false && ob_get_length() > 0) {
        ob_clean();
    }
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');

    // Only allow CORS from same origin
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        $allowed_origins = [
            'http://localhost',
            'http://127.0.0.1',
            $_SERVER['HTTP_HOST']
        ];
        if (in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
            header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
        }
    }

    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function respond_success($data = null, $message = 'OK', $meta = null)
{
    $payload = [
        'status' => 'success',
        'message' => $message,
        'data' => $data
    ];

    if ($meta !== null) {
        $payload['meta'] = $meta;
    }

    respond_json(200, $payload);
}

function respond_error($statusCode, $message, $errors = null)
{
    $payload = [
        'status' => 'error',
        'message' => $message
    ];

    // In production, hide detailed error information
    if ($errors !== null && !IS_PRODUCTION) {
        $payload['errors'] = $errors;
    }

    respond_json($statusCode, $payload);
}

/**
 * Respond with validation errors
 */
function respond_validation_error($errors)
{
    respond_error(400, 'Validasyon hatası', $errors);
}
?>