<?php
/*
 * fonksiyonlar/hava.php
 * Open-Meteo API istekleri, hava kodları, simgeler ve günlük tavsiyeler.
 */

/* ============================================================
 * OPEN-METEO HAVA DURUMU API
 * ========================================================== */

/**
 * Verilen adrese istek atar, gelen JSON'u PHP dizisine çevirir.
 * İnternet yoksa veya servis cevap vermezse null döner (sayfa çökmez).
 */
function apiGetir($url)
{
    // 10 saniyede cevap gelmezse vazgeç
    $ayarlar = stream_context_create(['http' => ['timeout' => 10]]);

    $cevap = @file_get_contents($url, false, $ayarlar);
    if ($cevap === false) {
        return null;
    }

    $veri = json_decode($cevap, true);
    if (!is_array($veri) || isset($veri['error'])) {
        return null;
    }
    return $veri;
}

/**
 * Bir bölgedeki TÜM illerin anlık hava durumunu ve 3 günlük tahminini
 * TEK istekle getirir (Open-Meteo virgülle ayrılmış birden çok koordinatı kabul eder).
 *
 * Dönüş: il koduna göre dizi → ['trabzon' => [...], 'rize' => [...], ...]
 * Hata olursa: null
 */
function bolgeHavaGetir($iller)
{
    $enlemler  = [];
    $boylamlar = [];
    foreach ($iller as $il) {
        $enlemler[]  = $il['lat'];
        $boylamlar[] = $il['lon'];
    }

    $parametreler = [
        'latitude'      => implode(',', $enlemler),
        'longitude'     => implode(',', $boylamlar),
        'current'       => 'temperature_2m,weather_code,is_day',
        'daily'         => 'weather_code,temperature_2m_max,temperature_2m_min,precipitation_probability_max,wind_speed_10m_max',
        'timezone'      => 'Europe/Istanbul',
        'forecast_days' => 3,
    ];
    $veri = apiGetir('https://api.open-meteo.com/v1/forecast?' . http_build_query($parametreler));

    if ($veri === null) {
        return null;
    }

    // Tek konum istendiğinde API dizi değil tek nesne döndürür; aynı biçime getiriyoruz.
    if (isset($veri['daily'])) {
        $veri = [$veri];
    }

    // Sonuçlar, istekteki sırayla gelir. İl kodlarıyla eşleştiriyoruz.
    $sonuc = [];
    $sira  = 0;
    foreach ($iller as $ilKodu => $il) {
        $sonuc[$ilKodu] = $veri[$sira] ?? null;
        $sira++;
    }
    return $sonuc;
}

/**
 * Tek bir ilin ayrıntılı anlık hava durumunu, 7 günlük tahminini
 * ve 7 gün boyunca saat saat verisini (7 x 24 = 168 saat) getirir.
 */
function ilHavaGetir($lat, $lon)
{
    $parametreler = [
        'latitude'      => $lat,
        'longitude'     => $lon,
        'current'       => 'temperature_2m,apparent_temperature,relative_humidity_2m,precipitation,precipitation_probability,weather_code,wind_speed_10m,is_day',
        'hourly'        => 'temperature_2m,precipitation_probability,precipitation,weather_code,wind_speed_10m,is_day',
        'daily'         => 'weather_code,temperature_2m_max,temperature_2m_min,apparent_temperature_max,apparent_temperature_min,precipitation_probability_max,precipitation_sum,wind_speed_10m_max,uv_index_max,sunrise,sunset',
        'timezone'      => 'Europe/Istanbul',
        'forecast_days' => 7,
    ];
    return apiGetir('https://api.open-meteo.com/v1/forecast?' . http_build_query($parametreler));
}

/**
 * Saatlik veriden yalnızca istenen güne ait saatleri ayıklar.
 * $baslangic verilirse (ör. "2026-10-26T14:00") ondan önceki saatler atlanır;
 * böylece "bugün" için geçmiş saatler gösterilmez.
 *
 * Dönüş: [['saat' => '14:00', 'sicaklik' => 18.2, 'olasilik' => 40, 'miktar' => 0.2, 'kod' => 61, 'ruzgar' => 12], ...]
 */
