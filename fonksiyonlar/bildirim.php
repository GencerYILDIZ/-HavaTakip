<?php
/*
 * fonksiyonlar/bildirim.php
 * Bildirim merkezi (navbar'daki zil simgesi).
 *
 * Takip listesindeki AKTİF kayıtlar için kullanıcının kayıtta seçtiği uyarılar
 * (yağış eşiği, don, kuvvetli rüzgâr, aşırı sıcak) BUGÜN ve YARIN için kontrol edilir.
 * Koşul tutarsa bildirim oluşur. Yeni bildirim gelince panel AÇILMAZ: assets/bildirim.wav sesi çalar,
 * zil sallanır ve zilin köşesinde kırmızı sayı (okunmamış bildirim sayısı, 99'dan fazlaysa "99+") görünür.
 * Paneli kullanıcı zile tıklayarak kendisi açar. İşlemler (× ve "Tümünü temizle") bildirim.php'dedir.
 *
 * Her kullanıcının bildirimleri oturumda ayrı tutulur: $_SESSION['bildirimMerkezi'][kullaniciAdi]
 *   liste      : [bildirimId => bildirim]  (en yeni en üstte)
 *   silinen    : [bildirimId => tarih]     kaldırılan bildirimler aynı gün tekrar gelmesin
 *   sonKontrol : son hava kontrolünün zamanı (time())
 *   imza       : takip listesinin özeti; liste değişince beklemeden yeniden kontrol edilir
 *   panelAcik  : × / "Tümünü temizle" sonrası panel açık kalsın (tek seferlik)
 *
 * Her bildirimde 'okundu' bilgisi vardır. Zilin üstündeki sayı yalnızca okunmamış bildirimleri gösterir;
 * kullanıcı zile tıklayıp paneli açınca hepsi okundu olur ve sayı kaybolur.
 *
 * Not: $_SESSION['bildirim'] tek seferlik mesajlar (mesajEkle) için kullanıldığından
 * burada farklı bir ad seçildi.
 */

// Takip listesi değişmediyse hava servisine en fazla 15 dakikada (900 sn) bir sorulur.
const BILDIRIM_KONTROL_ARALIGI = 900;

/**
 * Boş bir bildirim merkezi (oturumda ilk kez oluşturulurken kullanılır).
 */
function bildirimMerkeziBos()
{
    return [
        'liste'      => [],
        'silinen'    => [],
        'sonKontrol' => 0,
        'imza'       => '',
        'panelAcik'  => false,
    ];
}

/**
 * Bir takip kaydında kullanıcının seçtiği uyarılardan $gun. günde (0 = bugün, 1 = yarın)
 * koşulu tutanları döndürür. Eşikler takip listesindeki uyarılarla aynıdır:
 *   yağış  : yağış ihtimali ≥ kayıttaki eşik (%)
 *   don    : en düşük sıcaklık ≤ 0°
 *   rüzgâr : en yüksek rüzgâr ≥ 40 km/s
 *   sıcak  : en yüksek sıcaklık ≥ 32°
 */
function gunUyarilari($kayit, $hava, $gun)
{
    $sonuc = [];
    if ($kayit['durum'] != 'aktif' || $hava === null || !isset($hava['daily']['time'][$gun])) {
        return $sonuc;
    }

    $g      = $hava['daily'];
    $yagis  = $g['precipitation_probability_max'][$gun] ?? 0;
    $enDus  = (int) round($g['temperature_2m_min'][$gun]);
    $enYuk  = (int) round($g['temperature_2m_max'][$gun]);
    $ruzgar = (int) round($g['wind_speed_10m_max'][$gun] ?? 0);

    foreach ($kayit['uyarilar'] as $u) {
        if ($u == 'yagis' && $yagis >= $kayit['yagisEsigi']) {
            $sonuc[] = ['uyari' => 'yagis', 'tur' => 'semsiye',
                        'metin' => 'Yağış ihtimali %' . $yagis . ' (eşiğiniz %' . $kayit['yagisEsigi'] . '). Şemsiyenizi unutmayın.'];
        } elseif ($u == 'don' && $enDus <= 0) {
            $sonuc[] = ['uyari' => 'don', 'tur' => 'soguk',
                        'metin' => 'En düşük sıcaklık ' . $enDus . '°, don ve buzlanma riski var.'];
        } elseif ($u == 'ruzgar' && $ruzgar >= 40) {
            $sonuc[] = ['uyari' => 'ruzgar', 'tur' => 'ruzgar',
                        'metin' => 'Kuvvetli rüzgâr bekleniyor (' . $ruzgar . ' km/s).'];
        } elseif ($u == 'sicak' && $enYuk >= 32) {
            $sonuc[] = ['uyari' => 'sicak', 'tur' => 'sicak',
                        'metin' => 'En yüksek sıcaklık ' . $enYuk . '°, aşırı sıcak bekleniyor.'];
        }
    }
    return $sonuc;
}

