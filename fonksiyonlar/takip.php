<?php
/*
 * fonksiyonlar/takip.php
 * Takip listesi: kullanıcıya özel kayıtları ekleme, silme, doğrulama,
 * filtreleme ve sayma fonksiyonları.
 *
 * Kayıtlar oturumda, kullanıcı adına göre ayrı ayrı tutulur:
 *   $_SESSION['listeler']['kullanici'][3] = ['id' => 3, 'il' => 'trabzon', 'ilce' => 'akcaabat', ...]
 *   ('ilce' boşsa ya da hiç yoksa il merkezi takip edilir.)
 *   $_SESSION['sonIdler']['kullanici']    = 5   (son verilen kayıt numarası)
 * Böylece aynı tarayıcıda farklı hesaplarla giriş yapılsa da listeler karışmaz.
 */

/**
 * Kullanıcının oturumda bir listesi yoksa örnek kayıtları yükler.
 * Girişte ve "örnek kayıtlara dön" denildiğinde çağrılır.
 */
function ornekKayitlariYukle($kullaniciAdi, $ornekTakipler, $zorla = false)
{
    if (isset($_SESSION['listeler'][$kullaniciAdi]) && !$zorla) {
        return;   // liste zaten var, dokunma
    }

    $ornekler = $ornekTakipler[$kullaniciAdi] ?? [];

    $_SESSION['listeler'][$kullaniciAdi] = [];
    foreach ($ornekler as $kayit) {
        $_SESSION['listeler'][$kullaniciAdi][$kayit['id']] = $kayit;
    }
    // Yeni eklenecek kaydın numarası en büyük id'den devam eder
    $_SESSION['sonIdler'][$kullaniciAdi] = count($ornekler) > 0 ? max(array_column($ornekler, 'id')) : 0;
}

/**
 * Bir kullanıcının tüm takip kayıtlarını döndürür (yoksa boş dizi).
 */
function kullaniciListesi($kullaniciAdi)
{
    return $_SESSION['listeler'][$kullaniciAdi] ?? [];
}

/**
 * Formdan gelen ham veriyi doğrular.
 *
 * Dönüş: ['hatalar' => [...], 'veri' => [...]]
 *   hatalar : alan adı => hata mesajı (boşsa form geçerlidir)
 *   veri    : temizlenmiş, kaydedilmeye hazır değerler
 */
