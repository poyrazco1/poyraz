<?php
/** Müşteri takip — kod ile durum sorgulama (public) */
$code = strtoupper(trim($slug ?? get_param('kod')));
$record = null;
$notFound = false;
if ($code !== '' && preg_match('/^[A-Z0-9\-]{4,20}$/', $code)) {
    $record = Database::row('SELECT * FROM customer_tracking WHERE tracking_code = ? LIMIT 1', [$code]);
    $notFound = $record === null;
} elseif ($code !== '') {
    $notFound = true;
}

$breadcrumbs = [
    ['name' => t('breadcrumb.home'), 'path' => ''],
    ['name' => t('title.tracking'), 'path' => 'takip'],
];
$meta = [
    'title'       => t('tracking.title'),
    'description' => t('tracking.text'),
    'canonical'   => 'takip',
    'noindex'     => true,
];
require BASE_PATH . '/app/views/layout/header.php';
?>
<section class="page-hero">
    <div class="container">
        <?= breadcrumb_html($breadcrumbs) ?>
        <h1><?= e(t('tracking.title')) ?></h1>
        <p class="lead"><?= e(t('tracking.text')) ?></p>
    </div>
</section>
<section class="section">
    <div class="container form-wrap">
        <div class="form-card">
            <form method="get" action="<?= e(url('takip')) ?>">
                <div class="form-grid">
                    <div class="form-group full">
                        <label for="t-code"><?= e(t('field.tracking_code')) ?></label>
                        <input id="t-code" type="text" name="kod" maxlength="20" required
                               placeholder="DEMO1234" value="<?= e($code) ?>" style="text-transform:uppercase">
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"><?= e(t('btn.track')) ?></button>
                </div>
            </form>
        </div>

        <?php if ($notFound): ?>
        <div class="track-result">
            <div class="flash flash-error"><?= e(t('tracking.not_found')) ?></div>
        </div>
        <?php elseif ($record): ?>
        <div class="track-result">
            <div class="track-card">
                <div class="track-row">
                    <span class="k"><?= e(t('field.tracking_code')) ?></span>
                    <strong><?= e($record['tracking_code']) ?></strong>
                </div>
                <div class="track-row">
                    <span class="k"><?= e(t('field.full_name')) ?></span>
                    <strong><?= e($record['full_name']) ?></strong>
                </div>
                <div class="track-row">
                    <span class="k"><?= e(t('tracking.service')) ?></span>
                    <strong><?= e($record['service_type']) ?></strong>
                </div>
                <div class="track-row">
                    <span class="k"><?= e(t('tracking.status')) ?></span>
                    <span class="status-badge <?= e($record['status']) ?>"><?= e(t('status.' . $record['status'])) ?></span>
                </div>
                <?php if ($record['public_note']): ?>
                <div class="track-row">
                    <span class="k"><?= e(t('tracking.note')) ?></span>
                    <span><?= e($record['public_note']) ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>
<?php require BASE_PATH . '/app/views/layout/footer.php'; ?>
