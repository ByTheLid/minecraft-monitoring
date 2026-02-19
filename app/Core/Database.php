<?php

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            // Default path (when running via index.php)
            $configPath = dirname(__DIR__, 2) . '/config/database.php';
            
            if (!file_exists($configPath)) {
                // Fallback for CLI from root
                $configPath = __DIR__ . '/../../config/database.php';
            }
            
            if (!file_exists($configPath)) {
                 $configPath = 'c:/laragon/www/minecraft-monitoring/config/database.php';
            }

            $config = require $configPath;

            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $config['host'],
                $config['port'],
                $config['name'],
                $config['charset']
            );

            try {
                self::$instance = new PDO($dsn, $config['user'], $config['pass'], $config['options']);
            } catch (PDOException $e) {
                throw new PDOException('Database connection failed: ' . $e->getMessage());
            }
        }

        return self::$instance;
    }

    public static function reset(): void
    {
        self::$instance = null;
    }
}
