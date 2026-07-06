<?php
/**
 * Güvenli dosya yükleme yardımcıları.
 *
 * Kontroller:
 *  - PHP upload hata kodu
 *  - Boyut limiti (UPLOAD_MAX_BYTES)
 *  - Uzantı beyaz listesi
 *  - finfo ile gerçek MIME kontrolü
 *  - getimagesize ile görüntü doğrulaması
 *  - Rastgele üretilen güvenli dosya adı
 */

const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];
const IMAGE_MIMES = [
    'image/jpeg' => ['jpg', 'jpeg'],
    'image/png'  => ['png'],
    'image/webp' => ['webp'],
];
// Favicon için ek olarak .ico kabul edilir
const ICON_MIMES = [
    'image/vnd.microsoft.icon' => ['ico'],
    'image/x-icon'             => ['ico'],
    'image/svg+xml'            => ['svg'],
];

/**
 * Görsel yükler.
 *
 * @param array  $file   $_FILES['alan'] dizisi
 * @param string $subdir uploads altındaki hedef klasör (blog|gallery|services|references|settings)
 * @param bool   $required zorunlu mu
 * @param bool   $allowIcon favicon (.ico/.svg) kabul edilsin mi
 * @return array ['ok' => bool, 'path' => ?string, 'error' => ?string]
 *               path: 'assets/uploads/{subdir}/{ad}' şeklinde köke göre yol
 */
function upload_image(array $file, string $subdir, bool $required = false, bool $allowIcon = false): array
{
    $fail = fn (string $msg) => ['ok' => false, 'path' => null, 'error' => $msg];
    $skip = ['ok' => true, 'path' => null, 'error' => null];

    if (!isset($file['error']) || is_array($file['error'])) {
        return $required ? $fail(t('upload.invalid')) : $skip;
    }

    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            return $required ? $fail(t('upload.required')) : $skip;
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return $fail(t('upload.too_big', ['max' => (int) (UPLOAD_MAX_BYTES / 1048576)]));
        default:
            return $fail(t('upload.failed'));
    }

    if ($file['size'] <= 0 || $file['size'] > UPLOAD_MAX_BYTES) {
        return $fail(t('upload.too_big', ['max' => (int) (UPLOAD_MAX_BYTES / 1048576)]));
    }

    if (!is_uploaded_file($file['tmp_name'])) {
        return $fail(t('upload.invalid'));
    }

    // Gerçek MIME tespiti
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = (string) $finfo->file($file['tmp_name']);

    $mimeMap = $allowIcon ? IMAGE_MIMES + ICON_MIMES : IMAGE_MIMES;
    if (!isset($mimeMap[$mime])) {
        return $fail(t('upload.type'));
    }

    // Uzantı, MIME ile uyumlu olmalı (kullanıcının verdiği ada güvenilmez)
    $ext = $mimeMap[$mime][0];

    // Bitmap görüntüler ayrıca doğrulanır
    if (isset(IMAGE_MIMES[$mime]) && @getimagesize($file['tmp_name']) === false) {
        return $fail(t('upload.type'));
    }

    // SVG yüklemede script içeriğini reddet (yalnızca favicon için geçerli olabilir)
    if ($mime === 'image/svg+xml') {
        $svg = (string) file_get_contents($file['tmp_name']);
        if (preg_match('/<\s*script|on\w+\s*=|javascript:/i', $svg)) {
            return $fail(t('upload.type'));
        }
    }

    $subdir = preg_replace('/[^a-z0-9_\-]/', '', strtolower($subdir)) ?: 'misc';
    $dir = UPLOAD_DIR . '/' . $subdir;
    if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
        app_log('Upload klasoru olusturulamadi: ' . $dir);
        return $fail(t('upload.failed'));
    }
    if (!is_writable($dir)) {
        app_log('Upload klasoru yazilabilir degil: ' . $dir);
        return $fail(t('upload.failed'));
    }

    // Güvenli, tahmin edilemez dosya adı
    $name = date('Ymd') . '-' . bin2hex(random_bytes(8)) . '.' . $ext;
    $dest = $dir . '/' . $name;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        app_log('move_uploaded_file basarisiz: ' . $dest);
        return $fail(t('upload.failed'));
    }
    @chmod($dest, 0644);

    return ['ok' => true, 'path' => UPLOAD_URL . '/' . $subdir . '/' . $name, 'error' => null];
}

/** Upload edilmiş dosyayı diskten siler (yol uploads dışına çıkamaz). */
function delete_upload(?string $relative): void
{
    if (!$relative) {
        return;
    }
    $relative = str_replace(['..', "\0"], '', $relative);
    if (!str_starts_with($relative, UPLOAD_URL . '/')) {
        return; // sadece uploads içindekiler silinebilir
    }
    $full = BASE_PATH . '/' . $relative;
    if (is_file($full)) {
        @unlink($full);
    }
}
