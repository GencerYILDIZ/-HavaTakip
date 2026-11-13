<?php
/*
 * yonetim.php — Yönetim paneli (yalnızca admin)
 *
 * - Kullanıcı hesaplarını ve her birinin kayıt sayılarını gösterir.
 * - Tüm kullanıcıların takip kayıtlarını tek tabloda listeler.
 * - Kullanıcıya göre filtreleme (GET: yonetim.php?kullanici=kullanici).
 * - Admin, herhangi bir kullanıcının kaydını silebilir (sil.php'ye POST).
 * - Hesap yönetimi (CRUD): yeni hesap ve düzenleme hesap-duzenle.php'de,
 *   hesap silme hesap-sil.php'de (POST) yapılır.
 * - Site temasını (dört mevsim) seçme (tema-ayar.php'ye POST).
 * - İl ve ilçe koordinatları: 81 il listelenir; bir ile tıklanınca (<details>) o ilin
 *   ilçelerinin koordinatları açılır. Hava servisine sorulmaz (önbellek / hazır koordinat).
 *
 * Not: Kayıtlar oturumda (session) tutulduğu için admin, yalnızca bu tarayıcıda
 * giriş yapılmış hesapların kayıtlarını görür. Sonraki dönemde MySQL'e
 * geçildiğinde tüm kullanıcıların verisi tek veritabanında olacak.
 */

require_once 'baslat.php';
adminGerekli();

$bildirim = mesajAl();

/* ---------- Kullanıcı filtresi (GET) ---------- */
$secKullanici = $_GET['kullanici'] ?? '';
if ($secKullanici !== '' && !isset($kullanicilar[$secKullanici])) {
    $secKullanici = '';
}

/* ---------- Tüm kayıtları tek listede topla ---------- */
$tumKayitlar = [];
$ozet        = [];   // kullanıcı başına sayaçlar

foreach ($kullanicilar as $kadi => $hesap) {
    $liste = kullaniciListesi($kadi);
    $ozet[$kadi] = [
        'listeVar' => isset($_SESSION['listeler'][$kadi]),
        'sayac'    => takipSayaclari($liste, $kategoriler, $durumlar),
    ];

    if ($secKullanici !== '' && $secKullanici != $kadi) {
        continue;
    }
    foreach ($liste as $id => $kayit) {
        $kayit['sahibi'] = $kadi;
        $tumKayitlar[]   = $kayit;
    }
}

// Genel toplam (tüm kullanıcılar)
$genelToplam = 0;
$genelAktif  = 0;
foreach ($ozet as $o) {
    $genelToplam += $o['sayac']['toplam'];
    $genelAktif  += $o['sayac']['durum']['aktif'];
}

/* ---------- İl ve ilçe koordinatları (API'ye sormadan) ---------- */
$koordOnbellek = ilceOnbellekOku();   // dosya bir kez okunur, 973 ilçede tekrar kullanılır
$koordSayac    = ['il merkezi' => 0, 'önbellek' => 0, 'hazır koordinat' => 0];
$koordBolgeler = [];
foreach ($bolgeler as $bolgeKodu => $bolge) {
    foreach ($bolge['iller'] as $ilKodu => $il) {
        $satirlar = [];
        foreach (ilceListesi($ilceler, $ilKodu) as $ilceKodu => $ilceAdi) {
            $konum = ilceKonumuApisiz($ilKodu, $ilceKodu, $bolgeler, $koordOnbellek);
            if ($konum !== null) {
                $koordSayac[$konum['kaynak']]++;
            }
            $satirlar[] = ['kod' => $ilceKodu, 'ad' => $ilceAdi, 'konum' => $konum];
        }
        $koordBolgeler[$bolgeKodu]['ad']            = $bolge['ad'];
        $koordBolgeler[$bolgeKodu]['iller'][$ilKodu] = ['il' => $il, 'ilceler' => $satirlar];
    }
}
$kaynakSinif = ['il merkezi' => 'k-il', 'önbellek' => 'k-onbellek', 'hazır koordinat' => 'k-hazir'];

$sayfaBasligi = 'Yönetim';
$aktifMenu    = 'yonetim';
require 'header.php';
?>

