<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\SeoPage;
use App\Services\SeoService;

class SeoController extends Controller
{
    public function filterPage(Request $request): Response
    {
        $category = $request->param('category');
        $value = $request->param('value');
        $page = max(1, (int) $request->query('page', 1));
        $urlPath = "/servers/{$category}/{$value}";

        // Validate category
        if (!in_array($category, ['version', 'tag'])) {
            return $this->view('errors.404', [], 404);
        }

        $seoPage = SeoPage::findByPath($urlPath);
        if (!$seoPage) {
            return $this->view('errors.404', [], 404);
        }

        // Get filtered servers
        $servers = SeoService::getFilteredServers($category, $value, $page);

        // Render SEO text template
        $totalOnline = array_sum(array_column($servers['data'] ?? [], 'players_online'));
        $seoText = SeoService::renderTemplate($seoPage['seo_text_template'] ?? '', [
            'servers_count' => $seoPage['server_count'],
            'version' => $value,
            'category' => $category,
            'total_online' => $totalOnline,
        ]);

        return $this->view('seo.filter', [
            'seoPage' => $seoPage,
            'seoText' => $seoText,
            'servers' => $servers,
            'isIndexed' => (bool) $seoPage['is_indexed'],
            'category' => $category,
            'value' => $value,
        ]);
    }
}