function takipDogrula($girdi, $bolgeler, $kategoriler, $durumlar, $uyariTurleri, $mevcutKayitlar, $ilceler = [])
{
    $hatalar = [];

    // Gelen değerleri al ve baştaki/sondaki boşlukları temizle
    $il        = trim($girdi['il'] ?? '');
    $ilce      = trim($girdi['ilce'] ?? '');
    $baslik    = trim($girdi['baslik'] ?? '');
    $kategori  = trim($girdi['kategori'] ?? '');
    $durum     = trim($girdi['durum'] ?? '');
    $aciklama  = trim($girdi['aciklama'] ?? '');
    $esikMetin = trim($girdi['yagisEsigi'] ?? '');

    // Onay kutuları seçilmezse hiç gönderilmez; gelen değer dizi değilse boş dizi kabul et
    $uyarilar = $girdi['uyarilar'] ?? [];
    if (!is_array($uyarilar)) {
        $uyarilar = [];
    }

    // 1) İl: zorunlu ve listede olmalı
    if ($il === '') {
        $hatalar['il'] = 'Lütfen takip etmek istediğiniz ili seçin.';
    } elseif (ilBul($bolgeler, $il) === null) {
        $hatalar['il'] = 'Geçersiz bir il seçildi.';
    }

    // 1b) İlçe: isteğe bağlı (boş = il merkezi); seçildiyse o ilin ilçelerinden biri olmalı
    if ($ilce == 'merkez') {
        $ilce = '';
    }
    if ($ilce !== '' && !isset($hatalar['il']) && !isset(ilceListesi($ilceler, $il)[$ilce])) {
        $hatalar['ilce'] = 'Seçilen ilçe bu ile ait değil. Lütfen ilçeyi yeniden seçin.';
    }

    // 2) Başlık: zorunlu, 2–40 karakter
    if ($baslik === '') {
        $hatalar['baslik'] = 'Takip adı boş bırakılamaz.';
    } elseif (mb_strlen($baslik) < 2) {
        $hatalar['baslik'] = 'Takip adı en az 2 karakter olmalıdır.';
    } elseif (mb_strlen($baslik) > 40) {
        $hatalar['baslik'] = 'Takip adı en fazla 40 karakter olabilir (şu an ' . mb_strlen($baslik) . ').';
    } else {
        // Aynı yer (il + ilçe) için aynı adla ikinci bir kayıt olmasın
        foreach ($mevcutKayitlar as $kayit) {
            if ($kayit['il'] == $il && ($kayit['ilce'] ?? '') == $ilce && mb_strtolower($kayit['baslik']) == mb_strtolower($baslik)) {
                $hatalar['baslik'] = 'Bu yer için "' . $baslik . '" adıyla zaten bir takip kaydınız var.';
                break;
            }
        }
    }

    // 3) Kategori ve durum: zorunlu ve izin verilen değerlerden biri olmalı
    if ($kategori === '') {
        $hatalar['kategori'] = 'Lütfen bir kategori seçin.';
    } elseif (!isset($kategoriler[$kategori])) {
        $hatalar['kategori'] = 'Geçersiz bir kategori seçildi.';
    }

    if ($durum === '') {
        $hatalar['durum'] = 'Lütfen takibin durumunu seçin.';
    } elseif (!isset($durumlar[$durum])) {
        $hatalar['durum'] = 'Geçersiz bir durum seçildi.';
    }

    // 4) Uyarılar: her biri listede olmalı; aktif takipte en az bir uyarı seçilmeli
    $temizUyarilar = [];
    foreach ($uyarilar as $u) {
        if (isset($uyariTurleri[$u])) {
            $temizUyarilar[] = $u;
        } else {
            $hatalar['uyarilar'] = 'Geçersiz bir uyarı türü seçildi.';
        }
    }
    if (!isset($hatalar['uyarilar']) && $durum == 'aktif' && count($temizUyarilar) == 0) {
        $hatalar['uyarilar'] = 'Aktif bir takip için en az bir uyarı türü seçin.';
    }

    // 5) Yağış eşiği: yağış uyarısı seçildiyse 10–100 arası tam sayı olmalı
    $yagisEsigi = 50;
    if (in_array('yagis', $temizUyarilar)) {
        if ($esikMetin === '') {
            $hatalar['yagisEsigi'] = 'Yağış uyarısı için bir eşik değeri girin.';
        } else {
            $sayi = filter_var($esikMetin, FILTER_VALIDATE_INT, ['options' => ['min_range' => 10, 'max_range' => 100]]);
            if ($sayi === false) {
                $hatalar['yagisEsigi'] = 'Yağış eşiği 10 ile 100 arasında bir tam sayı olmalıdır.';
            } else {
                $yagisEsigi = $sayi;
            }
        }
    }

    // 6) Açıklama: isteğe bağlı, en fazla 200 karakter
    if (mb_strlen($aciklama) > 200) {
        $hatalar['aciklama'] = 'Açıklama en fazla 200 karakter olabilir (şu an ' . mb_strlen($aciklama) . ').';
    }

    return [
        'hatalar' => $hatalar,
        'veri'    => [
            'il'         => $il,
            'ilce'       => $ilce,
            'baslik'     => $baslik,
            'kategori'   => $kategori,
            'durum'      => $durum,
            'uyarilar'   => $temizUyarilar,
            'yagisEsigi' => $yagisEsigi,
            'aciklama'   => $aciklama,
        ],
    ];
}

/**
 * Doğrulanmış kaydı kullanıcının listesine ekler ve yeni kaydın id'sini döndürür.
 */
function takipEkle($kullaniciAdi, $veri)
{
    $id = ($_SESSION['sonIdler'][$kullaniciAdi] ?? 0) + 1;
    $_SESSION['sonIdler'][$kullaniciAdi] = $id;

    $veri['id']      = $id;
    $veri['eklenme'] = date('Y-m-d H:i');

    $_SESSION['listeler'][$kullaniciAdi][$id] = $veri;
    return $id;
}

