<?php
/**
 * D4stattoo — Ön kontrolcü (front controller)
 * Tüm SEO URL'leri .htaccess üzerinden buraya gelir.
 */

require_once __DIR__ . '/app/core/bootstrap.php';

$router = new Router();
$router->dispatch();

Lang::set($router->lang);

// Görünüme aktarılan global değişkenler
$currentLang = $router->lang;
$currentPath = $router->path;
$slug        = $router->slug;

// POST istekleri ilgili controller'da işlenir (CSRF kontrolü controller içinde)
$controllers = [
    'appointment' => 'AppointmentController.php',
    'quote'       => 'QuoteController.php',
    'contact'     => 'ContactController.php',
    'tracking'    => 'TrackingController.php',
];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($controllers[$router->view])) {
    require BASE_PATH . '/app/controllers/' . $controllers[$router->view];
    exit;
}

$viewFile = BASE_PATH . '/app/views/pages/' . $router->view . '.php';
if (!is_file($viewFile)) {
    http_response_code(404);
    $viewFile = BASE_PATH . '/app/views/pages/404.php';
}

require $viewFile;
