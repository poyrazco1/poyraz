<?php

declare(strict_types=1);

/**
 * Kur (döviz) işlemleri.
 *
 * Tüm döviz mantığı bu dosyada toplanır; dashboard ve kur çevirici bu
 * dosyanın fonksiyonlarını kullanır. Kur kodu başka sayfalara gömülmez.
 *
 * Kaynak: Frankfurter API (ECB verisi). TRY bazlı bir kur tablosu üretilir:
 *   rates = { "TRY": 1, "USD": <1 USD kaç TRY>, "EUR": <1 EUR kaç TRY> }
 *
 * API erişilemezse en son başarılı veri currency_cache tablosundan okunur.
 * Gereksiz istek atmamak için CURRENCY_CACHE_TTL kadar cache kullanılır.
 *
 * Doğrudan erişildiğinde (kur.php?action=...) JSON döndürür:
 *   ?action=rates                       -> güncel kur tablosu
 *   ?action=convert&amount=&from=&to=   -> dönüşüm sonucu
 */

require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/db.php';

/** Cache geçerlilik süresi (saniye). 30 dakika. */
const CURRENCY_CACHE_TTL = 1800;

/** Panelde desteklenen para birimleri. */
const CURRENCY_SUPPORTED = ['TRY', 'USD', 'EUR'];

/**
 * Bir URL'yi güvenli şekilde çeker. Başarısızsa null döndürür.
 */
function currency_http_get(string $url): ?string
{
    // Öncelik cURL; yoksa file_get_contents.
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        if ($ch === false) {
            return null;
        }
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 8,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 3,
            CURLOPT_USERAGENT      => 'PoyrazTechPanel/1.0',
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HTTPHEADER     => ['Accept: application/json'],
        ]);
        $body = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if (is_string($body) && $body !== '' && $code >= 200 && $code < 300) {
            return $body;
        }
        return null;
    }

    $context = stream_context_create([
        'http' => ['timeout' => 8, 'header' => "Accept: application/json\r\nUser-Agent: PoyrazTechPanel/1.0\r\n"],
        'ssl'  => ['verify_peer' => true, 'verify_peer_name' => true],
    ]);
    $body = @file_get_contents($url, false, $context);
    return is_string($body) && $body !== '' ? $body : null;
}

/**
 * Frankfurter API'den TRY bazlı kur tablosunu çeker.
 *
 * @return array<string, float>|null
 */
function currency_fetch_from_api(): ?array
{
    $base = rtrim((string) FRANKFURTER_API_URL, '/');
    // EUR bazından USD ve TRY al, USD->TRY değerini türet.
    $url = $base . '/latest?from=EUR&to=USD,TRY';

    $raw = currency_http_get($url);
    if ($raw === null) {
        app_log('Kur API yanıt vermedi: ' . $url, 'WARN');
        return null;
    }

    $data = json_decode($raw, true);
    if (!is_array($data) || !isset($data['rates']) || !is_array($data['rates'])) {
        app_log('Kur API çözümlenemedi.', 'WARN');
        return null;
    }

    $eurTry = isset($data['rates']['TRY']) ? (float) $data['rates']['TRY'] : 0.0;
    $eurUsd = isset($data['rates']['USD']) ? (float) $data['rates']['USD'] : 0.0;

    if ($eurTry <= 0 || $eurUsd <= 0) {
        app_log('Kur API geçersiz değer döndürdü.', 'WARN');
        return null;
    }

    $usdTry = $eurTry / $eurUsd;

    return [
        'TRY' => 1.0,
        'USD' => round($usdTry, 4),
        'EUR' => round($eurTry, 4),
    ];
}

/**
 * Kur tablosunu currency_cache tablosuna yazar.
 *
 * @param array<string, float> $rates
 */
function currency_store_cache(array $rates): void
{
    try {
        $stmt = db()->prepare(
            'INSERT INTO currency_cache (base_currency, rates_json, fetched_at, created_at)
             VALUES (:base, :rates, NOW(), NOW())'
        );
        $stmt->execute([
            ':base'  => 'TRY',
            ':rates' => json_encode($rates, JSON_UNESCAPED_UNICODE),
        ]);
    } catch (Throwable $e) {
        app_log('currency_cache yazılamadı: ' . $e->getMessage(), 'WARN');
    }
}

/**
 * currency_cache tablosundan en son kaydı okur.
 *
 * @return array{rates: array<string, float>, fetched_at: string}|null
 */
function currency_read_cache(): ?array
{
    try {
        $stmt = db()->query(
            'SELECT rates_json, fetched_at FROM currency_cache
             ORDER BY id DESC LIMIT 1'
        );
        $row = $stmt->fetch();
        if ($row === false) {
            return null;
        }
        $rates = json_decode((string) $row['rates_json'], true);
        if (!is_array($rates)) {
            return null;
        }
        $clean = [];
        foreach ($rates as $k => $v) {
            $clean[(string) $k] = (float) $v;
        }
        return ['rates' => $clean, 'fetched_at' => (string) $row['fetched_at']];
    } catch (Throwable $e) {
        app_log('currency_cache okunamadı: ' . $e->getMessage(), 'WARN');
        return null;
    }
}

