<?php

require_once __DIR__ . '/../auth.php';

class AuthController
{
    public static function loginForm(): void
    {
        render('auth/login');
    }

    public static function login(): void
    {
        verify_csrf();
        $email = trim((string)post('email', ''));
        $password = (string)post('password', '');

        $stmt = db()->prepare('SELECT * FROM users WHERE email = ? AND is_active = 1');
        $stmt->execute([strtolower($email)]);
        $user = $stmt->fetch();

        if (!$user || !self::verifyUserPassword($user, $password)) {
            render('auth/login', ['error' => 'Credenciales inválidas']);
            return;
        }

        login_user($user);
        redirect($user['role'] === 'teacher' ? '/teacher' : '/team');
    }


    private static function verifyUserPassword(array $user, string $password): bool
    {
        $storedHash = (string)($user['password_hash'] ?? '');
        if ($storedHash === '') {
            return false;
        }

        if (password_verify($password, $storedHash)) {
            if (password_needs_rehash($storedHash, PASSWORD_DEFAULT)) {
                self::rehashPassword((int)$user['id'], $password);
            }
            return true;
        }

        // Compatibilidad temporal: algunos despliegues guardaron SHA-256 plano.
        if (preg_match('/^[a-f0-9]{64}$/i', $storedHash) && hash_equals(strtolower($storedHash), hash('sha256', $password))) {
            self::rehashPassword((int)$user['id'], $password);
            return true;
        }

        return false;
    }

    private static function rehashPassword(int $userId, string $plainPassword): void
    {
        $stmt = db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        $stmt->execute([password_hash($plainPassword, PASSWORD_DEFAULT), $userId]);
    }

    public static function logout(): void
    {
        logout_user();
        redirect('/login');
    }

    public static function activateForm(string $token): void
    {
        $stmt = db()->prepare('SELECT id, email FROM users WHERE activation_token = ? AND is_active = 0');
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        if (!$user) {
            http_response_code(404);
            exit('Token inválido');
        }
        render('auth/activate', ['token' => $token, 'email' => $user['email']]);
    }

    public static function activate(string $token): void
    {
        verify_csrf();
        $password = (string)post('password', '');
        if (strlen($password) < 8) {
            render('auth/activate', ['token' => $token, 'error' => 'Mínimo 8 caracteres']);
            return;
        }

        $stmt = db()->prepare('UPDATE users SET password_hash = ?, activation_token = NULL, is_active = 1 WHERE activation_token = ? AND is_active = 0');
        $stmt->execute([password_hash($password, PASSWORD_DEFAULT), $token]);

        redirect('/login');
    }
}
