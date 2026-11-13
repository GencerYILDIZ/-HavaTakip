<?php
/*
 * header.php — Tüm sayfalarda ortak olan üst kısım (head + navbar).
 * Bu dosyayı dahil etmeden önce şu değişkenler tanımlanmalı:
 *   $sayfaBasligi : tarayıcı sekmesindeki başlık
 *   $aktifMenu    : 'anasayfa', 'bolgeler', 'takip', 'yonetim', 'hesabim', 'giris' veya 'kayit' (menüde vurgulanır)
 */
$uygulamaAdi = 'HavaTakip';

// Tema çerezden okunur (tema.php ayarlar). Geçersiz bir değer gelirse açık tema kullanılır.
$tema = (($_COOKIE['tema'] ?? '') == 'koyu') ? 'koyu' : 'acik';
?>
<!DOCTYPE html>
<html lang="tr" data-tema="<?php echo $tema; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($sayfaBasligi); ?> — <?php echo $uygulamaAdi; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
    <!-- ?v=… : style.css her değiştiğinde adres de değişir; tarayıcı eski (önbellekteki) stili kullanmaz -->
    <link rel="stylesheet" href="assets/style.css?v=<?php echo filemtime(__DIR__ . '/assets/style.css'); ?>">
</head>
<body data-mevsim="<?php echo $mevsim; ?>">

