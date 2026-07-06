<?php
/** 404 sayfası */
http_response_code(404);
$meta = [
    'title'     => t('404.title'),
    'canonical' => '404',
    'noindex'   => true,
];
require BASE_PATH . '/app/views/layout/header.php';
?>
<div class="error-page">
    <div class="code">404</div>
    <h1><?= e(t('404.title')) ?></h1>
    <p style="color:var(--muted)"><?= e(t('404.text')) ?></p>
    <p style="margin-top:24px">
        <a class="btn btn-primary" href="<?= e(url('')) ?>"><?= e(t('404.home')) ?></a>
    </p>
</div>
<?php require BASE_PATH . '/app/views/layout/footer.php'; ?>