function gununSaatleri($saatlik, $tarih, $baslangic = '')
{
    $sonuc = [];
    foreach ($saatlik['time'] as $i => $zaman) {
        // "2026-10-26T14:00" → ilk 10 karakter tarih
        if (substr($zaman, 0, 10) != $tarih) {
            continue;
        }
        if ($baslangic !== '' && $zaman < $baslangic) {
            continue;
        }
        $sonuc[] = [
            'saat'     => substr($zaman, 11, 5),
            'sicaklik' => $saatlik['temperature_2m'][$i],
            'olasilik' => $saatlik['precipitation_probability'][$i] ?? 0,
            'miktar'   => $saatlik['precipitation'][$i] ?? 0,
            'kod'      => $saatlik['weather_code'][$i],
            'ruzgar'   => $saatlik['wind_speed_10m'][$i],
            'gunduz'   => $saatlik['is_day'][$i] ?? 1,   // 1 = gündüz, 0 = gece
        ];
    }
    return $sonuc;
}

/**
 * Yağış ihtimali eşiğin üstünde olan ARDIŞIK saatleri aralık olarak birleştirir.
 * Örnek çıktı: [['bas' => '14:00', 'bit' => '18:00', 'basSaat' => 14, 'bitSaat' => 18, 'enYuksek' => 80], ...]
 * ('bit' aralığın bittiği saattir: 14:00, 15:00, 16:00, 17:00 yağışlı → 14:00–18:00)
 * basSaat / bitSaat: karşılaştırma için sayı olarak saat (gece yarısında biten aralıkta bitSaat = 24).
 */
function yagisAraliklari($saatler, $esik = 40)
{
    $araliklar = [];
    $acik      = null;   // şu an açık olan (devam eden) aralık

    foreach ($saatler as $s) {
        $saatSayi = (int) substr($s['saat'], 0, 2);

        if ($s['olasilik'] >= $esik) {
            if ($acik === null) {
                $acik = ['bas' => $s['saat'], 'basSaat' => $saatSayi, 'enYuksek' => $s['olasilik']];
            }
            $acik['enYuksek'] = max($acik['enYuksek'], $s['olasilik']);
            $acik['bitSaat']  = $saatSayi + 1;                              // bitiş = bu saat + 1
            $acik['bit']      = sprintf('%02d:00', $acik['bitSaat'] % 24);  // 24 → "00:00"
        } elseif ($acik !== null) {
            $araliklar[] = $acik;   // yağışsız saate gelindi → aralığı kapat
            $acik = null;
        }
    }
    if ($acik !== null) {
        $araliklar[] = $acik;       // gün sonuna kadar süren aralık
    }
    return $araliklar;
}

/**
 * Bir günün tahminine bakarak kullanıcıya tavsiyeler üretir.
 * Her tavsiye: ['tur' => 'soguk' | 'semsiye' | ..., 'metin' => '...']
 *
 * Giyim tavsiyesi sıcaklığı, hissedilen sıcaklığı, yağışı ve rüzgârı BİRLİKTE
 * değerlendirir. Örneğin 16° ve %98 yağışta "hırka alın" değil
 * "su geçirmez mont veya yağmurluk giyin" önerilir.
 *
 * $araliklar (isteğe bağlı): yagisAraliklari() sonucu. Verilirse şemsiye
 * tavsiyesinde yağışın hangi saatlerde beklendiği de yazılır.
 *
 * Tavsiyeler önem sırasına göre döner: fırtına/kar → giyim → şemsiye → rüzgâr → güneş.
 */
