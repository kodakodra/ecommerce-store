<?php

declare(strict_types=1);

$basePath=dirname(__DIR__);
require $basePath.'/src/helpers.php';
$store=require $basePath.'/config/store.php';
require $basePath.'/src/store.php';
require $basePath.'/src/contact.php';
require $basePath.'/src/stripe.php';
require $basePath.'/src/webhook.php';

ini_set('display_errors',env('APP_DEBUG','0')==='1'?'1':'0');
error_reporting(E_ALL);
date_default_timezone_set(env('APP_TIMEZONE','Europe/London')??'Europe/London');
start_session();

if(PHP_SAPI!=='cli'){
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline'; script-src 'none'; frame-ancestors 'none'; base-uri 'self'; form-action 'self'; connect-src 'none'");
    register_shutdown_function(static function():void{
        $error=error_get_last(); if($error===null||headers_sent())return;
        if(!in_array($error['type'],[E_ERROR,E_PARSE,E_CORE_ERROR,E_COMPILE_ERROR,E_USER_ERROR],true))return;
        http_response_code(500); if(ob_get_level()>0)ob_clean();
        echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Temporary problem</title><style>body{font-family:system-ui,sans-serif;max-width:44rem;margin:10vh auto;padding:1rem;line-height:1.6}</style></head><body><h1>Sorry, there is a temporary problem.</h1><p>Please try again shortly.</p></body></html>';
    });
}
