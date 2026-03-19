<?php
namespace App;

class Auth
{
    public static function login(string $email, string $password): bool
    {
        $db = Database::get();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND active = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = [
                'id'   => $user['id'],
                'name' => $user['name'],
                'email'=> $user['email'],
                'role' => $user['role'],
            ];
            return true;
        }
        return false;
    }

    public static function logout(): void
    {
        unset($_SESSION['user']);
        session_destroy();
    }

    public static function check(): bool
    {
        return isset($_SESSION['user']);
    }

    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function id(): int
    {
        return $_SESSION['user']['id'] ?? 0;
    }

    public static function role(): string
    {
        return $_SESSION['user']['role'] ?? 'staff';
    }

    public static function isAdmin(): bool
    {
        return self::role() === 'admin';
    }

    public static function require(): void
    {
        if (!self::check()) {
            redirect('/login');
        }
    }

    public static function requireAdmin(): void
    {
        self::require();
        if (!self::isAdmin()) {
            redirect('/?error=unauthorized');
        }
    }
}
