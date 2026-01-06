<?php

// Laravel router for PHP built-in server
// This file allows the PHP built-in server to serve static files
// and route all other requests to index.php

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Serve static files if they exist
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false;
}

// Route all other requests to index.php
require_once __DIR__ . '/index.php';

