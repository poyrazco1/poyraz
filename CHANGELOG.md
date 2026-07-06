# Changelog

## v1.0.0 — 2026-07-06

İlk sürüm. Plesk uyumlu, plain PHP 8.2 + MySQL, framework'süz.

### Part 1.1 — İskelet ve güvenli temel
- Dizin yapısı, `config.php`, `.htaccess` (rewrite + dizin koruması + güvenlik başlıkları)
- Çekirdek: `Database` (PDO), `Auth` (bcrypt + brute-force kilidi), `Router` (SEO URL + dil prefix), `Csrf`, `Lang`
- Yardımcılar: genel fonksiyonlar, güvenli session, form doğrulama, SEO etiketleri, güvenli upload
- Admin login/logout, log altyapısı (`storage/logs`)

### Part 1.2 — Veritabanı
- Tek dosya `database/install.sql`: 17 tablo (IF NOT EXISTS, utf8mb4_unicode_ci, InnoDB)
- INSERT IGNORE ile idempotent demo veri; tekrar import mevcut veriyi bozmaz
- TR dolu içerik: 11 hizmet SEO sayfası, 10 blog yazısı, 8 SSS, 7 fiyat kalemi, 5 yorum, 4 içerik sayfası, galeri (16 öğe)
- EN/AR/RU temel içerikler; admin hesabı bcrypt hash ile

### Part 2.1 — Public site
- Siyah/kırmızı/beyaz premium tema, tam responsive, yatay taşma yok
- Gruplu dropdown navbar, dil değiştirici, sabit WhatsApp butonu, pop-up teklif formu
- 19 public sayfa + dinamik `sitemap.xml` ve `robots.txt`
- Lightbox'lı, kategori filtreli galeri; önce/sonra karşılaştırma kartları

### Part 2.2 — Admin panel
- Koyu temalı, mobil uyumlu panel: dashboard + 15 modül
- İçerik CRUD'ları, görsel upload'lı formlar, dil/durum filtreleri, arama
- Randevu ve teklif detaylarından hazır mesajlı WhatsApp gönderimi
- Müşteri takip kodu üretimi ve public takip sayfası

### Part 2.3 — Formlar ve upload
- Randevu / fiyat teklifi / iletişim formları MySQL'e kayıt (PRG deseni)
- CSRF zorunlu, honeypot, server-side validation (telefon/e-posta)
- Upload: finfo MIME kontrolü, 5 MB limit, JPG/PNG/WEBP beyaz listesi, rastgele güvenli dosya adı, uploads içinde PHP çalıştırma engeli

### Part 2.4 — SEO
- Dinamik title/description/canonical/hreflang/OG/Twitter
- JSON-LD: TattooParlor, Article, FAQPage, BreadcrumbList
- Her hizmet için ayrı SEO sayfası; hedef anahtar kelimeler içeriklerde doğal kullanım

### Part 2.5 — Çok dil
- TR varsayılan (prefix'siz), EN/AR/RU `/en/...` yapısı, `/tr/...` → 301
- AR için `dir="rtl"` ve RTL uyumlu CSS (logical properties)
- Eksik çevirilerde TR fallback; admin'de dil seçimi ve filtreler

### Part 2.6 — Test ve teslim
- MariaDB'de gerçek import + yeniden import testi
- 29 public URL, 16 admin modülü, form kayıtları, upload, CSRF/XSS/sahte dosya güvenlik testleri
- README, KURULUM, ADMIN-KULLANIM dokümanları
