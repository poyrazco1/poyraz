<?php
/** Dinamik robots.txt — sitemap mutlak URL ister */
header('Content-Type: text/plain; charset=utf-8');
echo "User-agent: *\n";
echo "Allow: /\n";
echo "Disallow: /admin/\n";
echo "Disallow: /takip\n\n";
echo 'Sitemap: ' . base_url('sitemap.xml') . "\n";