<!-- ======================= NAVBAR ======================= -->
<header class="navbar">
    <?php
        // Navbar'ın iki yanındaki tarih ve saat kartları (footer.php her saniye günceller).
        // Değişken adları $nav… ile başlar: sayfaların kendi değişkenleriyle (ör. il.php'deki $simdi) çakışmasın.
        $navZaman      = time();
        $navAyKisa = (aktifDil() == 'en')
            ? ['', 'JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC']
            : ['', 'OCA', 'ŞUB', 'MAR', 'NİS', 'MAY', 'HAZ', 'TEM', 'AĞU', 'EYL', 'EKİ', 'KAS', 'ARA'];
        $navAylar      = ['', 'Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'];
        $navGunler  = ['Pazar', 'Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi'];
        $navAkrepAci    = (date('G', $navZaman) % 12) * 30 + date('i', $navZaman) * 0.5;   // akrep
        $navYelkovanAci  = date('i', $navZaman) * 6 + date('s', $navZaman) * 0.1;           // yelkovan
        $navSaniyeAci  = date('s', $navZaman) * 6;                                      // saniye ibresi
    ?>
    <!-- Solda: takvim yaprağı görünümünde bugünün tarihi -->
    <div class="navbar-tarih" title="Bugün">
        <span class="nt-takvim">
            <span class="nt-ay" id="navAy"><?php echo $navAyKisa[date('n', $navZaman)]; ?></span>
            <span class="nt-gun" id="navGun"><?php echo date('j', $navZaman); ?></span>
        </span>
        <span class="nt-metin">
            <strong id="navHaftaGunu"><?php echo $navGunler[date('w', $navZaman)]; ?></strong>
            <small id="navAyYil"><?php echo $navAylar[date('n', $navZaman)] . ' ' . date('Y', $navZaman); ?></small>
        </span>
    </div>
    <div class="navbar-ic">
        <a href="index.php" class="logo">
            <!-- Amblem: güneş ortada, dünya güneşin etrafında yörüngede dolaşır ve kendi ekseni
                 etrafında döner (kıtalar küre içinde kayar). Dünya yörüngenin arka yarısındayken
                 güneşin arkasında, ön yarısındayken önünde görünür. Yörünge rengi mevsim rengini alır.
                 Animasyonlar SVG'nin kendi (SMIL) animasyonlarıyla yapılır; JavaScript yoktur. -->
            <svg viewBox="0 0 48 48" class="logo-isaret" aria-hidden="true">
                <defs>
                    <linearGradient id="amblemZemin" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#1d4b7a"/><stop offset="1" stop-color="#0b1f3a"/></linearGradient>
                    <radialGradient id="amblemGunes" cx=".4" cy=".35" r=".7"><stop offset="0" stop-color="#fff9db"/><stop offset=".45" stop-color="#ffd43b"/><stop offset="1" stop-color="#f08c00"/></radialGradient>
                    <radialGradient id="amblemHale" r=".5"><stop offset=".45" stop-color="#ffd43b" stop-opacity=".55"/><stop offset="1" stop-color="#ffd43b" stop-opacity="0"/></radialGradient>
                    <radialGradient id="amblemOkyanus" cx=".35" cy=".3" r=".8"><stop offset="0" stop-color="#74c0fc"/><stop offset=".6" stop-color="#2b7bd8"/><stop offset="1" stop-color="#174a8c"/></radialGradient>
                    <linearGradient id="amblemKara" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#8ce99a"/><stop offset="1" stop-color="#2f9e44"/></linearGradient>
                    <radialGradient id="amblemGolge" cx=".3" cy=".4" r=".8"><stop offset=".5" stop-color="#000" stop-opacity="0"/><stop offset="1" stop-color="#03142b" stop-opacity=".6"/></radialGradient>
                    <clipPath id="amblemKure"><circle cx="24" cy="24" r="15"/></clipPath>
                    <!-- Dünya: 0,0 merkezli, yarıçapı 5,5 (çizim r=15'lik küreden küçültüldü) -->
                    <g id="amblemDunya">
                        <g transform="scale(.3667) translate(-24 -24)">
                            <circle cx="24" cy="24" r="15" fill="url(#amblemOkyanus)"/>
                            <g clip-path="url(#amblemKure)">
                                <!-- Kendi ekseni etrafında dönüş: kıtalar 6 sn'de bir tur kayar -->
                                <g fill="url(#amblemKara)">
                                    <animateTransform attributeName="transform" type="translate" from="0 0" to="-48 0" dur="6s" repeatCount="indefinite"/>
                                    <g><path d="M4 12C8 10 12 12 11 16S13 21 12 24 14 30 13 34 10 38 9 35 7 27 5 24 2 15 4 12Z"/><path d="M20 11C24 9 28 11 27 14S29 18 28 21 30 28 28 33 23 36 23 32 20 25 21 21 19 15 20 11Z"/><path d="M30 10C36 8 44 10 45 14S41 19 40 22 35 24 34 21 30 16 30 10Z"/><path d="M38 29C41 27 45 28 45 31S41 35 39 33 37 30 38 29Z"/></g>
                                    <g transform="translate(48 0)"><path d="M4 12C8 10 12 12 11 16S13 21 12 24 14 30 13 34 10 38 9 35 7 27 5 24 2 15 4 12Z"/><path d="M20 11C24 9 28 11 27 14S29 18 28 21 30 28 28 33 23 36 23 32 20 25 21 21 19 15 20 11Z"/><path d="M30 10C36 8 44 10 45 14S41 19 40 22 35 24 34 21 30 16 30 10Z"/><path d="M38 29C41 27 45 28 45 31S41 35 39 33 37 30 38 29Z"/></g>
                                </g>
                                <circle cx="24" cy="24" r="15" fill="url(#amblemGolge)"/>
                            </g>
                            <circle cx="24" cy="24" r="15" fill="none" stroke="#a5d8ff" stroke-opacity=".6" stroke-width="1.4"/>
                        </g>
                    </g>
                    <!-- Yörünge yolu: soldan başlar, önce ön (alt) yarı, sonra arka (üst) yarı -->
                    <path id="amblemYorunge" d="M6.5 24A17.5 6.5 0 0 0 41.5 24A17.5 6.5 0 0 0 6.5 24"/>
                </defs>
                <rect width="48" height="48" rx="13" fill="url(#amblemZemin)"/>
                <rect x="1" y="1" width="46" height="46" rx="12" fill="none" stroke="#ffffff" stroke-opacity=".12"/>

                <!-- Yörüngenin arka yarısı ve arkadaki dünya (güneşin arkasında kalır) -->
                <path d="M6.5 24A17.5 6.5 0 0 1 41.5 24" fill="none" class="logo-yorunge" stroke-width="1" stroke-opacity=".4"/>
                <use href="#amblemDunya" opacity="0">
                    <animateMotion dur="10s" repeatCount="indefinite"><mpath href="#amblemYorunge"/></animateMotion>
                    <animate attributeName="opacity" values="0;1" keyTimes="0;.5" calcMode="discrete" dur="10s" repeatCount="indefinite"/>
                </use>

                <!-- Güneş: ortada, hafif parlayan hâle ve dönen ışınlar -->
                <circle cx="24" cy="24" r="12" fill="url(#amblemHale)" class="logo-hale"/>
                <g class="logo-isinlar" stroke="#ffd43b" stroke-width="1.6" stroke-linecap="round">
                    <path d="M24 12.5v2.5M24 33v2.5M12.5 24h2.5M33 24h2.5M15.9 15.9l1.8 1.8M30.3 30.3l1.8 1.8M15.9 32.1l1.8-1.8M30.3 17.7l1.8-1.8"/>
                </g>
                <circle cx="24" cy="24" r="7.2" fill="url(#amblemGunes)"/>

                <!-- Yörüngenin ön yarısı ve öndeki dünya (güneşin önünden geçer) -->
                <path d="M6.5 24A17.5 6.5 0 0 0 41.5 24" fill="none" class="logo-yorunge" stroke-width="1.1"/>
                <use href="#amblemDunya">
                    <animateMotion dur="10s" repeatCount="indefinite"><mpath href="#amblemYorunge"/></animateMotion>
                    <animate attributeName="opacity" values="1;0" keyTimes="0;.5" calcMode="discrete" dur="10s" repeatCount="indefinite"/>
                </use>
            </svg>
            <span class="logo-yazi">
                <strong><span class="logo-hava">Hava</span><span class="logo-takip">Takip</span></strong>
                <small>Kişisel Hava Durumu Takip Sistemi</small>
            </span>
        </a>

        <!-- Küçük ekranlarda menü: ☰ düğmesi (label) bu kutuyu işaretler, CSS menüyü açar (JavaScript gerekmez) -->
        <input type="checkbox" id="menuAc" class="menu-ac-kutu" hidden>

        <!-- Menü: bağlantılar açık renkli bir kutunun içinde durur. Seçili sayfanın bağlantısı koyu, dolu bir kutuyla
             işaretlidir; fare hangi bağlantının üzerine gelirse bu koyu kutu (menu-gosterge) kayarak oraya gider,
             fare çıkınca seçili sayfaya döner (footer.php'deki küçük JavaScript). -->
        <nav class="menu">
            <span class="menu-gosterge" aria-hidden="true"></span>
            <a href="index.php" class="<?php echo ($aktifMenu == 'anasayfa') ? 'aktif' : ''; ?>">Ana Sayfa</a>
            <a href="index.php?menu=bolgeler#bolgeler" class="<?php echo ($aktifMenu == 'bolgeler') ? 'aktif' : ''; ?>">Bölgeler</a>
            <?php if (girisYapildiMi()): ?>
                <!-- Takip listesi yalnızca giriş yapan kullanıcıya görünür -->
                <a href="takip.php" class="<?php echo ($aktifMenu == 'takip') ? 'aktif' : ''; ?>">
                    Takip Listem
                    <span class="sayac"><?php echo count(kullaniciListesi(aktifKullanici()['kullaniciAdi'])); ?></span>
                </a>
                <?php if (adminMi()): ?>
                    <a href="yonetim.php" class="<?php echo ($aktifMenu == 'yonetim') ? 'aktif' : ''; ?>">Yönetim</a>
                <?php endif; ?>
            <?php endif; ?>
        </nav>

        <!-- Bildirim zili ayrı bir kutuda: telefonda menü kapalıyken de görünür -->
        <div class="menu navbar-zil">
            <?php if (girisYapildiMi()): ?>

                <!-- Bildirim zili: takip listesindeki uyarı tercihlerine göre (yağış eşiği, don, rüzgâr, sıcak).
                     Yeni bildirim gelince panel açılmaz: ses çalar, zil sallanır ve köşede kırmızı sayı görünür (99+);
                     kullanıcı zile tıklayıp paneli kendisi açar. Açılıp kapanma <details>
                     etiketiyle, × ve "Tümünü temizle" POST formlarıyla yapılır. Küçük bir JavaScript (footer.php)
                     sayfada boş bir yere tıklanınca paneli kapatır ve panel açılınca bildirimleri okundu yapar. -->
                <?php
                    $zilKullanici = aktifKullanici()['kullaniciAdi'];   // her kullanıcının bildirimleri ayrıdır
                    bildirimleriGuncelle($_SESSION['bildirimMerkezi'][$zilKullanici], kullaniciListesi($zilKullanici), $bolgeler, $ilceler);
                    $zil = bildirimPaneli($_SESSION['bildirimMerkezi'][$zilKullanici]);
                    // İşlemden sonra aynı sayfaya dönmek için bu sayfanın adresi (ör. il.php?il=ankara)
                    $buSayfa = basename($_SERVER['SCRIPT_NAME']) . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '');
                ?>
                <details class="bildirim-menu"<?php echo $zil['acik'] ? ' open' : ''; ?> data-okunmamis="<?php echo $zil['okunmamis']; ?>">
                    <summary class="bildirim-zil<?php echo ($zil['yeniSayi'] > 0) ? ' yeni' : ''; ?>"
                             title="Bildirimler" aria-label="Bildirimler (<?php echo $zil['okunmamis']; ?> okunmamış)">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                        <?php if ($zil['okunmamis'] > 0): ?>
                            <!-- Yalnızca okunmamış bildirimler sayılır (99'dan fazlaysa "99+"); panel açılınca kaybolur -->
                            <span class="zil-sayi"><?php echo $zil['rozet']; ?></span>
                        <?php endif; ?>
                    </summary>

                    <!-- Panel açılınca JavaScript bu formu arka planda gönderir (bildirimler okundu olur) -->
                    <form method="post" action="bildirim.php" class="bildirim-okundu-form" hidden>
                        <?php echo csrfAlani(); ?>
                        <input type="hidden" name="islem" value="okundu">
                    </form>

                    <div class="bildirim-panel">
                        <div class="bildirim-ust">
                            <strong>Bildirimler</strong>
                            <?php if ($zil['sayi'] > 0): ?>
                                <form method="post" action="bildirim.php">
                                    <?php echo csrfAlani(); ?>
                                    <input type="hidden" name="islem" value="temizle">
                                    <input type="hidden" name="donus" value="<?php echo e($buSayfa); ?>">
                                    <button type="submit" class="bildirim-temizle">Tümünü temizle</button>
                                </form>
                            <?php endif; ?>
                        </div>

                        <?php if ($zil['sayi'] == 0): ?>
                            <p class="bildirim-bos">Şu an bildiriminiz yok. Takip listenizdeki aktif kayıtlarda seçtiğiniz uyarılar bugün veya yarın gerçekleşecekse burada görünür.</p>
                        <?php else: ?>
                            <ul class="bildirim-liste">
                                <?php foreach ($zil['liste'] as $b): ?>
                                    <li class="bildirim bildirim-<?php echo $b['tur']; ?><?php echo empty($b['okundu']) ? ' yeni' : ''; ?>">
                                        <!-- Uyarının renkli, animasyonlu simgesi (şemsiye, atkı-bere, rüzgâr, sıcak) -->
                                        <span class="bildirim-ikon"><?php echo uyariIkonu($b['tur'], 'bildirim-gorsel'); ?></span>
                                        <a class="bildirim-metin" href="<?php echo e($b['adres']); ?>">
                                            <strong><?php echo e($b['baslik']); ?> · <?php echo e($b['ilAd']); ?></strong>
                                            <span><em class="bildirim-gun"><?php echo $b['gunEtiket']; ?></em> <?php echo e($b['metin']); ?></span>
                                        </a>
                                        <form method="post" action="bildirim.php">
                                            <?php echo csrfAlani(); ?>
                                            <input type="hidden" name="islem" value="sil">
                                            <input type="hidden" name="id" value="<?php echo e($b['id']); ?>">
                                            <input type="hidden" name="donus" value="<?php echo e($buSayfa); ?>">
                                            <button type="submit" class="bildirim-kapat" title="Bu bildirimi kaldır" aria-label="Bu bildirimi kaldır">×</button>
                                        </form>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>

                        <p class="bildirim-not">Bildirimler, <a href="takip.php">Takip Listem</a>'deki aktif kayıtlarda seçtiğiniz uyarılara göre oluşur.</p>
                    </div>
                </details>
                <?php if ($zil['ses']): ?>
                    <!-- Yeni bildirim sesi. Tarayıcılar, kullanıcı siteye hiç tıklamadan açılan ilk sayfada sesi engelleyebilir;
                         o durumda footer.php'deki JavaScript sesi kullanıcının ilk tıklamasında çalar. -->
                    <audio id="bildirimSesi" src="assets/bildirim.wav" autoplay></audio>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Hesap, tema ve dil düğmeleri -->
        <div class="menu navbar-arac">


            <?php if (girisYapildiMi()): ?>
                <!-- Kullanıcı bilgisi, hesap ayarları (dişli) ve çıkış (çıkış POST ile yapılır) -->
                <div class="kullanici-kutu">
                    <span class="hesap-bilgi">
                        <span class="avatar" aria-hidden="true"><?php echo e(mb_strtoupper(mb_substr(aktifKullanici()['adSoyad'], 0, 1))); ?></span>
                        <span class="kullanici-ad">
                            <?php echo e(aktifKullanici()['kullaniciAdi']); ?>
                            <small><?php echo adminMi() ? 'Yönetici' : 'Kullanıcı'; ?></small>
                        </span>
                    </span>
                    <!-- Dişli: "Hesabım" sayfası (bilgileri güncelleme, parola, hesap silme); admin ve kullanıcı için aynı -->
                    <a href="hesabim.php" class="hesap-ayar <?php echo ($aktifMenu == 'hesabim') ? 'aktif' : ''; ?>"
                       title="Hesap ayarları" aria-label="Hesap ayarları"><?php echo disliSimgesi(); ?></a>
                    <form action="cikis.php" method="post">
                        <?php echo csrfAlani(); ?>
                        <button type="submit" class="cikis-buton">Çıkış</button>
                    </form>
                </div>
            <?php else: ?>
                <!-- Giriş yapmamış ziyaretçi: Giriş Yap ve Kayıt Ol -->
                <div class="hesap-dugmeler">
                    <a href="giris.php" class="buton-giris <?php echo ($aktifMenu == 'giris') ? 'aktif' : ''; ?>">Giriş Yap</a>
                    <a href="kayit.php" class="buton <?php echo ($aktifMenu == 'kayit') ? 'aktif' : ''; ?>">Kayıt Ol</a>
                </div>
            <?php endif; ?>

            <!-- Tema düğmesi (navbar'ın en sağında): koyu temadayken güneş, açık temadayken ay simgesi -->
            <a href="tema.php" class="tema-buton"
               title="<?php echo ($tema == 'koyu') ? 'Açık temaya geç' : 'Koyu temaya geç'; ?>"
               aria-label="<?php echo ($tema == 'koyu') ? 'Açık temaya geç' : 'Koyu temaya geç'; ?>">
                <?php if ($tema == 'koyu'): ?>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M2 12h2M20 12h2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
                <?php else: ?>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/></svg>
                <?php endif; ?>
            </a>
            <!-- Dil seçimi (navbar'ın en sağında): TR / EN -->
            <div class="dil-secici" role="group" aria-label="Dil">
                <?php foreach (DILLER as $kod => $ad): ?>
                    <a href="<?php echo dilAdresi($kod); ?>" class="<?php echo ($kod == $dil) ? 'secili' : ''; ?>" title="<?php echo $ad; ?>" lang="<?php echo $kod; ?>"><?php echo strtoupper($kod); ?></a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- ☰ Menü düğmesi (yalnızca küçük ekranlarda görünür); açılınca X'e dönüşür -->
        <label for="menuAc" class="menu-ac" title="Menü" aria-label="Menüyü aç">
            <span></span><span></span><span></span>
        </label>
    </div>
    <!-- Sağda: analog saat kadranı ve dijital saat (Türkiye saati) -->
    <div class="navbar-saat" title="Türkiye saati">
        <svg class="ns-kadran" viewBox="0 0 40 40" aria-hidden="true">
            <circle class="ns-yuz" cx="20" cy="20" r="18"/>
            <g class="ns-cizgiler"><path d="M20 4v3M20 33v3M4 20h3M33 20h3"/></g>
            <line class="ns-akrep" id="ibreSaat" x1="20" y1="20" x2="20" y2="11" transform="rotate(<?php echo $navAkrepAci; ?> 20 20)"/>
            <line class="ns-yelkovan" id="ibreDakika" x1="20" y1="20" x2="20" y2="7.5" transform="rotate(<?php echo $navYelkovanAci; ?> 20 20)"/>
            <line class="ns-saniye" id="ibreSaniye" x1="20" y1="23" x2="20" y2="6" transform="rotate(<?php echo $navSaniyeAci; ?> 20 20)"/>
            <circle class="ns-orta" cx="20" cy="20" r="1.8"/>
        </svg>
        <span class="ns-metin">
            <strong translate="no"><span id="navSaat"><?php echo date('H', $navZaman); ?><span class="ns-nokta">:</span><?php echo date('i', $navZaman); ?></span><sup id="navSaniye"><?php echo date('s', $navZaman); ?></sup></strong>
            <small>Türkiye · GMT+3</small>
        </span>
    </div>
</header>

<!-- Mevsim atmosferi: ilkbaharda çiçek yaprakları, yazda ışık parıltıları, sonbaharda yapraklar, kışın kar taneleri.
     Yalnızca süs amaçlıdır; tıklamaları engellemez (pointer-events: none). -->
<div class="mevsim-atmosfer mevsim-<?php echo $mevsim; ?>" aria-hidden="true"><?php echo mevsimParcaciklari($mevsim, 14); ?></div>

<?php if (mevsimGirisiGoster($mevsim, !empty($acilisEfekti))): ?>
    <!-- Mevsim açılış efekti: siteye girişte (ve tema değişince) ekran boyunca kar / yaprak / çiçek / ışık yağar,
         birkaç saniye sonra kaybolur. İl sayfasında hava efekti oynuyorsa gösterilmez. -->
    <div class="mevsim-giris mevsim-<?php echo $mevsim; ?>" aria-hidden="true">
        <?php if ($mevsim == 'yaz'): ?><div class="mg-isinlar"></div><?php endif; ?>
        <div class="mg-baslik">
            <img src="assets/mevsim/<?php echo mevsimGorselleri($mevsim)[0]; ?>.svg" alt="">
            <span><?php echo MEVSIMLER[$mevsim]; ?></span>
        </div>
        <?php echo mevsimParcaciklari($mevsim, 46, 23); ?>
    </div>
<?php endif; ?>