function tavsiyeler($gunluk, $i, $araliklar = [])
{
    $enDus  = $gunluk['temperature_2m_min'][$i];
    $enYuk  = $gunluk['temperature_2m_max'][$i];
    $hisYuk = $gunluk['apparent_temperature_max'][$i] ?? $enYuk;   // gün içi en yüksek hissedilen
    $hisDus = $gunluk['apparent_temperature_min'][$i] ?? $enDus;
    $yagis  = $gunluk['precipitation_probability_max'][$i] ?? 0;
    $miktar = $gunluk['precipitation_sum'][$i] ?? 0;
    $ruzgar = $gunluk['wind_speed_10m_max'][$i] ?? 0;
    $uv     = $gunluk['uv_index_max'][$i] ?? 0;
    $tur    = havaTuru($gunluk['weather_code'][$i]);

    // Günün genel karakteri
    $yagmurlu = ($yagis >= 60 || $tur == 'yagmurlu' || $tur == 'firtina');
    $karli    = ($tur == 'karli');
    $ruzgarli = ($ruzgar >= 30);
    // Giyimde esas alınan sıcaklık: gerçek ve hissedilen en yüksekten düşük olanı
    $sicaklik = min($enYuk, $hisYuk);

    $liste = [];

    // 1) Önce tehlikeli durumlar
    if ($tur == 'firtina') {
        $liste[] = ['tur' => 'firtina', 'ikon' => 'firtina', 'metin' => 'Gök gürültülü sağanak bekleniyor. Açık alanlarda uzun süre kalmayın, ağaç altında beklemeyin.'];
    }
    if ($karli) {
        $liste[] = ['tur' => 'kar', 'ikon' => 'bot', 'metin' => 'Kar yağışı bekleniyor. Kaymayan bot giyin; yolculuğa çıkacaksanız zincir ve önlem alın.'];
    }

    // 2) Giyim: sıcaklık + yağış + rüzgâr birlikte
    if ($enDus <= 0 || $hisDus <= -3) {
        $metin = 'Don riski var. Kalın mont, atkı, bere ve eldiven giyin; sabah ve akşam yollarda buzlanmaya dikkat edin.';
        $liste[] = ['tur' => 'soguk', 'ikon' => 'kislik', 'metin' => $metin];
    } elseif ($sicaklik < 10) {
        $metin = $yagmurlu
            ? 'Soğuk ve yağışlı bir gün. Su geçirmez kalın bir mont, atkı ve su geçirmez ayakkabı tercih edin.'
            : 'Hava soğuk. Kalın bir mont giyin; atkı ve bere iyi olur.';
        $liste[] = ['tur' => 'soguk', 'ikon' => $yagmurlu ? 'yagmurluk' : 'mont', 'metin' => $metin];
    } elseif ($sicaklik < 18) {
        if ($yagmurlu) {
            $metin = 'Serin ve yağmurlu bir gün; hırka yetmez. Su geçirmez bir mont veya yağmurluk giyin, kapalı ayakkabı tercih edin.';
            $ikon  = 'yagmurluk';
        } elseif ($ruzgarli) {
            $metin = 'Serin ve rüzgârlı. Rüzgâr geçirmeyen bir mont giyin; hava hissedilenden daha soğuk gelebilir.';
            $ikon  = 'mont';
        } else {
            $metin = 'Serin bir gün. Bir ceket veya kalın bir hırka yeterli olur.';
            $ikon  = 'hirka';
        }
        $liste[] = ['tur' => 'serin', 'ikon' => $ikon, 'metin' => $metin];
    } elseif ($sicaklik < 25) {
        if ($yagmurlu) {
            $liste[] = ['tur' => 'serin', 'ikon' => 'yagmurluk', 'metin' => 'Hava ılık ama yağmurlu. İnce bir yağmurluk işinizi görür.'];
        } elseif ($enDus < 12) {
            $liste[] = ['tur' => 'serin', 'ikon' => 'hirka', 'metin' => 'Gündüz ılık (' . round($enYuk) . '°) ama sabah ve akşam serin (' . round($enDus) . '°). Yanınıza ince bir ceket alın.'];
        }
    } elseif ($sicaklik >= 32) {
        $liste[] = ['tur' => 'sicak', 'ikon' => 'sicak', 'metin' => 'Çok sıcak (' . round($enYuk) . '°). Açık renkli, ince giysiler giyin; bol su için, öğle saatlerinde güneşten kaçının.'];
    }

    // 3) Şemsiye (kar yağıyorsa ayrıca şemsiye önermeye gerek yok)
    if (!$karli && $yagis >= 30) {
        // Yağışın en yoğun olduğu aralığı bul
        $saatBilgisi = '';
        if (count($araliklar) > 0) {
            $metinler = [];
            foreach ($araliklar as $a) {
                $metinler[] = $a['bas'] . '–' . ($a['bitSaat'] == 24 ? '24:00' : $a['bit']);
            }
            $saatBilgisi = ' Beklenen saatler: ' . implode(', ', $metinler) . '.';
        }

        if ($yagis >= 60) {
            $ek = ($miktar >= 10) ? ' Toplam ' . $miktar . ' mm, yer yer kuvvetli olabilir.' : '';
            $liste[] = ['tur' => 'semsiye', 'ikon' => 'semsiye', 'metin' => 'Yağış ihtimali yüksek (%' . $yagis . '). Şemsiyenizi mutlaka alın.' . $ek . $saatBilgisi];
        } else {
            $liste[] = ['tur' => 'semsiye', 'ikon' => 'semsiye', 'metin' => 'Yağmur ihtimali var (%' . $yagis . '). Çantanızda şemsiye bulunsun.' . $saatBilgisi];
        }
    }

    // 4) Rüzgâr ve güneş
    if ($ruzgar >= 40) {
        $ek = ($yagis >= 30) ? ' Şemsiye kullanmak zor olabilir; kapüşonlu yağmurluk daha iyi seçim.' : '';
        $liste[] = ['tur' => 'ruzgar', 'ikon' => 'ruzgar', 'metin' => 'Kuvvetli rüzgâr (' . round($ruzgar) . ' km/s). Balkondaki eşyaları sabitleyin.' . $ek];
    }
    if ($uv >= 6) {
        $liste[] = ['tur' => 'gunes', 'ikon' => 'gozluk', 'metin' => 'UV indeksi yüksek (' . round($uv) . '). Güneş kremi ve gözlük kullanın.'];
    }

    // Hiçbir uyarı yoksa
    if (count($liste) == 0) {
        $liste[] = ['tur' => 'iyi', 'ikon' => 'iyi', 'metin' => 'Hava güzel görünüyor. Dışarıda vakit geçirmek için uygun bir gün.'];
    }
    return $liste;
}

