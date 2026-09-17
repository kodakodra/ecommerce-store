<?php

declare(strict_types=1);

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $path): string { return '/' . ltrim($path, '/'); }

function current_path(): string
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $path = '/' . trim($path, '/');
    return $path === '//' || $path === '' || $path === '/index.php' ? '/' : $path;
}

function env(string $key, ?string $default = null): ?string
{
    static $loaded = false; static $values = [];
    if (!$loaded) {
        $file = dirname(__DIR__) . '/.env';
        if (is_file($file) && is_readable($file)) {
            foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
                $line = trim($line);
                if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
                [$name, $value] = explode('=', $line, 2); $value = trim($value);
                if ((str_starts_with($value, '"') && str_ends_with($value, '"')) || (str_starts_with($value, "'") && str_ends_with($value, "'"))) $value = substr($value, 1, -1);
                $values[trim($name)] = $value;
            }
        }
        $loaded = true;
    }
    return array_key_exists($key, $values) ? $values[$key] : $default;
}

function is_post(): bool { return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'; }

function start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) return;
    session_set_cookie_params(['httponly'=>true,'secure'=>!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off','samesite'=>'Lax','path'=>'/']);
    session_start();
}

function csrf_token(): string { start_session(); $_SESSION['csrf_token'] ??= bin2hex(random_bytes(32)); return $_SESSION['csrf_token']; }
function csrf_valid(?string $token): bool { start_session(); return is_string($token) && $token !== '' && isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'],$token); }
function flash_set(string $type,string $message): void { start_session(); $_SESSION['flash']=['type'=>$type,'message'=>$message]; }
function flash_get(): ?array { start_session(); $flash=$_SESSION['flash']??null; unset($_SESSION['flash']); return is_array($flash)?$flash:null; }
function can_submit_contact(): bool { start_session(); return time()-(int)($_SESSION['last_contact_submit']??0)>=15; }
function redirect(string $path): never { header('Location: '.url($path),true,303); exit; }
function site_origin(array $store): string { $origin=rtrim((string)($store['site_url']??''),'/'); return $origin!==''?$origin:'http://localhost:8000'; }
function absolute_url(string $path,array $store): string { return site_origin($store).url($path); }
function money(int $pence,array $store): string { return ($store['currency_symbol']??'£').number_format($pence/100,2); }

function product_image(array $store, string $slug): ?string
{
    foreach ($store['products'] as $product) {
        if (($product['slug'] ?? '') === $slug) return $product['image'] ?? null;
    }
    return null;
}
