<?php
/**
 * SEO yardımcıları: meta etiketleri, canonical, hreflang,
 * Open Graph / Twitter kartları ve JSON-LD şemaları.
 */

/**
 * <head> için tüm SEO etiketlerini üretir.
 *
 * $meta: title, description, canonical(path), image, type(website|article),
 *        published, modified, noindex
 */
function seo_tags(array $meta): string
{
    $siteName = setting('site_name', SITE_NAME);
    $title = trim($meta['title'] ?? '');
    $fullTitle = $title === '' ? $siteName . ' — ' . setting('site_slogan', '')
                               : $title . ' | ' . $siteName;
    $desc = excerpt_of($meta['description'] ?? setting('site_description', ''), 300);
    $path = trim($meta['canonical'] ?? '', '/');
    $canonical = url($path);
    $image = $meta['image'] ?? null;
    $image = $image ? upload_url($image) : upload_url(setting('og_image', ''), 'img/og-default.svg');
    $type = $meta['type'] ?? 'website';

    $out  = '<title>' . e($fullTitle) . '</title>' . "\n";
    $out .= '<meta name="description" content="' . e($desc) . '">' . "\n";
    if (!empty($meta['noindex'])) {
        $out .= '<meta name="robots" content="noindex,nofollow">' . "\n";
    }
    $out .= '<link rel="canonical" href="' . e($canonical) . '">' . "\n";

    // hreflang alternatifleri
    foreach (active_languages() as $l) {
        $out .= '<link rel="alternate" hreflang="' . e($l['code']) . '" href="'
              . e(url($path, $l['code'])) . '">' . "\n";
    }
    $out .= '<link rel="alternate" hreflang="x-default" href="' . e(url($path, DEFAULT_LANGUAGE)) . '">' . "\n";

    // Open Graph
    $out .= '<meta property="og:site_name" content="' . e($siteName) . '">' . "\n";
    $out .= '<meta property="og:type" content="' . e($type) . '">' . "\n";
    $out .= '<meta property="og:title" content="' . e($fullTitle) . '">' . "\n";
    $out .= '<meta property="og:description" content="' . e($desc) . '">' . "\n";
    $out .= '<meta property="og:url" content="' . e($canonical) . '">' . "\n";
    $out .= '<meta property="og:image" content="' . e($image) . '">' . "\n";
    $out .= '<meta property="og:locale" content="' . e(og_locale(Lang::current())) . '">' . "\n";
    if ($type === 'article') {
        if (!empty($meta['published'])) {
            $out .= '<meta property="article:published_time" content="' . e(date('c', strtotime($meta['published']))) . '">' . "\n";
        }
        if (!empty($meta['modified'])) {
            $out .= '<meta property="article:modified_time" content="' . e(date('c', strtotime($meta['modified']))) . '">' . "\n";
        }
    }

    // Twitter
    $out .= '<meta name="twitter:card" content="summary_large_image">' . "\n";
    $out .= '<meta name="twitter:title" content="' . e($fullTitle) . '">' . "\n";
    $out .= '<meta name="twitter:description" content="' . e($desc) . '">' . "\n";
    $out .= '<meta name="twitter:image" content="' . e($image) . '">' . "\n";

    return $out;
}

function og_locale(string $lang): string
{
    return ['tr' => 'tr_TR', 'en' => 'en_US', 'ar' => 'ar_AR', 'ru' => 'ru_RU'][$lang] ?? 'tr_TR';
}

function jsonld(array $data): string
{
    return '<script type="application/ld+json">'
         . json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
         . '</script>' . "\n";
}

/** LocalBusiness / TattooParlor şeması */
function jsonld_local_business(): string
{
    return jsonld([
        '@context' => 'https://schema.org',
        '@type'    => 'TattooParlor',
        'name'     => setting('site_name', SITE_NAME),
        'description' => setting('site_description', ''),
        'url'      => base_url(),
        'image'    => upload_url(setting('logo', ''), 'img/logo.svg'),
        'telephone' => '+' . whatsapp_number(),
        'priceRange' => '₺₺',
        'address'  => [
            '@type' => 'PostalAddress',
            'addressLocality' => 'Bağcılar',
            'addressRegion'   => 'İstanbul',
            'addressCountry'  => 'TR',
        ],
        'openingHoursSpecification' => [[
            '@type' => 'OpeningHoursSpecification',
            'dayOfWeek' => ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'],
            'opens' => '09:00', 'closes' => '23:00',
        ]],
        'sameAs' => array_values(array_filter([setting('instagram_url', INSTAGRAM_URL)])),
    ]);
}

/** Blog yazısı için Article şeması */
function jsonld_article(array $post, string $urlPath): string
{
    return jsonld([
        '@context' => 'https://schema.org',
        '@type'    => 'Article',
        'headline' => $post['title'],
        'description' => excerpt_of($post['excerpt'] ?: $post['content'], 200),
        'image'    => upload_url($post['cover_image'] ?? null),
        'datePublished' => date('c', strtotime($post['published_at'] ?? $post['created_at'])),
        'dateModified'  => date('c', strtotime($post['updated_at'] ?? $post['created_at'])),
        'author'   => ['@type' => 'Organization', 'name' => setting('site_name', SITE_NAME)],
        'publisher' => [
            '@type' => 'Organization',
            'name'  => setting('site_name', SITE_NAME),
            'logo'  => ['@type' => 'ImageObject', 'url' => upload_url(setting('logo', ''), 'img/logo.svg')],
        ],
        'mainEntityOfPage' => url($urlPath),
    ]);
}

/** SSS için FAQPage şeması */
function jsonld_faq(array $items): string
{
    if (empty($items)) {
        return '';
    }
    $entities = [];
    foreach ($items as $item) {
        $entities[] = [
            '@type' => 'Question',
            'name'  => $item['question'],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => strip_tags($item['answer'])],
        ];
    }
    return jsonld([
        '@context' => 'https://schema.org',
        '@type'    => 'FAQPage',
        'mainEntity' => $entities,
    ]);
}

/** Breadcrumb şeması. $items: [['name' =>, 'path' =>], ...] */
function jsonld_breadcrumb(array $items): string
{
    $list = [];
    foreach ($items as $i => $item) {
        $list[] = [
            '@type' => 'ListItem',
            'position' => $i + 1,
            'name' => $item['name'],
            'item' => url($item['path']),
        ];
    }
    return jsonld([
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => $list,
    ]);
}

/** Görsel breadcrumb HTML'i (schema ile birlikte kullanılır) */
function breadcrumb_html(array $items): string
{
    $out = '<nav class="breadcrumb" aria-label="breadcrumb"><ol>';
    $last = count($items) - 1;
    foreach ($items as $i => $item) {
        if ($i === $last) {
            $out .= '<li aria-current="page">' . e($item['name']) . '</li>';
        } else {
            $out .= '<li><a href="' . e(url($item['path'])) . '">' . e($item['name']) . '</a></li>';
        }
    }
    return $out . '</ol></nav>';
}
