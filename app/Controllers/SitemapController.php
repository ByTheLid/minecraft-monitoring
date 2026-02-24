<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class SitemapController extends Controller
{
    public function index()
    {
        $db = Database::getInstance();
        $appUrl = env('APP_URL', 'http://localhost'); // Ensure absolute URLs
        
        // Ensure ends without slash
        $appUrl = rtrim($appUrl, '/');

        // Static Pages
        $urls = [
            ['loc' => $appUrl . '/', 'lastmod' => date('Y-m-d'), 'changefreq' => 'daily', 'priority' => '1.0'],
            ['loc' => $appUrl . '/servers', 'lastmod' => date('Y-m-d'), 'changefreq' => 'hourly', 'priority' => '0.9'],
            ['loc' => $appUrl . '/posts', 'lastmod' => date('Y-m-d'), 'changefreq' => 'daily', 'priority' => '0.8'],
            ['loc' => $appUrl . '/login', 'lastmod' => date('Y-m-d'), 'changefreq' => 'monthly', 'priority' => '0.5'],
            ['loc' => $appUrl . '/register', 'lastmod' => date('Y-m-d'), 'changefreq' => 'monthly', 'priority' => '0.5'],
        ];

        // Dynamic Servers
        $servers = $db->query("SELECT id, updated_at, created_at FROM servers WHERE is_active = 1 AND is_approved = 1 ORDER BY created_at DESC")->fetchAll();
        foreach ($servers as $server) {
            $lastmod = $server['updated_at'] ? date('Y-m-d', strtotime($server['updated_at'])) : date('Y-m-d', strtotime($server['created_at']));
            $urls[] = [
                'loc' => $appUrl . '/server/' . $server['id'],
                'lastmod' => $lastmod,
                'changefreq' => 'always',
                'priority' => '0.8'
            ];
        }

        // Dynamic Posts
        $posts = $db->query("SELECT id, published_at, created_at FROM posts WHERE is_published = 1 ORDER BY created_at DESC")->fetchAll();
        foreach ($posts as $post) {
            $lastmod = $post['published_at'] ? date('Y-m-d', strtotime($post['published_at'])) : date('Y-m-d', strtotime($post['created_at']));
            $urls[] = [
                'loc' => $appUrl . '/post/' . $post['id'],
                'lastmod' => $lastmod,
                'changefreq' => 'weekly',
                'priority' => '0.6'
            ];
        }

        // SEO Filter Pages (only indexed)
        $seoPages = $db->query("SELECT url_path, updated_at FROM seo_pages WHERE is_indexed = 1")->fetchAll();
        foreach ($seoPages as $seoPage) {
            $urls[] = [
                'loc' => $appUrl . $seoPage['url_path'],
                'lastmod' => date('Y-m-d', strtotime($seoPage['updated_at'])),
                'changefreq' => 'daily',
                'priority' => '0.7'
            ];
        }

        // Build XML
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"/>');

        foreach ($urls as $url) {
            $node = $xml->addChild('url');
            $node->addChild('loc', htmlspecialchars($url['loc']));
            $node->addChild('lastmod', $url['lastmod']);
            $node->addChild('changefreq', $url['changefreq']);
            $node->addChild('priority', $url['priority']);
        }

        header('Content-Type: application/xml; charset=utf-8');
        echo $xml->asXML();
        exit;
    }
}
