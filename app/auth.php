<?php

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

function init_session(): void
{
    $config = require __DIR__ . '/config.php';
    session_name($config['session_name']);
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function login_user(array $user): void
{
    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['role'] = $user['role'];
}

function logout_user(): void
{
    $_SESSION = [];
    session_destroy();
}

function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    $stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

function require_login(): array
{
    $user = current_user();
    if (!$user) {
        redirect('/login');
    }
    return $user;
}

function require_role(string $role): array
{
    $user = require_login();
    if ($user['role'] !== $role) {
        http_response_code(403);
        exit('No autorizado');
    }
    return $user;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = random_token();
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(): void
{
    $token = post('_csrf', '');
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(419);
        exit('CSRF inválido');
    }
}

function ensure_team_member(int $userId, int $teamId): void
{
    $stmt = db()->prepare('SELECT COUNT(*) FROM team_members WHERE user_id = ? AND team_id = ?');
    $stmt->execute([$userId, $teamId]);
    if ((int)$stmt->fetchColumn() < 1) {
        http_response_code(403);
        exit('No perteneces a este equipo');
    }
}
