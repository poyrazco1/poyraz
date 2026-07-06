<?php
require_once dirname(__DIR__) . '/app/core/bootstrap.php';

// CSRF korumalı çıkış (GET ile token istenir, linke ?t= eklenir)
$token = $_GET['t'] ?? ($_POST['csrf_token'] ?? null);
if (Auth::check() && Csrf::verify(is_string($token) ? $token : null)) {
    Auth::logout();
}
redirect(base_url('admin/login.php'));
