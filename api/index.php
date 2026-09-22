<?php

// Ensure session save path is writable in serverless environment (/tmp)
if (session_status() === PHP_SESSION_NONE) {
    if (is_dir('/tmp') && is_writable('/tmp')) {
        @session_save_path('/tmp');
    }
    @session_start();
}

$rawUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri    = rawurldecode($rawUri);

if ($uri === '/' || $uri === '') {
    chdir(__DIR__ . '/../visitor');
    require __DIR__ . '/../visitor/index.php';
    exit;
}

$filePath = __DIR__ . '/..' . $uri;

// Check alternate path for filenames containing '+'
if (!file_exists($filePath)) {
    $altPath = __DIR__ . '/..' . str_replace(' ', '+', $uri);
    if (file_exists($altPath)) {
        $filePath = $altPath;
    }
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
        $isDownload = isset($_GET['download']) && $_GET['download'] == '1';
        $disposition = $isDownload ? 'attachment' : 'inline';

        header('Content-Type: ' . $mimeTypes[$extension]);
        header('Content-Length: ' . filesize($filePath));
        header('Content-Disposition: ' . $disposition . '; filename="' . basename($filePath) . '"');
        readfile($filePath);
        exit;
    }

    if ($extension === 'php') {
        chdir(dirname($filePath));
        require $filePath;
        exit;
    }
}

// If an asset under /assets/uploads/ was requested but not found locally, redirect to Supabase Storage
if (strpos($uri, '/assets/uploads/') === 0 || strpos($uri, 'assets/uploads/') !== false) {
    $filename = basename($uri);
    $supabaseUrl = isset($_ENV['SUPABASE_URL']) && $_ENV['SUPABASE_URL'] !== '' ? $_ENV['SUPABASE_URL'] : 'https://ofcftaqpuvpedcmakfii.supabase.co';
    $bucket = isset($_ENV['SUPABASE_BUCKET']) && $_ENV['SUPABASE_BUCKET'] !== '' ? $_ENV['SUPABASE_BUCKET'] : 'dokumen';
    
    $publicUrl = rtrim($supabaseUrl, '/') . '/storage/v1/object/public/' . $bucket . '/' . rawurlencode($filename);
    header('Location: ' . $publicUrl);
    exit;
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