/**
 * Güncel kur tablosunu döndürür. Cache TTL süresi dolmadıysa cache kullanılır;
 * API başarısız olursa son cache verisine düşülür.
 *
 * @return array{base:string, rates:array<string,float>, fetched_at:string, source:string, stale:bool}
 */
function currency_get_rates(bool $force = false): array
{
    $cache = currency_read_cache();

    // Cache güncel mi?
    if (!$force && $cache !== null) {
        $age = time() - (int) strtotime($cache['fetched_at']);
        if ($age >= 0 && $age < CURRENCY_CACHE_TTL) {
            return [
                'base'       => 'TRY',
                'rates'      => currency_normalize($cache['rates']),
                'fetched_at' => $cache['fetched_at'],
                'source'     => 'cache',
                'stale'      => false,
            ];
        }
    }

    // API'yi dene.
    $fresh = currency_fetch_from_api();
    if ($fresh !== null) {
        currency_store_cache($fresh);
        return [
            'base'       => 'TRY',
            'rates'      => $fresh,
            'fetched_at' => date('Y-m-d H:i:s'),
            'source'     => 'api',
            'stale'      => false,
        ];
    }

    // API başarısız: en son cache'e düş (bayat olsa da).
    if ($cache !== null) {
        return [
            'base'       => 'TRY',
            'rates'      => currency_normalize($cache['rates']),
            'fetched_at' => $cache['fetched_at'],
            'source'     => 'cache',
            'stale'      => true,
        ];
    }

    // Hiç veri yok: güvenli boş tablo.
    return [
        'base'       => 'TRY',
        'rates'      => ['TRY' => 1.0, 'USD' => 0.0, 'EUR' => 0.0],
        'fetched_at' => '',
        'source'     => 'none',
        'stale'      => true,
    ];
}

/**
 * Kur tablosunu desteklenen birimlerle sınırlandırıp normalize eder.
 *
 * @param array<string, float> $rates
 * @return array<string, float>
 */
function currency_normalize(array $rates): array
{
    $out = ['TRY' => 1.0];
    foreach (['USD', 'EUR'] as $cur) {
        $out[$cur] = isset($rates[$cur]) ? (float) $rates[$cur] : 0.0;
    }
    return $out;
}

/**
 * Belirtilen tutarı bir para biriminden diğerine çevirir.
 *
 * @param array<string, float>|null $rates Hazır kur tablosu (yoksa çekilir).
 * @return float|null Geçersiz birim/kur durumunda null.
 */
function currency_convert(float $amount, string $from, string $to, ?array $rates = null): ?float
{
    $from = strtoupper(trim($from));
    $to   = strtoupper(trim($to));

    if (!in_array($from, CURRENCY_SUPPORTED, true) || !in_array($to, CURRENCY_SUPPORTED, true)) {
        return null;
    }

    if ($rates === null) {
        $data  = currency_get_rates();
        $rates = $data['rates'];
    }

    $fromRate = $rates[$from] ?? 0.0; // 1 birim = kaç TRY
    $toRate   = $rates[$to] ?? 0.0;

    if ($fromRate <= 0 || $toRate <= 0) {
        return null;
    }

    // TRY üzerinden dönüşüm.
    $inTry = $amount * $fromRate;
    return $inTry / $toRate;
}

/* ---------------------------------------------------------------------------
 * Doğrudan erişim: JSON API
 * ------------------------------------------------------------------------- */
$scriptFile = isset($_SERVER['SCRIPT_FILENAME']) ? realpath((string) $_SERVER['SCRIPT_FILENAME']) : '';
$thisFile   = realpath(__FILE__);

if ($scriptFile !== false && $thisFile !== false && $scriptFile === $thisFile) {
    // Bu dosya doğrudan çağrıldı; JSON servisi olarak davran.
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');

    $action = input('action', 'rates');

    try {
        if ($action === 'convert') {
            $amount = (float) str_replace(',', '.', input('amount', '0'));
            $from   = input('from', 'USD');
            $to     = input('to', 'TRY');
            $result = currency_convert($amount, $from, $to);

            if ($result === null) {
                http_response_code(422);
                echo json_encode([
                    'ok'      => false,
                    'message' => 'Geçersiz para birimi veya kur verisi bulunamadı.',
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }

            echo json_encode([
                'ok'     => true,
                'amount' => $amount,
                'from'   => strtoupper($from),
                'to'     => strtoupper($to),
                'result' => round($result, 2),
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Varsayılan: güncel kur tablosu.
        $data = currency_get_rates();
        echo json_encode([
            'ok'         => true,
            'base'       => $data['base'],
            'rates'      => $data['rates'],
            'fetched_at' => $data['fetched_at'],
            'source'     => $data['source'],
            'stale'      => $data['stale'],
        ], JSON_UNESCAPED_UNICODE);
        exit;
    } catch (Throwable $e) {
        app_log('kur.php JSON hatası: ' . $e->getMessage(), 'ERROR');
        http_response_code(500);
        echo json_encode([
            'ok'      => false,
            'message' => 'Kur servisi şu anda yanıt veremiyor.',
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
