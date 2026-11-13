<?php
/*
 * fonksiyonlar/ilce.php
 * İlçe listesi, ilçe koordinatlarının bulunması ve "il: ilçe" araması.
 *
 * İlçe koordinatları nasıl bulunur?
 *  1. "Merkez" ilçesi → il merkezinin koordinatı ($bolgeler dizisinden).
 *  2. Daha önce bulunduysa → veri/ilce-konumlari.json dosyasından (önbellek).
 *  3. Bulunmadıysa → Open-Meteo Geocoding API'ye sorulur:
 *       https://geocoding-api.open-meteo.com/v1/search?name=Erbaa&countryCode=TR
 *     Gelen sonuçlardan ili (admin1) eşleşen seçilir ve dosyaya kaydedilir.
 *  4. Geocoding ilçeyi bulamazsa (ör. Adana / Çukurova gibi yeni kurulan ilçeler) ya da
 *     bulduğu yer ilçeden çok uzaksa → veri/ilce-merkezleri.json dosyasındaki hazır koordinat.
 *
 * veri/ilce-merkezleri.json: 922 ilçenin (Merkez ilçeler hariç) hazır koordinatı.
 * Koordinatlar, sitenin zaten kullandığı Open-Meteo Geocoding servisinden (GeoNames verisi,
 * CC BY 4.0) ilçe merkezleri sorgulanarak bir kez toplanmıştır. Servisin bulamadığı birkaç yeni
 * ilçe için ilçe merkezinin bilinen konumu elle girilmiştir. Böylece hiçbir ilçe verisiz kalmaz.
 */

define('ILCE_ONBELLEK', veriYolu('ilce-konumlari.json'));   // bkz. veriYolu() — genel.php
const ILCE_MERKEZLERI = __DIR__ . '/../veri/ilce-merkezleri.json';

// Geocoding'in bulduğu yer, ilçenin hazır koordinatından bu kadar (km) uzaksa yanlış yer sayılır
const ILCE_EN_FAZLA_SAPMA = 50;

/**
 * Bir ilin ilçelerini [ilceKodu => ilceAdi] biçiminde döndürür.
 * Örnek: ilceListesi($ilceler, 'tokat') → ['merkez' => 'Merkez', 'almus' => 'Almus', ...]
 */
function ilceListesi($ilceler, $ilKodu)
{
    $liste = [];
    foreach ($ilceler[$ilKodu] ?? [] as $ad) {
        $liste[slugYap($ad)] = $ad;
    }
    return $liste;
}

/**
 * İlçelerin hazır koordinatları (veri/ilce-merkezleri.json): ['adana/cukurova' => ['lat' => .., 'lon' => ..], ...]
 */
function ilceMerkezleri()
{
    static $merkezler = null;
    if ($merkezler === null) {
        $veri = file_exists(ILCE_MERKEZLERI) ? json_decode(file_get_contents(ILCE_MERKEZLERI), true) : null;
        $merkezler = is_array($veri) ? $veri : [];
    }
    return $merkezler;
}

/**
 * İki nokta arasındaki yaklaşık uzaklık (km). Kısa mesafeler için yeterince doğrudur.
 */
function uzaklikKm($lat1, $lon1, $lat2, $lon2)
{
    $x = deg2rad($lon2 - $lon1) * cos(deg2rad(($lat1 + $lat2) / 2));
    $y = deg2rad($lat2 - $lat1);
    return sqrt($x * $x + $y * $y) * 6371;
}

/**
 * Önbellek dosyasını okur (yoksa boş dizi).
 */
function ilceOnbellekOku()
{
    if (!file_exists(ILCE_ONBELLEK)) {
        return [];
    }
    $veri = json_decode(file_get_contents(ILCE_ONBELLEK), true);
    return is_array($veri) ? $veri : [];
}

/**
 * Bulunan koordinatı önbellek dosyasına ekler.
 * Klasör yazılamıyorsa sessizce geçer (site yine çalışır, yalnızca her seferinde API'ye sorulur).
 */