<main class="yonetim">
    <p class="yol"><a href="index.php">Ana Sayfa</a> › Yönetim</p>

    <!-- ================= ÜST BÖLÜM ================= -->
    <section class="yonetim-hero">
        <div class="yh-metin">
            <span class="yh-etiket">Yönetici paneli</span>
            <h1>Yönetim paneli</h1>
            <p>Hoş geldiniz, <?php echo e(aktifKullanici()['adSoyad']); ?>. Hesapları, site temasını, takip kayıtlarını ve koordinatları buradan yönetebilirsiniz.</p>
        </div>
        <div class="yh-eylemler">
            <a href="hesap-duzenle.php" class="yh-buton ana">+ Yeni hesap</a>
            <a href="#koordinatlar" class="yh-buton">Koordinatlar</a>
            <a href="ilce-kontrol.php" class="yh-buton">İlçe kontrolü</a>
        </div>
        <!-- Süs: sağda yavaşça dönen halkalar -->
        <span class="yh-halka h1" aria-hidden="true"></span><span class="yh-halka h2" aria-hidden="true"></span>
    </section>

    <!-- Bölümler arasında hızlı geçiş -->
    <nav class="yonetim-sekme" aria-label="Bölümler">
        <a href="#genel">Genel bakış</a>
        <a href="#site-temasi">Site teması</a>
        <a href="#hesaplar">Kullanıcılar</a>
        <a href="#kayitlar">Takip kayıtları</a>
        <a href="#koordinatlar">Koordinatlar</a>
    </nav>

    <?php if ($bildirim !== null): ?>
        <p class="mesaj <?php echo $bildirim['tur']; ?>"><?php echo e($bildirim['metin']); ?></p>
    <?php endif; ?>

    <!-- ================= SAYAÇLAR ================= -->
    <div class="ystat-satir" id="genel">
        <div class="ystat" style="--sira:0">
            <span class="ystat-ikon i-kullanici"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0M16 4.5a3.5 3.5 0 0 1 0 7M18 14.5a6 6 0 0 1 3.5 5.5"/></svg></span>
            <span class="ystat-metin"><strong><?php echo count($kullanicilar); ?></strong><span>Kullanıcı hesabı</span></span>
        </div>
        <div class="ystat" style="--sira:1">
            <span class="ystat-ikon i-kayit"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 6h12M8 12h12M8 18h12M4 6h.01M4 12h.01M4 18h.01"/></svg></span>
            <span class="ystat-metin"><strong><?php echo $genelToplam; ?></strong><span>Toplam takip kaydı</span></span>
        </div>
        <div class="ystat" style="--sira:2">
            <span class="ystat-ikon i-aktif"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5l11 7-11 7z"/></svg></span>
            <span class="ystat-metin"><strong><?php echo $genelAktif; ?></strong><span>Aktif</span></span>
        </div>
        <div class="ystat" style="--sira:3">
            <span class="ystat-ikon i-pasif"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5v14M16 5v14"/></svg></span>
            <span class="ystat-metin"><strong><?php echo $genelToplam - $genelAktif; ?></strong><span>Duraklatıldı</span></span>
        </div>
    </div>

    <!-- ================= SİTE TEMASI (dört mevsim) =================
         Otomatik: aya göre değişir. Bir mevsim seçilirse tüm site o mevsimde sabit kalır. -->
    <section class="panel" id="site-temasi">
        <div class="panel-baslik">
            <div>
                <h2>Site teması</h2>
                <p>Otomatik seçilirse tema aya göre değişir; bir mevsim seçilirse site o mevsimde sabit kalır.</p>
            </div>
        </div>
        <form action="tema-ayar.php" method="post" class="tema-secim">
            <?php echo csrfAlani(); ?>
            <?php $ayar = mevsimAyari(); ?>
            <div class="tema-kartlar">
                <label class="tema-kart tema-oto <?php echo ($ayar == 'oto') ? 'secili' : ''; ?>">
                    <input type="radio" name="mevsim" value="oto" <?php echo ($ayar == 'oto') ? 'checked' : ''; ?>>
                    <span class="tema-onizleme"><img src="assets/mevsim/<?php echo mevsimGorselleri(ayaGoreMevsim((int) date('n')))[0]; ?>.svg" alt=""></span>
                    <strong>Otomatik</strong>
                    <small>Aya göre · şu an <?php echo MEVSIMLER[ayaGoreMevsim((int) date('n'))]; ?></small>
                </label>
                <?php foreach (MEVSIMLER as $kod => $ad): ?>
                    <label class="tema-kart tema-<?php echo $kod; ?> <?php echo ($ayar == $kod) ? 'secili' : ''; ?>">
                        <input type="radio" name="mevsim" value="<?php echo $kod; ?>" <?php echo ($ayar == $kod) ? 'checked' : ''; ?>>
                        <span class="tema-onizleme"><img src="assets/mevsim/<?php echo mevsimGorselleri($kod)[0]; ?>.svg" alt=""></span>
                        <strong><?php echo $ad; ?></strong>
                        <small>Sabit</small>
                    </label>
                <?php endforeach; ?>
            </div>
            <div class="panel-alt"><button type="submit" class="buton-ana">Temayı kaydet</button></div>
        </form>
    </section>

    <!-- ================= KULLANICILAR (hesap yönetimi) ================= -->
    <section class="panel" id="hesaplar">
        <div class="panel-baslik">
            <div>
                <h2>Kullanıcılar</h2>
                <p>Hesap ekleme, düzenleme ve silme (CRUD).</p>
            </div>
            <a href="hesap-duzenle.php" class="buton-ana">+ Yeni hesap</a>
        </div>
        <div class="tablo-kutu">
            <table class="tablo tablo-mobil">
                <thead>
                    <tr><th>Kullanıcı</th><th>E-posta</th><th>Rol</th><th>Kayıt</th><th>Aktif</th><th>Duraklatıldı</th><th>İşlemler</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($kullanicilar as $kadi => $hesap): ?>
                        <tr>
                            <td data-label="Kullanıcı">
                                <span class="tablo-kisi">
                                    <span class="avatar kucuk" aria-hidden="true"><?php echo e(mb_strtoupper(mb_substr($hesap['adSoyad'], 0, 1))); ?></span>
                                    <span><strong><?php echo e($kadi); ?></strong><?php echo ($kadi === aktifKullanici()['kullaniciAdi']) ? ' <span class="soluk">(siz)</span>' : ''; ?>
                                        <small><?php echo e($hesap['adSoyad']); ?></small></span>
                                </span>
                            </td>
                            <td data-label="E-posta"><?php echo e($hesap['eposta'] ?? '—'); ?></td>
                            <td data-label="Rol"><span class="rol rol-<?php echo $hesap['rol']; ?>"><?php echo $hesap['rol'] == 'admin' ? 'Yönetici' : 'Kullanıcı'; ?></span></td>
                            <?php if ($ozet[$kadi]['listeVar']): ?>
                                <td data-label="Kayıt"><?php echo $ozet[$kadi]['sayac']['toplam']; ?></td>
                                <td data-label="Aktif"><?php echo $ozet[$kadi]['sayac']['durum']['aktif']; ?></td>
                                <td data-label="Duraklatıldı"><?php echo $ozet[$kadi]['sayac']['durum']['pasif']; ?></td>
                            <?php else: ?>
                                <td colspan="3" class="soluk" data-label="Kayıt">Bu oturumda henüz giriş yapmadı</td>
                            <?php endif; ?>
                            <td data-label="İşlemler">
                                <!-- İşlemler: hepsi aynı boyda, simgeli düğmeler -->
                                <div class="tablo-islem">
                                    <a href="yonetim.php?kullanici=<?php echo urlencode($kadi); ?>#kayitlar" class="islem-buton" title="Bu kullanıcının takip kayıtları">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 6h11M9 12h11M9 18h11M4.5 6h.01M4.5 12h.01M4.5 18h.01"/></svg>Kayıtları
                                    </a>
                                    <a href="hesap-duzenle.php?k=<?php echo urlencode($kadi); ?>" class="islem-buton" title="Hesabı düzenle">
                                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20h4L19 9l-4-4L4 16z"/><path d="M13.5 6.5l4 4"/></svg>Düzenle
                                    </a>
                                    <?php if ($kadi === aktifKullanici()['kullaniciAdi']): ?>
                                        <a href="hesabim.php" class="islem-buton" title="Kendi hesabınızı Hesabım sayfasından silebilirsiniz">
                                            <?php echo disliSimgesi(); ?>Hesabım
                                        </a>
                                    <?php else: ?>
                                        <form action="hesap-sil.php" method="post" class="sil-form"
                                              onsubmit="return confirm('&quot;<?php echo e($kadi); ?>&quot; hesabı ve takip listesi silinsin mi?');">
                                            <?php echo csrfAlani(); ?>
                                            <input type="hidden" name="k" value="<?php echo e($kadi); ?>">
                                            <button type="submit" class="islem-buton sil" title="Hesabı sil">
                                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/></svg>Sil
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- ================= TÜM KAYITLAR ================= -->
    <section class="panel" id="kayitlar">
        <div class="panel-baslik">
            <div>
                <h2>Takip kayıtları</h2>
                <p>Tüm kullanıcıların kayıtları; kullanıcıya göre süzülebilir ve silinebilir.</p>
            </div>
            <form action="yonetim.php" method="get" class="filtre kucuk">
                <div class="filtre-alan">
                    <label for="f-kullanici">Kullanıcı</label>
                    <select id="f-kullanici" name="kullanici">
                        <option value="">Tümü</option>
                        <?php foreach ($kullanicilar as $kadi => $hesap): ?>
                            <option value="<?php echo e($kadi); ?>" <?php echo ($secKullanici == $kadi) ? 'selected' : ''; ?>><?php echo e($kadi); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="buton-ana">Göster</button>
            </form>
        </div>

        <?php if (count($tumKayitlar) == 0): ?>
            <div class="bos-liste"><p>Gösterilecek kayıt yok.</p></div>
        <?php else: ?>
            <div class="tablo-kutu">
                <table class="tablo tablo-mobil">
                    <thead>
                        <tr><th>Kullanıcı</th><th>Takip adı</th><th>İl</th><th>Kategori</th><th>Durum</th><th>Eklenme</th><th></th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tumKayitlar as $kayit): ?>
                            <tr>
                                <td data-label="Kullanıcı"><?php echo e($kayit['sahibi']); ?></td>
                                <td data-label="Takip adı"><strong><?php echo e($kayit['baslik']); ?></strong></td>
                                <td data-label="İl"><a href="<?php echo e(takipAdresi($kayit)); ?>" translate="no"><?php echo e(takipYerAdi($kayit, $bolgeler, $ilceler)); ?></a></td>
                                <td data-label="Kategori"><?php echo e($kategoriler[$kayit['kategori']]); ?></td>
                                <td data-label="Durum"><span class="etiket-durum durum-<?php echo $kayit['durum']; ?>"><?php echo e($durumlar[$kayit['durum']]); ?></span></td>
                                <td data-label="Eklenme"><?php echo eklenmeTarihi($kayit['eklenme']); ?></td>
                                <td class="tablo-sag">
                                    <form action="sil.php" method="post" class="sil-form"
                                          onsubmit="return confirm('Bu kayıt silinsin mi?');">
                                        <?php echo csrfAlani(); ?>
                                        <input type="hidden" name="id" value="<?php echo $kayit['id']; ?>">
                                        <input type="hidden" name="kullanici" value="<?php echo e($kayit['sahibi']); ?>">
                                        <input type="hidden" name="donus" value="yonetim">
                                        <input type="hidden" name="filtre_kullanici" value="<?php echo e($secKullanici); ?>">
                                        <button type="submit" class="sil-buton">Sil</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <p class="soluk kucuk-not">
            Kayıtlar oturumda tutulduğu için bu panel yalnızca bu tarayıcıda giriş yapılmış hesapları gösterir.
            Kalıcı ve ortak veri için sonraki aşamada MySQL kullanılacaktır.
        </p>
    </section>

    <!-- ================= İL VE İLÇE KOORDİNATLARI =================
         81 il bölgelere göre listelenir. Her il bir <details> kutusudur: başlıkta ilin koordinatı,
         tıklanınca o ilin ilçeleri ve koordinatları açılır. Koordinatlar hava servisine sorulmadan
         gösterilir (önbellek ya da hazır koordinat). Arama kutusu küçük bir JavaScript'le illeri süzer. -->
    <section class="panel" id="koordinatlar">
        <div class="panel-baslik">
            <div>
                <h2>İl ve ilçe koordinatları</h2>
                <p>Bir ile tıklayın, o ilin ilçelerinin koordinatları açılsın. "Tüm ilçeleri aç" ile 973 ilçenin hepsi görünür.</p>
            </div>
            <div class="koord-arac-satir">
                <div class="koord-ara">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
                    <input type="search" id="koordAra" placeholder="İl veya ilçe ara…" aria-label="İl veya ilçe ara" autocomplete="off">
                </div>
                <!-- Tüm illeri açar / kapatır (JavaScript kapalıysa iller tek tek açılabilir) -->
                <button type="button" class="koord-hepsi" hidden
                        data-metin-ac="Tüm ilçeleri aç" data-metin-kapat="Hepsini kapat">Tüm ilçeleri aç</button>
            </div>
        </div>

        <div class="koord-ozet">
            <span><strong><?php echo toplamIlSayisi($bolgeler); ?></strong> il</span>
            <span><strong><?php echo array_sum(array_map('count', $ilceler)); ?></strong> ilçe</span>
            <span class="k-il"><strong><?php echo $koordSayac['il merkezi']; ?></strong> il merkezi</span>
            <span class="k-onbellek"><strong><?php echo $koordSayac['önbellek']; ?></strong> önbellek</span>
            <span class="k-hazir"><strong><?php echo $koordSayac['hazır koordinat']; ?></strong> hazır koordinat</span>
            <a href="ilce-kontrol.php" class="koord-arac">Geocoding ile kontrol et →</a>
        </div>

        <?php foreach ($koordBolgeler as $bolgeKodu => $kb): ?>
            <div class="koord-bolge" data-bolge="<?php echo $bolgeKodu; ?>">
                <h3><?php echo e($kb['ad']); ?> <span><?php echo count($kb['iller']); ?> il</span></h3>
                <div class="koord-liste">
                    <?php foreach ($kb['iller'] as $ilKodu => $ki): ?>
                        <details class="koord-il" data-ara="<?php echo e(slugYap($ki['il']['ad'])); ?>">
                            <summary>
                                <span class="ki-ad" translate="no"><?php echo e($ki['il']['ad']); ?></span>
                                <span class="ki-koord" title="Enlem, boylam"><?php echo number_format($ki['il']['lat'], 4, '.', ''); ?>, <?php echo number_format($ki['il']['lon'], 4, '.', ''); ?></span>
                                <span class="ki-sayi"><?php echo count($ki['ilceler']); ?> ilçe</span>
                                <span class="ki-ok" aria-hidden="true"></span>
                            </summary>
                            <div class="koord-ilceler">
                                <div class="ki-araclar">
                                    <a href="il.php?il=<?php echo $ilKodu; ?>">Hava durumu →</a>
                                    <a href="<?php echo e(haritaAdresi($ki['il']['lat'], $ki['il']['lon'], 9)); ?>" target="_blank" rel="noopener">Haritada aç ↗</a>
                                </div>
                                <div class="tablo-kaydir">
                                <table class="tablo kucuk koord-tablo">
                                    <thead><tr><th>İlçe</th><th>Enlem</th><th>Boylam</th><th>Kaynak</th><th></th></tr></thead>
                                    <tbody>
                                        <?php foreach ($ki['ilceler'] as $ilce): ?>
                                            <tr data-ara="<?php echo e(slugYap($ilce['ad'])); ?>">
                                                <td data-label="İlçe"><a href="il.php?il=<?php echo $ilKodu; ?>&amp;ilce=<?php echo $ilce['kod']; ?>" translate="no"><?php echo e($ilce['ad']); ?></a></td>
                                                <?php if ($ilce['konum'] === null): ?>
                                                    <td colspan="3" data-label="Enlem"><span class="etiket-durum durum-pasif">Bulunamadı</span></td>
                                                <?php else: ?>
                                                    <td data-label="Enlem"><?php echo number_format($ilce['konum']['lat'], 4, '.', ''); ?></td>
                                                    <td data-label="Boylam"><?php echo number_format($ilce['konum']['lon'], 4, '.', ''); ?></td>
                                                    <td data-label="Kaynak"><span class="kaynak <?php echo $kaynakSinif[$ilce['konum']['kaynak']]; ?>"><?php echo e($ilce['konum']['kaynak']); ?></span></td>
                                                <?php endif; ?>
                                                <td class="tablo-sag">
                                                    <?php if ($ilce['konum'] !== null): ?>
                                                        <a href="<?php echo e(haritaAdresi($ilce['konum']['lat'], $ilce['konum']['lon'])); ?>" target="_blank" rel="noopener" title="Haritada aç">Harita ↗</a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                </div>
                            </div>
                        </details>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
        <p class="koord-yok soluk" hidden>Aramaya uyan il veya ilçe yok.</p>
    </section>