/**
 * "Bugün için tavsiyeler" kutularındaki renkli, animasyonlu simge (assets/tavsiye/).
 * $tavsiye: tavsiyeler() sonucundaki bir öğe ('ikon' anahtarı simgeyi belirler:
 * mont, kislik, hirka, yagmurluk, semsiye, ruzgar, gozluk, sicak, bot, firtina, iyi).
 */
function tavsiyeIkonu($tavsiye)
{
    $ikon = $tavsiye['ikon'] ?? 'iyi';
    return '<img class="tavsiye-ikon" src="assets/tavsiye/' . $ikon . '.svg" alt="" aria-hidden="true">';
}

/**
 * Uyarı türüne göre renkli, animasyonlu simge (assets/tavsiye/ klasöründeki SVG'ler).
 * Bildirim panelinde ve Takip Listem kartlarındaki uyarılarda kullanılır.
 *   semsiye → şemsiye (yağış), soguk → atkı-bere (don), ruzgar → rüzgâr, sicak → güneş/termometre
 */
function uyariIkonu($tur, $sinif = 'uyari-ikon')
{
    $dosyalar = [
        'semsiye' => 'semsiye', 'soguk' => 'kislik', 'ruzgar' => 'ruzgar', 'sicak' => 'sicak',
        'kar' => 'bot', 'firtina' => 'firtina', 'serin' => 'hirka', 'gunes' => 'gozluk', 'iyi' => 'iyi',
    ];
    $dosya = $dosyalar[$tur] ?? 'iyi';
    return '<img class="' . $sinif . '" src="assets/tavsiye/' . $dosya . '.svg" alt="" aria-hidden="true">';
}

/**
 * Tavsiye türüne göre küçük çizgi simgesi (bildirim paneli ve takip listesi uyarılarında).
 */
