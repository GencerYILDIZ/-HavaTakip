<?php
/*
 * il.php — Bir ilin ayrıntılı hava durumu
 *
 * Kullanım:
 *   il.php?il=istanbul                  → il merkezinin hava durumu
 *   il.php?il=istanbul&gun=3            → 7 günlük listeden 4. gün (0 = bugün) seçili
 *   il.php?il=istanbul&ilce=besiktas    → Beşiktaş ilçesinin hava durumu
 *   il.php?il=istanbul&ilceler=1        → ilçe listesi açık gelir
 */

require_once 'baslat.php';   // oturum, veri ve fonksiyonlar

/* ---------- 1) Hangi il, hangi gün? ---------- */
$ilKodu  = $_GET['il'] ?? '';
$bulunan = ilBul($bolgeler, $ilKodu);   // geçersizse null

// Gün 0 ile 6 arasında bir tam sayı olmalı; değilse bugünü (0) göster
$secilenGun = (int) ($_GET['gun'] ?? 0);
if ($secilenGun < 0 || $secilenGun > 6) {
    $secilenGun = 0;
}

/* ---------- İlçe seçildi mi? ---------- */
$ilceKodu      = $_GET['ilce'] ?? '';
$ilceAdi       = '';
$ozelAd        = '';   // ilçe başlıklarında translate="no" (bkz. fonksiyonlar/dil.php)
$ilceUyari     = '';
$ilceSecimAcik = isset($_GET['ilceler']) || $ilceKodu !== '';   // ilçe listesi açık mı gelsin?

$hava = null;
if ($bulunan !== null) {
    $il       = $bulunan['il'];
    $ilceListe = ilceListesi($ilceler, $ilKodu);   // ['erbaa' => 'Erbaa', ...]

    // Varsayılan: il merkezinin koordinatı
    $lat = $il['lat'];
    $lon = $il['lon'];

    if ($ilceKodu !== '') {
        if (!isset($ilceListe[$ilceKodu])) {
            $ilceUyari = 'Bu ilde böyle bir ilçe yok; il merkezinin hava durumu gösteriliyor.';
            $ilceKodu  = '';
        } else {
            $konum = ilceKonumu($ilKodu, $ilceKodu, $bolgeler, $ilceler);
            if ($konum === null) {
                $ilceUyari = $ilceListe[$ilceKodu] . ' ilçesinin konumu şu an bulunamadı; il merkezinin hava durumu gösteriliyor.';
                $ilceKodu  = '';
            } else {
                $lat     = $konum['lat'];
                $lon     = $konum['lon'];
                $ilceAdi = $ilceListe[$ilceKodu];
            }
        }
    }

    $hava = ilHavaGetir($lat, $lon);

    // Hava kalitesi (Open-Meteo Air Quality API). Alınamazsa null; sayfa yine çalışır.
    $havaKalitesi = havaKalitesiGetir($lat, $lon);

    // Sayfadaki bağlantılarda kullanılacak adres (ilçe seçiliyse ilçe de eklenir)
    $adres = 'il.php?il=' . $ilKodu . ($ilceKodu !== '' ? '&ilce=' . $ilceKodu : '');

    // Sayfa başlığı: "Erbaa", "Tokat Merkez" veya "Tokat"
    if ($ilceKodu === '') {
        $yerAdi = $il['ad'];
    } elseif ($ilceKodu == 'merkez') {
        $yerAdi = $il['ad'] . ' Merkez';
    } else {
        $yerAdi = $ilceAdi;
    }
    // İlçe adı özel addır; İngilizce sayfada çevrilmesin ("Pazar" ilçesi "Sunday" olmasın)
    $ozelAd = ($yerAdi === $ilceAdi) ? ' translate="no"' : '';
}

