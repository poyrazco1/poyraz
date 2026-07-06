<?php
/**
 * Inline SVG ikon sistemi — CDN bağımlılığı yok.
 * Kullanım: icon('whatsapp', 'icon-sm')  →  <svg ...>...</svg>
 * Bilinmeyen ikon adı fallback ikona düşer; boş dönmez.
 */

/**
 * @param string $name  ikon adı (whitelist)
 * @param string $class ek CSS sınıfı
 * @param int    $size  px cinsinden genişlik/yükseklik
 */
function icon(string $name, string $class = '', int $size = 24): string
{
    $paths = icon_paths();
    $body = $paths[$name] ?? $paths['fallback'];
    $cls = trim('icon icon--' . preg_replace('/[^a-z0-9\-]/', '', $name) . ' ' . $class);
    return '<svg class="' . e($cls) . '" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24"'
         . ' fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"'
         . ' aria-hidden="true" focusable="false">' . $body . '</svg>';
}

/** Hizmet tablosundaki icon koduna uygun ikon adı */
function service_icon_name(?string $code): string
{
    return match ($code) {
        'minimal'   => 'sparkle',
        'realistic' => 'image',
        'fineline'  => 'needle',
        'blackwork' => 'brush',
        'color'     => 'sparkle',
        'coverup'   => 'shield',
        'renewal'   => 'brush',
        'lettering' => 'edit',
        'portrait'  => 'user',
        'geometric' => 'gallery',
        'tribal'    => 'needle',
        default     => 'brush',
    };
}

