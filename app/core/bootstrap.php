<?php
/**
 * Ortak başlangıç: config, çekirdek sınıflar ve yardımcılar.
 * Hem public site (index.php) hem admin paneli bunu kullanır.
 */

if (!defined('BASE_PATH')) {
    require_once dirname(__DIR__, 2) . '/config.php';
}

require_once BASE_PATH . '/app/helpers/functions.php';
require_once BASE_PATH . '/app/helpers/security.php';
require_once BASE_PATH . '/app/helpers/seo.php';
require_once BASE_PATH . '/app/helpers/upload.php';
require_once BASE_PATH . '/app/helpers/icons.php';
require_once BASE_PATH . '/app/helpers/media.php';
require_once BASE_PATH . '/app/core/Database.php';
require_once BASE_PATH . '/app/core/Lang.php';
require_once BASE_PATH . '/app/core/Csrf.php';
require_once BASE_PATH . '/app/core/Auth.php';
require_once BASE_PATH . '/app/core/Router.php';

// Yakalanmayan hatalar log'a yazılır, kullanıcıya sade bir sayfa gösterilir.
set_exception_handler(function (Throwable $e) {
    app_log('HATA: ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    if (APP_DEBUG) {
        http_response_code(500);
        echo '<pre>' . e($e->getMessage() . "\n" . $e->getTraceAsString()) . '</pre>';
    } else {
        http_response_code(500);
        echo '<!doctype html><meta charset="utf-8"><title>Hata</title>'
           . '<div style="font-family:sans-serif;padding:40px;text-align:center">'
           . '<h1>Beklenmeyen bir hata oluştu</h1><p>Lütfen daha sonra tekrar deneyin.</p></div>';
    }
    exit;
});

secure_session_start();

// Varsayılan dil yüklenir; router dili belirledikten sonra tekrar set edilir.
Lang::set(DEFAULT_LANGUAGE);