if ($hava !== null) {
    $gunluk = $hava['daily'];
    $simdi  = $hava['current'];

    /* ---------- 2) Seçilen günün saatleri ve yağış aralıkları ---------- */
    $secilenTarih = $gunluk['time'][$secilenGun];

    // Günün 24 saatinin tamamı (bugün için geçmiş saatler de gösterilir, soluk renkte)
    $saatler   = gununSaatleri($hava['hourly'], $secilenTarih);
    $araliklar = yagisAraliklari($saatler, 40);

    // Şu anki saat (ör. 14). Bugünün geçmiş / şimdiki saatlerini ayırmak için.
    $suAnkiSaat = (int) substr($simdi['time'], 11, 2);

    /* ---------- Anlık kart için: bugün HENÜZ BİTMEMİŞ yağış aralıkları ---------- */
    $bugunSaatleri = gununSaatleri($hava['hourly'], $gunluk['time'][0]);
    $bugunMetinler = [];
    foreach (yagisAraliklari($bugunSaatleri, 40) as $a) {
        if ($a['bitSaat'] > $suAnkiSaat) {
            $bas = ($a['basSaat'] <= $suAnkiSaat) ? 'Şu an' : $a['bas'];        // devam ediyorsa "Şu an"
            $bit = ($a['bitSaat'] == 24) ? '24:00' : $a['bit'];                 // gece yarısı → "24:00"
            $bugunMetinler[] = $bas . '–' . $bit;
        }
    }

    // Kutudaki yağış ihtimali: saatlik verideki şu anki saatin değeri
    // (aşağıdaki saatlik ızgaradaki "Şimdi" kutusuyla aynı sayı görünsün diye)
    $suAnkiYagis = $simdi['precipitation_probability'] ?? 0;
    foreach ($bugunSaatleri as $s) {
        if ((int) substr($s['saat'], 0, 2) == $suAnkiSaat) {
            $suAnkiYagis = $s['olasilik'];
        }
    }

    /* ---------- Sayfa açılış efekti: şu anki havaya göre ----------
     * Şehir (veya ilçe) her açıldığında oynar; aynı sayfada gün seçerken ya da ilçe listesini
     * açarken (adreste gun= / ilceler= varsa) tekrar oynamaz.
     */
    $gunduz = $simdi['is_day'] ?? 1;
    $sahne  = havaSahnesi($simdi['weather_code'], $simdi['wind_speed_10m'], $gunduz);
    $acilisEfekti = (!isset($_GET['gun']) && !isset($_GET['ilceler'])) || isset($_GET['efekt']);
    // Ekranın ortasında büyük gösterilecek simge ve yazı
    $merkezSimge = [
        'ikon'  => havaIkonu($simdi['weather_code'], $gunduz),
        'yer'   => $yerAdi,
        'ozelAd' => $ozelAd !== '',
        'durum' => havaMetni($simdi['weather_code']) . ' · ' . round($simdi['temperature_2m']) . '°',
    ];

    /* ---------- 3) Sıcaklık çubukları için haftanın en düşük/en yüksek değeri ---------- */
    $haftaMin = min($gunluk['temperature_2m_min']);
    $haftaMax = max($gunluk['temperature_2m_max']);
    $aralik   = max($haftaMax - $haftaMin, 1);  // 0'a bölmeyi önle
}

$sayfaBasligi = ($bulunan !== null) ? $yerAdi . ' Hava Durumu' : 'İl bulunamadı';
$aktifMenu    = 'bolgeler';
require 'header.php';
?>

<main>

<?php if ($bulunan === null): ?>
    <!-- Geçersiz veya boş il kodu -->
    <p class="mesaj hata">Aradığınız il bulunamadı.</p>
    <p><a href="index.php">← Ana sayfaya dön</a></p>

