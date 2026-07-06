# D4stattoo — Plesk Kurulum Rehberi

Bu rehber, projeyi sıfırdan bir Plesk hosting'e kurmanız için adım adım hazırlanmıştır.
Toplam süre: ~10 dakika.

## 1. ZIP'i Plesk Dosya Yöneticisine Yükleyin

1. Plesk paneline giriş yapın.
2. **Dosyalar (File Manager)** bölümünü açın.
3. `httpdocs` klasörüne girin (varsa eski `index.html` vb. dosyaları silin).
4. **Yükle** butonuyla proje ZIP dosyasını yükleyin.

## 2. Kök Dizine Çıkarın

1. Yüklenen ZIP'e sağ tıklayıp **Arşivden Çıkar (Extract Files)** deyin.
2. Çıkarma sonrası `httpdocs` içinde doğrudan `index.php`, `config.php`, `admin/`, `app/` görünmelidir.
   - Dosyalar bir alt klasöre çıktıysa (örn. `httpdocs/proje/`), hepsini seçip `httpdocs` köküne taşıyın.
3. ZIP dosyasını silebilirsiniz.

## 3. Veritabanı Oluşturun

1. Plesk > **Veritabanları** > **Veritabanı Ekle**.
2. Örnek değerler:
   - Veritabanı adı: `d4stattoo`
   - Kullanıcı: `d4user`
   - Şifre: güçlü bir şifre belirleyin (not alın)
3. **Tamam** ile oluşturun.

## 4. database/install.sql Dosyasını Import Edin

1. Oluşturduğunuz veritabanının yanındaki **phpMyAdmin** bağlantısını açın.
2. Üst menüden **İçe Aktar (Import)** sekmesine geçin.
3. **Dosya seç** ile projedeki `database/install.sql` dosyasını seçin
   (bilgisayarınıza indirip seçebilir ya da Plesk dosya yöneticisinden indirebilirsiniz).
4. Karakter seti `utf8mb4` olarak kalsın, **Git (Go)** deyin.
5. "Import has been successfully finished" mesajını görmelisiniz.
   17 tablo ve demo içerik otomatik yüklenir. Dosya yanlışlıkla ikinci kez
   import edilirse mevcut veriler bozulmaz.

## 5. config.php Dosyasını Düzenleyin

Plesk Dosya Yöneticisi'nde `config.php`'ye sağ tıklayıp **Düzenle** deyin ve şu satırları kendi bilgilerinizle değiştirin:

```php
define('DB_HOST', 'localhost');          // Plesk'te genelde localhost
define('DB_NAME', 'd4stattoo');          // 3. adımda verdiğiniz ad
define('DB_USER', 'd4user');             // 3. adımdaki kullanıcı
define('DB_PASS', 'BURAYA-SIFRENIZ');    // 3. adımdaki şifre

define('APP_URL', 'https://www.siteniz.com'); // sonda / OLMADAN
```

Diğer ayarlar (WhatsApp numarası, Instagram) varsayılan olarak doludur;
isterseniz admin panelden de değiştirebilirsiniz.

## 6. Upload Klasör İzinlerini Kontrol Edin

Dosya Yöneticisi'nde şu klasörlerin izinlerinin **yazılabilir** olduğundan emin olun
(genelde 755 yeterlidir; sorun olursa 775 deneyin):

- `assets/uploads` (ve altındaki blog, gallery, services, references, settings)
- `storage/logs`

## 7. Siteyi Açın

`https://www.siteniz.com` adresine gidin. Demo içerikli ana sayfa gelmelidir.
Alt sayfaları da test edin: `/hizmetler`, `/randevu-al`, `/blog`.

> Alt sayfalar 404 veriyorsa: Plesk > **Apache & nginx Ayarları** bölümünde
> `.htaccess` dosyasının işlendiğinden emin olun. Alt dizine kurulum yaptıysanız
> `.htaccess` içindeki `# RewriteBase /` satırını açıp yolu yazın.

## 8. Admin Panelden Giriş Yapın

1. `https://www.siteniz.com/admin` adresini açın.
2. Giriş bilgileri:
   - E-posta: `hasanacar6161@gmail.com`
   - Şifre: `Hasanacar123123++`

## 9. Logo / Favicon / Site Ayarlarını Düzenleyin

1. Panelde **Site Ayarları** modülünü açın.
2. Logo ve favicon dosyalarınızı yükleyin.
3. Hero başlık, slogan, telefon, WhatsApp, Instagram ve çalışma saatlerini kontrol edin.
4. **Kaydet** deyin — değişiklikler anında sitede görünür.
5. Demo içerikleri (hizmet metinleri, blog yazıları, galeri görselleri, yorumlar)
   ilgili modüllerden kendi içeriklerinizle değiştirin.

## Kurulum Sonrası Kontrol Listesi

- [ ] Ana sayfa ve alt sayfalar açılıyor
- [ ] `/admin` girişi çalışıyor
- [ ] Site Ayarları'ndan logo yüklenebiliyor
- [ ] Randevu formu gönderilebiliyor ve panelde görünüyor
- [ ] `sitemap.xml` açılıyor
- [ ] HTTPS sertifikası aktif (Plesk > SSL/TLS, Let's Encrypt ücretsizdir)
