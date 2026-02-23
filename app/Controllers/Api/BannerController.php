<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Models\Server;

class BannerController extends Controller
{
    public function generate(Request $request): void
    {
        $id = (int) $request->param('id');
        $server = Server::getDetail($id);

        // Constants for the canvas
        $width = 468;
        $height = 80;

        // Create the image canvas
        $image = imagecreatetruecolor($width, $height);

        // Colors
        $bgColor = imagecolorallocate($image, 30, 30, 36); // #1E1E24
        $textColor = imagecolorallocate($image, 255, 255, 255); // White
        $subTextColor = imagecolorallocate($image, 150, 150, 150); // Gray
        $onlineColor = imagecolorallocate($image, 76, 175, 80); // Green
        $offlineColor = imagecolorallocate($image, 244, 67, 54); // Red
        $goldColor = imagecolorallocate($image, 255, 193, 7); // Gold

        // Fill background
        imagefilledrectangle($image, 0, 0, $width, $height, $bgColor);

        if (!$server || !$server['is_approved'] || !$server['is_active']) {
            // Server not found or not active
            imagestring($image, 5, 20, 30, "Server Not Found or Offline", $offlineColor);
        } else {
            // Draw Server Banner

            // Server Name
            // In a production environment with TTF fonts we would use imagettftext
            // For universal compatibility here without requiring TTF files, we use imagestring
            $name = $server['name'];
            if (strlen($name) > 25) {
                $name = substr($name, 0, 22) . '...';
            }
            imagestring($image, 5, 15, 15, $name, $textColor);

            // Server IP
            $ipPort = $server['ip'] . ($server['port'] != 25565 ? ':' . $server['port'] : '');
            imagestring($image, 4, 15, 35, $ipPort, $subTextColor);

            // Version
            imagestring($image, 3, 15, 55, "Version: " . ($server['version'] ?? 'Unknown'), $subTextColor);

            // Status & Players (Right side)
            $isOnline = $server['is_online'] ?? false;
            if ($isOnline) {
                $statusText = "ONLINE";
                $statusColor = $onlineColor;
                $playersText = ($server['players_online'] ?? 0) . " / " . ($server['players_max'] ?? 0) . " Players";
            } else {
                $statusText = "OFFLINE";
                $statusColor = $offlineColor;
                $playersText = "0 / 0 Players";
            }

            // Align status text to right
            $statusX = $width - (strlen($statusText) * 9) - 15;
            imagestring($image, 5, $statusX, 15, $statusText, $statusColor);

            // Align players text to right
            $playersX = $width - (strlen($playersText) * 7) - 15;
            imagestring($image, 4, $playersX, 35, $playersText, $textColor);

            // Votes
            $votesText = "Votes: " . ($server['vote_count'] ?? 0);
            $votesX = $width - (strlen($votesText) * 7) - 15;
            imagestring($image, 4, $votesX, 55, $votesText, $goldColor);
        }

        // Output image
        header('Content-Type: image/png');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        imagepng($image);
        imagedestroy($image);
        exit;
    }
}