<?php else: ?>
    <p class="yol">
        <a href="index.php">Ana Sayfa</a> ›
        <a href="index.php?bolge=<?php echo $bulunan['bolgeKodu']; ?>#bolge-detay"><?php echo e($bulunan['bolgeAd']); ?></a> ›
        <?php if ($ilceKodu !== ''): ?>
            <a href="il.php?il=<?php echo $ilKodu; ?>"><?php echo e($il['ad']); ?></a> › <span translate="no"><?php echo e($ilceAdi); ?></span>
        <?php else: ?>
            <?php echo e($il['ad']); ?>
        <?php endif; ?>
    </p>

    <?php if ($ilceUyari !== ''): ?>
        <p class="mesaj uyari"><?php echo e($ilceUyari); ?></p>
    <?php endif; ?>

    <?php if ($hava === null): ?>
        <h1<?php echo $ozelAd; ?>><?php echo e($yerAdi); ?></h1>
        <p class="mesaj uyari">Hava durumu verisi şu an alınamadı. İnternet bağlantınızı kontrol edip sayfayı yenileyin.</p>
        <?php require 'ilce-secim.php'; ?>

    <?php else: ?>

        <!-- ================= HAVA EFEKTİ =================
             Sayfa açılırken şu anki havaya göre bulutlar kenarlardan girer, yağmur/kar yağar,
             güneş ya da ay doğar; birkaç saniye sonra efekt kaybolur. Kartın arkasında sakin hâli sürer. -->
        <?php if ($acilisEfekti): ?>
            <?php echo sahneHtml($sahne, 'giris', $merkezSimge); ?>
        <?php endif; ?>

        <!-- ================= ANLIK DURUM ================= -->
        <section class="anlik-kart sahneli">
            <?php echo sahneHtml($sahne, 'kart'); ?>
            <div class="anlik-sol">
                <div class="anlik-baslik">
                    <h1<?php echo $ozelAd; ?>><?php echo e($yerAdi); ?></h1>
                    <!-- İlçe seçimi: aşağıdaki ilçe listesini açar -->
                    <a href="<?php echo $adres; ?>&ilceler=1#ilce-secim" class="ilce-buton">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21s-7-6.1-7-11.5A7 7 0 0 1 19 9.5C19 14.9 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/></svg>
                        <?php echo $ilceKodu !== '' ? 'İlçe değiştir' : 'İlçe seç'; ?> (<?php echo count($ilceListe); ?>)
                    </a>
                </div>
                <p class="anlik-alt">
                    <?php echo uzunTarih($simdi['time']); ?> · <?php echo saat($simdi['time']); ?>
                    <br>
                    <?php if ($ilceKodu !== ''): ?>
                        <?php echo e($il['ad']); ?> · <?php echo e($bulunan['bolgeAd']); ?> Bölgesi
                    <?php else: ?>
                        <?php echo e($bulunan['bolgeAd']); ?> Bölgesi · il merkezi
                    <?php endif; ?>
                </p>
                <div class="anlik-derece">
                    <?php echo havaSimgesi($simdi['weather_code'], $gunduz); ?>
                    <span><?php echo round($simdi['temperature_2m']); ?>°</span>
                </div>
                <p class="anlik-durum">
                    <?php echo havaMetni($simdi['weather_code']); ?>
                    <span class="anlik-minmax">
                        En yüksek <?php echo round($gunluk['temperature_2m_max'][0]); ?>° ·
                        En düşük <?php echo round($gunluk['temperature_2m_min'][0]); ?>°
                    </span>
                </p>

                <?php if (girisYapildiMi()): ?>
                    <?php
                        // Bu yer (il ya da ilçe) kullanıcının takip listesinde kaç kez var?
                        $takipIlce   = ($ilceKodu == 'merkez' || $ilceAdi === '') ? '' : $ilceKodu;
                        $takipSayisi = 0;
                        foreach (kullaniciListesi(aktifKullanici()['kullaniciAdi']) as $t) {
                            if ($t['il'] == $ilKodu && ($t['ilce'] ?? '') == $takipIlce) {
                                $takipSayisi++;
                            }
                        }
                        // GET ile il ve ilçe bilgisi forma taşınır: ekle.php?il=istanbul&ilce=besiktas
                        $ekleAdresi = 'ekle.php?il=' . $ilKodu . ($takipIlce !== '' ? '&ilce=' . $takipIlce : '');
                    ?>
                    <a href="<?php echo e($ekleAdresi); ?>" class="takip-ekle-buton">+ Takip listeme ekle</a>
                    <?php if ($takipSayisi > 0): ?>
                        <a href="takip.php" class="takip-durum">✓ <?php echo $takipSayisi; ?> takip kaydınızda var</a>
                    <?php endif; ?>
                <?php else: ?>
                    <!-- Giriş yapılmamışsa takip listesi gösterilmez; giriş sonrası forma dönülür -->
                    <a href="giris.php?geri=<?php echo urlencode('ekle.php?il=' . $ilKodu . (($ilceKodu !== '' && $ilceKodu != 'merkez') ? '&ilce=' . $ilceKodu : '')); ?>" class="takip-ekle-buton ikincil">Takip etmek için giriş yapın</a>
                <?php endif; ?>
            </div>

            <ul class="anlik-bilgiler">
                <li><span>Hissedilen</span><strong><?php echo round($simdi['apparent_temperature']); ?>°</strong></li>
                <li><span>Nem</span><strong>%<?php echo $simdi['relative_humidity_2m']; ?></strong></li>
                <li><span>Rüzgâr</span><strong><?php echo round($simdi['wind_speed_10m']); ?> km/s</strong></li>
                <li class="yagis-kutu">
                    <!-- Kutunun tamamı bağlantı: bugünün saatlik ayrıntısına gider -->
                    <a href="<?php echo $adres; ?>&gun=0#gun-detay" title="Bugünün saat saat hava durumu">
                        <span>Yağış ihtimali</span>
                        <strong>%<?php echo $suAnkiYagis; ?></strong>
                        <small>Gün geneli %<?php echo $gunluk['precipitation_probability_max'][0] ?? 0; ?> · <?php echo $gunluk['precipitation_sum'][0]; ?> mm</small>

                        <span class="yagis-saatleri">
                            <?php if (count($bugunMetinler) == 0): ?>
                                Bugün kalan saatlerde yağış beklenmiyor
                            <?php else: ?>
                                ☂ Yağış: <?php echo implode(', ', $bugunMetinler); ?>
                            <?php endif; ?>
                        </span>
                        <span class="yagis-link">Saat saat gör →</span>
                    </a>
                </li>
                <li>
                    <span>Gün doğumu / batımı</span>
                    <strong><?php echo saat($gunluk['sunrise'][0]); ?> – <?php echo saat($gunluk['sunset'][0]); ?></strong>
                </li>
            </ul>
        </section>

        <!-- ================= İLÇE SEÇİMİ (kartın hemen altında) ================= -->
        <?php require 'ilce-secim.php'; ?>

        <!-- ================= HAVA KALİTESİ ================= -->
        <section class="hava-kalitesi">
            <h2>Hava kalitesi</h2>
            <?php if ($havaKalitesi === null): ?>
                <p class="mesaj uyari">Hava kalitesi verisi şu an alınamadı.</p>
            <?php else: ?>
                <?php
                    $hk   = $havaKalitesi['current'];
                    $aqi  = (int) round($hk['european_aqi']);
                    $hkd  = havaKalitesiDurumu($aqi);
                    // Kirleticiler: [API adı, kısa ad, açıklama]
                    $kirleticiler = [
                        ['pm2_5', 'PM2.5', 'İnce partikül'],
                        ['pm10', 'PM10', 'Partikül madde'],
                        ['nitrogen_dioxide', 'NO₂', 'Azot dioksit'],
                        ['ozone', 'O₃', 'Ozon'],
                        ['sulphur_dioxide', 'SO₂', 'Kükürt dioksit'],
                        ['carbon_monoxide', 'CO', 'Karbon monoksit'],
                    ];
                ?>
                <div class="hk-kart hk-<?php echo $hkd['sinif']; ?>">
                    <div class="hk-ozet">
                        <div class="hk-deger">
                            <strong><?php echo $aqi; ?></strong>
                            <span>Avrupa AQI</span>
                        </div>
                        <div class="hk-metin">
                            <p class="hk-durum"><?php echo $hkd['ad']; ?></p>
                            <p><?php echo $hkd['tavsiye']; ?></p>
                            <?php if ($havaKalitesi['gunEnYuksek'] !== null): ?>
                                <p class="hk-not">Bugün beklenen en yüksek değer: <strong><?php echo round($havaKalitesi['gunEnYuksek']); ?></strong> (<?php echo havaKalitesiDurumu($havaKalitesi['gunEnYuksek'])['ad']; ?>)</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Ölçek: 0–20 iyi … 100+ son derece kötü; işaret şu anki değeri gösterir -->
                    <div class="hk-olcek">
                        <div class="hk-cubuk"><span class="hk-isaret" style="left: <?php echo min($aqi, 100); ?>%;"></span></div>
                        <div class="hk-etiketler"><span>İyi</span><span>Makul</span><span>Orta</span><span>Kötü</span><span>Çok kötü</span></div>
                    </div>

                    <ul class="hk-kirleticiler">
                        <?php foreach ($kirleticiler as $k): ?>
                            <li>
                                <strong><?php echo $k[1]; ?></strong>
                                <span><?php echo isset($hk[$k[0]]) ? round($hk[$k[0]], 1) : '—'; ?> <small>µg/m³</small></span>
                                <em><?php echo $k[2]; ?></em>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <p class="hk-kaynak">Kaynak: Open-Meteo Hava Kalitesi (Copernicus CAMS) · anlık değerler</p>
                </div>
            <?php endif; ?>
        </section>

        <!-- ================= BUGÜN İÇİN TAVSİYELER ================= -->
        <section class="tavsiyeler">
            <h2>Bugün için tavsiyeler</h2>
            <div class="tavsiye-liste">
                <?php foreach (tavsiyeler($gunluk, 0, yagisAraliklari($bugunSaatleri, 40)) as $t): ?>
                    <div class="tavsiye tavsiye-<?php echo $t['tur']; ?>">
                        <?php echo tavsiyeIkonu($t); ?>
                        <p><?php echo e($t['metin']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- ================= 7 GÜNLÜK TAHMİN ================= -->
        <section class="haftalik">
            <h2>7 Günlük Tahmin</h2>
            <p class="ipucu">Saat saat ayrıntı için bir güne tıklayın.</p>

            <div class="hafta-liste">
                <?php foreach ($gunluk['time'] as $i => $tarih): ?>
                    <?php
                        $kod   = $gunluk['weather_code'][$i];
                        $enDus = $gunluk['temperature_2m_min'][$i];
                        $enYuk = $gunluk['temperature_2m_max'][$i];
                        $yagis = $gunluk['precipitation_probability_max'][$i] ?? 0;

                        // Sıcaklık çubuğunun konumu (yüzde olarak)
                        $sol      = ($enDus - $haftaMin) / $aralik * 100;
                        $genislik = ($enYuk - $enDus) / $aralik * 100;
                    ?>
                    <a href="<?php echo $adres; ?>&gun=<?php echo $i; ?>#gun-detay"
                       class="hafta-gun <?php echo ($i == $secilenGun) ? 'secili' : ''; ?>">
                        <div class="hg-tarih">
                            <strong><?php echo gunEtiketi($tarih, $i); ?></strong>
                            <span><?php echo kisaTarih($tarih); ?></span>
                        </div>

                        <div class="hg-durum">
                            <?php echo havaSimgesi($kod); ?>
                            <span><?php echo havaMetni($kod); ?></span>
                        </div>

                        <div class="hg-yagis <?php echo ($yagis >= 50) ? 'cok' : ''; ?>">
                            ☂ %<?php echo $yagis; ?>
                            <small><?php echo $gunluk['precipitation_sum'][$i]; ?> mm</small>
                        </div>

                        <div class="hg-ruzgar">
                            <?php echo round($gunluk['wind_speed_10m_max'][$i]); ?> km/s
                            <small>rüzgâr</small>
                        </div>

                        <div class="hg-sicaklik">
                            <span class="min"><?php echo round($enDus); ?>°</span>
                            <span class="cubuk">
                                <span style="left: <?php echo round($sol); ?>%; width: <?php echo max(round($genislik), 4); ?>%;"></span>
                            </span>
                            <span class="max"><?php echo round($enYuk); ?>°</span>
                        </div>

                        <span class="hg-ok" aria-hidden="true">›</span>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- ================= SEÇİLEN GÜNÜN SAATLİK DETAYI ================= -->
        <section id="gun-detay" class="gun-detay">
            <h2>
                <?php echo ($secilenGun == 0) ? 'Bugün' : gunEtiketi($secilenTarih, $secilenGun); ?>
                <span><?php echo uzunTarih($secilenTarih); ?></span>
            </h2>

            <!-- Yağış saat aralıkları -->
            <?php if (count($araliklar) == 0): ?>
                <p class="mesaj bilgi">Bu gün belirgin bir yağış beklenmiyor.</p>
            <?php else: ?>
                <div class="mesaj yagis-uyari">
                    <strong>☂ Yağış beklenen saatler:</strong>
                    <?php foreach ($araliklar as $a): ?>
                        <?php $gecti = ($secilenGun == 0 && $a['bitSaat'] <= $suAnkiSaat); ?>
                        <span class="aralik <?php echo $gecti ? 'gecti' : ''; ?>">
                            <?php echo $a['bas']; ?>–<?php echo ($a['bitSaat'] == 24) ? '24:00' : $a['bit']; ?>
                            <em>%<?php echo $a['enYuksek']; ?></em>
                            <?php echo $gecti ? '<small>geçti</small>' : ''; ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- O günün tavsiyeleri (bugün zaten yukarıda gösterildi) -->
            <?php if ($secilenGun > 0): ?>
                <div class="tavsiye-liste kucuk">
                    <?php foreach (tavsiyeler($gunluk, $secilenGun, $araliklar) as $t): ?>
                        <div class="tavsiye tavsiye-<?php echo $t['tur']; ?>">
                            <?php echo tavsiyeIkonu($t); ?>
                            <p><?php echo e($t['metin']); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Saat saat şerit -->
            <?php if (count($saatler) == 0): ?>
                <p class="mesaj bilgi">Bu gün için saatlik veri yok.</p>
            <?php else: ?>
                <p class="ipucu">Günün 24 saati · yağış ihtimali %40 ve üzeri olan saatler vurgulanır.</p>
                <div class="saatlik">
                    <?php foreach ($saatler as $s): ?>
                        <?php
                            // Bugün için: geçmiş saat mi, şu anki saat mi?
                            $saatSayi = (int) substr($s['saat'], 0, 2);
                            $durum = '';
                            if ($secilenGun == 0 && $saatSayi < $suAnkiSaat) {
                                $durum = 'gecmis';
                            } elseif ($secilenGun == 0 && $saatSayi == $suAnkiSaat) {
                                $durum = 'simdi';
                            }
                        ?>
                        <div class="saat <?php echo ($s['olasilik'] >= 40) ? 'yagisli' : ''; ?> <?php echo $durum; ?>">
                            <span class="saat-zaman"><?php echo ($durum == 'simdi') ? 'Şimdi' : $s['saat']; ?></span>
                            <span title="<?php echo havaMetni($s['kod']); ?>"><?php echo havaSimgesi($s['kod'], $s['gunduz']); ?></span>
                            <span class="saat-derece"><?php echo round($s['sicaklik']); ?>°</span>
                            <span class="saat-yagis">
                                <span class="saat-cubuk"><span style="height: <?php echo $s['olasilik']; ?>%;"></span></span>
                                %<?php echo $s['olasilik']; ?>
                            </span>
                            <?php if ($s['miktar'] > 0): ?>
                                <span class="saat-mm"><?php echo $s['miktar']; ?> mm</span>
                            <?php endif; ?>
                            <span class="saat-ruzgar"><?php echo round($s['ruzgar']); ?> km/s</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    <?php endif; ?>

    <!-- Aynı bölgedeki diğer iller -->
    <section class="diger-iller">
        <h3><?php echo e($bulunan['bolgeAd']); ?> Bölgesindeki diğer iller</h3>
        <div>
            <?php foreach ($bolgeler[$bulunan['bolgeKodu']]['iller'] as $kod => $digerIl): ?>
                <?php if ($kod != $ilKodu): ?>
                    <a href="il.php?il=<?php echo $kod; ?>"><?php echo e($digerIl['ad']); ?></a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

</main>

<?php require 'footer.php'; ?>
