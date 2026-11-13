<?php
/*
 * fonksiyonlar/genel.php
 * Metin, arama, tarih biçimlendirme ve güvenli çıktı fonksiyonları.
 */

/* ============================================================
 * METİN ve ARAMA
 * ========================================================== */

/**
 * Metni aramaya uygun hâle getirir: küçük harf, Türkçe karakter yok, boşluk yok.
 * Örnek: "Şanlıurfa" → "sanliurfa",  "İSTANBUL" → "istanbul"
 */
function slugYap($metin)
{
    $turkce  = ['İ', 'I', 'ı', 'Ş', 'ş', 'Ğ', 'ğ', 'Ü', 'ü', 'Ö', 'ö', 'Ç', 'ç'];
    $ingiliz = ['i', 'i', 'i', 's', 's', 'g', 'g', 'u', 'u', 'o', 'o', 'c', 'c'];

    $metin = str_replace($turkce, $ingiliz, trim($metin));
    $metin = strtolower($metin);
    $metin = preg_replace('/[^a-z0-9]/', '', $metin); // harf ve rakam dışını sil
    return $metin;
}

/**
 * Aranan kelimeyi içeren illeri bulur.
 * İç içe foreach: önce bölgeler, sonra her bölgenin illeri dolaşılır.
 * Dönen her sonuç: ['bolge' => 'karadeniz', 'il' => 'trabzon', 'ad' => 'Trabzon', 'bolgeAd' => 'Karadeniz']
 */
function ilAra($bolgeler, $aranan)
{
    $aranan   = slugYap($aranan);
    $sonuclar = [];

    foreach ($bolgeler as $bolgeKodu => $bolge) {
        foreach ($bolge['iller'] as $ilKodu => $il) {
            // str_contains: $ilKodu içinde $aranan geçiyor mu? (PHP 8)
            if (str_contains($ilKodu, $aranan)) {
                $sonuclar[] = [
                    'bolge'   => $bolgeKodu,
                    'il'      => $ilKodu,
                    'ad'      => $il['ad'],
                    'bolgeAd' => $bolge['ad'],
                ];
            }
        }
    }
    return $sonuclar;
}

/**
 * Tüm illerin toplam sayısını döndürür (81 olmalı).
 */
function toplamIlSayisi($bolgeler)
{
    $toplam = 0;
    foreach ($bolgeler as $bolge) {
        $toplam += count($bolge['iller']);
    }
    return $toplam;
}

/**
 * İl kodundan ilin bilgisini ve bulunduğu bölgeyi bulur.
 * Bulunamazsa null döner.
 */
function ilBul($bolgeler, $ilKodu)
{
    foreach ($bolgeler as $bolgeKodu => $bolge) {
        if (isset($bolge['iller'][$ilKodu])) {
            return [
                'il'        => $bolge['iller'][$ilKodu],
                'bolgeKodu' => $bolgeKodu,
                'bolgeAd'   => $bolge['ad'],
            ];
        }
    }
    return null;
}


/* ============================================================
 * TARİH ve YARDIMCI
 * ========================================================== */

/**
 * Tarihi gün adına çevirir. 0. gün "Bugün", 1. gün "Yarın", sonrası gün adı.
 */
function gunEtiketi($tarih, $sira)
{
    if ($sira == 0) {
        return 'Bugün';
    } elseif ($sira == 1) {
        return 'Yarın';
    }
    $gunler = ['Pazar', 'Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi'];
    return $gunler[date('w', strtotime($tarih))]; // date('w'): 0 = Pazar
}

/**
 * Tarihi "27 Ekim" biçimine çevirir.
 */
function kisaTarih($tarih)
{
    $aylar = ['', 'Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran',
              'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'];
    $zaman = strtotime($tarih);
    return date('j', $zaman) . ' ' . $aylar[date('n', $zaman)];
}

/**
 * Tarihi "26 Ekim 2026, Pazartesi" biçimine çevirir.
 */
function uzunTarih($tarih)
{
    $gunler = ['Pazar', 'Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi'];
    $zaman  = strtotime($tarih);
    return kisaTarih($tarih) . ' ' . date('Y', $zaman) . ', ' . $gunler[date('w', $zaman)];
}

/**
 * "2026-10-26T07:12" biçimindeki zamandan yalnızca saati alır → "07:12"
 */
function saat($zaman)
{
    return date('H:i', strtotime($zaman));
}

/**
 * Kısa yazım: htmlspecialchars ile güvenli çıktı.
 */
function e($metin)
{
    return htmlspecialchars($metin, ENT_QUOTES, 'UTF-8');
}

/**
 * veri/ klasöründeki bir dosyanın kullanılacak yolunu döndürür.
 *
 * Normalde (ör. Windows'ta XAMPP) dosya veri/ klasöründedir.
 * Bazı bilgisayarlarda (ör. macOS'ta XAMPP) web sunucusu başka bir kullanıcıyla çalıştığı için
 * veri/ klasörüne yazamayabilir. O zaman dosyanın bir kopyası sistemin geçici klasöründe
 * tutulur ve okuma / yazma oradan yapılır. Böylece hesap ve tema kaydetme her bilgisayarda,
 * hiçbir ayar (chmod) yapmadan çalışır. İlk kullanımda veri/ klasöründeki dosya oraya kopyalanır.
 */
function veriYolu($dosyaAdi)
{
    $klasor = __DIR__ . '/../veri';
    $asil   = $klasor . '/' . $dosyaAdi;

    // Asıl klasöre (ve varsa dosyaya) yazılabiliyorsa olduğu gibi kullanılır
    if (is_writable($klasor) && (!file_exists($asil) || is_writable($asil))) {
        return $asil;
    }

    // Yazılamıyorsa: geçici klasörde projeye özel bir klasör (her proje kopyası ayrı)
    $yedekKlasor = sys_get_temp_dir() . '/havatakip-' . md5(realpath($klasor) ?: $klasor);
    if (!is_dir($yedekKlasor)) {
        @mkdir($yedekKlasor, 0700, true);
    }
    $yedek = $yedekKlasor . '/' . $dosyaAdi;
    if (!file_exists($yedek) && file_exists($asil)) {
        @copy($asil, $yedek);   // başlangıç verisi (ör. 2 test hesabı) korunur
    }
    return $yedek;
}

/**
 * Ayarlar (dişli çark) simgesi: navbar'daki hesap ayarları ve Takip Listem'deki "Düzenle" düğmesinde kullanılır.
 */
function disliSimgesi()
{
    return '<svg class="disli" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>';
}
