<?php

// Forward all requests to the root directory
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Serve static assets directly if they exist
$filePath = __DIR__ . '/..' . $uri;

if ($uri === '/' || $uri === '') {
    chdir(__DIR__ . '/../visitor');
    require __DIR__ . '/../visitor/index.php';
    exit;
}

if (file_exists($filePath) && !is_dir($filePath)) {
    // Set appropriate content type for static assets
    $extension = pathinfo($filePath, PATHINFO_EXTENSION);
    $mimeTypes = [
        'css'  => 'text/css',
        'js'   => 'application/javascript',
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif'  => 'image/gif',
        'svg'  => 'image/svg+xml',
        'pdf'  => 'application/pdf',
    ];

    if (isset($mimeTypes[$extension])) {
        header('Content-Type: ' . $mimeTypes[$extension]);
        readfile($filePath);
        exit;
    }

    if ($extension === 'php') {
        chdir(dirname($filePath));
        require $filePath;
        exit;
    }
}

// Default fallback to visitor index
chdir(__DIR__ . '/../visitor');
require __DIR__ . '/../visitor/index.php';