function tavsiyeSimgesi($tur)
{
    $cizimler = [
        'soguk'   => '<path d="M12 2v20M4.9 7l14.2 10M4.9 17L19.1 7M9 4l3 2 3-2M9 20l3-2 3 2"/>',
        'serin'   => '<path d="M8 3l-4 3v4h3v11h10V10h3V6l-4-3a4 4 0 0 1-8 0z"/>',
        'sicak'   => '<path d="M14 14.8V4a2 2 0 0 0-4 0v10.8a4 4 0 1 0 4 0z"/><path d="M12 9v7"/>',
        'semsiye' => '<path d="M12 3a9 9 0 0 1 9 9H3a9 9 0 0 1 9-9z"/><path d="M12 12v7a2 2 0 0 1-4 0"/>',
        'kar'     => '<path d="M12 2v20M2 12h20M5 5l14 14M19 5L5 19"/>',
        'firtina' => '<path d="M13 2L5 14h6l-1 8 8-12h-6z"/>',
        'ruzgar'  => '<path d="M3 8h10a3 3 0 1 0-3-3M3 12h15a3 3 0 1 1-3 3M3 16h7"/>',
        'gunes'   => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M2 12h2M20 12h2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>',
        'iyi'     => '<path d="M20 6L9 17l-5-5"/>',
    ];
    return '<svg class="simge" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" '
         . 'stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
         . ($cizimler[$tur] ?? $cizimler['iyi'])
         . '</svg>';
}

/* ============================================================
 * HAVA KODU → METİN ve SİMGE
 * ========================================================== */

/**
 * WMO hava kodunu (API sadece sayı döndürür) bir türe çevirir.
 * Tür hem simge seçmek hem de Türkçe açıklama için kullanılır.
 */
function havaTuru($kod)
{
    if ($kod == 0) {
        return 'acik';
    } elseif ($kod <= 2) {
        return 'parcali';
    } elseif ($kod == 3) {
        return 'kapali';
    } elseif ($kod <= 48) {
        return 'sisli';
    } elseif ($kod <= 67 || ($kod >= 80 && $kod <= 82)) {
        return 'yagmurlu';
    } elseif ($kod <= 77 || $kod == 85 || $kod == 86) {
        return 'karli';
    } else {
        return 'firtina';
    }
}

/**
 * Hava türünün Türkçe açıklaması.
 */
function havaMetni($kod)
{
    $metinler = [
        'acik'     => 'Açık',
        'parcali'  => 'Parçalı bulutlu',
        'kapali'   => 'Kapalı',
        'sisli'    => 'Sisli',
        'yagmurlu' => 'Yağmurlu',
        'karli'    => 'Karlı',
        'firtina'  => 'Gök gürültülü',
    ];
    return $metinler[havaTuru($kod)];
}

/**
 * Hava koduna (ve gece/gündüze) göre kullanılacak simge dosyasının adı.
 * Simgeler assets/ikonlar/ klasöründeki animasyonlu SVG dosyalarıdır.
 *   0 açık · 1 çoğunlukla açık · 2 parçalı bulutlu · 3 kapalı · 45-48 sis
 *   51-67, 80-82 yağmur · 71-77, 85-86 kar · 95-99 gök gürültülü fırtına
 */
function havaIkonu($kod, $gunduz = 1)
{
    if ($kod == 0) {
        return $gunduz ? 'gunesli' : 'acik-gece';
    } elseif ($kod == 1) {
        return $gunduz ? 'parcali-bulutlu' : 'ay-yildiz';
    } elseif ($kod == 2) {
        return $gunduz ? 'bulutlu' : 'bulutlu-gece';
    } elseif ($kod == 3) {
        return 'kapali';
    } elseif ($kod <= 48) {
        return 'sis';
    } elseif (in_array($kod, [63, 65, 67, 81, 82])) {
        return 'saganak';
    } elseif ($kod <= 67 || $kod == 80) {
        return 'hafif-yagmur';
    } elseif (in_array($kod, [75, 86])) {
        return 'yogun-kar';
    } elseif ($kod <= 77 || $kod == 85) {
        return 'kar';
    }
    return 'firtina';
}

