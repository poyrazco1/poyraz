<?php
/** Dil değiştirici — aktif sayfanın diğer dillerdeki karşılığına bağlanır */
$switchPath = $currentPath ?? '';
?>
<div class="lang-switcher" data-dropdown>
    <button type="button" class="lang-btn" aria-label="<?= e(t('lang.switch')) ?>" aria-expanded="false">
        <?= e(strtoupper(Lang::current())) ?> <span class="caret"></span>
    </button>
    <ul class="lang-menu">
        <?php foreach (active_languages() as $l): ?>
        <li>
            <a href="<?= e(url($switchPath, $l['code'])) ?>"
               <?= $l['code'] === Lang::current() ? 'class="active"' : '' ?>
               hreflang="<?= e($l['code']) ?>"><?= e($l['name']) ?></a>
        </li>
        <?php endforeach; ?>
    </ul>
</div>
