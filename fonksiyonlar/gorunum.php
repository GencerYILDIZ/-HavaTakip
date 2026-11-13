<?php
/*
 * fonksiyonlar/gorunum.php
 * Dört mevsim teması ve il sayfasındaki hava efektleri için yardımcı fonksiyonlar.
 *
 * Mevsim teması yönetim panelinden seçilir ve tüm site için geçerlidir:
 *   "oto"  → bugünün ayına göre: Mart–Mayıs ilkbahar · Haziran–Ağustos yaz ·
 *            Eylül–Kasım sonbahar · Aralık–Şubat kış
 *   ya da admin'in sabitlediği mevsim (ilkbahar, yaz, sonbahar, kis)
 * Seçim veri/ayarlar.json dosyasında saklanır. Mevsim <body data-mevsim="..."> etiketine
 * yazılır; renkler ve arka plan CSS'te body[data-mevsim="..."] kurallarıyla değişir.
 */

const MEVSIMLER = [
    'ilkbahar' => 'İlkbahar',
    'yaz'      => 'Yaz',
    'sonbahar' => 'Sonbahar',
    'kis'      => 'Kış',
];

define('AYAR_DOSYASI', veriYolu('ayarlar.json'));   // bkz. veriYolu() — genel.php

/**
 * Ay numarasından (1–12) mevsim kodu.
 */
function ayaGoreMevsim($ay)
{
    if ($ay >= 3 && $ay <= 5) {
        return 'ilkbahar';
    } elseif ($ay >= 6 && $ay <= 8) {
        return 'yaz';
    } elseif ($ay >= 9 && $ay <= 11) {
        return 'sonbahar';
    }
    return 'kis';
}

/**
 * Site ayarlarını okur (dosya yoksa varsayılanlar).
 */
function ayarlariOku()
{
    $varsayilan = ['mevsim' => 'oto'];
    if (!file_exists(AYAR_DOSYASI)) {
        return $varsayilan;
    }
    $veri = json_decode(file_get_contents(AYAR_DOSYASI), true);
    return is_array($veri) ? array_merge($varsayilan, $veri) : $varsayilan;
}

/**
 * Site ayarlarını kaydeder. Başarılıysa true.
 */
function ayarlariKaydet($ayarlar)
{
    $klasor = dirname(AYAR_DOSYASI);
    if (!is_dir($klasor)) {
        @mkdir($klasor, 0775, true);
    }
    return @file_put_contents(AYAR_DOSYASI, json_encode($ayarlar, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), LOCK_EX) !== false;
}

/**
 * Admin'in seçtiği tema ayarı: 'oto' veya bir mevsim kodu.
 */
function mevsimAyari()
{
    $ayar = ayarlariOku()['mevsim'];
    return ($ayar == 'oto' || isset(MEVSIMLER[$ayar])) ? $ayar : 'oto';
}

/**
 * Sayfada kullanılacak mevsim.
 */
function aktifMevsim()
{
    $ayar = mevsimAyari();
    return ($ayar == 'oto') ? ayaGoreMevsim((int) date('n')) : $ayar;
}

/**
 * Mevsim açılış efekti bu sayfada gösterilsin mi?
 * Ana sayfa her açıldığında, diğer sayfalarda ise oturumda bir kez ve mevsim değiştiğinde gösterilir.
 * Hava efektinin oynadığı il sayfasında gösterilmez (iki efekt üst üste binmesin).
 */
function mevsimGirisiGoster($mevsim, $havaEfektiVar)
{
    if ($havaEfektiVar) {
        return false;
    }
    // Ana sayfa her açıldığında (bölge seçimi / arama dışında) mevsim efekti oynar
    $anaSayfa = basename($_SERVER['SCRIPT_NAME']) == 'index.php' && !isset($_GET['bolge']) && !isset($_GET['ara']) && !isset($_GET['menu']);
    if (!$anaSayfa && ($_SESSION['mevsimGirisi'] ?? '') === $mevsim) {
        return false;
    }
    $_SESSION['mevsimGirisi'] = $mevsim;
    return true;
}

/**
 * Mevsime göre parçacık görselleri (assets/mevsim/ klasörü).
 */
