<?php
// src/controllers/SeoController.php

class SeoController {
    
    public function sitemap() {
        $db = (new Database())->connect();
        $host = "http://" . $_SERVER['HTTP_HOST'];
        
        // Static Pages
        $urls = [
            ['loc' => $host . '/', 'priority' => '1.0', 'changefreq' => 'daily'],
            ['loc' => $host . '/login', 'priority' => '0.5', 'changefreq' => 'monthly'],
            ['loc' => $host . '/admin/login', 'priority' => '0.1', 'changefreq' => 'monthly'],
        ];

        // Fetch Dynamic Pages (Events)
        // Only active future events
        $today = date('Y-m-d');
        $events = $db->query("SELECT id, updated_at, event_date FROM events WHERE status = 'active' AND (event_date >= '$today' OR event_date IS NULL) AND type != 'interno'")->fetchAll();
        
        foreach ($events as $evt) {
            // Assuming we might have a detail page in future, for now they are on home anchor #eventos
            // If we had detail pages: $host . '/eventos/' . $evt['id']
            // Since they are on home, we don't add specific URLs unless we implement a detail view.
            // However, listing them helps if we had a detail route.
            // Let's stick to the main sections since it's a single-page style mostly.
        }

        // Output XML
        header("Content-Type: application/xml; charset=utf-8");
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        
        foreach ($urls as $url) {
            echo '<url>';
            echo '<loc>' . htmlspecialchars($url['loc']) . '</loc>';
            echo '<lastmod>' . date('c') . '</lastmod>'; // Ideally fetch from DB updated_at
            echo '<changefreq>' . $url['changefreq'] . '</changefreq>';
            echo '<priority>' . $url['priority'] . '</priority>';
            echo '</url>';
        }
        
        echo '</urlset>';
    }
    
    public function robots() {
        header("Content-Type: text/plain");
        $host = "http://" . $_SERVER['HTTP_HOST'];
        
        echo "User-agent: *\n";
        echo "Disallow: /admin/\n";
        echo "Disallow: /uploads/private/\n";
        echo "Allow: /\n\n";
        echo "Sitemap: $host/sitemap.xml";
    }
}
