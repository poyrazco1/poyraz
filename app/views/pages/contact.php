<?php
/** İletişim sayfası + formu (POST işleme ContactController'da) */
$errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_errors']);

$breadcrumbs = [
    ['name' => t('breadcrumb.home'), 'path' => ''],
    ['name' => t('title.contact'), 'path' => 'iletisim'],
];
$meta = [
    'title'       => t('title.contact') . ' — ' . setting('location_text', 'İstanbul / Bağcılar'),
    'description' => t('home.contact_text'),
    'canonical'   => 'iletisim',
];
$extraHead = jsonld_breadcrumb($breadcrumbs);
require BASE_PATH . '/app/views/layout/header.php';
?>
<section class="page-hero">
    <div class="container">
        <?= breadcrumb_html($breadcrumbs) ?>
        <h1><?= e(t('title.contact')) ?></h1>
        <p class="lead"><?= e(t('home.contact_text')) ?></p>
    </div>
</section>
<section class="section">
    <div class="container detail-layout">
        <div>
            <?php if ($errors): ?>
            <div class="flash flash-error"><?= e(t('form.error_general')) ?></div>
            <?php endif; ?>
            <div class="form-card">
                <h2 style="font-size:1.3rem"><?= e(t('form.contact_title')) ?></h2>
                <form method="post" action="<?= e(url('iletisim')) ?>" novalidate>
                    <?= Csrf::field() ?>
                    <input type="text" name="website" value="" class="hp-field" tabindex="-1" autocomplete="off" aria-hidden="true">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="c-name"><?= e(t('field.full_name')) ?> <span class="req">*</span></label>
                            <input id="c-name" type="text" name="full_name" required maxlength="150" value="<?= e(old('full_name')) ?>">
                            <?php if (isset($errors['full_name'])): ?><span class="field-error"><?= e($errors['full_name']) ?></span><?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label for="c-phone"><?= e(t('field.phone')) ?> <span class="req">*</span></label>
                            <input id="c-phone" type="tel" name="phone" required maxlength="20" value="<?= e(old('phone')) ?>">
                            <?php if (isset($errors['phone'])): ?><span class="field-error"><?= e($errors['phone']) ?></span><?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label for="c-email"><?= e(t('field.email')) ?></label>
                            <input id="c-email" type="email" name="email" maxlength="190" value="<?= e(old('email')) ?>">
                            <?php if (isset($errors['email'])): ?><span class="field-error"><?= e($errors['email']) ?></span><?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label for="c-subject"><?= e(t('field.subject')) ?></label>
                            <input id="c-subject" type="text" name="subject" maxlength="190" value="<?= e(old('subject')) ?>">
                        </div>
                        <div class="form-group full">
                            <label for="c-message"><?= e(t('field.message')) ?> <span class="req">*</span></label>
                            <textarea id="c-message" name="message" required maxlength="3000"><?= e(old('message')) ?></textarea>
                            <?php if (isset($errors['message'])): ?><span class="field-error"><?= e($errors['message']) ?></span><?php endif; ?>
                        </div>
                    </div>
                    <div class="form-actions">
                        <label class="form-consent">
                            <input type="checkbox" name="kvkk" value="1" required>
                            <span><?= str_replace(':url', e(url('kvkk')), t('form.kvkk_consent')) ?></span>
                        </label>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary"><?= e(t('btn.send')) ?></button>
                    </div>
                </form>
            </div>
        </div>
        <aside class="sidebar">
            <div class="side-card">
                <h3><?= e(t('footer.contact')) ?></h3>
                <ul class="footer-contact" style="list-style:none;padding:0;display:grid;gap:10px">
                    <li><?= icon('location', 'icon-sm') ?> <?= e(setting('location_text', 'İstanbul / Bağcılar')) ?></li>
                    <li><?= icon('phone', 'icon-sm') ?> <a href="tel:+<?= e(whatsapp_number()) ?>"><?= e(setting('phone', '+90 505 801 61 26')) ?></a></li>
                    <li><?= icon('clock', 'icon-sm') ?> <?= e(setting('working_hours')) ?></li>
                </ul>
                <a class="btn btn-wa btn-block" style="margin-top:14px" href="<?= e(whatsapp_link(t('whatsapp.default_message'))) ?>" target="_blank" rel="noopener"><?= icon('whatsapp', 'icon-sm') ?> <?= e(t('btn.whatsapp')) ?></a>
                <a class="btn btn-outline btn-block" style="margin-top:10px" href="<?= e(setting('instagram_url', INSTAGRAM_URL)) ?>" target="_blank" rel="noopener"><?= icon('instagram', 'icon-sm') ?> <?= e(t('btn.instagram')) ?></a>
            </div>
        </aside>
    </div>
</section>
<?php old_clear(); require BASE_PATH . '/app/views/layout/footer.php'; ?>
