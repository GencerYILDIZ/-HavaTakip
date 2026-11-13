<?php
/*
 * index.php — Ana sayfa
 *
 * Adres çubuğundaki GET parametrelerine göre çalışır:
 *   index.php                             → Arama + bölge kartları
 *   index.php?bolge=karadeniz             → + bölgenin illeri ve 3 günlük tahminleri
 *   index.php?bolge=karadeniz&il=trabzon  → + Trabzon vurgulanır
 *   index.php?ara=trab                    → arama (il, ilçe veya "il: ilçe", ör. "İstanbul: Beşiktaş")
 */

require_once 'baslat.php';   // oturum, veri ve fonksiyonlar

/* ---------- 1) Adres çubuğundan gelen veriler ---------- */
// ?? : parametre yoksa boş metin kullan
$bolgeKodu = $_GET['bolge'] ?? '';
$ilKodu    = $_GET['il'] ?? '';
$aranan    = trim($_GET['ara'] ?? '');

$hata           = '';
$aramaSonuclari = [];

/* ---------- 2) Arama: il, ilçe veya "il: ilçe" ---------- */
const EN_FAZLA_SONUC = 40;

if ($aranan !== '') {
    if (mb_strlen($aranan) < 2) {
        $hata = 'Lütfen aramak için en az 2 harf yazın.';
    } else {
        $aramaSonuclari = genelAra($bolgeler, $ilceler, $aranan);

        // Birebir eşleşenler (ör. "Rize" veya "İstanbul: Beşiktaş")
        $tamEslesenler = [];
        foreach ($aramaSonuclari as $sonuc) {
            if ($sonuc['tam']) {
                $tamEslesenler[] = $sonuc;
            }
        }
        // Birebir eşleşme varsa yalnızca onları göster.
        // (Tek ise doğrudan gidilir; "Ereğli" gibi iki ilde aynı adlı ilçe varsa ikisi listelenir.)
        if (count($tamEslesenler) > 0) {
            $aramaSonuclari = $tamEslesenler;
        }

        if (count($aramaSonuclari) == 0) {
            $hata = '"' . $aranan . '" için il veya ilçe bulunamadı. Örnek: "Tokat" ya da "İstanbul: Beşiktaş".';
        } elseif (count($aramaSonuclari) == 1) {
            // Tek sonuç: doğrudan il veya ilçe sayfasına git
            header('Location: ' . aramaAdresi($aramaSonuclari[0]));
            exit;
        }
    }
}

/* ---------- 3) Gelen bölge geçerli mi? ---------- */
$secilenBolge = null;
$havalar      = null;

if ($bolgeKodu !== '') {
    if (isset($bolgeler[$bolgeKodu])) {
        $secilenBolge = $bolgeler[$bolgeKodu];
        // Bölgedeki tüm illerin hava durumu tek istekle
        $havalar = bolgeHavaGetir($secilenBolge['iller']);
    } else {
        $hata = 'Geçersiz bölge seçildi.';
    }
}

/* ---------- 4) Giriş yapılmışsa: takip ettiği şehirlerin özeti ----------
 * Giriş yapılmamışsa bu bölüm hiç oluşturulmaz (takip listesi gizli kalır).
 */
$benimTakiplerim = [];
$takipHavalari   = null;
if (girisYapildiMi()) {
    $benimTakiplerim = kullaniciListesi(aktifKullanici()['kullaniciAdi']);
    krsort($benimTakiplerim);
    $benimTakiplerim = array_slice($benimTakiplerim, 0, 6, true);   // en fazla 6 kayıt

    if (count($benimTakiplerim) > 0) {
        $konumlar = [];
        foreach ($benimTakiplerim as $id => $kayit) {
            $konumlar[$id] = takipKonumu($kayit, $bolgeler, $ilceler);
        }
        $takipHavalari = bolgeHavaGetir($konumlar);
    }
}
/* ---------- 5) Üst bölümün iki yanındaki şehir kartları: solda İstanbul, sağda Ankara ----------
 * İki şehrin anlık havası tek istekte alınır. Servis cevap vermezse kartlar gösterilmez.
 */
$vitrinIller = [];
foreach (['istanbul', 'ankara'] as $vitrinKod) {
    $vitrinBilgi = ilBul($bolgeler, $vitrinKod);
    if ($vitrinBilgi !== null) {
        $vitrinIller[$vitrinKod] = $vitrinBilgi['il'];
    }
}
$vitrinHava = count($vitrinIller) > 0 ? bolgeHavaGetir($vitrinIller) : null;

$bildirim = mesajAl();