/** İkonların iç SVG gövdeleri (24x24, stroke: currentColor) */
function icon_paths(): array
{
    static $p = null;
    if ($p !== null) {
        return $p;
    }
    $p = [
        'fallback'  => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="1" fill="currentColor"/>',
        'menu'      => '<line x1="4" y1="7" x2="20" y2="7"/><line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="17" x2="20" y2="17"/>',
        'close'     => '<line x1="6" y1="6" x2="18" y2="18"/><line x1="18" y1="6" x2="6" y2="18"/>',
        'whatsapp'  => '<path fill="currentColor" stroke="none" d="M16 3C9.4 3 4 8.3 4 14.9c0 2.6.8 5 2.3 7L4 29l7.3-2.3c1.9 1 3.9 1.5 4.7 1.5 6.6 0 12-5.3 12-11.9S22.6 3 16 3zm0 21.6c-1.4 0-3.2-.5-4.6-1.3l-.5-.3-4.3 1.4 1.4-4.2-.3-.5c-1.2-1.7-1.9-3.6-1.9-5.8C5.8 9.5 10.4 5 16 5s10.2 4.5 10.2 9.9-4.6 9.7-10.2 9.7zm5.6-7.3c-.3-.2-1.8-.9-2.1-1-.3-.1-.5-.2-.7.2-.2.3-.8 1-.9 1.1-.2.2-.3.2-.6.1-.3-.2-1.3-.5-2.4-1.5-.9-.8-1.5-1.8-1.7-2.1-.2-.3 0-.5.1-.6l.5-.5c.1-.2.2-.3.3-.5.1-.2 0-.4 0-.6l-1-2.3c-.2-.6-.5-.5-.7-.5h-.6c-.2 0-.5.1-.8.4-.3.3-1 1-1 2.5s1.1 2.9 1.2 3.1c.2.2 2.1 3.2 5.1 4.5.7.3 1.3.5 1.7.6.7.2 1.4.2 1.9.1.6-.1 1.8-.7 2-1.4.3-.7.3-1.3.2-1.5-.1-.1-.3-.2-.5-.3z" transform="scale(0.75)"/>',
        'instagram' => '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.2" cy="6.8" r="1" fill="currentColor" stroke="none"/>',
        'phone'     => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3 19.5 19.5 0 0 1-6-6 19.8 19.8 0 0 1-3-8.7A2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 2 .7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.9.5 2.8.7a2 2 0 0 1 1.7 2z"/>',
        'clock'     => '<circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15.5 14"/>',
        'location'  => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/>',
        'calendar'  => '<rect x="3" y="4" width="18" height="17" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="9" x2="21" y2="9"/>',
        'mail'      => '<rect x="2" y="4" width="20" height="16" rx="2"/><polyline points="2.5 6.5 12 13 21.5 6.5"/>',
        'user'      => '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-6.5 8-6.5s8 2.5 8 6.5"/>',
        'shield'    => '<path d="M12 22s8-3.5 8-10V5l-8-3-8 3v7c0 6.5 8 10 8 10z"/><polyline points="8.5 11.5 11 14 15.5 9"/>',
        'needle'    => '<line x1="19" y1="5" x2="9" y2="15"/><path d="M19 5l1.5-1.5M9 15l-4.2 5.2a.7.7 0 0 1-1-1L9 15z"/><line x1="14.5" y1="6.5" x2="17.5" y2="9.5"/>',
        'sparkle'   => '<path d="M12 3l1.9 5.6L19.5 10l-5.6 1.9L12 17.5l-1.9-5.6L4.5 10l5.6-1.4z"/><line x1="19" y1="17" x2="19" y2="21"/><line x1="17" y1="19" x2="21" y2="19"/>',
        'brush'     => '<path d="M9.1 14.9L3 21l6.1-1.6a2.6 2.6 0 1 0 0-4.5z"/><path d="M14 4l6 6-8.5 6.5-4-4z"/>',
        'image'     => '<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="M21 15l-5-5-9 9"/>',
        'gallery'   => '<rect x="3" y="3" width="8" height="8" rx="1.5"/><rect x="13" y="3" width="8" height="8" rx="1.5"/><rect x="3" y="13" width="8" height="8" rx="1.5"/><rect x="13" y="13" width="8" height="8" rx="1.5"/>',
        'blog'      => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="8" y1="13" x2="16" y2="13"/><line x1="8" y1="17" x2="13" y2="17"/>',
        'price'     => '<path d="M20.6 13.4L11 3.8A2 2 0 0 0 9.6 3H5a2 2 0 0 0-2 2v4.6c0 .5.2 1 .6 1.4l9.6 9.6a2 2 0 0 0 2.8 0l4.6-4.6a2 2 0 0 0 0-2.6z"/><circle cx="7.5" cy="7.5" r="1" fill="currentColor" stroke="none"/>',
        'faq'       => '<circle cx="12" cy="12" r="9"/><path d="M9.2 9a3 3 0 0 1 5.8 1c0 2-3 2.4-3 4"/><circle cx="12" cy="17.2" r=".8" fill="currentColor" stroke="none"/>',
        'campaign'  => '<path d="M3 11l14-6v14L3 13v-2z"/><path d="M17 8.5a3.5 3.5 0 0 1 0 7"/><path d="M7.5 13.5V18a1.5 1.5 0 0 0 3 0v-3.5"/>',
        'language'  => '<circle cx="12" cy="12" r="9"/><line x1="3" y1="12" x2="21" y2="12"/><path d="M12 3a13.5 13.5 0 0 1 0 18M12 3a13.5 13.5 0 0 0 0 18"/>',
        'dashboard' => '<rect x="3" y="3" width="8" height="10" rx="1.5"/><rect x="13" y="3" width="8" height="6" rx="1.5"/><rect x="13" y="11" width="8" height="10" rx="1.5"/><rect x="3" y="15" width="8" height="6" rx="1.5"/>',
        'settings'  => '<line x1="4" y1="7" x2="7" y2="7"/><line x1="11" y1="7" x2="20" y2="7"/><circle cx="9" cy="7" r="2"/><line x1="4" y1="12" x2="13" y2="12"/><line x1="17" y1="12" x2="20" y2="12"/><circle cx="15" cy="12" r="2"/><line x1="4" y1="17" x2="5" y2="17"/><line x1="9" y1="17" x2="20" y2="17"/><circle cx="7" cy="17" r="2"/>',
        'edit'      => '<path d="M17 3a2.8 2.8 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5z"/>',
        'delete'    => '<polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>',
        'eye'       => '<path d="M1.5 12S5.5 5 12 5s10.5 7 10.5 7-4 7-10.5 7S1.5 12 1.5 12z"/><circle cx="12" cy="12" r="3"/>',
        'plus'      => '<line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>',
        'search'    => '<circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.5" y2="16.5"/>',
        'filter'    => '<path d="M22 3H2l8 9.5V19l4 2v-8.5z"/>',
        'check'     => '<polyline points="20 6 9 17 4 12"/>',
        'warning'   => '<path d="M10.3 3.9L1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/><line x1="12" y1="9" x2="12" y2="13"/><circle cx="12" cy="16.8" r=".8" fill="currentColor" stroke="none"/>',
        'upload'    => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 8 12 3 17 8"/><line x1="12" y1="3" x2="12" y2="15"/>',
        'logout'    => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>',
        'arrow-right' => '<line x1="4" y1="12" x2="20" y2="12"/><polyline points="13 5 20 12 13 19"/>',
        'home'      => '<path d="M3 10.5L12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/>',
        'quote'     => '<path fill="currentColor" stroke="none" d="M7.2 6C4.9 7.4 3.5 9.6 3.5 12.6c0 3 1.8 5 4.2 5 2 0 3.5-1.4 3.5-3.4 0-1.9-1.3-3.2-3.1-3.2-.3 0-.7 0-.9.1.3-1.7 1.6-3.2 3.2-4L7.2 6zm9.5 0c-2.3 1.4-3.7 3.6-3.7 6.6 0 3 1.8 5 4.2 5 2 0 3.5-1.4 3.5-3.4 0-1.9-1.3-3.2-3.1-3.2-.3 0-.7 0-.9.1.3-1.7 1.6-3.2 3.2-4L16.7 6z"/>',
        'star'      => '<path d="M12 2.5l2.9 6 6.6.9-4.8 4.6 1.2 6.5L12 17.4l-5.9 3.1 1.2-6.5L2.5 9.4l6.6-.9z"/>',
    ];
    return $p;
}
