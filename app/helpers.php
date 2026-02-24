<?php

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void
{
    $config = require __DIR__ . '/config.php';
    $base = rtrim($config['base_url'], '/');
    header('Location: ' . $base . $path);
    exit;
}

function random_token(): string
{
    return bin2hex(random_bytes(32));
}

function post(string $key, $default = null)
{
    return $_POST[$key] ?? $default;
}

function get(string $key, $default = null)
{
    return $_GET[$key] ?? $default;
}

function validate_email(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function sendMail(string $to, string $subject, string $body): bool
{
    $config = require __DIR__ . '/config.php';
    $headers = 'From: ' . $config['mail_from'] . "\r\n" . 'Content-Type: text/plain; charset=utf-8';
    return @mail($to, $subject, $body, $headers);
}

function csv_response(string $filename): void
{
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
}

function render(string $view, array $data = []): void
{
    extract($data);
    $viewFile = __DIR__ . '/views/' . $view . '.php';
    include __DIR__ . '/views/layouts/header.php';
    include $viewFile;
    include __DIR__ . '/views/layouts/footer.php';
}
