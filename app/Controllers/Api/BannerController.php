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

        if (!$server || !$server['is_active']) {
            $this->send404Banner();
            return;
        }

        // Configuration
        $width = 468;
        $height = 60;
        
        $fontBoldPath = realpath(__DIR__ . '/../../../public/fonts/Inter-Bold.ttf');
        $fontRegPath = realpath(__DIR__ . '/../../../public/fonts/Inter-Regular.ttf');

        $fontBold = $fontBoldPath ?: 5; // Built-in font fallback
        $fontReg = $fontRegPath ?: 4;

        // Create image
        $image = imagecreatetruecolor($width, $height);

        // Colors
        $bg = imagecolorallocate($image, 30, 41, 59); // Slate-800
        $bgAccent = imagecolorallocate($image, 15, 23, 42); // Slate-900
        $white = imagecolorallocate($image, 255, 255, 255);
        $gray = imagecolorallocate($image, 148, 163, 184); // Slate-400
        $green = imagecolorallocate($image, 16, 185, 129); // Emerald-500
        $red = imagecolorallocate($image, 239, 68, 68); // Red-500
        $gold = imagecolorallocate($image, 251, 191, 36); // Amber-400

        // Fill background
        imagefilledrectangle($image, 0, 0, $width, $height, $bg);
        
        // Draw subtle gradient/accent (left side darker)
        for ($i = 0; $i < 120; $i++) {
            $alpha = (int) (127 - ($i * (127/120)));
            $overlay = imagecolorallocatealpha($image, 15, 23, 42, $alpha);
            imagefilledrectangle($image, $i*2, 0, ($i*2)+2, $height, $overlay);
        }

        // Add Favicon
        if (!empty($server['favicon_base64'])) {
            $faviconData = explode(',', $server['favicon_base64']);
            if (isset($faviconData[1])) {
                $iconData = base64_decode($faviconData[1]);
                $icon = @imagecreatefromstring($iconData);
                if ($icon) {
                    imagecopyresampled($image, $icon, 10, 10, 0, 0, 40, 40, imagesx($icon), imagesy($icon));
                    imagedestroy($icon);
                } else {
                    imagefilledrectangle($image, 10, 10, 50, 50, $bgAccent); // fallback
                }
            }
        } else {
            imagefilledrectangle($image, 10, 10, 50, 50, $bgAccent);
        }

        // Server Name & Verified Badge
        $name = mb_substr($server['name'], 0, 25);
        if (is_int($fontBold)) {
            imagestring($image, $fontBold, 65, 12, $name, $white);
        } else {
            imagettftext($image, 14, 0, 65, 28, $white, $fontBold, $name);
        }
        
        // IP Address
        $ipText = $server['ip'] . ($server['port'] != 25565 ? ':' . $server['port'] : '');
        if (is_int($fontReg)) {
            imagestring($image, $fontReg, 65, 32, mb_substr($ipText, 0, 35), $gray);
        } else {
            imagettftext($image, 11, 0, 65, 48, $gray, $fontReg, mb_substr($ipText, 0, 35));
        }

        // Right side info (Status & Players)
        $isOnline = (bool) ($server['is_online'] ?? false);
        $players = (int)($server['players_online'] ?? 0);
        $maxPlayers = (int)($server['players_max'] ?? 0);

        // Status Circle
        $statusColor = $isOnline ? $green : $red;
        imagefilledellipse($image, $width - 80, 22, 12, 12, $statusColor);
        
        if (is_int($fontBold)) {
            imagestring($image, $fontBold, $width - 65, 14, $isOnline ? 'ONLINE' : 'OFFLINE', $statusColor);
        } else {
            imagettftext($image, 10, 0, $width - 60, 26, $statusColor, $fontBold, $isOnline ? 'ONLINE' : 'OFFLINE');
        }

        // Players count
        if ($isOnline) {
            $playerText = "{$players} / {$maxPlayers}";
            if (is_int($fontReg)) {
                imagestring($image, $fontReg, $width - (strlen($playerText)*8) - 10, 32, $playerText, $white);
            } else {
                $pBox = imagettfbbox(11, 0, $fontReg, $playerText);
                $pWidth = $pBox[2] - $pBox[0];
                imagettftext($image, 11, 0, $width - $pWidth - 15, 48, $white, $fontReg, $playerText);
            }
        } else {
            if (is_int($fontReg)) {
                imagestring($image, $fontReg, $width - 85, 32, "unreachable", $gray);
            } else {
                imagettftext($image, 11, 0, $width - 85, 48, $gray, $fontReg, "unreachable");
            }
        }

        // Output image with caching headers
        header('Content-Type: image/png');
        header('Cache-Control: public, max-age=300'); // Cache for 5 mins
        imagepng($image);
        imagedestroy($image);
        exit;
    }

    private function send404Banner(): void
    {
        $width = 468;
        $height = 60;
        $image = imagecreatetruecolor($width, $height);
        $bg = imagecolorallocate($image, 30, 41, 59);
        $red = imagecolorallocate($image, 239, 68, 68);
        imagefilledrectangle($image, 0, 0, $width, $height, $bg);
        
        header('Content-Type: image/png');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        imagestring($image, 5, 150, 22, 'SERVER NOT FOUND', $red);
        imagepng($image);
        imagedestroy($image);
        exit;
    }
}
