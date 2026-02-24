<?php

namespace App\Services;

use App\Core\Database;
use App\Models\User;

class SecurityService
{
    const GRACE_PERIOD_HOURS = 72;

    /**
     * Evaluate risk level and set requires_2fa flag if user is high-risk
     */
    public static function evaluateRisk(int $userId): void
    {
        $user = User::find($userId);
        if (!$user || $user['two_factor_enabled']) {
            return; // Already secured or not found
        }

        $isHighRisk = false;

        // Condition 1: Has positive balance (paid user)
        if (($user['balance'] ?? 0) > 0) {
            $isHighRisk = true;
        }

        // Condition 2: Owns a top-20 ranked server
        $db = Database::getInstance();
        $stmt = $db->prepare("
            SELECT COUNT(*) FROM servers s
            JOIN server_rankings sr ON s.id = sr.server_id
            WHERE s.user_id = ? AND s.is_active = 1
            ORDER BY sr.rank_score DESC LIMIT 20
        ");
        $stmt->execute([$userId]);
        if ((int) $stmt->fetchColumn() > 0) {
            $topIds = $db->query("
                SELECT s.user_id FROM servers s
                JOIN server_rankings sr ON s.id = sr.server_id
                WHERE s.is_active = 1
                ORDER BY sr.rank_score DESC LIMIT 20
            ")->fetchAll(\PDO::FETCH_COLUMN);
            
            if (in_array($userId, $topIds)) {
                $isHighRisk = true;
            }
        }

        if ($isHighRisk && !$user['requires_2fa']) {
            User::update($userId, [
                'requires_2fa' => 1,
                'two_factor_grace_until' => date('Y-m-d H:i:s', time() + self::GRACE_PERIOD_HOURS * 3600),
            ]);
        }
    }

    /**
     * Check if critical actions should be blocked (grace period expired, 2FA not set up)
     */
    public static function isCriticalBlocked(array $user): bool
    {
        if (!($user['requires_2fa'] ?? false)) return false;
        if ($user['two_factor_enabled'] ?? false) return false;

        // Grace period still active
        if (!empty($user['two_factor_grace_until']) && strtotime($user['two_factor_grace_until']) > time()) {
            return false;
        }

        return true; // Grace expired, 2FA not set up -> block
    }

    /**
     * Should a warning banner be shown?
     */
    public static function shouldShowWarning(array $user): bool
    {
        return ($user['requires_2fa'] ?? false) && !($user['two_factor_enabled'] ?? false);
    }

    /**
     * Generate backup codes for a user
     */
    public static function generateBackupCodes(int $userId): array
    {
        $db = Database::getInstance();

        // Delete old codes
        $db->prepare("DELETE FROM two_factor_backup_codes WHERE user_id = ?")->execute([$userId]);

        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $code = strtoupper(bin2hex(random_bytes(4))); // 8 char hex codes
            $db->prepare("INSERT INTO two_factor_backup_codes (user_id, code) VALUES (?, ?)")
               ->execute([$userId, $code]);
            $codes[] = $code;
        }

        return $codes;
    }

    /**
     * Verify a backup code
     */
    public static function verifyBackupCode(int $userId, string $code): bool
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT id FROM two_factor_backup_codes WHERE user_id = ? AND code = ? AND is_used = 0");
        $stmt->execute([$userId, strtoupper(trim($code))]);
        $row = $stmt->fetch();

        if ($row) {
            $db->prepare("UPDATE two_factor_backup_codes SET is_used = 1 WHERE id = ?")->execute([$row['id']]);
            return true;
        }

        return false;
    }
}
