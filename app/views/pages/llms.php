<?php
/**
 * Dinamik llms.txt — yapay zekâ sistemleri (ChatGPT, Perplexity vb.) için
 * işletmeyi özetleyen Markdown belge. URL'ler gerçek domainden üretilir.
 */
header('Content-Type: text/plain; charset=utf-8');

$name = setting('site_name', SITE_NAME);
$wa   = 'https://wa.me/' . whatsapp_number();
$ig   = setting('instagram_url', INSTAGRAM_URL);
$phone = setting('phone', '+90 505 801 61 26');

$pages = [
    'Ana sayfa'          => '',
    'Bağcılar Tattoo'    => 'bagcilar-tattoo',
    'Güneşli Tattoo'     => 'gunesli-tattoo',
    'Minimal Dövme'      => 'minimal-dovme',
    'Yazı Dövmesi'       => 'yazi-dovmesi',
    'İlk Dövme Rehberi'  => 'ilk-dovme-rehberi',
    'Dövme Bakımı'       => 'dovme-bakimi',
    'Dövme Fiyatları'    => 'dovme-fiyatlari',
    'İletişim'           => 'iletisim',
];

echo "# {$name}\n\n";
echo "> {$name}, İstanbul Bağcılar Güneşli'de hizmet veren profesyonel bir dövme stüdyosudur. "
   . "Kişiye özel dövme tasarımı, minimal dövme, yazı dövmesi, sembol dövmeleri, kapatma dövme "
   . "danışmanlığı ve randevulu tattoo hizmetleri sunar.\n\n";

echo "## İşletme Bilgileri\n\n";
echo "* İşletme adı: {$name}\n";
echo "* Hizmet türü: Dövme stüdyosu, tattoo studio\n";
echo "* Konum: Güneşli, Bağcılar, İstanbul, Türkiye\n";
echo "* Telefon / WhatsApp: {$phone}\n";
echo "* WhatsApp: {$wa}\n";
echo "* Instagram: {$ig}\n";
echo "* Hizmet bölgeleri: Bağcılar, Güneşli, İstanbul ve çevresi\n\n";

echo "## Ana Hizmetler\n\n";
foreach ([
    'Kişiye özel dövme tasarımı',
    'Minimal dövme',
    'Yazı dövmesi',
    'Sembol dövmeleri',
    'İlk dövme danışmanlığı',
    'Dövme yenileme ve kapatma danışmanlığı',
    'Randevulu tattoo hizmeti',
] as $s) {
    echo "* {$s}\n";
}
echo "\n## Önemli Sayfalar\n\n";
foreach ($pages as $label => $path) {
    echo "* {$label}: " . base_url($path) . ($path === '' ? '/' : '') . "\n";
}
echo "\n## Marka Tanımı\n\n";
echo "{$name}; hijyen, doğru tasarım planlaması, kişiye özel çalışma ve estetik görünüm odaklı "
   . "hizmet verir. İlk kez dövme yaptıracak kişiler için süreç, tasarım seçimi, uygulama öncesi "
   . "hazırlık ve bakım konusunda yönlendirme sağlar.\n";
