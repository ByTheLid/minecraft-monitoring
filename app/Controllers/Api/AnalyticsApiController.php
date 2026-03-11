<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

class AnalyticsApiController extends Controller
{
    public function getChart(Request $request): Response
    {
        $serverId = (int) $request->param('id');
        $range = $request->input('range', '24h');

        $db = Database::getInstance();

        $labels = [];
        $playersData = [];
        $uptimeData = [];

        if ($range === '24h') {
            // For 24h, we use direct server_stats for finer granularity 
            // (or the cache grouped by hours). The cache is already hourly.
            $sql = "SELECT date_hour as time_label, avg_players, is_online_percent 
                    FROM server_analytics_cache 
                    WHERE server_id = ? AND date_hour >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                    ORDER BY date_hour ASC";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([$serverId]);
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Fill holes with 0 if needed (optional)
            foreach ($rows as $row) {
                $labels[] = date('H:i', strtotime($row['time_label']));
                $playersData[] = (int) $row['avg_players'];
                $uptimeData[] = (float) $row['is_online_percent'];
            }
        } elseif ($range === '7d' || $range === '30d') {
            $days = $range === '7d' ? 7 : 30;
            // Aggregate by Day
            $sql = "SELECT DATE(date_hour) as day_label, 
                           ROUND(AVG(avg_players)) as daily_players, 
                           ROUND(AVG(is_online_percent), 2) as daily_uptime
                    FROM server_analytics_cache
                    WHERE server_id = ? AND date_hour >= DATE_SUB(NOW(), INTERVAL ? DAY)
                    GROUP BY day_label
                    ORDER BY day_label ASC";
                    
            $stmt = $db->prepare($sql);
            $stmt->execute([$serverId, $days]);
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($rows as $row) {
                $labels[] = date('M d', strtotime($row['day_label']));
                $playersData[] = (int) $row['daily_players'];
                $uptimeData[] = (float) $row['daily_uptime'];
            }
        }

        return $this->json([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Average Players',
                    'data' => $playersData,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'type' => 'line'
                ],
                [
                    'label' => 'Uptime %',
                    'data' => $uptimeData,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'type' => 'bar'
                ]
            ]
        ]);
    }
}
