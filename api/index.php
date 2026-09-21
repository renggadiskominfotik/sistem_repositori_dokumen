<?php

// Ensure session save path is writable in serverless environment (/tmp)
if (session_status() === PHP_SESSION_NONE) {
    if (is_dir('/tmp') && is_writable('/tmp')) {
        @session_save_path('/tmp');
    }
    @session_start();
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$filePath = __DIR__ . '/..' . $uri;

if ($uri === '/' || $uri === '') {
    chdir(__DIR__ . '/../visitor');
    require __DIR__ . '/../visitor/index.php';
    exit;
}

if (file_exists($filePath) && !is_dir($filePath)) {
    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    $mimeTypes = [
        'css'   => 'text/css',
        'js'    => 'application/javascript',
        'png'   => 'image/png',
        'jpg'   => 'image/jpeg',
        'jpeg'  => 'image/jpeg',
        'gif'   => 'image/gif',
        'svg'   => 'image/svg+xml',
        'pdf'   => 'application/pdf',
        'doc'   => 'application/msword',
        'docx'  => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls'   => 'application/vnd.ms-excel',
        'xlsx'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
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

// If an asset or non-php file was requested but does not exist, return true 404
$extension = strtolower(pathinfo($uri, PATHINFO_EXTENSION));
if (!empty($extension) && $extension !== 'php') {
    http_response_code(404);
    header('Content-Type: text/plain');
    echo '404 File Not Found';
    exit;
}

// Default fallback to visitor index
chdir(__DIR__ . '/../visitor');
require __DIR__ . '/../visitor/index.php';