function mevsimGorselleri($mevsim)
{
    $gorseller = [
        'ilkbahar' => ['cicek', 'cicek', 'tac-yaprak'],
        'yaz'      => ['gun-isigi', 'gun-isigi', 'isik'],
        'sonbahar' => ['akcaagac-turuncu', 'akcaagac-kirmizi', 'akcaagac-sari'],
        'kis'      => ['kar-tanesi'],
    ];
    return $gorseller[$mevsim];
}

/**
 * Mevsim parçacıkları: $adet tane görsel, her biri farklı konum, boyut, hız ve gecikmeyle.
 * Konum ve süreler CSS değişkenleriyle (--x, --g, --h, --b) verilir.
 */
function mevsimParcaciklari($mevsim, $adet, $tohum = 11)
{
    $gorseller = mevsimGorselleri($mevsim);
    $html = '';
    for ($i = 1; $i <= $adet; $i++) {
        $gorsel = $gorseller[$i % count($gorseller)];
        $html  .= '<img src="assets/mevsim/' . $gorsel . '.svg" alt="" style="--x:' . round(sahneSayi($i, $tohum) * 100, 1)
                . '%;--g:' . round(sahneSayi($i, $tohum + 3) * 3.2, 2) . 's;--h:' . round(0.7 + sahneSayi($i, $tohum + 5) * 0.7, 2)
                . ';--b:' . round(0.6 + sahneSayi($i, $tohum + 9) * 0.9, 2) . ';--d:' . round(sahneSayi($i, $tohum + 13) * 2 - 1, 2) . '">';
    }
    return $html;
}

/**
 * Efektlerde kullanılan parçacıklar (yağmur damlası, kar tanesi, yıldız…) için
 * tekrarlanabilir "rastgele" sayılar. Her sayfa açılışında aynı düzen oluşur.
 * Dönüş: 0 ile 1 arasında bir sayı.
 */
function sahneSayi($sira, $tohum)
{
    $x = sin($sira * 12.9898 + $tohum * 78.233) * 43758.5453;
    return $x - floor($x);
}

/**
 * Bir sahne türü için yağmur / kar / yıldız parçacıklarının HTML'i.
 * $adet: parçacık sayısı, $sinif: CSS sınıfı (damla, tane, yildiz, yaprak, cizgi)
 */
function sahneParcaciklari($adet, $sinif, $tohum = 1)
{
    $html = '';
    for ($i = 1; $i <= $adet; $i++) {
        $sol    = round(sahneSayi($i, $tohum) * 100, 1);           // yatay konum (%)
        $ust    = round(sahneSayi($i, $tohum + 7) * 100, 1);       // dikey konum (%) — yıldızlar için
        $gecik  = round(sahneSayi($i, $tohum + 3) * 3, 2);         // animasyon gecikmesi (sn)
        $sure   = round(0.6 + sahneSayi($i, $tohum + 5) * 0.9, 2); // animasyon süresi çarpanı
        $boyut  = round(0.6 + sahneSayi($i, $tohum + 9) * 0.8, 2); // boyut çarpanı
        $html  .= '<i class="' . $sinif . '" style="--x:' . $sol . '%;--y:' . $ust . '%;--g:' . $gecik
                . 's;--h:' . $sure . ';--b:' . $boyut . '"></i>';
    }
    return $html;
}

/**
 * İl sayfasındaki hava efektinin HTML'i.
 * $sahne : havaSahnesi() sonucu (gunesli, bulutlu, yagmurlu, karli, firtina, sisli, ruzgarli, gece, bulutlu-gece)
 * $yer   : 'giris' → sayfa açılırken tüm ekranı kaplayan, birkaç saniyede kaybolan katman
 *          'kart'  → anlık durum kartının arkasında sürekli oynayan sakin katman
 * Efektler yalnızca CSS animasyonudur (JavaScript yok).
 */
