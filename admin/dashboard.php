<?php
require_once __DIR__ . '/partials/top.php';

$stats = [
    'appointments' => (int) Database::value('SELECT COUNT(*) FROM appointments'),
    'new_appointments' => (int) Database::value("SELECT COUNT(*) FROM appointments WHERE status = 'new'"),
    'quotes'       => (int) Database::value('SELECT COUNT(*) FROM quote_requests'),
    'new_quotes'   => (int) Database::value("SELECT COUNT(*) FROM quote_requests WHERE status = 'new'"),
    'gallery'      => (int) Database::value('SELECT COUNT(*) FROM gallery_items WHERE status = 1'),
    'posts'        => (int) Database::value('SELECT COUNT(*) FROM blog_posts WHERE status = 1'),
    'messages'     => (int) Database::value("SELECT COUNT(*) FROM contact_messages WHERE status = 'new'"),
    'services'     => (int) Database::value('SELECT COUNT(*) FROM services WHERE status = 1'),
];
$lastAppointments = Database::all('SELECT * FROM appointments ORDER BY created_at DESC LIMIT 6');
$lastMessages = Database::all('SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 6');

$pageTitle = 'Dashboard';
$active = 'dashboard';
require __DIR__ . '/partials/header.php';
?>
<div class="page-head">
    <div>
        <h1>Hoş geldin, <?= e(Auth::name()) ?> 👋</h1>
        <p>Stüdyonun güncel durumu aşağıda.</p>
    </div>
    <a class="btn btn-primary" href="<?= e(base_url()) ?>" target="_blank">Siteyi Görüntüle ↗</a>
</div>

<div class="stat-grid">
    <div class="stat-card <?= $stats['new_appointments'] ? 'hot' : '' ?>">
        <div class="num"><?= icon('calendar', 'icon-lg') ?> <?= $stats['appointments'] ?></div>
        <div class="lbl">Toplam Randevu (<?= $stats['new_appointments'] ?> yeni)</div>
    </div>
    <div class="stat-card <?= $stats['new_quotes'] ? 'hot' : '' ?>">
        <div class="num"><?= icon('price', 'icon-lg') ?> <?= $stats['quotes'] ?></div>
        <div class="lbl">Fiyat Teklifi (<?= $stats['new_quotes'] ?> bekleyen)</div>
    </div>
    <div class="stat-card">
        <div class="num"><?= icon('gallery', 'icon-lg') ?> <?= $stats['gallery'] ?></div>
        <div class="lbl">Galeri Görseli</div>
    </div>
    <div class="stat-card">
        <div class="num"><?= icon('blog', 'icon-lg') ?> <?= $stats['posts'] ?></div>
        <div class="lbl">Yayında Blog Yazısı</div>
    </div>
</div>

<div class="two-col">
    <div class="panel">
        <h2>Son Randevu Talepleri</h2>
        <div class="table-wrap" style="border:0">
            <table>
                <thead><tr><th>Ad Soyad</th><th>Bölge</th><th>Durum</th><th>Tarih</th></tr></thead>
                <tbody>
                <?php if (!$lastAppointments): ?>
                    <tr class="empty-row"><td colspan="4">Henüz randevu talebi yok.</td></tr>
                <?php endif; ?>
                <?php foreach ($lastAppointments as $a): ?>
                    <tr>
                        <td><a href="<?= e(admin_url('appointments.php', ['action' => 'view', 'id' => $a['id']])) ?>"><?= e($a['full_name']) ?></a></td>
                        <td><?= e($a['tattoo_area']) ?></td>
                        <td><?= status_badge($a['status']) ?></td>
                        <td><?= e(format_date($a['created_at'], true)) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p style="margin:14px 0 0"><a class="btn btn-ghost btn-sm" href="<?= e(admin_url('appointments.php')) ?>">Tüm randevular →</a></p>
    </div>
    <div class="panel">
        <h2>Son İletişim Mesajları</h2>
        <div class="table-wrap" style="border:0">
            <table>
                <thead><tr><th>Ad Soyad</th><th>Konu</th><th>Durum</th><th>Tarih</th></tr></thead>
                <tbody>
                <?php if (!$lastMessages): ?>
                    <tr class="empty-row"><td colspan="4">Henüz mesaj yok.</td></tr>
                <?php endif; ?>
                <?php foreach ($lastMessages as $m): ?>
                    <tr>
                        <td><a href="<?= e(admin_url('messages.php', ['action' => 'view', 'id' => $m['id']])) ?>"><?= e($m['full_name']) ?></a></td>
                        <td><?= e(excerpt_of($m['subject'] ?: $m['message'], 40)) ?></td>
                        <td><?= status_badge($m['status']) ?></td>
                        <td><?= e(format_date($m['created_at'], true)) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p style="margin:14px 0 0"><a class="btn btn-ghost btn-sm" href="<?= e(admin_url('messages.php')) ?>">Tüm mesajlar →</a></p>
    </div>
</div>
<?php require __DIR__ . '/partials/footer.php'; ?>
