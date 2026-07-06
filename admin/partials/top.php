<?php
/**
 * Her admin sayfasının başında yer alır: bootstrap + oturum + CSRF kontrolü.
 */
require_once dirname(__DIR__, 2) . '/app/core/bootstrap.php';

Auth::requireLogin();

// Tüm POST istekleri CSRF token ister
Csrf::check();

/** Admin sayfaları için yönlendirme yardımcıları */
function admin_url(string $page, array $params = []): string
{
    $qs = $params ? '?' . http_build_query($params) : '';
    return base_url('admin/' . $page . $qs);
}

function admin_redirect(string $page, array $params = []): never
{
    redirect(admin_url($page, $params));
}

/** Durumlara renkli rozet */
function status_badge(string $status): string
{
    $map = [
        'new'         => ['badge-red', 'Yeni'],
        'pending'     => ['badge-amber', 'Beklemede'],
        'confirmed'   => ['badge-blue', 'Onaylandı'],
        'quoted'      => ['badge-blue', 'Teklif Verildi'],
        'accepted'    => ['badge-green', 'Kabul Edildi'],
        'in_progress' => ['badge-amber', 'Devam Ediyor'],
        'completed'   => ['badge-green', 'Tamamlandı'],
        'cancelled'   => ['badge-gray', 'İptal'],
        'closed'      => ['badge-gray', 'Kapatıldı'],
        'read'        => ['badge-green', 'Okundu'],
        '1'           => ['badge-green', 'Aktif'],
        '0'           => ['badge-gray', 'Pasif'],
    ];
    [$cls, $label] = $map[$status] ?? ['badge-gray', $status];
    return '<span class="badge ' . $cls . '">' . e($label) . '</span>';
}

/** Dil seçim <select> alanı */
function lang_select(string $name, string $selected = 'tr', bool $withAll = false): string
{
    $out = '<select name="' . e($name) . '" id="' . e($name) . '">';
    if ($withAll) {
        $out .= '<option value="">Tüm Diller</option>';
    }
    foreach (Database::all('SELECT code, name FROM languages ORDER BY sort_order') as $l) {
        $sel = $l['code'] === $selected ? ' selected' : '';
        $out .= '<option value="' . e($l['code']) . '"' . $sel . '>' . e(strtoupper($l['code']) . ' — ' . $l['name']) . '</option>';
    }
    return $out . '</select>';
}

/** Müşteriye hazır WhatsApp mesajı linki üretir */
function admin_wa_link(string $phone, string $message): string
{
    $digits = preg_replace('/\D/', '', $phone);
    if (str_starts_with($digits, '0')) {
        $digits = '9' . $digits; // 05xx → 905xx
    }
    if (!str_starts_with($digits, '90') && strlen($digits) === 10) {
        $digits = '90' . $digits;
    }
    return 'https://wa.me/' . $digits . '?text=' . rawurlencode($message);
}
