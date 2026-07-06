<?php
/**
 * Çok dil sistemi.
 * - Arayüz metinleri app/lang/{code}.php dosyalarından gelir.
 * - Eksik anahtarlar için TR fallback uygulanır.
 * - İçerikler (blog, hizmet vb.) DB'de lang kolonuyla tutulur;
 *   fallback sorguları helpers/functions.php içindedir.
 */
class Lang
{
    private static string $current = DEFAULT_LANGUAGE;
    private static array $strings  = [];
    private static array $fallback = [];

    public static function set(string $code): void
    {
        $code = preg_replace('/[^a-z]/', '', strtolower($code)) ?: DEFAULT_LANGUAGE;
        self::$current = $code;

        $file = BASE_PATH . '/app/lang/' . $code . '.php';
        self::$strings = is_file($file) ? (array) require $file : [];

        if ($code !== DEFAULT_LANGUAGE) {
            $trFile = BASE_PATH . '/app/lang/' . DEFAULT_LANGUAGE . '.php';
            self::$fallback = is_file($trFile) ? (array) require $trFile : [];
        } else {
            self::$fallback = self::$strings;
        }
    }

    public static function current(): string
    {
        return self::$current;
    }

    /** AR için sağdan sola */
    public static function isRtl(): bool
    {
        return self::$current === 'ar';
    }

    public static function get(string $key, array $replace = []): string
    {
        $value = self::$strings[$key] ?? self::$fallback[$key] ?? $key;
        foreach ($replace as $k => $v) {
            $value = str_replace(':' . $k, (string) $v, $value);
        }
        return $value;
    }
}