/**
 * Takip listesinin kısa özeti. Kayıt eklenir, silinir veya değişirse özet de değişir;
 * böylece 15 dakika beklenmeden hemen yeniden kontrol yapılır.
 */
function takipImzasi($kayitlar)
{
    $ozet = [];
    foreach ($kayitlar as $id => $k) {
        $ozet[$id] = [$k['il'], $k['ilce'] ?? '', $k['baslik'], $k['durum'], $k['uyarilar'], $k['yagisEsigi']];
    }
    return md5(json_encode($ozet));
}

/**
 * Bildirimleri takip listesine ve güncel hava tahminine göre günceller.
 * $merkez : oturumdaki bildirim merkezi (& ile gelir, fonksiyon doğrudan onu değiştirir)
 * $kayitlar : kullanıcının takip kayıtları
 * $ilceler  : ilçe listesi (kayıtta ilçe seçildiyse ilçenin konumu kullanılır)
 */
function bildirimleriGuncelle(&$merkez, $kayitlar, $bolgeler, $ilceler = [])
{
    if (!is_array($merkez)) {
        $merkez = bildirimMerkeziBos();
    }
    $bugun = date('Y-m-d');

    // 1) Geçmiş güne ait, silinmiş / duraklatılmış kayda ait veya uyarısı artık seçili olmayan bildirimler kaldırılır
    foreach ($merkez['liste'] as $id => $b) {
        $kayit = $kayitlar[$b['kayitId']] ?? null;
        if ($b['tarih'] < $bugun || $kayit === null || $kayit['durum'] != 'aktif'
            || !in_array($b['uyari'], $kayit['uyarilar'])) {
            unset($merkez['liste'][$id]);
        }
    }
    // Kullanıcının kaldırdığı bildirimlerin kaydı da günü geçince silinir
    foreach ($merkez['silinen'] as $id => $tarih) {
        if ($tarih < $bugun) {
            unset($merkez['silinen'][$id]);
        }
    }

    // 2) Liste değişmediyse ve son kontrolün üzerinden 15 dakika geçmediyse servise tekrar sorulmaz
    $imza = takipImzasi($kayitlar);
    if ($imza === $merkez['imza'] && time() - $merkez['sonKontrol'] < BILDIRIM_KONTROL_ARALIGI) {
        return;
    }
    $merkez['imza']       = $imza;
    $merkez['sonKontrol'] = time();

    // 3) En az bir uyarı seçilmiş AKTİF kayıtların konumları → tek istekte hava durumu
    $konumlar = [];
    foreach ($kayitlar as $id => $kayit) {
        if ($kayit['durum'] != 'aktif' || count($kayit['uyarilar']) == 0) {
            continue;
        }
        $konum = takipKonumu($kayit, $bolgeler, $ilceler);   // ilçe seçildiyse ilçenin konumu
        if ($konum !== null) {
            $konumlar[$id] = $konum;
        }
    }
    if (count($konumlar) == 0) {
        return;
    }
    $havalar = bolgeHavaGetir($konumlar);
    if ($havalar === null) {
        return;   // servis yanıt vermezse eldeki bildirimler olduğu gibi kalır
    }

    // 4) Bugün (0) ve yarın (1) için koşullar kontrol edilir
    $yeniler  = [];
    $gecerli  = [];   // tahmine göre şu an geçerli olan bildirimler
    foreach ($konumlar as $id => $il) {
        $hava = $havalar[$id] ?? null;
        for ($gun = 0; $gun <= 1; $gun++) {
            foreach (gunUyarilari($kayitlar[$id], $hava, $gun) as $u) {
                $tarih = $hava['daily']['time'][$gun];
                $bid   = $id . '-' . $u['uyari'] . '-' . $tarih;   // ör. "3-yagis-2026-11-04"
                $gecerli[$bid] = true;

                if (isset($merkez['silinen'][$bid])) {
                    continue;   // kullanıcı bu bildirimi kaldırmıştı, tekrar gösterilmez
                }
                if (isset($merkez['liste'][$bid])) {
                    $merkez['liste'][$bid]['metin'] = $u['metin'];   // zaten var: yalnızca değer güncellenir
                    continue;
                }
                $yeniler[$bid] = [
                    'id'      => $bid,
                    'kayitId' => $id,
                    'il'      => $kayitlar[$id]['il'],
                    'ilce'    => $kayitlar[$id]['ilce'] ?? '',
                    'ilAd'    => $il['ad'],   // "Ankara" ya da "Çankaya, Ankara"
                    'baslik'  => $kayitlar[$id]['baslik'],
                    'uyari'   => $u['uyari'],
                    'tur'     => $u['tur'],
                    'metin'   => $u['metin'],
                    'tarih'   => $tarih,
                    'zaman'   => date('Y-m-d H:i'),
                    'yeni'    => true,
                    'okundu'  => false,
                ];
            }
        }
    }

    // Tahmin değiştiyse artık tutmayan eski bildirimler kaldırılır (ör. yağış ihtimali eşiğin altına düştü)
    foreach ($merkez['liste'] as $bid => $b) {
        if (isset($konumlar[$b['kayitId']]) && !isset($gecerli[$bid])) {
            unset($merkez['liste'][$bid]);
        }
    }

    // Yeni bildirimler listenin en üstüne eklenir
    $merkez['liste'] = $yeniler + $merkez['liste'];
}