function sahneHtml($sahne, $yer, $merkez = null)
{
    $giris = ($yer == 'giris');
    $html  = '<div class="sahne sahne-' . $yer . ' sahne-' . $sahne . '" aria-hidden="true">';

    // Açılışta ekranın tam ortasında büyük, animasyonlu hava simgesi ve şehrin adı (~3,5 sn)
    if ($giris && $merkez !== null) {
        $html .= '<div class="s-merkez s-merkez-' . $merkez['ikon'] . '">'
               . '<div class="s-merkez-ikon"><img src="assets/ikonlar/' . $merkez['ikon'] . '.svg" alt=""></div>'
               . '<p class="s-merkez-yer"' . (!empty($merkez['ozelAd']) ? ' translate="no"' : '') . '>' . e($merkez['yer']) . '</p>'
               . '<p class="s-merkez-durum">' . e($merkez['durum']) . '</p></div>';
    }

    // Güneş ve ışık (sağ üstten girer). Gündüz bulutluyken de güneş bulutların arkasında görünür;
    // gece ise güneş yerine ay çıkar (aşağıda).
    if ($sahne == 'gunesli') {
        $html .= '<div class="s-isik"></div><img class="s-gunes" src="assets/ikonlar/gunesli.svg" alt="">';
    } elseif ($sahne == 'bulutlu') {
        $html .= '<img class="s-gunes s-gunes-bulutlu" src="assets/ikonlar/gunesli.svg" alt="">';
    }

    // Gece: dolunay (sol üstten girer) ve yıldızlar
    if ($sahne == 'gece' || $sahne == 'bulutlu-gece') {
        $html .= '<img class="s-ay" src="assets/ikonlar/dolunay.svg" alt="">';
        $html .= sahneParcaciklari($sahne == 'gece' ? 46 : 22, 'yildiz', 2);
    }

    // Bulutlar: soldan ve sağdan üçer bulut girer
    if (in_array($sahne, ['bulutlu', 'yagmurlu', 'karli', 'firtina', 'bulutlu-gece'])) {
        $renk = ($sahne == 'firtina') ? 'bulut-koyu' : (($sahne == 'bulutlu') ? 'bulut' : 'bulut-gri');
        foreach (['sol', 'sag'] as $taraf) {
            for ($i = 1; $i <= 3; $i++) {
                $html .= '<img class="s-bulut ' . $taraf . ' b' . $i . '" src="assets/sahne/' . $renk . '.svg" alt="">';
            }
        }
    }

    // Yağış ve diğer olaylar
    if ($sahne == 'yagmurlu') {
        $html .= sahneParcaciklari($giris ? 70 : 40, 'damla', 3);
    } elseif ($sahne == 'firtina') {
        $html .= sahneParcaciklari($giris ? 90 : 50, 'damla hizli', 4) . '<div class="s-simsek"></div>';
    } elseif ($sahne == 'karli') {
        $html .= sahneParcaciklari($giris ? 60 : 40, 'tane', 5);
    } elseif ($sahne == 'sisli') {
        for ($i = 1; $i <= 5; $i++) {
            $html .= '<i class="s-sis s' . $i . '"></i>';
        }
    } elseif ($sahne == 'ruzgarli') {
        $html .= sahneParcaciklari($giris ? 18 : 12, 'cizgi', 6) . sahneParcaciklari($giris ? 10 : 6, 'yaprak', 8);
    }

    return $html . '</div>';
}

/**
 * Bölge kartlarının sağ üstündeki simge (harita değil; bölgeyi anlatan küçük bir çizim).
 * Hepsi bu projede elle çizilmiş basit SVG'lerdir. "oynar" sınıflı parça CSS ile hareket eder
 * (bkz. style.css → BÖLGE SİMGELERİ). Örnek: bolgeSimgesi('karadeniz') → bulut ve yağmur.
 *
 *   marmara  : boğaz köprüsü          ege       : antik sütunlar ve güneş
 *   akdeniz  : dalgalar üstünde yelkenli   icanadolu : peri bacaları ve sıcak hava balonu
 *   karadeniz: yeşil tepeler, bulut, yağmur doguanadolu: karlı dağlar ve kar
 *   guneydoguanadolu: kızgın güneş ve sıcak hava dalgaları
 */
