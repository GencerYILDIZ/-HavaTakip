<?php
/*
 * ikonlar.php — Sitedeki animasyonlu simgeler
 *
 * İki grup vardır; üstteki sekmelerle seçilir (sekmeler JavaScript'siz, gizli seçim düğmeleriyle çalışır):
 *   Hava simgeleri          : assets/ikonlar/  (16 simge + dolunay)  → havaSimgesi()
 *   Uyarı / tavsiye simgeleri: assets/tavsiye/ (11 simge)            → tavsiyeIkonu(), uyariIkonu()
 * ikonlar.php?sekme=uyari → sayfa uyarı simgeleriyle açılır.
 *
 * Simgeler SVG dosyalarıdır. Animasyonları dosyanın
 * içindeki CSS ile çalışır; sayfada <img src="assets/ikonlar/gunesli.svg"> ile kullanılır.
 * havaSimgesi($kod, $gunduz) fonksiyonu hava koduna göre doğru dosyayı seçer.
 */

require_once 'baslat.php';

// [dosya adı, Türkçe ad, nerede kullanılır]
$simgeler = [
    ['gunesli',         'Güneşli',            'Açık hava, gündüz (kod 0)'],
    ['parcali-bulutlu', 'Parçalı bulutlu',    'Çoğunlukla açık, gündüz (kod 1)'],
    ['bulutlu',         'Bulutlu',            'Parçalı bulutlu, gündüz (kod 2)'],
    ['kapali',          'Kapalı',             'Kapalı hava (kod 3)'],
    ['hafif-yagmur',    'Hafif yağmur',       'Çisenti ve hafif yağmur (51–61, 80)'],
    ['saganak',         'Sağanak',            'Şiddetli yağmur ve sağanak (63–67, 81–82)'],
    ['firtina',         'Gök gürültülü',      'Gök gürültülü fırtına (95–99)'],
    ['kar',             'Kar',                'Hafif ve orta kar (71–73, 77, 85)'],
    ['yogun-kar',       'Yoğun kar',          'Yoğun kar (75, 86)'],
    ['sis',             'Sis',                'Sis ve kırağı (45, 48)'],
    ['ruzgarli',        'Rüzgârlı',           'Kuvvetli rüzgâr uyarıları'],
    ['gun-dogumu',      'Gün doğumu',         'Gün doğumu saati'],
    ['gun-batimi',      'Gün batımı',         'Gün batımı saati'],
    ['acik-gece',       'Açık gece',          'Açık hava, gece (kod 0)'],
    ['bulutlu-gece',    'Bulutlu gece',       'Parçalı bulutlu, gece (kod 2)'],
    ['ay-yildiz',       'Ay ve yıldızlar',    'Çoğunlukla açık, gece (kod 1)'],
    ['dolunay',         'Dolunay',            'Gece sayfa efekti'],
];

// Uyarı ve tavsiye simgeleri: [dosya adı, Türkçe ad, ne zaman gösterilir, bildirimde de kullanılır mı]
$uyariSimgeleri = [
    ['semsiye',   'Şemsiye',          'Yağış ihtimali yüksekse ("Şemsiyenizi alın")',                 true],
    ['yagmurluk', 'Yağmurluk',        'Hava ılık ama yağmurluysa ya da soğuk ve yağmurluysa',          false],
    ['mont',      'Mont',             'Hava soğuksa (en yüksek 10° altı)',                            false],
    ['kislik',    'Atkı ve bere',     'Çok soğuk ya da don riski varsa',                              true],
    ['hirka',     'Hırka',            'Gündüz ılık, sabah ve akşam serinse',                          false],
    ['bot',       'Bot',              'Kar yağışı bekleniyorsa',                                      false],
    ['firtina',   'Fırtına',          'Gök gürültülü sağanak bekleniyorsa',                           false],
    ['ruzgar',    'Rüzgâr',           'Kuvvetli rüzgâr bekleniyorsa (40 km/s ve üzeri)',              true],
    ['sicak',     'Aşırı sıcak',      'Çok sıcaksa (32° ve üzeri)',                                   true],
    ['gozluk',    'Güneş gözlüğü',    'UV indeksi yüksekse',                                         false],
    ['iyi',       'Güzel hava',       'Hiçbir uyarı yoksa',                                           false],
];
$uyariSekmesi = (($_GET['sekme'] ?? '') == 'uyari');

