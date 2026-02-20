<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Server;
use App\Models\BoostPackage;
use App\Models\BoostPurchase;

class BoostApiController extends Controller
{
    public function purchase(Request $request): Response
    {
        $user = auth();
        $data = $request->all();

        $serverId = (int) ($data['server_id'] ?? 0);
        $packageId = (int) ($data['package_id'] ?? 0);

        $server = Server::find($serverId);
        if (!$server || $server['user_id'] != $user['id']) {
            return $this->error('FORBIDDEN', 'You can only boost your own servers', [], 403);
        }

        $package = BoostPackage::find($packageId);
        if (!$package || !$package['is_active']) {
            return $this->error('NOT_FOUND', 'Boost package not found', [], 404);
        }

        $id = BoostPurchase::purchase(
            $user['id'],
            $serverId,
            $packageId,
            $package['points'],
            $package['duration_days']
        );

        return $this->success(['id' => $id, 'points' => $package['points'], 'expires_in_days' => $package['duration_days']]);
    }
}