$sayfaBasligi = 'Ana Sayfa';
$aktifMenu    = ($secilenBolge === null && ($_GET['menu'] ?? '') != 'bolgeler') ? 'anasayfa' : 'bolgeler';
require 'header.php';
?>

<!-- ======================= ARAMA (ortada) ======================= -->
<section class="kahraman">
    <?php if ($vitrinHava !== null): ?>
        <!-- Solda İstanbul, sağda Ankara: anlık hava (tıklanınca il sayfası açılır) -->
        <?php $vitrinYon = ['istanbul' => 'sol', 'ankara' => 'sag']; ?>
        <?php foreach ($vitrinIller as $vitrinKod => $vitrinIl): ?>
            <?php $vh = $vitrinHava[$vitrinKod] ?? null; ?>
            <?php if ($vh !== null && isset($vh['current'])): ?>
                <a href="il.php?il=<?php echo $vitrinKod; ?>" class="sehir-kart <?php echo $vitrinYon[$vitrinKod] ?? 'sol'; ?>">
                    <span class="sk-ust">
                        <span class="sk-ad" translate="no"><?php echo e($vitrinIl['ad']); ?></span>
                        <span class="sk-canli">Şu an</span>
                    </span>
                    <span class="sk-orta">
                        <?php echo havaSimgesi($vh['current']['weather_code'], $vh['current']['is_day'] ?? 1); ?>
                        <strong><?php echo round($vh['current']['temperature_2m']); ?>°</strong>
                    </span>
                    <span class="sk-durum"><?php echo e(havaMetni($vh['current']['weather_code'])); ?></span>
                    <span class="sk-alt">
                        <span>↑ <?php echo round($vh['daily']['temperature_2m_max'][0]); ?>°</span>
                        <span>↓ <?php echo round($vh['daily']['temperature_2m_min'][0]); ?>°</span>
                        <span>☂ %<?php echo $vh['daily']['precipitation_probability_max'][0] ?? 0; ?></span>
                    </span>
                    <span class="sk-git">Ayrıntılar →</span>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>

    <h1>Şehrinizin havası, tek bakışta.</h1>
    <p>Türkiye'nin <?php echo toplamIlSayisi($bolgeler); ?> ili ve <?php echo array_sum(array_map('count', $ilceler)); ?> ilçesi için anlık hava durumu, 3 ve 7 günlük tahmin.</p>

    <!-- Arama: GET ile çalışır → index.php?ara=...
         Yazarken açılan öneri kutusu, "Konumum" düğmesi ve × (temizle) düğmesi assets/arama.js ile çalışır.
         JavaScript kapalıysa form yine normal arama yapar. Metinler (data-metin-*) seçili dile çevrilir. -->
    <form action="index.php" method="get" class="arama" autocomplete="off"
          data-metin-il="İl" data-metin-ilce="İlçe" data-metin-tum="tüm ilçeler" data-metin-tumalt="İlçelerin listesi"
          data-metin-bos="Sonuç bulunamadı. Örnek: Tokat ya da İstanbul: Beşiktaş"
          data-metin-konumbekle="Konumunuz alınıyor…" data-metin-konumbulundu="Size en yakın yer:"
          data-metin-konumred="Konum izni verilmedi. Tarayıcının adres çubuğundaki konum simgesinden izin verebilirsiniz."
          data-metin-konumhata="Konumunuz şu an alınamadı. Lütfen tekrar deneyin.">
        <svg viewBox="0 0 24 24" class="arama-simge" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/></svg>
        <input type="text" name="ara" placeholder="İl veya il: ilçe yazın… (ör. İstanbul: Beşiktaş)"
               value="<?php echo e($aranan); ?>" aria-label="İl veya ilçe ara"
               role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="oneriKutu">
        <button type="button" class="arama-temizle" title="Temizle" aria-label="Temizle" hidden>
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
        </button>
        <!-- Konumum: tarayıcıdan konum alınır, en yakın il / ilçe bulunur ve sayfası açılır -->
        <button type="button" class="konum-buton" title="Konumumun hava durumu" hidden>
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 3L3 10.5l7.5 3 3 7.5z"/></svg>
            <span>Konumum</span>
        </button>
        <button type="submit" class="arama-git">Ara</button>

        <!-- Öneriler buraya yazılır -->
        <div class="oneri-kutu" id="oneriKutu" role="listbox" hidden></div>
    </form>
    <p class="konum-durum" aria-live="polite" hidden></p>

    <?php
    /* Öneri kutusunun verisi: 81 il ve "Merkez" dışındaki tüm ilçeler.
     * Her satır: [tür (0 il, 1 ilçe), ad, üst bilgi (ilin bölgesi / ilçenin ili), il kodu, ilçe kodu, enlem, boylam]
     * Konumum özelliği en yakın yeri bu koordinatlarla bulur (ilçelerde hazır koordinatlar, illerde il merkezi). */
    $aramaVerisi = [];
    $hazirKoordinatlar = ilceMerkezleri();
    foreach ($bolgeler as $b) {
        $bolgeAdi = (aktifDil() == 'en') ? cevir($b['ad']) : $b['ad'];
        foreach ($b['iller'] as $kod => $ilSecenek) {
            $aramaVerisi[] = [0, $ilSecenek['ad'], $bolgeAdi, $kod, '', $ilSecenek['lat'], $ilSecenek['lon']];
            foreach (ilceListesi($ilceler, $kod) as $ilceKod => $ilceAdi) {
                if ($ilceKod == 'merkez') {
                    continue;
                }
                $nokta = $hazirKoordinatlar[$kod . '/' . $ilceKod] ?? null;
                $aramaVerisi[] = [1, $ilceAdi, $ilSecenek['ad'], $kod, $ilceKod, $nokta['lat'] ?? null, $nokta['lon'] ?? null];
            }
        }
    }
    ?>
    <script type="application/json" id="aramaVerisi"><?php echo json_encode($aramaVerisi, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG); ?></script>
    <script src="assets/arama.js?v=<?php echo filemtime(__DIR__ . '/assets/arama.js'); ?>" defer></script>

    <!-- Örnek aramalar: tıklanınca aynı GET aramasını yapar -->
    <p class="arama-ornek">
        Örnek:
        <a href="index.php?ara=<?php echo urlencode('Tokat'); ?>">Tokat</a>
        <a href="index.php?ara=<?php echo urlencode('İstanbul: Beşiktaş'); ?>">İstanbul: Beşiktaş</a>
        <a href="index.php?ara=<?php echo urlencode('Rize: tüm ilçeler'); ?>">Rize: tüm ilçeler</a>
        <a href="index.php?ara=<?php echo urlencode('İstanbul: tüm ilçeler'); ?>">İstanbul: tüm ilçeler</a>
    </p>

    <?php if ($hata !== ''): ?>
        <p class="mesaj hata"><?php echo e($hata); ?></p>
    <?php endif; ?>

    <?php if (count($aramaSonuclari) > 1): ?>
        <!-- Birden fazla sonuç varsa seçenekleri göster -->
        <div class="sonuclar">
            <span><?php echo count($aramaSonuclari); ?> sonuç<?php echo count($aramaSonuclari) > EN_FAZLA_SONUC ? ' (ilk ' . EN_FAZLA_SONUC . ')' : ''; ?>:</span>
            <?php foreach (array_slice($aramaSonuclari, 0, EN_FAZLA_SONUC) as $sonuc): ?>
                <a href="<?php echo aramaAdresi($sonuc); ?>" translate="no">
                    <?php echo e($sonuc['ad']); ?>
                    <small><?php echo e($sonuc['ust']); ?></small>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<main>
    <?php if ($bildirim !== null): ?>
        <p class="mesaj <?php echo $bildirim['tur']; ?>"><?php echo e($bildirim['metin']); ?></p>
    <?php endif; ?>

    <?php if (girisYapildiMi()): ?>
    <!-- ======================= TAKİP ETTİKLERİM (yalnızca girişte) ======================= -->
    <section class="ozet-takip">
        <div class="baslik-satir">
            <h2>Takip ettikleriniz</h2>
            <a href="takip.php" class="detay-link">Tümünü yönet →</a>
        </div>

        <?php if (count($benimTakiplerim) == 0): ?>
            <p class="mesaj bilgi">Henüz takip ettiğiniz bir il yok. <a href="ekle.php">İlk takibinizi ekleyin.</a></p>
        <?php else: ?>
            <div class="ozet-kartlar">
                <?php foreach ($benimTakiplerim as $id => $kayit): ?>
                    <?php $h = $takipHavalari[$id] ?? null; ?>
                    <a href="<?php echo e(takipAdresi($kayit)); ?>" class="ozet-kart <?php echo $kayit['durum'] == 'pasif' ? 'pasif' : ''; ?>">
                        <span class="ozet-ad"><?php echo e($kayit['baslik']); ?></span>
                        <span class="ozet-il" translate="no"><?php echo e(takipYerAdi($kayit, $bolgeler, $ilceler)); ?></span>
                        <?php if ($h !== null): ?>
                            <span class="ozet-hava">
                                <?php echo havaSimgesi($h['current']['weather_code'], $h['current']['is_day'] ?? 1); ?>
                                <strong><?php echo round($h['current']['temperature_2m']); ?>°</strong>
                            </span>
                            <?php if (count(takipUyarilari($kayit, $h)) > 0): ?>
                                <span class="ozet-uyari">⚠ Uyarı var</span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
    <?php endif; ?>

    <!-- ======================= BÖLGELER ======================= -->
    <section id="bolgeler">
        <h2>Bölgeler</h2>
        <!-- Her kartın sağ üstünde bölgeyi anlatan, elle çizilmiş küçük bir simge vardır
             (bolgeSimgesi() — fonksiyonlar/gorunum.php). Kartlar sayfa açılırken sırayla belirir (--sira). -->
        <div class="bolgeler">
            <?php $sira = 0; foreach ($bolgeler as $kod => $b): ?>
                <a href="index.php?bolge=<?php echo $kod; ?>#bolge-detay"
                   class="bolge <?php echo ($kod == $bolgeKodu) ? 'secili' : ''; ?>" style="--sira: <?php echo $sira++; ?>">
                    <span class="bolge-simge-kutu"><?php echo bolgeSimgesi($kod); ?></span>
                    <span class="bolge-ad"><?php echo e($b['ad']); ?></span>
                    <span class="bolge-sayi"><?php echo count($b['iller']); ?> il</span>
                    <span class="bolge-not"><?php echo e($b['aciklama']); ?></span>
                    <span class="bolge-git" aria-hidden="true">İlleri gör <span>→</span></span>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- ======================= SEÇİLEN BÖLGENİN İLLERİ ======================= -->
    <?php if ($secilenBolge !== null): ?>
    <section id="bolge-detay">
        <div class="baslik-satir">
            <h2><?php echo e($secilenBolge['ad']); ?> Bölgesi</h2>
            <span class="rozet"><?php echo count($secilenBolge['iller']); ?> il</span>
        </div>

        <?php if ($havalar === null): ?>
            <p class="mesaj uyari">Hava durumu verisi şu an alınamadı. İnternet bağlantınızı kontrol edip sayfayı yenileyin.</p>
        <?php endif; ?>

        <div class="iller">
            <?php foreach ($secilenBolge['iller'] as $kod => $il): ?>
                <?php $hava = $havalar[$kod] ?? null; ?>

                <article id="il-<?php echo $kod; ?>" class="il <?php echo ($kod == $ilKodu) ? 'vurgulu' : ''; ?>">

                    <!-- İl adı ve anlık sıcaklık -->
                    <div class="il-bas">
                        <h3><a href="il.php?il=<?php echo $kod; ?>"><?php echo e($il['ad']); ?></a></h3>
                        <?php if ($hava !== null): ?>
                            <div class="simdi">
                                <?php echo havaSimgesi($hava['current']['weather_code'], $hava['current']['is_day'] ?? 1); ?>
                                <span class="derece"><?php echo round($hava['current']['temperature_2m']); ?>°</span>
                                <span class="durum"><?php echo havaMetni($hava['current']['weather_code']); ?></span>
                            </div>
                        <?php else: ?>
                            <p class="veri-yok">Veri yok</p>
                        <?php endif; ?>
                        <a class="detay-link" href="il.php?il=<?php echo $kod; ?>">7 günlük tahmin →</a>
                    </div>

                    <!-- 3 günlük tahmin -->
                    <?php if ($hava !== null): ?>
                        <div class="gunler">
                            <?php foreach ($hava['daily']['time'] as $i => $tarih): ?>
                                <?php
                                    $kodGun  = $hava['daily']['weather_code'][$i];
                                    $yagis   = $hava['daily']['precipitation_probability_max'][$i];
                                ?>
                                <div class="gun">
                                    <span class="gun-ad"><?php echo gunEtiketi($tarih, $i); ?></span>
                                    <span title="<?php echo havaMetni($kodGun); ?>"><?php echo havaSimgesi($kodGun); ?></span>
                                    <span class="gun-derece">
                                        <strong><?php echo round($hava['daily']['temperature_2m_max'][$i]); ?>°</strong>
                                        <?php echo round($hava['daily']['temperature_2m_min'][$i]); ?>°
                                    </span>
                                    <?php if ($yagis !== null && $yagis >= 50): ?>
                                        <span class="yagis cok" title="Yağış ihtimali">☂ %<?php echo $yagis; ?></span>
                                    <?php elseif ($yagis !== null): ?>
                                        <span class="yagis" title="Yağış ihtimali">%<?php echo $yagis; ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                </article>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</main>

<?php require 'footer.php'; ?>