$sayfaBasligi = 'Hava Simgeleri';
$aktifMenu    = '';
require 'header.php';
?>

<main>
    <p class="yol"><a href="index.php">Ana Sayfa</a> › Hava simgeleri</p>
    <h1 class="sayfa-baslik">Hava ve uyarı simgeleri</h1>
    <p class="sayfa-aciklama">Sitede kullanılan tüm animasyonlu simgeler. Gece saatlerinde güneş yerine ay ve yıldızlı simgeler, uyarılarda ise havaya uygun giysi ve eşya simgeleri kullanılır.</p>

    <!-- Sekmeler: gizli seçim düğmeleri (radio) + etiketler; CSS seçili gruba göre galerileri gösterir -->
    <div class="simge-sekmeler">
        <input type="radio" name="simgeSekme" id="sekmeTumu" class="sekme-kutu" >
        <input type="radio" name="simgeSekme" id="sekmeHava" class="sekme-kutu" <?php echo $uyariSekmesi ? '' : 'checked'; ?>>
        <input type="radio" name="simgeSekme" id="sekmeUyari" class="sekme-kutu" <?php echo $uyariSekmesi ? 'checked' : ''; ?>>

        <div class="sekme-dugmeler" role="tablist" aria-label="Simge grupları">
            <label for="sekmeTumu">Tümü <span><?php echo count($simgeler) + count($uyariSimgeleri); ?></span></label>
            <label for="sekmeHava">Hava simgeleri <span><?php echo count($simgeler); ?></span></label>
            <label for="sekmeUyari">Uyarı ve tavsiye simgeleri <span><?php echo count($uyariSimgeleri); ?></span></label>
        </div>

        <section class="simge-grup grup-hava">
            <h2>Hava simgeleri <span><?php echo count($simgeler); ?> animasyon</span></h2>
            <div class="simge-galeri">
                <?php foreach ($simgeler as $i => $s): ?>
                    <figure class="simge-kart" style="--sira: <?php echo $i; ?>">
                        <img src="assets/ikonlar/<?php echo $s[0]; ?>.svg" alt="<?php echo e($s[1]); ?>">
                        <figcaption>
                            <strong><?php echo ($i + 1) . '. ' . e($s[1]); ?></strong>
                            <span><?php echo e($s[2]); ?></span>
                        </figcaption>
                    </figure>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="simge-grup grup-uyari">
            <h2>Uyarı ve tavsiye simgeleri <span><?php echo count($uyariSimgeleri); ?> animasyon</span></h2>
            <p class="grup-aciklama">İl sayfasındaki "Bugün için tavsiyeler" kutularında, Takip Listem kartlarındaki uyarılarda ve bildirim zilinde kullanılır.</p>
            <div class="simge-galeri">
                <?php foreach ($uyariSimgeleri as $i => $u): ?>
                    <figure class="simge-kart uyari-kart" style="--sira: <?php echo $i; ?>">
                        <?php if ($u[3]): ?><em class="bildirim-rozeti">Bildirimde</em><?php endif; ?>
                        <img src="assets/tavsiye/<?php echo $u[0]; ?>.svg" alt="<?php echo e($u[1]); ?>">
                        <figcaption>
                            <strong><?php echo ($i + 1) . '. ' . e($u[1]); ?></strong>
                            <span><?php echo e($u[2]); ?></span>
                        </figcaption>
                    </figure>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
</main>

<?php require 'footer.php'; ?>
