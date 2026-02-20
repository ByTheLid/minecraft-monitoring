<?php

namespace App\Controllers\User;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Models\Server;
use App\Models\BoostPackage;
use App\Models\User;

class BoostController extends Controller
{
    public function storeForm(Request $request): Response
    {
        $id = (int) $request->param('id');
        $server = Server::find($id);

        if (!$server || ($server['user_id'] != auth()['id'] && !is_admin())) {
            return $this->redirect('/dashboard');
        }

        $packages = BoostPackage::getActive();
        $user = User::find(auth()['id']);
        
        // Calculate Star price dynamically (base = 50, doubles for each existing star)
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT MAX(pkg.stars) FROM boost_purchases bp JOIN boost_packages pkg ON bp.package_id = pkg.id WHERE bp.server_id = ? AND bp.expires_at > NOW()");
        $stmt->execute([$id]);
        $currentStars = (int) $stmt->fetchColumn();
        $starPrice = 50 * pow(2, $currentStars);

        return $this->view('dashboard.boost-store', [
            'server' => $server,
            'packages' => $packages,
            'user' => $user,
            'currentStars' => $currentStars,
            'starPrice' => $starPrice
        ]);
    }

    public function purchase(Request $request): Response
    {
        $id = (int) $request->param('id');
        $server = Server::find($id);

        if (!$server || ($server['user_id'] != auth()['id'] && !is_admin())) {
            return $this->redirect('/dashboard');
        }

        $packageId = $request->input('package_id');
        $user = User::find(auth()['id']);
        $db = Database::getInstance();

        if ($packageId === 'star') {
            // Star logic
            $stmt = $db->prepare("SELECT MAX(pkg.stars) FROM boost_purchases bp JOIN boost_packages pkg ON bp.package_id = pkg.id WHERE bp.server_id = ? AND bp.expires_at > NOW()");
            $stmt->execute([$id]);
            $currentStars = (int) $stmt->fetchColumn();
            
            if ($currentStars >= 3) {
                flash('error', 'Server already has maximum stars (3).');
                return $this->redirect("/dashboard/server/{$id}/boost");
            }
            
            $price = 50 * pow(2, $currentStars);
            
            if ((float)$user['balance'] < $price) {
                flash('error', "Insufficient balance. You need $price coins.");
                return $this->redirect("/dashboard/server/{$id}/boost");
            }
            
            // Create a temporary/hidden star package or use a special logic
            // To apply the star, we'll create an invisible boost package dynamically if it doesn't exist, or just insert it.
            // For simplicity, let's create a dedicated "Star Boost +1" package if it doesn't exist, and assign it.
            
            $starPackageName = "Star Level " . ($currentStars + 1);
            $stmt = $db->prepare("SELECT id FROM boost_packages WHERE name = ? LIMIT 1");
            $stmt->execute([$starPackageName]);
            $starPkgId = $stmt->fetchColumn();
            
            if (!$starPkgId) {
                $db->prepare("INSERT INTO boost_packages (name, price, points, duration_days, stars, is_active) VALUES (?, ?, 0, 30, ?, 0)")
                   ->execute([$starPackageName, $price, $currentStars + 1]);
                $starPkgId = $db->lastInsertId();
            }
            
            // Deduction
            User::update($user['id'], ['balance' => (float)$user['balance'] - $price]);
            
            $db->prepare("
                INSERT INTO boost_purchases (server_id, user_id, package_id, points, expires_at)
                VALUES (?, ?, ?, 0, DATE_ADD(NOW(), INTERVAL 30 DAY))
            ")->execute([$id, $user['id'], $starPkgId]);

            flash('success', "Purchased Star Level " . ($currentStars + 1) . "!");
            return $this->redirect("/dashboard/server/{$id}/boost");
        }

        // Normal Package Logic
        $pkg = BoostPackage::find((int) $packageId);
        
        if (!$pkg || !$pkg['is_active']) {
            flash('error', 'Invalid package selected.');
            return $this->redirect("/dashboard/server/{$id}/boost");
        }

        if ((float)$user['balance'] < (float)$pkg['price']) {
            flash('error', "Insufficient balance. You need {$pkg['price']} coins.");
            return $this->redirect("/dashboard/server/{$id}/boost");
        }

        // Deduct balance
        User::update($user['id'], ['balance' => (float)$user['balance'] - (float)$pkg['price']]);

        // Add Boost
        $db->prepare("
            INSERT INTO boost_purchases (server_id, user_id, package_id, points, expires_at)
            VALUES (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL ? DAY))
        ")->execute([$id, $user['id'], $pkg['id'], $pkg['points'], $pkg['duration_days']]);

        flash('success', "Purchased '{$pkg['name']}' successfully!");
        return $this->redirect("/dashboard/server/{$id}/boost");
    }
}
