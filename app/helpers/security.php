<?php
/**
 * Güvenlik yardımcıları: oturum başlatma ve form doğrulama.
 */

/** Güvenli session başlatır (httponly, samesite, strict mode). */
function secure_session_start(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
          || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_name('d4session');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// ---------------------------------------------------------------------------
// Form doğrulama
// ---------------------------------------------------------------------------

/**
 * Basit kural tabanlı doğrulayıcı.
 *
 * $rules örneği:
 *   ['full_name' => 'required|min:3|max:120',
 *    'email'     => 'required|email',
 *    'phone'     => 'required|phone']
 *
 * Dönüş: hata mesajları dizisi (boşsa geçerli).
 */
function validate(array $data, array $rules): array
{
    $errors = [];
    foreach ($rules as $field => $ruleStr) {
        $value = trim((string) ($data[$field] ?? ''));
        $label = t('field.' . $field);
        if ($label === 'field.' . $field) {
            $label = ucfirst(str_replace('_', ' ', $field));
        }

        foreach (explode('|', $ruleStr) as $rule) {
            $param = null;
            if (str_contains($rule, ':')) {
                [$rule, $param] = explode(':', $rule, 2);
            }
            switch ($rule) {
                case 'required':
                    if ($value === '') {
                        $errors[$field] = t('validation.required', ['field' => $label]);
                    }
                    break;
                case 'email':
                    if ($value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $errors[$field] = t('validation.email', ['field' => $label]);
                    }
                    break;
                case 'phone':
                    if ($value !== '' && !valid_phone($value)) {
                        $errors[$field] = t('validation.phone', ['field' => $label]);
                    }
                    break;
                case 'min':
                    if ($value !== '' && mb_strlen($value) < (int) $param) {
                        $errors[$field] = t('validation.min', ['field' => $label, 'min' => $param]);
                    }
                    break;
                case 'max':
                    if (mb_strlen($value) > (int) $param) {
                        $errors[$field] = t('validation.max', ['field' => $label, 'max' => $param]);
                    }
                    break;
                case 'in':
                    $options = explode(',', (string) $param);
                    if ($value !== '' && !in_array($value, $options, true)) {
                        $errors[$field] = t('validation.invalid', ['field' => $label]);
                    }
                    break;
                case 'date':
                    if ($value !== '' && strtotime($value) === false) {
                        $errors[$field] = t('validation.invalid', ['field' => $label]);
                    }
                    break;
            }
            if (isset($errors[$field])) {
                break; // alan başına ilk hata yeter
            }
        }
    }
    return $errors;
}

/** Telefon kontrolü: +, boşluk, tire kabul; 10-15 rakam ister. */
function valid_phone(string $phone): bool
{
    if (!preg_match('/^[0-9+\-\s().]{7,20}$/', $phone)) {
        return false;
    }
    $digits = preg_replace('/\D/', '', $phone);
    return strlen($digits) >= 10 && strlen($digits) <= 15;
}

/** POST değerini kırpılmış string olarak alır. */
function post(string $key, string $default = ''): string
{
    return trim((string) ($_POST[$key] ?? $default));
}

/** GET değerini güvenli string olarak alır. */
function get_param(string $key, string $default = ''): string
{
    return trim((string) ($_GET[$key] ?? $default));
}

/** Tam sayı GET/POST parametresi */
function int_param(string $key, int $default = 0): int
{
    $v = $_GET[$key] ?? $_POST[$key] ?? $default;
    return (int) $v;
}

/**
 * Basit honeypot spam kontrolü: formlardaki gizli "website" alanı
 * doluysa istek bot kabul edilir.
 */
function is_spam_submission(): bool
{
    return trim((string) ($_POST['website'] ?? '')) !== '';
}
