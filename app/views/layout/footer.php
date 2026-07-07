<?php
/** Ortak alt şablon: footer + WhatsApp butonu + popup form + JS */
$footServices = rows_lang(
    'SELECT title, slug FROM services WHERE lang = ? AND status = 1 ORDER BY sort_order LIMIT 6'
);
?>
</main>
<footer class="site-footer">
    <div class="container footer-grid">
        <div class="footer-col footer-brand">
            <img src="<?= e(setting('logo') ? upload_url(setting('logo')) : asset('img/logo.svg')) ?>"
                 alt="<?= e(setting('site_name', SITE_NAME)) ?> Güneşli Bağcılar dövme stüdyosu" class="footer-logo">
            <p><?= e(setting('footer_text')) ?></p>
            <address class="footer-address">
                <strong><?= e(setting('site_name', SITE_NAME)) ?></strong><br>
                <?= icon('location', 'icon-sm') ?> Güneşli, Bağcılar / İstanbul<br>
                <?= icon('phone', 'icon-sm') ?> WhatsApp: <a href="tel:+<?= e(whatsapp_number()) ?>"><?= e(setting('phone', '+90 505 801 61 26')) ?></a>
            </address>
            <div class="footer-social">
                <a href="<?= e(whatsapp_link('Merhaba, ' . setting('site_name', SITE_NAME) . ' hakkında bilgi almak istiyorum.')) ?>" target="_blank" rel="noopener" class="social-link"><?= icon('whatsapp', 'icon-sm') ?> WhatsApp ile Randevu Al</a>
                <a href="<?= e(setting('instagram_url', INSTAGRAM_URL)) ?>" target="_blank" rel="noopener" aria-label="Instagram" class="social-link"><?= icon('instagram', 'icon-sm') ?> @d4stattoo</a>
            </div>
        </div>
        <div class="footer-col">
            <h3><?= e(t('footer.quick_links')) ?></h3>
            <ul>
                <li><a href="<?= e(url('hakkimizda')) ?>"><?= e(t('nav.about')) ?></a></li>
                <li><a href="<?= e(url('galeri')) ?>"><?= e(t('nav.portfolio')) ?></a></li>
                <li><a href="<?= e(url('randevu-al')) ?>"><?= e(t('nav.appointment')) ?></a></li>
                <li><a href="<?= e(url('fiyat-teklifi-al')) ?>"><?= e(t('btn.quote')) ?></a></li>
                <li><a href="<?= e(url('sss')) ?>"><?= e(t('nav.faq')) ?></a></li>
                <li><a href="<?= e(url('istanbul')) ?>">İstanbul Tattoo Bölgeleri</a></li>
                <li><a href="<?= e(url('takip')) ?>"><?= e(t('nav.tracking')) ?></a></li>
                <li><a href="<?= e(url('kvkk')) ?>"><?= e(t('nav.kvkk')) ?></a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h3><?= e(t('footer.services')) ?></h3>
            <ul>
                <?php foreach ($footServices as $s): ?>
                <li><a href="<?= e(url('hizmetler/' . $s['slug'])) ?>"><?= e($s['title']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="footer-col">
            <h3><?= e(t('footer.contact')) ?></h3>
            <ul class="footer-contact">
                <li><?= icon('location', 'icon-sm') ?> <?= e(setting('location_text', 'İstanbul / Bağcılar')) ?></li>
                <li><?= icon('phone', 'icon-sm') ?> <a href="tel:+<?= e(whatsapp_number()) ?>"><?= e(setting('phone', '+90 505 801 61 26')) ?></a></li>
                <li><?= icon('clock', 'icon-sm') ?> <?= e(setting('working_hours', t('footer.hours_value'))) ?></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container">
            <span>© <?= date('Y') ?> <?= e(setting('site_name', SITE_NAME)) ?> — <?= e(t('footer.rights')) ?></span>
        </div>
    </div>
</footer>

<?php require BASE_PATH . '/app/views/layout/whatsapp-floating-button.php'; ?>
<?php if (setting('popup_show', '1') === '1') {
    require BASE_PATH . '/app/views/layout/popup-quote-form.php';
} ?>

<script src="<?= e(asset('js/main.js')) ?>" defer></script>
</body>
</html>
