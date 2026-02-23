<?php

namespace Database\Migrations;

use App\Core\Database;

class Migration_022_create_server_reviews
{
    public function up(): void
    {
        $db = Database::getInstance();

        $sql = "
            CREATE TABLE IF NOT EXISTS server_reviews (
                id INT AUTO_INCREMENT PRIMARY KEY,
                server_id INT NOT NULL,
                user_id INT NOT NULL,
                rating TINYINT NOT NULL CHECK (rating >= 1 AND rating <= 5),
                comment TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (server_id) REFERENCES servers(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                UNIQUE KEY unique_server_user_review (server_id, user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";

        $db->exec($sql);
    }

    public function down(): void
    {
        $db = Database::getInstance();
        $db->exec("DROP TABLE IF EXISTS server_reviews");
    }
}
