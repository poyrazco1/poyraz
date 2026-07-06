<?php /** Pop-up hızlı teklif formu — main.js gecikmeli açar, kapatınca tekrar göstermez */ ?>
<div class="popup-overlay" id="quotePopup" hidden>
    <div class="popup-box" role="dialog" aria-modal="true" aria-labelledby="popupTitle">
        <button type="button" class="popup-close" data-popup-close aria-label="<?= e(t('btn.close')) ?>">×</button>
        <h2 id="popupTitle"><?= e(t('popup.title')) ?></h2>
        <p><?= e(t('popup.text')) ?></p>
        <form method="post" action="<?= e(url('fiyat-teklifi-al')) ?>" class="popup-form">
            <?= Csrf::field() ?>
            <input type="hidden" name="form_source" value="popup">
            <input type="text" name="website" value="" class="hp-field" tabindex="-1" autocomplete="off" aria-hidden="true">
            <label class="sr-only" for="pq-name"><?= e(t('field.full_name')) ?></label>
            <input id="pq-name" type="text" name="full_name" required maxlength="150" placeholder="<?= e(t('field.full_name')) ?>">
            <label class="sr-only" for="pq-phone"><?= e(t('field.phone')) ?></label>
            <input id="pq-phone" type="tel" name="phone" required maxlength="20" placeholder="<?= e(t('field.phone')) ?>">
            <label class="sr-only" for="pq-style"><?= e(t('field.style')) ?></label>
            <input id="pq-style" type="text" name="style" maxlength="150" placeholder="<?= e(t('field.style')) ?> — <?= e(t('placeholder.tattoo_area')) ?>">
            <button type="submit" class="btn btn-primary btn-block"><?= e(t('btn.quote')) ?></button>
        </form>
        <a class="popup-wa" href="<?= e(whatsapp_link(t('whatsapp.default_message'))) ?>" target="_blank" rel="noopener">
            <?= e(t('btn.whatsapp_quote')) ?>
        </a>
    </div>
</div>
