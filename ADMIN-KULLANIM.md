# D4stattoo — Admin Panel Kullanım Rehberi

Panel adresi: `https://siteniz.com/admin`

## Giriş ve Güvenlik

- Giriş e-posta + şifre iledir; 5 hatalı denemeden sonra giriş 15 dakika kilitlenir.
- Şifreler veritabanında bcrypt ile saklanır, düz metin tutulmaz.
- Panelden çıkmak için sol menünün altındaki **Çıkış Yap**'ı kullanın.
- Şifre değiştirmek için yeni hash üretin (`php -r "echo password_hash('yeni', PASSWORD_BCRYPT);"`)
  ve phpMyAdmin'de `admins` tablosundaki `password_hash` alanına yapıştırın.

## Modüller

### Dashboard
Toplam randevu, bekleyen fiyat teklifi, galeri görseli ve blog sayıları;
son randevular ve son iletişim mesajları. Kırmızı çerçeveli kartlar bekleyen iş demektir.

### Site Ayarları
Sitenin bütün "sabit" metin ve görselleri buradan yönetilir:
- **Metinler:** site adı, slogan, hero başlık/alt metin/buton yazıları, footer metni, sanatçı adı ve tanıtımı, telefon, WhatsApp, Instagram, çalışma saatleri.
- **Diller:** sağ üstteki dil seçimiyle her dil için ayrı metin girersiniz. Bir dilde boş bırakılan alan sitede TR değeriyle gösterilir.
- **Görseller:** logo, favicon, sosyal paylaşım görseli (OG) ve sanatçı fotoğrafı. Yeni dosya yüklediğinizde eskisi otomatik silinir.

### Sayfalar
Hakkımızda, Hijyen, Bakım Talimatları ve KVKK gibi içerik sayfaları.
HTML destekler (p, h2-h4, listeler, tablo, görsel). Slug alanı URL'yi belirler:
`hakkimizda` → `siteniz.com/hakkimizda`. Yeni sayfa eklerseniz otomatik olarak
`siteniz.com/{slug}` adresinde yayınlanır ve sitemap'e eklenir.

### Hizmetler
Her hizmet `/hizmetler/{slug}` adresinde ayrı bir SEO sayfasıdır.
- **Detay içerik** alanında önerilen bölüm yapısı: "Bu Stil Nedir?", "Kimler İçin Uygundur?", "Ortalama Süreç", "İyileşme ve Bakım", "Fiyat Nasıl Belirlenir?" (h2 başlıklarıyla).
- Meta title / description alanları Google sonuç görünümünü belirler.
- Sıralama sayısı küçük olan önce gösterilir; pasif hizmet sitede görünmez.

### Blog Yazıları & Kategorileri
- Yazılar kapak görseli, özet, etiket ve SEO alanlarıyla eklenir.
- **Taslak** durumundaki yazı sitede görünmez.
- "Akademi" kategorisine eklenen yazılar `/akademi` sayfasında da listelenir.

### Galeri & Kategorileri
Üç görsel türü vardır:
- **Portfolyo Görseli** — galeri sayfası ve ana sayfa vitrini.
- **Önce / Sonra** — iki görsel yüklenir, `/once-sonra` sayfasında çift olarak gösterilir.
- **Dövme Modeli** — `/dovme-modelleri` sayfasında listelenir.

Görseller hangi boyutta yüklenirse yüklensin sitede sabit oranlı kartlara otomatik hizalanır (kırpma `object-fit` ile yapılır); yine de 800px+ genişlik önerilir. Alt metin alanı SEO için önemlidir.

### Randevular
Formdan gelen talepler burada listelenir. Detay sayfasında:
- Durumu değiştirin: Yeni → Onaylandı → Tamamlandı / İptal.
- **Admin notu** ekleyin (müşteri görmez).
- **WhatsApp'tan Mesaj Gönder** butonu, müşterinin numarasına talep özetini içeren hazır bir mesaj taslağıyla WhatsApp açar.

### Fiyat Teklifleri
Teklif formu ve pop-up'tan gelen talepler. Referans görseli detayda görüntülenir.
Durumlar: Yeni → Teklif Verildi → Kabul Edildi / Kapatıldı.
**WhatsApp'tan Teklif Gönder** ile hazır mesajla dönüş yapabilirsiniz.

### İletişim Mesajları
Mesajı açmak okundu sayar; listeden **Okundu / Bekliyor** durumunu elle de değiştirebilirsiniz.

### Fiyat Listesi
`/fiyat-listesi` sayfasındaki kalemler. Fiyat metnini "1.500 ₺'den başlayan örnek fiyat"
gibi yazın — "kesin fiyat için ön değerlendirme gerekir" notu sayfada otomatik gösterilir.

### Yorumlar
Google / Instagram / Site kaynaklı müşteri yorumları. Ana sayfadaki "Müşterilerimiz Ne Diyor?"
bölümünde son 3 aktif yorum gösterilir.

### Kampanyalar
Aktif ve tarih aralığı geçerli olan son kampanya, ana sayfanın en üstünde kırmızı bant
olarak çıkar. Sağ üstteki **Ana Sayfada Göster/Gizle** düğmesi bandı tamamen kapatır.

### Müşteri Takip
1. **Kod Oluştur** ile müşteri kaydı açın — sistem benzersiz bir takip kodu üretir.
2. Kodu **WhatsApp'la Gönder** butonuyla müşteriye iletin.
3. Müşteri `siteniz.com/takip` adresinden kodla durumunu ve "müşteriye açık not"u görür.
4. "Özel not" yalnızca panelde görünür.

### Dil Yönetimi
- TR / EN / AR / RU dillerini aktif-pasif yapabilirsiniz (TR kapatılamaz).
- Tabloda her dildeki içerik sayıları görünür; eksik dillerde site TR içeriğe düşer.
- Menü/buton çevirileri `app/lang/*.php` dosyalarındadır (anahtar-değer, düzenlemesi kolay).

## İpuçları

- Her listede dil ve durum filtreleri vardır; büyük listelerde arama kutusunu kullanın.
- Silme işlemleri onay ister ve geri alınamaz; içeriği silmek yerine **Pasif** yapmayı düşünün.
- Upload limiti 5 MB'dir ve yalnızca JPG/PNG/WEBP kabul edilir (favicon için ICO/SVG de olur).