/**
 * UPDATE — Doğrulanmış yeni değerleri mevcut kaydın üzerine yazar (ekle.php?id=… düzenleme formu).
 * Kaydın numarası ve eklenme tarihi değişmez; düzenlenme zamanı 'guncelleme' alanına yazılır.
 * Kayıt yoksa false döner.
 */
function takipGuncelle($kullaniciAdi, $id, $veri)
{
    if (!isset($_SESSION['listeler'][$kullaniciAdi][$id])) {
        return false;
    }
    $eski = $_SESSION['listeler'][$kullaniciAdi][$id];
    $veri['id']         = $eski['id'] ?? $id;
    $veri['eklenme']    = $eski['eklenme'];
    $veri['guncelleme'] = date('Y-m-d H:i');
    $_SESSION['listeler'][$kullaniciAdi][$id] = $veri;
    return true;
}

/**
 * Kullanıcının listesinden bir kaydı siler.
 * Silinen kaydı döndürür; kayıt yoksa null döner.
 */
function takipSil($kullaniciAdi, $id)
{
    if (!isset($_SESSION['listeler'][$kullaniciAdi][$id])) {
        return null;
    }
    $silinen = $_SESSION['listeler'][$kullaniciAdi][$id];
    unset($_SESSION['listeler'][$kullaniciAdi][$id]);
    return $silinen;
}

/**
 * Bir kaydın durumunu değiştirir (Aktif ⇄ Duraklatıldı).
 * Dönüş: true (değişti) · null (kayıt yok) · 'uyari-yok' (aktif kayıtta en az bir uyarı olmalı)
 */
function takipDurumDegistir($kullaniciAdi, $id, $durum)
{
    if (!isset($_SESSION['listeler'][$kullaniciAdi][$id])) {
        return null;
    }
    $kayit = $_SESSION['listeler'][$kullaniciAdi][$id];
    // Ekleme formundaki kural burada da geçerli: aktif bir takipte en az bir uyarı seçili olmalı
    if ($durum == 'aktif' && count($kayit['uyarilar']) == 0) {
        return 'uyari-yok';
    }
    $_SESSION['listeler'][$kullaniciAdi][$id]['durum'] = $durum;
    return true;
}

/**
 * Kayıtları kategori ve/veya duruma göre süzer.
 * Boş metin verilen ölçüt uygulanmaz ("tümü" anlamına gelir).
 */
function takipFiltrele($kayitlar, $kategori, $durum)
{
    $sonuc = [];
    foreach ($kayitlar as $id => $kayit) {
        if ($kategori !== '' && $kayit['kategori'] != $kategori) {
            continue;
        }
        if ($durum !== '' && $kayit['durum'] != $durum) {
            continue;
        }
        $sonuc[$id] = $kayit;
    }
    return $sonuc;
}

/**
 * Toplam, duruma göre ve kategoriye göre kayıt sayılarını hesaplar.
 * Dönüş: ['toplam' => 7, 'durum' => ['aktif' => 5, 'pasif' => 2], 'kategori' => ['ev' => 1, ...]]
 */
function takipSayaclari($kayitlar, $kategoriler, $durumlar)
{
    $sayac = ['toplam' => count($kayitlar), 'durum' => [], 'kategori' => []];

    // Önce her değeri 0 ile başlat, böylece hiç kaydı olmayanlar da 0 görünür
    foreach ($durumlar as $kod => $ad) {
        $sayac['durum'][$kod] = 0;
    }
    foreach ($kategoriler as $kod => $ad) {
        $sayac['kategori'][$kod] = 0;
    }

    foreach ($kayitlar as $kayit) {
        $sayac['durum'][$kayit['durum']]++;
        $sayac['kategori'][$kayit['kategori']]++;
    }
    return $sayac;
}

/**
 * Bir takip kaydının kullanıcının seçtiği uyarılara göre bugün tetiklenen uyarılarını bulur.
 * $hava: bolgeHavaGetir() sonucundaki tek bir konumun verisi.
 * Duraklatılmış kayıtlarda uyarı üretilmez.
 */