/**
 * Navbar'daki zil ve panel için bilgileri hazırlar; ardından bildirimleri "görüldü" yapar.
 * Dönüş:
 *   liste    : gösterilecek bildirimler (gunNo: 0 bugün / 1 yarın, gunEtiket: "Bugün" / "Yarın")
 *   sayi     : toplam bildirim
 *   okunmamis: kullanıcının henüz bakmadığı bildirim sayısı (zilin üstündeki sayı)
 *   yeniSayi : bu sayfada ilk kez gösterilen bildirim sayısı
 *   acik     : panel açık mı gelsin? (yalnızca × / "Tümünü temizle" sonrası; yeni bildirim paneli açmaz)
 *   ses      : bildirim sesi çalsın mı? (yalnızca yeni bildirim varsa)
 */
function bildirimPaneli(&$merkez)
{
    if (!is_array($merkez)) {
        $merkez = bildirimMerkeziBos();
    }
    $bugun    = date('Y-m-d');
    $liste     = [];
    $yeniSayi  = 0;
    $okunmamis = 0;

    foreach ($merkez['liste'] as $id => $b) {
        $b['gunNo']     = ($b['tarih'] == $bugun) ? 0 : 1;
        $b['gunEtiket'] = gunEtiketi($b['tarih'], $b['gunNo']);
        if ($b['yeni']) {
            $yeniSayi++;
        }
        if (empty($b['okundu'])) {
            $okunmamis++;
        }
        $b['adres'] = 'il.php?il=' . $b['il'] . (!empty($b['ilce']) ? '&ilce=' . $b['ilce'] : '')
                    . '&gun=' . $b['gunNo'] . '&efekt=1#gun-detay';   // efekt=1: hava ekranın ortasında gösterilir
        $liste[] = $b;
        $merkez['liste'][$id]['yeni'] = false;   // bir kez gösterildi; sayfa yenilenince ses tekrar çalmaz
    }

    $acik = !empty($merkez['panelAcik']);   // yeni bildirim paneli açmaz; kullanıcı zile tıklayıp kendisi açar
    $merkez['panelAcik'] = false;

    return [
        'liste'    => $liste,
        'sayi'      => count($liste),
        'okunmamis' => $okunmamis,
        'rozet'     => ($okunmamis > 99) ? '99+' : (string) $okunmamis,   // zilin köşesindeki kırmızı sayı
        'yeniSayi'  => $yeniSayi,
        'acik'     => $acik,
        'ses'      => $yeniSayi > 0,
    ];
}

/**
 * Tek bir bildirimi kaldırır (× düğmesi). Kaldırılan bildirim aynı gün için tekrar oluşmaz.
 */
function bildirimSil(&$merkez, $bildirimId)
{
    if (!is_array($merkez) || !isset($merkez['liste'][$bildirimId])) {
        return false;
    }
    $merkez['silinen'][$bildirimId] = $merkez['liste'][$bildirimId]['tarih'];
    unset($merkez['liste'][$bildirimId]);
    return true;
}

/**
 * Tüm bildirimleri kaldırır ("Tümünü temizle"). Kaç bildirim kaldırıldığını döndürür.
 */
function bildirimleriTemizle(&$merkez)
{
    if (!is_array($merkez)) {
        return 0;
    }
    $sayi = count($merkez['liste']);
    foreach ($merkez['liste'] as $bildirimId => $b) {
        $merkez['silinen'][$bildirimId] = $b['tarih'];
    }
    $merkez['liste'] = [];
    return $sayi;
}

/**
 * Tüm bildirimleri "okundu" yapar (kullanıcı zile tıklayıp paneli açtığında).
 * Bildirimler listede kalır; yalnızca zilin üstündeki sayı kaybolur.
 */
function bildirimleriOkunduYap(&$merkez)
{
    if (!is_array($merkez)) {
        return;
    }
    foreach ($merkez['liste'] as $id => $b) {
        $merkez['liste'][$id]['okundu'] = true;
    }
}
