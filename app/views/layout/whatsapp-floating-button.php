<?php /** Sabit WhatsApp butonu */ ?>
<a class="wa-float" href="<?= e(whatsapp_link(t('whatsapp.default_message'))) ?>"
   target="_blank" rel="noopener" aria-label="<?= e(t('whatsapp.floating_label')) ?>">
    <?= icon('whatsapp', '', 24) ?>
    <span class="wa-float-label"><?= e(t('whatsapp.floating_label')) ?></span>
</a>
