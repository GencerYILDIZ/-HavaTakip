<?php
/*
 * takip.php — Takip Listem (yalnızca giriş yapan kullanıcılar)
 *
 * GET ile filtreleme:
 *   takip.php?kategori=seyahat            → yalnızca Seyahat kategorisi
 *   takip.php?durum=aktif                 → yalnızca aktif takipler
 *   takip.php?kategori=is&durum=pasif     → ikisi birlikte
 *
 * Her kayıt için canlı hava durumu ve kullanıcının seçtiği uyarılardan
 * bugün tetiklenenler gösterilir. Silme işlemi sil.php'ye POST ile gönderilir.
 */

require_once 'baslat.php';
girisGerekli();

$kullanici    = aktifKullanici();
$kullaniciAdi = $kullanici['kullaniciAdi'];

/* ---------- "Örnek kayıtlara dön" (POST) ---------- */
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['sifirla']) && csrfGecerliMi()) {
    ornekKayitlariYukle($kullaniciAdi, $ornekTakipler, true);
    mesajEkle('bilgi', 'Takip listeniz örnek kayıtlara döndürüldü.');
    header('Location: takip.php');
    exit;
}

$bildirim = mesajAl();

/* ---------- 1) Filtreler (GET) ----------
 * Listede olmayan bir değer gelirse (ör. ?kategori=xyz) filtre yok sayılır.
 */
$secKategori = $_GET['kategori'] ?? '';
$secDurum    = $_GET['durum'] ?? '';
$filtreUyari = '';

if ($secKategori !== '' && !isset($kategoriler[$secKategori])) {
    $filtreUyari = 'Geçersiz kategori filtresi yok sayıldı.';
    $secKategori = '';
}
if ($secDurum !== '' && !isset($durumlar[$secDurum])) {
    $filtreUyari = 'Geçersiz durum filtresi yok sayıldı.';
    $secDurum = '';
}
$filtreVar = ($secKategori !== '' || $secDurum !== '');

/* ---------- 2) Kayıtlar, sayaçlar ve filtreleme ---------- */
$tumKayitlar = kullaniciListesi($kullaniciAdi);
krsort($tumKayitlar);   // en son eklenen en üstte

$sayac    = takipSayaclari($tumKayitlar, $kategoriler, $durumlar);
$takipler = takipFiltrele($tumKayitlar, $secKategori, $secDurum);
$yeniId   = (int) ($_GET['yeni'] ?? 0);

/* ---------- 3) Canlı hava durumu: görünen kayıtlar için TEK istek ---------- */
$havalar = null;
if (count($takipler) > 0) {
    $konumlar = [];
    foreach ($takipler as $id => $kayit) {
        $konumlar[$id] = takipKonumu($kayit, $bolgeler, $ilceler);   // ilçe seçildiyse ilçenin konumu
    }
    $havalar = bolgeHavaGetir($konumlar);
}

// Bugün en az bir uyarısı tetiklenen kayıt sayısı (sayaç kartı için)
$uyariVerenSayisi = 0;
$kayitUyarilari   = [];
foreach ($takipler as $id => $kayit) {
    $kayitUyarilari[$id] = takipUyarilari($kayit, $havalar[$id] ?? null);
    if (count($kayitUyarilari[$id]) > 0) {
        $uyariVerenSayisi++;
    }
}

$sayfaBasligi = 'Takip Listem';
$aktifMenu    = 'takip';
require 'header.php';
?>