function takipUyarilari($kayit, $hava)
{
    $sonuc = [];
    if ($kayit['durum'] != 'aktif' || $hava === null) {
        return $sonuc;
    }

    $g      = $hava['daily'];
    $yagis  = $g['precipitation_probability_max'][0] ?? 0;
    $enDus  = $g['temperature_2m_min'][0];
    $enYuk  = $g['temperature_2m_max'][0];
    $ruzgar = $g['wind_speed_10m_max'][0] ?? 0;

    foreach ($kayit['uyarilar'] as $u) {
        if ($u == 'yagis' && $yagis >= $kayit['yagisEsigi']) {
            $sonuc[] = ['tur' => 'semsiye', 'metin' => 'Bugün yağış ihtimali %' . $yagis . ' (eşiğiniz %' . $kayit['yagisEsigi'] . ').'];
        } elseif ($u == 'don' && $enDus <= 0) {
            $sonuc[] = ['tur' => 'soguk', 'metin' => 'Gece sıcaklık ' . round($enDus) . '°, don riski var.'];
        } elseif ($u == 'ruzgar' && $ruzgar >= 40) {
            $sonuc[] = ['tur' => 'ruzgar', 'metin' => 'Kuvvetli rüzgâr bekleniyor (' . round($ruzgar) . ' km/s).'];
        } elseif ($u == 'sicak' && $enYuk >= 32) {
            $sonuc[] = ['tur' => 'sicak', 'metin' => 'Sıcaklık ' . round($enYuk) . '° olacak, aşırı sıcak.'];
        }
    }
    return $sonuc;
}

/**
 * "2026-10-24 10:00" → "24 Ekim, 10:00"
 */
function eklenmeTarihi($zaman)
{
    return kisaTarih($zaman) . ', ' . date('H:i', strtotime($zaman));
}

/* ----------------------------------------------------------------------
 * Takip kaydının yeri (il veya ilçe)
 * -------------------------------------------------------------------- */

/**
 * Kaydın gösterilecek yer adı: "Çankaya, Ankara" ya da yalnızca "Ankara".
 */
function takipYerAdi($kayit, $bolgeler, $ilceler)
{
    $ilBilgi = ilBul($bolgeler, $kayit['il']);
    $ilAdi   = $ilBilgi['il']['ad'] ?? $kayit['il'];
    $ilce    = $kayit['ilce'] ?? '';
    if ($ilce === '') {
        return $ilAdi;
    }
    $ilceAdi = ilceListesi($ilceler, $kayit['il'])[$ilce] ?? '';
    return $ilceAdi !== '' ? $ilceAdi . ', ' . $ilAdi : $ilAdi;
}

/**
 * Kaydın hava durumu sayfasının adresi: il.php?il=ankara&ilce=cankaya
 */
function takipAdresi($kayit)
{
    $ilce = $kayit['ilce'] ?? '';
    return 'il.php?il=' . $kayit['il'] . ($ilce !== '' ? '&ilce=' . $ilce : '');
}

/**
 * bolgeHavaGetir() için kaydın konumu: ['ad' => ..., 'lat' => ..., 'lon' => ...].
 * İlçe seçildiyse ilçenin koordinatı (önbellekten / geocoding), bulunamazsa il merkezi kullanılır.
 * İl geçersizse null.
 */
function takipKonumu($kayit, $bolgeler, $ilceler)
{
    $ilBilgi = ilBul($bolgeler, $kayit['il']);
    if ($ilBilgi === null) {
        return null;
    }
    $konum = $ilBilgi['il'];
    $ilce  = $kayit['ilce'] ?? '';
    if ($ilce !== '') {
        $ilceKonum = ilceKonumu($kayit['il'], $ilce, $bolgeler, $ilceler);
        if ($ilceKonum !== null) {
            $konum['lat'] = $ilceKonum['lat'];
            $konum['lon'] = $ilceKonum['lon'];
        }
        $konum['ad'] = takipYerAdi($kayit, $bolgeler, $ilceler);
    }
    return $konum;
}

