<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class DesignController extends Controller
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function toggle()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $preference = $input['design'] ?? 'modern';

        if (!in_array($preference, ['modern', 'pixel'])) {
            return $this->json(['success' => false, 'message' => 'Invalid design preference'], 400);
        }

        if (auth()) {
            $stmt = $this->db->prepare("UPDATE users SET design_preference = ? WHERE id = ?");
            $stmt->execute([$preference, auth()['id']]);
            
            // Update session data
            $_SESSION['user']['design_preference'] = $preference;
        } else {
            // Set cookie for 30 days
            setcookie('design_preference', $preference, time() + (86400 * 30), "/");
        }

        return $this->json([
            'success' => true,
            'design' => $preference,
            'message' => 'Design preference saved'
        ]);
    }
}
