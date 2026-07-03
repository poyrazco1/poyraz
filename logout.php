<?php

declare(strict_types=1);

/**
 * Çıkış.
 *
 * Oturumu ve "beni hatırla" token/çerezini temizler, ana sayfaya döner.
 */

require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';

logout_user();

// Yeni bir oturumda flash mesajı gösterebilmek için oturumu tekrar başlat.
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name(SESSION_NAME);
    session_start();
}
flash('success', 'Oturumunuz güvenli şekilde kapatıldı.');

redirect('/login.php');
