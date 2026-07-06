<?php
require_once dirname(__DIR__) . '/app/core/bootstrap.php';

redirect(base_url(Auth::check() ? 'admin/dashboard.php' : 'admin/login.php'));
