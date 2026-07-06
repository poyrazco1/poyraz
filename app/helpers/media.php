<?php
/**
 * Görsel fallback sistemi — kırık <img> kalmasın.
 * Yol boşsa ya da dosya diskte yoksa kategoriye uygun demo SVG döner.
 */

const DEMO_DIR = 'assets/uploads/demo';

/**
 * Görsel URL'i üretir; yol boşsa veya dosya yoksa fallback'e düşer.
 *
 * @param string|null $path     köke göre yol (assets/uploads/... veya assets/img/...)
 * @param string      $fallback köke göre fallback yolu
 */
function media_url(?string $path, string $fallback = ''): string
{
    if ($fallback === '') {
        $fallback = DEMO_DIR . '/default-gallery-demo.svg';
    }
    if ($path !== null && $path !== '') {
        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }
        $clean = ltrim(str_replace(['..', "\0"], '', $path), '/');
        if (is_file(BASE_PATH . '/' . $clean)) {
            return base_url($clean);
        }
    }
    return base_url($fallback);
}

/** Hizmet slug/başlığına uygun demo görsel yolu */
function service_demo_image(?string $slugOrTitle): string
{
    $key = slugify((string) $slugOrTitle);
    $map = [
        'minimal-tattoo'      => 'minimal-tattoo-demo.svg',
        'fine-line-tattoo'    => 'fine-line-tattoo-demo.svg',
        'realistic-tattoo'    => 'realistic-tattoo-demo.svg',
        'portre-dovme'        => 'portrait-tattoo-demo.svg',
        'portrait-tattoo'     => 'portrait-tattoo-demo.svg',
        'cover-up'            => 'cover-up-tattoo-demo.svg',
        'blackwork'           => 'blackwork-tattoo-demo.svg',
        'color-tattoo'        => 'color-tattoo-demo.svg',
        'geometrik-dovme'     => 'geometric-tattoo-demo.svg',
        'geometric-tattoo'    => 'geometric-tattoo-demo.svg',
        'tribal'              => 'tribal-tattoo-demo.svg',
        'yazi-dovmesi'        => 'writing-tattoo-demo.svg',
        'eski-dovme-yenileme' => 'cover-up-tattoo-demo.svg',
    ];
    // Başlıkla da eşleşebilsin (örn. "Minimal Tattoo")
    foreach ($map as $slug => $file) {
        if ($key === $slug || str_contains($key, $slug) || str_contains($slug, $key)) {
            return DEMO_DIR . '/' . $file;
        }
    }
    return DEMO_DIR . '/default-service-demo.svg';
}

function gallery_fallback_image(): string
{
    return DEMO_DIR . '/default-gallery-demo.svg';
}

function blog_fallback_image(): string
{
    return DEMO_DIR . '/default-blog-demo.svg';
}

/**
 * Galeri tablosu boşken gösterilecek demo öğeler.
 * @return array<int, array{title:string,image:string,alt_text:string,cat_slug:string}>
 */
function demo_gallery_items(): array
{
    $items = [
        ['Minimal Tattoo', 'minimal-tattoo-demo.svg', 'minimal'],
        ['Fine Line Tattoo', 'fine-line-tattoo-demo.svg', 'fine-line'],
        ['Realistic Tattoo', 'realistic-tattoo-demo.svg', 'realistic'],
        ['Blackwork', 'blackwork-tattoo-demo.svg', 'blackwork'],
        ['Color Tattoo', 'color-tattoo-demo.svg', 'renkli'],
        ['Geometrik Dövme', 'geometric-tattoo-demo.svg', 'blackwork'],
        ['Portre Dövme', 'portrait-tattoo-demo.svg', 'realistic'],
        ['Tribal', 'tribal-tattoo-demo.svg', 'blackwork'],
    ];
    return array_map(fn ($i) => [
        'title'    => $i[0] . ' — Demo',
        'image'    => DEMO_DIR . '/' . $i[1],
        'alt_text' => $i[0] . ' demo görsel',
        'cat_slug' => $i[2],
    ], $items);
}
