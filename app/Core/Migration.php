<?php

namespace App\Core;

use PDO;

class Migration
{
    private PDO $db;
    private string $migrationsDir;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->migrationsDir = dirname(__DIR__, 2) . '/database/migrations';
    }

    public function init(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS migrations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL UNIQUE,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
    }

    public function getExecuted(): array
    {
        $stmt = $this->db->query("SELECT name FROM migrations ORDER BY id");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getPending(): array
    {
        $executed = $this->getExecuted();
        $files = glob($this->migrationsDir . '/*.php');
        sort($files);

        $pending = [];
        foreach ($files as $file) {
            $name = basename($file, '.php');
            if (!in_array($name, $executed)) {
                $pending[$name] = $file;
            }
        }

        return $pending;
    }

    public function migrate(): array
    {
        $this->init();
        $pending = $this->getPending();
        $migrated = [];

        foreach ($pending as $name => $file) {
            $migration = require $file;

            if (isset($migration['up'])) {
                try {
                    $this->db->beginTransaction();

                    if (is_callable($migration['up'])) {
                        $migration['up']($this->db);
                    } else {
                        $this->db->exec($migration['up']);
                    }

                    $stmt = $this->db->prepare("INSERT INTO migrations (name) VALUES (?)");
                    $stmt->execute([$name]);

                    if ($this->db->inTransaction()) {
                        $this->db->commit();
                    }
                    $migrated[] = $name;
                } catch (\Throwable $e) {
                    if ($this->db->inTransaction()) {
                        $this->db->rollBack();
                    }
                    throw new \RuntimeException("Migration {$name} failed: " . $e->getMessage());
                }
            }
        }

        return $migrated;
    }

    public function rollback(int $steps = 1): array
    {
        $executed = array_reverse($this->getExecuted());
        $rolled = [];

        for ($i = 0; $i < $steps && $i < count($executed); $i++) {
            $name = $executed[$i];
            $file = $this->migrationsDir . "/{$name}.php";

            if (!file_exists($file)) {
                continue;
            }

            $migration = require $file;

            if (isset($migration['down'])) {
                try {
                    $this->db->beginTransaction();

                    if (is_callable($migration['down'])) {
                        $migration['down']($this->db);
                    } else {
                        $this->db->exec($migration['down']);
                    }

                    $stmt = $this->db->prepare("DELETE FROM migrations WHERE name = ?");
                    $stmt->execute([$name]);

                    $this->db->commit();
                    $rolled[] = $name;
                } catch (\Throwable $e) {
                    $this->db->rollBack();
                    throw new \RuntimeException("Rollback {$name} failed: " . $e->getMessage());
                }
            }
        }

        return $rolled;
    }
}
