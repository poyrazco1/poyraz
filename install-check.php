<?php

declare(strict_types=1);

/**
 * Kurulum kontrol aracı.
 *
 * Plesk'e yükledikten sonra temel gereksinimleri ve veritabanı bağlantısını
 * kontrol eder. Kullanıcıya sade bir sonuç listesi gösterir. DEBUG kapalıyken
 * teknik hata detayları gizlenir.
 */

require_once __DIR__ . '/config.php';

if (defined('DEFAULT_TIMEZONE') && DEFAULT_TIMEZONE !== '') {
    date_default_timezone_set(DEFAULT_TIMEZONE);
}

/**
 * @var array<int, array{label:string, ok:bool, detail:string}> $checks
 */
$checks = [];

/**
 * Kontrol sonucu ekler.
 */
$add = static function (string $label, bool $ok, string $detail = '') use (&$checks): void {
    $checks[] = ['label' => $label, 'ok' => $ok, 'detail' => $detail];
};

// 1) PHP sürümü
$phpOk = version_compare(PHP_VERSION, '8.2.0', '>=');
$add('PHP sürümü (>= 8.2)', $phpOk, 'Mevcut: ' . PHP_VERSION);

// 2) PDO
$add('PDO eklentisi', extension_loaded('pdo'));

// 3) PDO MySQL
$add('PDO MySQL sürücüsü', extension_loaded('pdo_mysql'));

// 4) config.php okunabiliyor mu?
$configReadable = is_readable(__DIR__ . '/config.php');
$add('config.php okunabilir', $configReadable);

// 5) kur.php erişilebilir mi?
$add('kur.php mevcut', is_file(__DIR__ . '/kur.php'));

// 6) logs klasörü yazılabilir mi?
$logsDir = __DIR__ . '/logs';
if (!is_dir($logsDir)) {
    @mkdir($logsDir, 0755, true);
}
$add('logs klasörü yazılabilir', is_dir($logsDir) && is_writable($logsDir));

// 7) Veritabanı bağlantısı ve tablolar
$pdo = null;
$dbOk = false;
try {
    if (extension_loaded('pdo_mysql')) {
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        $dbOk = true;
    }
} catch (Throwable $e) {
    $dbOk = false;
}
$add('Veritabanı bağlantısı', $dbOk, DEBUG && !$dbOk ? 'Bağlantı kurulamadı.' : '');

/**
 * Bir tablonun var olup olmadığını güvenli kontrol eder.
 */
$tableExists = static function (?PDO $pdo, string $table): bool {
    if (!$pdo instanceof PDO) {
        return false;
    }
    try {
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) FROM information_schema.tables
             WHERE table_schema = :db AND table_name = :t'
        );
        $stmt->execute([':db' => DB_NAME, ':t' => $table]);
        return (int) $stmt->fetchColumn() > 0;
    } catch (Throwable $e) {
        return false;
    }
};

foreach (['users', 'roles', 'password_resets', 'currency_cache'] as $table) {
    $add('Tablo: ' . $table, $tableExists($pdo, $table));
}

$allOk = true;
foreach ($checks as $c) {
    if (!$c['ok']) {
        $allOk = false;
        break;
    }
}
?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kurulum Kontrolü · <?= htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="robots" content="noindex, nofollow">
    <style>
        *,*::before,*::after{box-sizing:border-box}
        body{font-family:system-ui,-apple-system,"Segoe UI",Arial,sans-serif;margin:0;background:#F3F4F6;color:#1F2937;padding:24px}
        .wrap{max-width:640px;margin:0 auto}
        .card{background:#fff;border:1px solid #E2E5EA;border-radius:10px;padding:24px;box-shadow:0 1px 2px rgba(0,0,0,.04)}
        h1{font-size:20px;margin:0 0 4px}
        .lead{color:#6B7280;font-size:14px;margin:0 0 20px}
        .summary{padding:12px 14px;border-radius:8px;font-weight:600;margin-bottom:18px;font-size:14px}
        .summary.ok{background:#ECFDF3;color:#15803d;border:1px solid #bbf7d0}
        .summary.bad{background:#FEF2F2;color:#b91c1c;border:1px solid #fecaca}
        ul{list-style:none;margin:0;padding:0}
        li{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 4px;border-bottom:1px solid #EEF0F3}
        li:last-child{border-bottom:0}
        .label{font-size:14px}
        .detail{display:block;color:#6B7280;font-size:12px;margin-top:2px}
        .badge{flex:0 0 auto;font-size:12px;font-weight:600;padding:4px 10px;border-radius:999px}
        .badge.pass{background:#ECFDF3;color:#15803d}
        .badge.fail{background:#FEF2F2;color:#b91c1c}
        .foot{margin-top:20px;font-size:13px;color:#6B7280}
        a{color:#1F2937}
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card">
            <h1>Kurulum Kontrolü</h1>
            <p class="lead"><?= htmlspecialchars(SITE_NAME, ENT_QUOTES, 'UTF-8') ?> için temel gereksinimler.</p>

            <?php if ($allOk): ?>
                <div class="summary ok">Tüm kontroller başarılı. Sistem çalışmaya hazır.</div>
            <?php else: ?>
                <div class="summary bad">Bazı kontroller başarısız. Lütfen aşağıdaki maddeleri gözden geçirin.</div>
            <?php endif; ?>

            <ul>
                <?php foreach ($checks as $c): ?>
                    <li>
                        <span class="label">
                            <?= htmlspecialchars($c['label'], ENT_QUOTES, 'UTF-8') ?>
                            <?php if ($c['detail'] !== ''): ?>
                                <span class="detail"><?= htmlspecialchars($c['detail'], ENT_QUOTES, 'UTF-8') ?></span>
                            <?php endif; ?>
                        </span>
                        <span class="badge <?= $c['ok'] ? 'pass' : 'fail' ?>"><?= $c['ok'] ? 'Tamam' : 'Hata' ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>

            <p class="foot">
                Kontroller tamamlandıysa güvenlik için bu dosyayı (<code>install-check.php</code>) ve
                <code>kur.php</code> dışındaki kurulum yardımcılarını sunucudan kaldırabilirsiniz.
                <br><a href="<?= htmlspecialchars(rtrim((string) BASE_URL, '/') . '/index.php', ENT_QUOTES, 'UTF-8') ?>">Ana sayfaya git</a>
            </p>
        </div>
    </div>
</body>
</html>