function ilceOnbellekYaz($anahtar, $konum)
{
    $veri = ilceOnbellekOku();
    $veri[$anahtar] = $konum;

    $klasor = dirname(ILCE_ONBELLEK);
    if (!is_dir($klasor)) {
        @mkdir($klasor, 0775, true);
    }
    @file_put_contents(ILCE_ONBELLEK, json_encode($veri, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}

/**
 * Geocoding API'ye bir isim sorar ve Türkiye'deki sonuçları döndürür.
 */
function geocodingAra($isim)
{
    $parametreler = [
        'name'        => $isim,
        'count'       => 20,
        'language'    => 'tr',
        'countryCode' => 'TR',
    ];
    $veri = apiGetir('https://geocoding-api.open-meteo.com/v1/search?' . http_build_query($parametreler));
    return $veri['results'] ?? [];
}

/**
 * Geocoding sonuçları arasından doğru ilçeyi seçer.
 * Kural: sonucun ili (admin1) bizim ilimizle aynı olmalı.
 * Birden çok uygun sonuç varsa ilçe merkezi olanlar (PPLA2, ADM2) öne alınır.
 */
function ilceSonucuSec($sonuclar, $ilAdi, $ilceAdi)
{
    $oncelik = ['ADM2' => 1, 'PPLA2' => 2, 'PPLA' => 3, 'PPLA3' => 4, 'PPL' => 5];
    $enIyi = null;
    $enIyiPuan = 99;

    foreach ($sonuclar as $s) {
        if (slugYap($s['admin1'] ?? '') != slugYap($ilAdi)) {
            continue;   // başka ilde aynı adlı bir yer
        }
        $puan = $oncelik[$s['feature_code'] ?? ''] ?? 9;
        // İlçe adı admin2 alanında da geçiyorsa daha güvenilir
        if (slugYap($s['admin2'] ?? '') == slugYap($ilceAdi)) {
            $puan -= 0.5;
        }
        if ($puan < $enIyiPuan) {
            $enIyi = $s;
            $enIyiPuan = $puan;
        }
    }
    return $enIyi;
}

/**
 * Bir ilçenin koordinatını bulur.
 * Dönüş: ['lat' => ..., 'lon' => ..., 'kaynak' => 'il merkezi' | 'önbellek' | 'geocoding' | 'hazır koordinat'] veya null.
 * Hazır koordinat dosyası sayesinde geçerli her ilçe için bir konum döner.
 */
function ilceKonumu($ilKodu, $ilceKodu, $bolgeler, $ilceler)
{
    $bulunan = ilBul($bolgeler, $ilKodu);
    $liste   = ilceListesi($ilceler, $ilKodu);
    if ($bulunan === null || !isset($liste[$ilceKodu])) {
        return null;
    }

    // 1) Merkez ilçe → il merkezi
    if ($ilceKodu == 'merkez') {
        return ['lat' => $bulunan['il']['lat'], 'lon' => $bulunan['il']['lon'], 'kaynak' => 'il merkezi'];
    }

    $anahtar = $ilKodu . '/' . $ilceKodu;
    $hazir   = ilceMerkezleri()[$anahtar] ?? null;   // ilçe sınırından hesaplanmış nokta

    // Bulunan nokta ilçenin hazır koordinatına yakın mı? (aynı adlı başka bir yer seçilmesin)
    $yakinMi = function ($konum) use ($hazir) {
        return $hazir === null || uzaklikKm($konum['lat'], $konum['lon'], $hazir['lat'], $hazir['lon']) <= ILCE_EN_FAZLA_SAPMA;
    };

    // 2) Önbellek
    $onbellek  = ilceOnbellekOku();
    if (isset($onbellek[$anahtar]) && $yakinMi($onbellek[$anahtar])) {
        return $onbellek[$anahtar] + ['kaynak' => 'önbellek'];
    }

    // 3) Geocoding API: önce yalnızca ilçe adıyla, bulunamazsa "ilçe, il" biçiminde
    $ilceAdi = $liste[$ilceKodu];
    $ilAdi   = $bulunan['il']['ad'];
    $secilen = ilceSonucuSec(geocodingAra($ilceAdi), $ilAdi, $ilceAdi);
    if ($secilen === null) {
        $secilen = ilceSonucuSec(geocodingAra($ilceAdi . ', ' . $ilAdi), $ilAdi, $ilceAdi);
    }
    if ($secilen !== null) {
        $konum = ['lat' => round($secilen['latitude'], 4), 'lon' => round($secilen['longitude'], 4)];
        if ($yakinMi($konum)) {
            ilceOnbellekYaz($anahtar, $konum);
            return $konum + ['kaynak' => 'geocoding'];
        }
    }

    // 4) Geocoding bulamadı ya da yanlış yer buldu → hazır koordinat
    if ($hazir !== null) {
        return $hazir + ['kaynak' => 'hazır koordinat'];
    }
    return null;
}

/**
 * Arama kutusu: il, ilçe veya "il: ilçe".
 *
 *   "tokat"              → Tokat ili
 *   "istanbul: beşiktaş" → İstanbul'un Beşiktaş ilçesi
 *   "istanbul:" veya "istanbul: tüm ilçeler" → İstanbul'un tüm ilçeleri
 *   "beşiktaş"           → adı uyan tüm ilçeler (ve iller)
 *
 * Dönen her sonuç: ['il' => 'istanbul', 'ilce' => 'besiktas' veya '', 'ad' => 'Beşiktaş', 'ust' => 'İstanbul', 'tam' => true/false]
 * 'tam' = aranan kelimeyle birebir aynı mı (tek sonuca doğrudan gitmek için).
 */
function genelAra($bolgeler, $ilceler, $aranan)
{
    $sonuclar = [];

    if (str_contains($aranan, ':')) {
        // ---- "il: ilçe" biçimi ----
        [$ilParca, $ilceParca] = array_map('trim', explode(':', $aranan, 2));
        $ilSlug   = slugYap($ilParca);
        $ilceSlug = in_array(slugYap($ilceParca), ['tumilceler', 'tumu', 'hepsi']) ? '' : slugYap($ilceParca);   // "tüm ilçeler" → hepsi

        foreach ($bolgeler as $bolge) {
            foreach ($bolge['iller'] as $ilKodu => $il) {
                if ($ilSlug === '' || !str_contains($ilKodu, $ilSlug)) {
                    continue;
                }
                foreach (ilceListesi($ilceler, $ilKodu) as $ilceKodu => $ilceAdi) {
                    if ($ilceSlug === '' || str_contains($ilceKodu, $ilceSlug)) {
                        $sonuclar[] = [
                            'il' => $ilKodu, 'ilce' => $ilceKodu, 'ad' => $ilceAdi, 'ust' => $il['ad'],
                            'tam' => ($ilKodu == $ilSlug && $ilceKodu == $ilceSlug),
                        ];
                    }
                }
            }
        }
        return $sonuclar;
    }

    // ---- Tek kelime: önce iller, sonra ilçeler ----
    $slug = slugYap($aranan);
    foreach ($bolgeler as $bolge) {
        foreach ($bolge['iller'] as $ilKodu => $il) {
            if (str_contains($ilKodu, $slug)) {
                $sonuclar[] = ['il' => $ilKodu, 'ilce' => '', 'ad' => $il['ad'], 'ust' => $bolge['ad'], 'tam' => ($ilKodu == $slug)];
            }
        }
    }
    foreach ($ilceler as $ilKodu => $adlar) {
        $ilAdi = ilBul($bolgeler, $ilKodu)['il']['ad'];
        foreach (ilceListesi($ilceler, $ilKodu) as $ilceKodu => $ilceAdi) {
            // "Merkez" 51 ilde var; yalnızca "il: Merkez" biçiminde aranabilir
            if ($ilceKodu != 'merkez' && str_contains($ilceKodu, $slug)) {
                $sonuclar[] = ['il' => $ilKodu, 'ilce' => $ilceKodu, 'ad' => $ilceAdi, 'ust' => $ilAdi, 'tam' => ($ilceKodu == $slug)];
            }
        }
    }
    return $sonuclar;
}

/**
 * Bir arama sonucunun bağlantısı.
 */
function aramaAdresi($sonuc)
{
    return 'il.php?il=' . $sonuc['il'] . ($sonuc['ilce'] !== '' ? '&ilce=' . $sonuc['ilce'] : '');
}

/**
 * Bir ilçenin koordinatı — hava servisine (Geocoding API) SORMADAN.
 * Yönetim panelindeki "İl ve ilçe koordinatları" listesi 973 ilçeyi bir anda gösterdiği için
 * kullanılır: Merkez → il koordinatı · daha önce bulunduysa önbellek · yoksa hazır koordinat.
 */
function ilceKonumuApisiz($ilKodu, $ilceKodu, $bolgeler, $onbellek = null)
{
    $bulunan = ilBul($bolgeler, $ilKodu);
    if ($bulunan === null) {
        return null;
    }
    if ($ilceKodu == 'merkez') {
        return ['lat' => $bulunan['il']['lat'], 'lon' => $bulunan['il']['lon'], 'kaynak' => 'il merkezi'];
    }
    $anahtar  = $ilKodu . '/' . $ilceKodu;
    $onbellek = $onbellek ?? ilceOnbellekOku();
    $hazir    = ilceMerkezleri()[$anahtar] ?? null;
    if (isset($onbellek[$anahtar])) {
        $k = $onbellek[$anahtar];
        if ($hazir === null || uzaklikKm($k['lat'], $k['lon'], $hazir['lat'], $hazir['lon']) <= ILCE_EN_FAZLA_SAPMA) {
            return ['lat' => $k['lat'], 'lon' => $k['lon'], 'kaynak' => 'önbellek'];
        }
    }
    return $hazir !== null ? $hazir + ['kaynak' => 'hazır koordinat'] : null;
}

/**
 * Bir koordinatın OpenStreetMap'teki adresi (yönetim panelindeki "Haritada aç" bağlantısı).
 */
function haritaAdresi($lat, $lon, $yakinlik = 11)
{
    return 'https://www.openstreetmap.org/?mlat=' . $lat . '&mlon=' . $lon . '#map=' . $yakinlik . '/' . $lat . '/' . $lon;
}

