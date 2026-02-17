<?php

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    protected static string $table = 'users';

    public static function findByUsername(string $username): ?array
    {
        return static::findBy('username', $username);
    }

    public static function findByEmail(string $email): ?array
    {
        return static::findBy('email', $email);
    }

    public static function register(string $username, string $email, string $password): int
    {
        return static::create([
            'username' => $username,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_BCRYPT),
        ]);
    }

    public static function verifyPassword(array $user, string $password): bool
    {
        return password_verify($password, $user['password_hash']);
    }

    public static function isUsernameTaken(string $username): bool
    {
        return static::findByUsername($username) !== null;
    }

    public static function isEmailTaken(string $email): bool
    {
        return static::findByEmail($email) !== null;
    }
}