/**
 * Hava simgesi (animasyonlu SVG). $gunduz = 0 ise gece simgeleri (ay, yıldız) kullanılır.
 */
function havaSimgesi($kod, $gunduz = 1)
{
    return '<img class="simge hava-ikon" src="assets/ikonlar/' . havaIkonu($kod, $gunduz) . '.svg" alt="" aria-hidden="true">';
}

/**
 * İl sayfası açılırken gösterilecek hava efektinin türü.
 * Yağış ve sis önceliklidir; sonra kuvvetli rüzgâr, sonra bulut / güneş / gece.
 */
function havaSahnesi($kod, $ruzgar, $gunduz)
{
    $tur = havaTuru($kod);
    if ($tur == 'firtina' || $tur == 'yagmurlu' || $tur == 'karli' || $tur == 'sisli') {
        return $tur;
    }
    if ($ruzgar >= 30) {
        return 'ruzgarli';
    }
    if (!$gunduz) {
        return ($kod <= 1) ? 'gece' : 'bulutlu-gece';
    }
    return ($kod <= 1) ? 'gunesli' : 'bulutlu';
}

/* ------------------------------------------------------------
 * HAVA KALİTESİ (Open-Meteo Air Quality API, CAMS verisi)
 * ---------------------------------------------------------- */

/**
 * Bir konumun anlık hava kalitesini ve bugünkü en yüksek Avrupa AQI değerini getirir.
 * Kirleticiler µg/m³ cinsindendir. Hata olursa null.
 */
function havaKalitesiGetir($lat, $lon)
{
    $parametreler = [
        'latitude'      => $lat,
        'longitude'     => $lon,
        'current'       => 'european_aqi,pm2_5,pm10,nitrogen_dioxide,ozone,sulphur_dioxide,carbon_monoxide',
        'hourly'        => 'european_aqi',
        'timezone'      => 'Europe/Istanbul',
        'forecast_days' => 1,
    ];
    $veri = apiGetir('https://air-quality-api.open-meteo.com/v1/air-quality?' . http_build_query($parametreler));
    if ($veri === null || !isset($veri['current']['european_aqi'])) {
        return null;
    }
    $saatlik = array_filter($veri['hourly']['european_aqi'] ?? [], 'is_numeric');
    $veri['gunEnYuksek'] = count($saatlik) > 0 ? max($saatlik) : null;
    return $veri;
}

/**
 * Avrupa Hava Kalitesi İndeksi (AQI) kategorisi.
 * 0–20 iyi · 20–40 makul · 40–60 orta · 60–80 kötü · 80–100 çok kötü · 100+ son derece kötü
 */
function havaKalitesiDurumu($aqi)
{
    if ($aqi <= 20) {
        return ['sinif' => 'iyi', 'ad' => 'İyi', 'tavsiye' => 'Hava temiz; açık havada rahatça vakit geçirebilirsiniz.'];
    } elseif ($aqi <= 40) {
        return ['sinif' => 'makul', 'ad' => 'Makul', 'tavsiye' => 'Hava kalitesi kabul edilebilir düzeyde.'];
    } elseif ($aqi <= 60) {
        return ['sinif' => 'orta', 'ad' => 'Orta', 'tavsiye' => 'Hassas kişiler (astım, kalp hastaları) uzun süreli yoğun egzersizi azaltmalı.'];
    } elseif ($aqi <= 80) {
        return ['sinif' => 'kotu', 'ad' => 'Kötü', 'tavsiye' => 'Açık havada yoğun egzersizi azaltın; hassas kişiler dışarıda daha az kalmalı.'];
    } elseif ($aqi <= 100) {
        return ['sinif' => 'cok-kotu', 'ad' => 'Çok kötü', 'tavsiye' => 'Mümkünse açık havada uzun süre kalmayın; pencereleri kapalı tutun.'];
    }
    return ['sinif' => 'asiri', 'ad' => 'Son derece kötü', 'tavsiye' => 'Açık havadaki etkinliklerden kaçının; herkes için sağlık riski var.'];
}
