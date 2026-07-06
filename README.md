# D4stattoo — Tattoo Studio Web Sitesi

İstanbul / Bağcılar'daki D4stattoo dövme stüdyosu için **Plesk uyumlu, plain PHP 8.2 + MySQL** tabanlı, admin panelli, çok dilli (TR/EN/AR/RU) kurumsal web sitesi.

Framework yok, Composer yok, build adımı yok — **ZIP'i sunucuya atıp kurulur.**

## Özellikler

- 🖤 Siyah / kırmızı / beyaz premium tasarım, tam responsive, AR için RTL
- 🌐 Çok dilli yapı: TR (varsayılan), EN, AR, RU — `/en/...` URL yapısı, TR fallback
- 📄 19 public sayfa: ana sayfa, hakkımızda, sanatçı, 11 hizmet SEO sayfası, galeri, önce/sonra, hijyen, dövme modelleri, randevu, fiyat teklifi, fiyat listesi, SSS, blog, akademi, bakım talimatları, iletişim, KVKK, müşteri takip
- 📝 Formlar MySQL'e kaydedilir: randevu, fiyat teklifi (referans görsel uploadlı), iletişim
- 🛠 16 modüllü admin panel: dashboard, site ayarları (logo/favicon/hero metinleri), sayfalar, hizmetler, blog + kategoriler, galeri + kategoriler, SSS, fiyat listesi, yorumlar, kampanyalar, randevular, teklifler, mesajlar, müşteri takip, dil yönetimi
- 💬 WhatsApp entegrasyonu: sabit buton, otomatik mesajlı teklif butonları, admin panelden müşteriye hazır mesaj gönderme
- 🔍 SEO: dinamik title/description, canonical, hreflang, Open Graph, Twitter Card, JSON-LD (TattooParlor, Article, FAQPage, BreadcrumbList), dinamik `sitemap.xml` ve `robots.txt`
- 🔒 Güvenlik: CSRF token (tüm POST'lar), bcrypt şifre, brute-force kilidi, XSS escape, MIME + boyut kontrollü upload, güvenli dosya adı, uploads klasöründe PHP çalıştırma engeli, honeypot spam koruması

## Gereksinimler

| Bileşen | Sürüm |
|---|---|
| PHP | 8.1+ (8.2 önerilir; 8.4'e kadar test edildi) |
| MySQL / MariaDB | MySQL 5.7+ / MariaDB 10.3+ |
| Apache | mod_rewrite açık (Plesk varsayılanı) |
| PHP eklentileri | pdo_mysql, fileinfo, mbstring |

## Hızlı Kurulum (Plesk)

Ayrıntılı adımlar için **[KURULUM.md](KURULUM.md)** dosyasına bakın.

1. Proje ZIP'ini Plesk Dosya Yöneticisi ile `httpdocs` köküne çıkarın.
2. Plesk > Veritabanları'ndan bir veritabanı + kullanıcı oluşturun.
3. phpMyAdmin > İçe Aktar ile **`database/install.sql`** dosyasını import edin (tek dosya, tüm tablolar + demo içerik).
4. `config.php` içinde `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` ve `APP_URL` değerlerini düzenleyin.
5. `assets/uploads` ve `storage/logs` klasörlerinin yazılabilir olduğunu kontrol edin (755/775).
6. Siteyi açın; `/admin` adresinden panele giriş yapın.

## Admin Giriş Bilgileri

- **Panel:** `https://siteniz.com/admin`
- **E-posta:** `hasanacar6161@gmail.com`
- **Şifre:** `Hasanacar123123++`

> Şifre veritabanında `password_hash` (bcrypt) ile saklanır. `config.php` içindeki
> `ADMIN_EMAIL` / `ADMIN_PASSWORD_HASH`, veritabanına erişilemediğinde çalışan
> kurtarma girişidir. Yeni hash üretmek için:
> `php -r "echo password_hash('yeni-sifre', PASSWORD_BCRYPT);"`

## Dizin Yapısı

```
├── index.php              # Ön kontrolcü (tüm SEO URL'ler)
├── config.php             # TEK yapılandırma dosyası
├── .htaccess              # Plesk/Apache rewrite + güvenlik
├── admin/                 # Admin paneli (bağımsız PHP sayfaları)
├── app/
│   ├── core/              # Database, Auth, Router, Csrf, Lang, bootstrap
│   ├── helpers/           # functions, security, seo, upload
│   ├── controllers/       # Form POST işleyicileri
│   ├── views/layout/      # header, footer, navbar, dil, whatsapp, popup
│   ├── views/pages/       # Public sayfa görünümleri
│   └── lang/              # tr.php, en.php, ar.php, ru.php
├── assets/
│   ├── css/  js/  img/    # Statik dosyalar (style.css, admin.css, main.js)
│   └── uploads/           # Panelden yüklenen dosyalar (yazılabilir olmalı)
├── database/install.sql   # TEK kurulum SQL dosyası
└── storage/logs/          # Hata logları (yazılabilir olmalı)
```

## Sık Hatalar ve Çözümleri

| Sorun | Çözüm |
|---|---|
| "Veritabanına bağlanılamadı" | `config.php` içindeki DB bilgilerini Plesk'teki değerlerle karşılaştırın. Plesk'te host genelde `localhost`'tur. |
| Ana sayfa açılıyor ama alt sayfalar 404 | mod_rewrite kapalı olabilir ya da `.htaccess` işlenmiyordur (Plesk > Apache & nginx Ayarları). Alt dizine kurduysanız `.htaccess` içindeki `RewriteBase` satırını açın. |
| Görsel yüklenmiyor | `assets/uploads` iznini 755/775 yapın; PHP `upload_max_filesize` ≥ 5M olmalı (Plesk > PHP Ayarları). |
| Site kök yerine alt dizinde | `APP_URL`'i tam adresle doldurun (örn. `https://site.com/alt-dizin`). |
| Türkçe karakter bozuk | Veritabanı `utf8mb4_unicode_ci` olmalı; install.sql'i yeniden import edin. |
| Beyaz sayfa | `storage/logs/php-error.log` dosyasına bakın; geçici olarak `config.php` içinde `APP_DEBUG` true yapın. |

## Dokümanlar

- [KURULUM.md](KURULUM.md) — adım adım Plesk kurulumu
- [ADMIN-KULLANIM.md](ADMIN-KULLANIM.md) — panel kullanım rehberi
- [CHANGELOG.md](CHANGELOG.md) — sürüm geçmişi