<main class="yonetim takip-sayfa">
    <p class="yol"><a href="index.php">Ana Sayfa</a> › Takip Listem</p>

    <!-- ================= ÜST BÖLÜM ================= -->
    <section class="yonetim-hero takip-hero">
        <div class="yh-metin">
            <span class="yh-etiket">Kişisel takip</span>
            <h1>Takip Listem</h1>
            <p>Merhaba <?php echo e($kullanici['adSoyad']); ?>, takip ettiğiniz şehirler ve bugünkü uyarılarınız.</p>
        </div>
        <div class="yh-eylemler">
            <a href="ekle.php" class="yh-buton ana">+ Yeni takip ekle</a>
            <a href="index.php?menu=bolgeler#bolgeler" class="yh-buton">Bölgelere göz at</a>
        </div>
        <!-- Süs: sağda yavaşça dönen halkalar -->
        <span class="yh-halka h1" aria-hidden="true"></span><span class="yh-halka h2" aria-hidden="true"></span>
    </section>

    <?php if ($bildirim !== null): ?>
        <p class="mesaj <?php echo $bildirim['tur']; ?>"><?php echo e($bildirim['metin']); ?></p>
    <?php endif; ?>
    <?php if ($filtreUyari !== ''): ?>
        <p class="mesaj uyari"><?php echo e($filtreUyari); ?></p>
    <?php endif; ?>

    <!-- ================= SAYAÇLAR ================= -->
    <div class="ystat-satir">
        <div class="ystat" style="--sira:0">
            <span class="ystat-ikon i-kayit"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 6h12M8 12h12M8 18h12M4 6h.01M4 12h.01M4 18h.01"/></svg></span>
            <span class="ystat-metin"><strong><?php echo $sayac['toplam']; ?></strong><span>Toplam takip</span></span>
        </div>
        <div class="ystat" style="--sira:1">
            <span class="ystat-ikon i-aktif"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5l11 7-11 7z"/></svg></span>
            <span class="ystat-metin"><strong><?php echo $sayac['durum']['aktif']; ?></strong><span><?php echo e($durumlar['aktif']); ?></span></span>
        </div>
        <div class="ystat" style="--sira:2">
            <span class="ystat-ikon i-pasif"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5v14M16 5v14"/></svg></span>
            <span class="ystat-metin"><strong><?php echo $sayac['durum']['pasif']; ?></strong><span><?php echo e($durumlar['pasif']); ?></span></span>
        </div>
        <div class="ystat" style="--sira:3">
            <span class="ystat-ikon i-uyari"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3l9.5 17h-19z"/><path d="M12 10v4M12 17h.01"/></svg></span>
            <span class="ystat-metin">
                <strong><?php echo $havalar === null && count($takipler) > 0 ? '—' : $uyariVerenSayisi; ?></strong>
                <span>Bugün uyarı veren<?php echo $filtreVar ? ' (filtrede)' : ''; ?></span>
            </span>
        </div>
    </div>

    <!-- ================= FİLTRE (GET) ================= -->
    <section class="panel takip-filtre">
        <!-- Hızlı kategori düğmeleri (aynı filtreyi bağlantıyla uygular) -->
        <div class="hizli-filtre">
            <a href="takip.php<?php echo $secDurum !== '' ? '?durum=' . $secDurum : ''; ?>"
               class="<?php echo ($secKategori === '') ? 'secili' : ''; ?>">Tümü <span><?php echo $sayac['toplam']; ?></span></a>
            <?php foreach ($kategoriler as $kod => $ad): ?>
                <a href="takip.php?<?php echo http_build_query(array_filter(['kategori' => $kod, 'durum' => $secDurum])); ?>"
                   class="<?php echo ($secKategori == $kod) ? 'secili' : ''; ?>">
                    <?php echo e($ad); ?> <span><?php echo $sayac['kategori'][$kod]; ?></span>
                </a>
            <?php endforeach; ?>
        </div>

        <form action="takip.php" method="get" class="filtre kucuk">
            <div class="filtre-alan">
                <label for="f-kategori">Kategori</label>
                <select id="f-kategori" name="kategori">
                    <option value="">Tümü (<?php echo $sayac['toplam']; ?>)</option>
                    <?php foreach ($kategoriler as $kod => $ad): ?>
                        <option value="<?php echo $kod; ?>" <?php echo ($secKategori == $kod) ? 'selected' : ''; ?>>
                            <?php echo e($ad); ?> (<?php echo $sayac['kategori'][$kod]; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filtre-alan">
                <label for="f-durum">Durum</label>
                <select id="f-durum" name="durum">
                    <option value="">Tümü (<?php echo $sayac['toplam']; ?>)</option>
                    <?php foreach ($durumlar as $kod => $ad): ?>
                        <option value="<?php echo $kod; ?>" <?php echo ($secDurum == $kod) ? 'selected' : ''; ?>>
                            <?php echo e($ad); ?> (<?php echo $sayac['durum'][$kod]; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="buton-ana">Filtrele</button>
            <?php if ($filtreVar): ?>
                <a href="takip.php" class="buton-ikincil">Filtreyi temizle</a>
            <?php endif; ?>
        </form>

        <?php if ($filtreVar): ?>
            <p class="sonuc-bilgi">
                <?php echo count($takipler); ?> / <?php echo $sayac['toplam']; ?> kayıt gösteriliyor
                <?php if ($secKategori !== ''): ?> · Kategori: <strong><?php echo e($kategoriler[$secKategori]); ?></strong><?php endif; ?>
                <?php if ($secDurum !== ''): ?> · Durum: <strong><?php echo e($durumlar[$secDurum]); ?></strong><?php endif; ?>
            </p>
        <?php endif; ?>
    </section>

    <?php if ($havalar === null && count($takipler) > 0): ?>
        <p class="mesaj uyari">Hava durumu verisi şu an alınamadı; kayıtlarınız aşağıda.</p>
    <?php endif; ?>

    <!-- ================= LİSTE ================= -->
    <?php if ($sayac['toplam'] == 0): ?>
        <div class="bos-liste">
            <p>Henüz takip ettiğiniz bir il yok.</p>
            <a href="ekle.php" class="buton-ana">İlk takibinizi ekleyin</a>
        </div>
    <?php elseif (count($takipler) == 0): ?>
        <div class="bos-liste">
            <p>Bu filtreye uyan kayıt yok.</p>
            <a href="takip.php" class="buton-ikincil">Filtreyi temizle</a>
        </div>
    <?php else: ?>
        <div class="takipler">
            <?php $sira = 0; foreach ($takipler as $id => $kayit): ?>
                <?php
                    $ilBilgi  = ilBul($bolgeler, $kayit['il']);
                    $hava     = $havalar[$id] ?? null;
                    $uyarilar = $kayitUyarilari[$id];
                ?>
                <article id="takip-<?php echo $id; ?>" style="--sira: <?php echo $sira++; ?>"
                         class="takip <?php echo ($id == $yeniId) ? 'yeni' : ''; ?> <?php echo ($kayit['durum'] == 'pasif') ? 'pasif' : ''; ?> <?php echo count($uyarilar) > 0 ? 'uyarili' : ''; ?>">

                    <div class="takip-ust">
                        <div>
                            <h2><?php echo e($kayit['baslik']); ?></h2>
                            <a class="takip-il" href="<?php echo e(takipAdresi($kayit)); ?>">
                                <span translate="no"><?php echo e(takipYerAdi($kayit, $bolgeler, $ilceler)); ?></span> · <?php echo e($ilBilgi['bolgeAd']); ?>
                            </a>
                        </div>

                        <?php if ($hava !== null): ?>
                            <div class="takip-hava">
                                <?php echo havaSimgesi($hava['current']['weather_code'], $hava['current']['is_day'] ?? 1); ?>
                                <strong><?php echo round($hava['current']['temperature_2m']); ?>°</strong>
                                <span>
                                    <?php echo round($hava['daily']['temperature_2m_max'][0]); ?>° /
                                    <?php echo round($hava['daily']['temperature_2m_min'][0]); ?>°
                                    · ☂ %<?php echo $hava['daily']['precipitation_probability_max'][0] ?? 0; ?>
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="takip-etiketler">
                        <span class="etiket-kategori"><?php echo e($kategoriler[$kayit['kategori']]); ?></span>
                        <span class="etiket-durum durum-<?php echo $kayit['durum']; ?>"><?php echo e($durumlar[$kayit['durum']]); ?></span>
                        <?php foreach ($kayit['uyarilar'] as $u): ?>
                            <span class="etiket-uyari">
                                <?php echo e($uyariTurleri[$u]); ?><?php echo ($u == 'yagis') ? ' ≥ %' . $kayit['yagisEsigi'] : ''; ?>
                            </span>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($kayit['aciklama'] !== ''): ?>
                        <!-- Kullanıcı girdisi: htmlspecialchars ile güvenli çıktı -->
                        <p class="takip-aciklama"><?php echo e($kayit['aciklama']); ?></p>
                    <?php endif; ?>

                    <?php if (count($uyarilar) > 0): ?>
                        <div class="takip-uyarilar">
                            <?php foreach ($uyarilar as $u): ?>
                                <div class="tavsiye tavsiye-<?php echo $u['tur']; ?>">
                                    <?php echo uyariIkonu($u['tur']); ?>
                                    <p><?php echo e($u['metin']); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php elseif ($kayit['durum'] == 'aktif' && $hava !== null): ?>
                        <p class="takip-sakin">✓ Bugün seçtiğiniz uyarılardan hiçbiri beklenmiyor.</p>
                    <?php endif; ?>

                    <!-- Durum değiştirme: Aktif ⇄ Duraklatıldı (POST ile durum.php'ye).
                         Kaydın şu anki durumu seçili görünür; diğer düğmeye basınca durum değişir. -->
                    <div class="takip-eylemler">
                    <form action="durum.php" method="post" class="durum-degistir">
                        <?php echo csrfAlani(); ?>
                        <input type="hidden" name="id" value="<?php echo $id; ?>">
                        <input type="hidden" name="filtre_kategori" value="<?php echo $secKategori; ?>">
                        <input type="hidden" name="filtre_durum" value="<?php echo $secDurum; ?>">
                        <?php foreach ($durumlar as $kod => $ad): ?>
                            <?php $secili = ($kayit['durum'] == $kod); ?>
                            <button type="submit" name="durum" value="<?php echo $kod; ?>"
                                    class="durum-dugme durum-<?php echo $kod; ?><?php echo $secili ? ' secili' : ''; ?>"
                                    aria-pressed="<?php echo $secili ? 'true' : 'false'; ?>"<?php echo $secili ? ' disabled' : ''; ?>
                                    title="<?php echo $secili ? 'Şu anki durum' : ($kod == 'aktif' ? 'Takibi yeniden başlat' : 'Takibi duraklat'); ?>">
                                <?php if ($kod == 'aktif'): ?>
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5l11 7-11 7z"/></svg>
                                <?php else: ?>
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5v14M16 5v14"/></svg>
                                <?php endif; ?>
                                <?php echo e($ad); ?>
                            </button>
                        <?php endforeach; ?>
                    </form>

                    <!-- Düzenle: kaydın tüm bilgileri (yer, ad, kategori, durum, uyarılar, açıklama) ekle.php?id=… formunda değiştirilir -->
                    <a href="ekle.php?id=<?php echo $id; ?>" class="duzenle-buton" title="Takip ettiğini düzenle">
                        <?php echo disliSimgesi(); ?>
                        Düzenle
                    </a>
                    </div>

                    <div class="takip-alt">
                        <span>
                            Eklendi: <?php echo eklenmeTarihi($kayit['eklenme']); ?>
                            · <a href="<?php echo e(takipAdresi($kayit)); ?>">7 günlük tahmin →</a>
                        </span>

                        <!-- Silme: POST ile sil.php'ye. onsubmit → tarayıcıda onay penceresi -->
                        <form action="sil.php" method="post" class="sil-form"
                              onsubmit="return confirm('&quot;<?php echo e(addslashes($kayit['baslik'])); ?>&quot; silinsin mi?');">
                            <?php echo csrfAlani(); ?>
                            <input type="hidden" name="id" value="<?php echo $id; ?>">
                            <input type="hidden" name="donus" value="takip">
                            <input type="hidden" name="filtre_kategori" value="<?php echo $secKategori; ?>">
                            <input type="hidden" name="filtre_durum" value="<?php echo $secDurum; ?>">
                            <button type="submit" class="sil-buton" title="Bu kaydı sil">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/></svg>
                                Sil
                            </button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Oturumu örnek verilere döndürme (sunum ve test için) -->
    <form action="takip.php" method="post" class="sifirla"
          onsubmit="return confirm('Takip listeniz örnek kayıtlara döndürülsün mü? Eklediğiniz kayıtlar silinir.');">
        <?php echo csrfAlani(); ?>
        <button type="submit" name="sifirla" value="1" class="buton-metin">Örnek kayıtlara dön</button>
    </form>
</main>

<?php require 'footer.php'; ?>