</main>

<script>
/* Koordinatlar bölümü:
 *  - Arama kutusu il VE ilçe adlarında arar (Türkçe harfler sadeleştirilerek karşılaştırılır).
 *    İl adı uyuyorsa ilin tüm ilçeleri görünür; yalnızca ilçe adı uyuyorsa il kendiliğinden açılır
 *    ve yalnızca uyan ilçeler gösterilir.
 *  - "Tüm ilçeleri aç" düğmesi 81 ilin hepsini açar (973 ilçe), "Hepsini kapat" kapatır. */
(function () {
    var kutu = document.getElementById('koordAra');
    var hepsi = document.querySelector('.koord-hepsi');
    if (!kutu) {
        return;
    }
    var iller = document.querySelectorAll('.koord-il');
    var sade = function (m) {
        return m.toLocaleLowerCase('tr').replace(/ı/g, 'i').replace(/ş/g, 's').replace(/ğ/g, 'g')
                .replace(/ü/g, 'u').replace(/ö/g, 'o').replace(/ç/g, 'c').replace(/[^a-z0-9]/g, '');
    };

    kutu.addEventListener('input', function () {
        var aranan = sade(kutu.value), gorunen = 0;
        document.querySelectorAll('.koord-bolge').forEach(function (bolge) {
            var bolgedeVar = 0;
            bolge.querySelectorAll('.koord-il').forEach(function (il) {
                var ilUyuyor = aranan === '' || il.dataset.ara.indexOf(aranan) !== -1;
                var ilceVar = 0;
                il.querySelectorAll('tbody tr').forEach(function (satir) {
                    var uyuyor = ilUyuyor || satir.dataset.ara.indexOf(aranan) !== -1;
                    satir.hidden = !uyuyor;
                    if (uyuyor) { ilceVar++; }
                });
                var goster = ilUyuyor || ilceVar > 0;
                il.hidden = !goster;
                // Yalnızca ilçe adı uyduysa il açılır ki uyan ilçe görünsün
                if (aranan !== '' && !ilUyuyor && ilceVar > 0) {
                    il.open = true;
                    il.dataset.aramaActi = '1';
                } else if (il.dataset.aramaActi) {
                    il.open = false;
                    delete il.dataset.aramaActi;
                }
                if (goster) { bolgedeVar++; }
            });
            bolge.hidden = bolgedeVar === 0;
            gorunen += bolgedeVar;
        });
        document.querySelector('.koord-yok').hidden = gorunen !== 0;
    });

    if (hepsi) {
        hepsi.hidden = false;
        hepsi.addEventListener('click', function () {
            var ac = !hepsi.classList.contains('acik');
            iller.forEach(function (il) { il.open = ac; });
            hepsi.classList.toggle('acik', ac);
            hepsi.textContent = ac ? hepsi.dataset.metinKapat : hepsi.dataset.metinAc;
        });
    }
})();
</script>

<?php require 'footer.php'; ?>
