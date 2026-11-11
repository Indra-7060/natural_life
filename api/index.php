<?php

/**
 * Vercel entry point. Every request that is not a static file in public/ lands here
 * (see vercel.json) and is handed to the normal front controller.
 * Not used on ordinary PHP hosting.
 */

// Vercel variable names cannot contain dots, so "ccavenue_merchantId" is also
// made available under the name the application reads: "ccavenue.merchantId".
foreach (getenv() as $name => $value) {
    if (preg_match('/^(app|database|ccavenue|email|encryption)_[A-Za-z0-9_]+$/', $name)) {
        $dotted = str_replace('_', '.', $name);
        if (getenv($dotted) === false) {
            putenv($dotted . '=' . $value);
            $_ENV[$dotted]    = $value;
            $_SERVER[$dotted] = $value;
        }
    }
}

// The visitor's address: Vercel sets this header itself and overwrites anything the visitor sends.
if (! empty($_SERVER['HTTP_X_REAL_IP']) && filter_var($_SERVER['HTTP_X_REAL_IP'], FILTER_VALIDATE_IP)) {
    $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_X_REAL_IP'];
}

// Vercel serves every request over HTTPS (plain HTTP is redirected before it gets here), but the
// PHP runtime does not say so. Without this the application redirects to HTTPS again and again.
if (getenv('VERCEL')) {
    $_SERVER['HTTPS']                  = 'on';
    $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
    $_SERVER['REQUEST_SCHEME']         = 'https';
    $_SERVER['SERVER_PORT']            = '443';
}

// Make the framework see the request as if public/index.php had been called directly.
$_SERVER['SCRIPT_NAME']     = '/index.php';
$_SERVER['PHP_SELF']        = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = dirname(__DIR__) . '/public/index.php';

require dirname(__DIR__) . '/public/index.php';
