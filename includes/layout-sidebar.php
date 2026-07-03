<?php

declare(strict_types=1);

/**
 * Panel düzeni — sol menü (sidebar).
 *
 * $activeNav değişkeni layout-header.php içinde belirlenir. Menü öğeleri
 * yetkiye göre gösterilir; yeni modüller buraya kolayca eklenebilir.
 */

if (!isset($activeNav)) {
    $activeNav = '';
}

/**
 * Sidebar menü tanımı. İleride modül eklerken bu diziye ekleyin.
 * Her öğe: key, label, icon (inline SVG), href, capability (opsiyonel).
 *
 * @var array<int, array{key:string,label:string,href:string,icon:string,capability?:string}> $navItems
 */
$navItems = [
    [
        'key'   => 'dashboard',
        'label' => 'Panel',
        'href'  => url('dashboard.php'),
        'icon'  => '<path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/>',
    ],
    [
        'key'        => 'currency',
        'label'      => 'Kur Çevirici',
        'href'       => url('modules/currency/index.php'),
        'icon'       => '<path d="M12 3a9 9 0 100 18 9 9 0 000-18zm.9 13.4v1.1h-1.6v-1.1c-1.3-.2-2.3-1-2.4-2.3h1.5c.1.6.6 1 1.7 1 1 0 1.5-.4 1.5-1 0-.5-.4-.8-1.6-1.1-1.6-.4-2.9-.9-2.9-2.4 0-1.1.9-1.9 2.2-2.1V4.9h1.6V6c1.2.2 2.1.9 2.2 2.1h-1.5c-.1-.5-.5-.9-1.4-.9-.9 0-1.4.4-1.4.9 0 .5.5.7 1.7 1 1.7.4 2.8 1 2.8 2.5 0 1.2-.9 2-2.1 2.2z"/>',
        'capability' => 'currency.view',
    ],
    [
        'key'        => 'settings',
        'label'      => 'Ayarlar',
        'href'       => url('modules/settings/index.php'),
        'icon'       => '<path d="M19.4 13a7.8 7.8 0 000-2l2-1.6-2-3.4-2.4 1a7.6 7.6 0 00-1.7-1l-.4-2.5h-3.9l-.4 2.5c-.6.2-1.2.6-1.7 1l-2.4-1-2 3.4L4.6 11a7.8 7.8 0 000 2l-2 1.6 2 3.4 2.4-1c.5.4 1.1.8 1.7 1l.4 2.5h3.9l.4-2.5c.6-.2 1.2-.6 1.7-1l2.4 1 2-3.4-2-1.6zM12 15.5a3.5 3.5 0 110-7 3.5 3.5 0 010 7z"/>',
        'capability' => 'settings.view',
    ],
];
?>
<aside class="sidebar" data-sidebar>
    <div class="sidebar__brand">
        <span class="sidebar__logo">P</span>
        <span class="sidebar__brand-text"><?= e(SITE_NAME) ?></span>
    </div>

    <nav class="sidebar__nav" aria-label="Ana menü">
        <ul>
            <?php foreach ($navItems as $item): ?>
                <?php
                if (isset($item['capability']) && !user_can($item['capability'])) {
                    continue;
                }
                $isActive = ($activeNav === $item['key']);
                ?>
                <li>
                    <a class="sidebar__link<?= $isActive ? ' is-active' : '' ?>" href="<?= e($item['href']) ?>">
                        <svg class="sidebar__icon" viewBox="0 0 24 24" aria-hidden="true"><?= $item['icon'] ?></svg>
                        <span><?= e($item['label']) ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>

    <div class="sidebar__footer">
        <span class="sidebar__version">Sürüm 1.0</span>
    </div>
</aside>
