<?php
/**
 * SEO dostu URL yönlendirici.
 *
 * URL yapısı:
 *   /                      → TR ana sayfa (varsayılan dil, prefix'siz)
 *   /en/... /ar/... /ru/...→ diğer diller
 *   /tr/...                → 301 ile prefix'siz haline yönlenir (canonical çakışmasın)
 *   /hizmetler/{slug}      → hizmet detay
 *   /blog/{slug}           → blog detay
 */
class Router
{
    private array $routes = [
        ''                  => 'home',
        'hakkimizda'        => 'about',
        'sanatci'           => 'artist',
        'sanatcilar'        => 'artist',
        'hizmetler'         => 'services',
        'galeri'            => 'gallery',
        'portfolyo'         => 'gallery',
        'once-sonra'        => 'before-after',
        'hijyen'            => 'hygiene',
        'dovme-modelleri'   => 'models',
        'randevu-al'        => 'appointment',
        'fiyat-teklifi-al'  => 'quote',
        'fiyat-listesi'     => 'prices',
        'sss'               => 'faq',
        'blog'              => 'blog',
        'akademi'           => 'academy',
        'bakim-talimatlari' => 'aftercare',
        'iletisim'          => 'contact',
        'kvkk'              => 'kvkk',
        'gizlilik-politikasi' => 'kvkk',
        'takip'             => 'tracking',
        'istanbul'          => 'locations',
        'sitemap.xml'       => 'sitemap',
        'robots.txt'        => 'robots',
    ];

    /** slug alan dinamik rotalar: prefix => view */
    private array $dynamic = [
        'hizmetler' => 'service-detail',
        'blog'      => 'blog-detail',
        'akademi'   => 'blog-detail',
        'takip'     => 'tracking',
        'istanbul'  => 'location-detail',
        'sayfa'     => 'page',
    ];

    public string $lang  = DEFAULT_LANGUAGE;
    public string $view  = '404';
    public string $path  = '';   // dil prefix'i çıkarılmış yol
    public ?string $slug = null;

    public function dispatch(): void
    {
        $uri  = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?? '/';

        // Alt dizin kurulumu desteği: index.php'nin bulunduğu kök yolu çıkar
        $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
        if ($base !== '' && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base));
        }
        $path = trim(urldecode($path), '/');

        // Dil tespiti
        $segments = $path === '' ? [] : explode('/', $path);
        $langs = active_language_codes();

        if (!empty($segments)) {
            $first = strtolower($segments[0]);
            if ($first === DEFAULT_LANGUAGE) {
                // /tr/... → / (301, canonical tekilliği)
                array_shift($segments);
                redirect(url(implode('/', $segments)), 301);
            }
            if (in_array($first, $langs, true) && $first !== DEFAULT_LANGUAGE) {
                $this->lang = $first;
                array_shift($segments);
            }
        }

        $this->path = implode('/', $segments);

        // Tam eşleşme
        if (isset($this->routes[$this->path])) {
            $this->view = $this->routes[$this->path];
            return;
        }

        // Dinamik eşleşme: prefix/slug
        if (count($segments) === 2) {
            $prefix = $segments[0];
            if (isset($this->dynamic[$prefix])) {
                $this->view = $this->dynamic[$prefix];
                $this->slug = $segments[1];
                return;
            }
        }

        // Tek segment → dinamik sayfa (pages tablosu) denenir
        if (count($segments) === 1 && preg_match('/^[a-z0-9\-]+$/', $segments[0])) {
            $this->view = 'page';
            $this->slug = $segments[0];
            return;
        }

        $this->view = '404';
    }
}