function bolgeSimgesi($kod)
{
    $cizimler = [
        'marmara' =>
            '<path class="ss-su" d="M4 48c5 0 5-3 10-3s5 3 10 3 5-3 10-3 5 3 10 3 5-3 10-3 5 3 10 3"/>'
          . '<path d="M14 40V12M50 40V12M10 40h44"/>'
          . '<path d="M14 13c6 14 12 20 18 20s12-6 18-20"/>'
          . '<path d="M20 26v14M26 31v9M32 33v7M38 31v9M44 26v14"/>'
          . '<g class="oynar ss-araba"><rect x="6" y="36.5" width="7" height="3" rx="1.2"/></g>',
        'ege' =>
            '<g class="oynar ss-gunes"><circle cx="50" cy="13" r="5"/><path d="M50 3.5v2.5M50 20v2.5M40.5 13H43M57 13h2.5M43.3 6.3l1.8 1.8M54.9 17.9l1.8 1.8M43.3 19.7l1.8-1.8M54.9 8.1l1.8-1.8"/></g>'
          . '<path d="M6 26 L22 18 L38 26 Z"/><path d="M7 29h30"/>'
          . '<path d="M11 29v17M17 29v17M27 29v17M33 29v17"/>'
          . '<path d="M5 49h34"/>'
          . '<path class="ss-su" d="M42 49c3 0 3-2 6-2s3 2 6 2"/>',
        'akdeniz' =>
            '<g class="oynar ss-yelken"><path d="M32 10v28"/><path class="ss-dolu" d="M33 12c8 6 11 16 10 24H33z"/><path d="M31 16c-6 5-8 12-8 20h8z"/><path d="M21 40h24l-4 5H25z"/></g>'
          . '<path class="ss-su" d="M4 50c5 0 5-3 10-3s5 3 10 3 5-3 10-3 5 3 10 3 5-3 10-3 5 3 10 3"/>'
          . '<circle cx="12" cy="13" r="4"/>',
        'icanadolu' =>
            '<path d="M6 50c2-10 3-18 7-18s5 8 7 18"/><path d="M10 33c0-3 1.5-5 3-5s3 2 3 5"/>'
          . '<path d="M20 50c2-12 4-21 9-21s6 9 8 21"/><path d="M24.5 30c0-3.5 2-6 4.5-6s4.5 2.5 4.5 6"/>'
          . '<path d="M4 50h56"/>'
          . '<g class="oynar ss-balon"><path d="M48 25c-6 0-9-4.5-9-9a9 9 0 0 1 18 0c0 4.5-3 9-9 9z"/><path d="M48 7c-3 3-3 15 0 18M48 7c3 3 3 15 0 18"/><path d="M45.5 25l1 4h3l1-4"/><rect x="46" y="29" width="4" height="3" rx=".6"/></g>',
        'karadeniz' =>
            '<path d="M4 50c6-10 12-14 18-14s10 6 14 6 8-8 14-8 8 6 10 16"/>'
          . '<path d="M14 50l3-8 3 8M40 50l3-9 3 9"/>'
          . '<g class="oynar ss-bulut"><path d="M18 22h26a7 7 0 0 0 0-14 10 10 0 0 0-19-2 7 7 0 0 0-7 16z"/></g>'
          . '<path class="ss-yagmur" d="M22 27l-2 5M31 27l-2 5M40 27l-2 5"/>',
        'doguanadolu' =>
            '<path d="M2 50L22 20l10 14 8-10 22 26z"/>'
          . '<path class="ss-dolu" d="M17 27.5L22 20l5 7.5-2.5-1.5-2.5 2-2.5-2z"/>'
          . '<path class="ss-dolu" d="M36.5 28.5L40 24l3.5 4.5-1.8-1-1.7 1.4-1.7-1.4z"/>'
          . '<g class="ss-kar"><path class="k1" d="M10 8v4M8 10h4"/><path class="k2" d="M48 6v4M46 8h4"/><path class="k3" d="M30 4v4M28 6h4"/></g>',
        'guneydoguanadolu' =>
            '<g class="oynar ss-gunes"><circle cx="32" cy="22" r="8"/><path d="M32 6v4M32 34v4M16 22h4M44 22h4M20.7 10.7l2.8 2.8M40.5 30.5l2.8 2.8M20.7 33.3l2.8-2.8M40.5 13.5l2.8-2.8"/></g>'
          . '<path class="ss-sicak" d="M10 44c3-2 6 2 9 0s6-2 9 0 6 2 9 0 6-2 9 0 6 2 9 0"/>'
          . '<path d="M6 51h52"/>',
    ];
    $ic = $cizimler[$kod] ?? $cizimler['icanadolu'];
    return '<svg class="bolge-simge simge-' . e($kod) . '" viewBox="0 0 64 56" aria-hidden="true">' . $ic . '</svg>';
}
