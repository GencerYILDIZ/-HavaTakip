<?php
/*
 * ekle.php — Takip kaydı ekleme ve DÜZENLEME formu
 *
 * GET  ile açıldığında : boş form gösterilir.
 *                        ekle.php?il=istanbul → il alanı önceden seçili gelir.
 *                        ekle.php?id=3        → DÜZENLEME: 3 numaralı kaydın bilgileri formda gelir
 *                                               (Takip Listem'deki kartta "⚙ Düzenle").
 * POST ile gönderildiğinde: veriler doğrulanır.
 *     - Hata varsa     → form, girilen değerler ve hata mesajlarıyla tekrar gösterilir.
 *     - Hata yoksa     → kayıt $_SESSION'a eklenir (düzenlemede mevcut kaydın üzerine yazılır)
 *                        ve takip.php'ye yönlendirilir.
 *
 * Neden yönlendirme? (Post/Redirect/Get yöntemi)
 * Kayıttan sonra sayfa POST ile kalırsa, kullanıcı F5'e bastığında form
 * tekrar gönderilir ve aynı kayıt iki kez eklenir. Yönlendirme bunu önler.
 */

require_once 'baslat.php';
girisGerekli();   // giriş yapmayan bu sayfayı göremez

$kullaniciAdi = aktifKullanici()['kullaniciAdi'];
$hatalar      = [];

// Düzenleme mi? (ekle.php?id=3 ya da formdaki gizli id alanı)
$duzenleId = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$duzenle   = ($duzenleId > 0);
$tumKayitlar = kullaniciListesi($kullaniciAdi);
if ($duzenle && !isset($tumKayitlar[$duzenleId])) {
    mesajEkle('hata', 'Düzenlenecek kayıt bulunamadı. Silinmiş olabilir.');
    header('Location: takip.php');
    exit;
}

// Formdaki alanların başlangıç değerleri
$form = [
    'il'         => $_GET['il'] ?? '',   // il.php'den gelindiyse önceden seçili
    'ilce'       => $_GET['ilce'] ?? '', // ilçe sayfasından gelindiyse ilçe de seçili
    'baslik'     => '',
    'kategori'   => '',
    'durum'      => 'aktif',
    'uyarilar'   => ['yagis'],
    'yagisEsigi' => '50',
    'aciklama'   => '',
];
if ($duzenle) {
    // Düzenlemede form, kaydın şu anki değerleriyle dolu gelir
    $k = $tumKayitlar[$duzenleId];
    $form = [
        'il'         => $k['il'],
        'ilce'       => $k['ilce'] ?? '',
        'baslik'     => $k['baslik'],
        'kategori'   => $k['kategori'],
        'durum'      => $k['durum'],
        'uyarilar'   => $k['uyarilar'],
        'yagisEsigi' => (string) $k['yagisEsigi'],
        'aciklama'   => $k['aciklama'],
    ];
}

/* ---------- Form gönderildi mi? ---------- */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Aynı ad kontrolünde düzenlenen kaydın kendisi sayılmaz
    $digerKayitlar = $tumKayitlar;
    unset($digerKayitlar[$duzenleId]);
    $sonuc = takipDogrula($_POST, $bolgeler, $kategoriler, $durumlar, $uyariTurleri, $digerKayitlar, $ilceler);

    // Form bu sitenin formu mu? (CSRF kontrolü)
    if (!csrfGecerliMi()) {
        $sonuc['hatalar']['genel'] = 'Form süresi dolmuş olabilir. Lütfen tekrar gönderin.';
    }

    if (count($sonuc['hatalar']) == 0) {
        // BAŞARILI: kaydı ekle ya da güncelle, mesajı oturuma yaz, listeye yönlendir
        if ($duzenle) {
            takipGuncelle($kullaniciAdi, $duzenleId, $sonuc['veri']);
            mesajEkle('basari', '"' . $sonuc['veri']['baslik'] . '" kaydı güncellendi.');
            header('Location: takip.php?yeni=' . $duzenleId . '#takip-' . $duzenleId);
            exit;
        }
        $yeniId = takipEkle($kullaniciAdi, $sonuc['veri']);

        mesajEkle('basari', '"' . $sonuc['veri']['baslik'] . '" takip listenize eklendi.');
        header('Location: takip.php?yeni=' . $yeniId . '#takip-' . $yeniId);
        exit;
    }

    // BAŞARISIZ: hataları göster, kullanıcının girdiklerini formda koru
    $hatalar = $sonuc['hatalar'];
    $form = [
        'il'         => $_POST['il'] ?? '',
        'ilce'       => $_POST['ilce'] ?? '',
        'baslik'     => $_POST['baslik'] ?? '',
        'kategori'   => $_POST['kategori'] ?? '',
        'durum'      => $_POST['durum'] ?? '',
        'uyarilar'   => $sonuc['veri']['uyarilar'],
        'yagisEsigi' => $_POST['yagisEsigi'] ?? '',
        'aciklama'   => $_POST['aciklama'] ?? '',
    ];
}

/**
 * Bir alanın hata mesajını (varsa) gösterir. Sadece bu sayfada kullanılır.
 */
function alanHatasi($hatalar, $alan)
{
    if (isset($hatalar[$alan])) {
        echo '<p class="alan-hata">' . e($hatalar[$alan]) . '</p>';
    }
}

$sayfaBasligi = $duzenle ? 'Takibi Düzenle' : 'Yeni Takip Ekle';
$aktifMenu    = 'takip';
require 'header.php';
?>

<main class="dar">
    <p class="yol"><a href="index.php">Ana Sayfa</a> › <a href="takip.php">Takip Listem</a> › <?php echo $duzenle ? 'Takibi düzenle' : 'Yeni Takip'; ?></p>

    <?php if ($duzenle): ?>
        <h1 class="sayfa-baslik duzenle-baslik">
            <?php echo disliSimgesi(); ?>
            Takibi düzenle
        </h1>
        <p class="sayfa-aciklama">"<?php echo e($tumKayitlar[$duzenleId]['baslik']); ?>" kaydının yerini, adını, durumunu, uyarılarını ve açıklamasını değiştirebilirsiniz.</p>
    <?php else: ?>
        <h1 class="sayfa-baslik">Yeni takip ekle</h1>
        <p class="sayfa-aciklama">Takip etmek istediğiniz ili (isterseniz ilçeyi de) seçin, ona bir ad verin ve hangi durumlarda uyarılmak istediğinizi belirtin.</p>
    <?php endif; ?>

    <?php if (count($hatalar) > 0): ?>
        <!-- Hata özeti -->
        <div class="mesaj hata">
            <strong><?php echo $duzenle ? 'Kayıt güncellenemedi.' : 'Kayıt eklenemedi.'; ?> Lütfen <?php echo count($hatalar); ?> alanı düzeltin:</strong>
            <ul>
                <?php foreach ($hatalar as $mesaj): ?>
                    <li><?php echo e($mesaj); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!--
        method="post" : veriler adres çubuğunda görünmeden gönderilir.
        novalidate    : tarayıcının kendi kontrolünü kapatır; böylece doğrulamanın
                        PHP tarafında yapıldığı ve hata mesajları görülebilir.
    -->
    <form action="ekle.php" method="post" class="form" novalidate>
        <?php echo csrfAlani(); ?>
        <?php if ($duzenle): ?>
            <!-- Düzenlenen kaydın numarası -->
            <input type="hidden" name="id" value="<?php echo $duzenleId; ?>">
        <?php endif; ?>

        <!-- İl -->
        <div class="alan <?php echo isset($hatalar['il']) ? 'hatali' : ''; ?>">
            <label for="il">İl <span class="zorunlu">*</span></label>
            <select id="il" name="il">
                <option value="">— İl seçin —</option>
                <?php foreach ($bolgeler as $b): ?>
                    <optgroup label="<?php echo e($b['ad']); ?>">
                        <?php foreach ($b['iller'] as $kod => $il): ?>
                            <option value="<?php echo $kod; ?>" <?php echo ($form['il'] == $kod) ? 'selected' : ''; ?>>
                                <?php echo e($il['ad']); ?>
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endforeach; ?>
            </select>
            <?php alanHatasi($hatalar, 'il'); ?>
        </div>

        <!-- İlçe: il seçilince o ilin ilçeleri listelenir (boş = il merkezi).
             Liste sayfa yüklenirken PHP ile seçili ilin ilçeleriyle doldurulur;
             il değiştirilince aşağıdaki küçük JavaScript listeyi yeniler. -->
        <?php $formIlceleri = ilceListesi($ilceler, $form['il']); unset($formIlceleri['merkez']); ?>
        <div class="alan <?php echo isset($hatalar['ilce']) ? 'hatali' : ''; ?>">
            <label for="ilce">İlçe <span class="istege-bagli">(isteğe bağlı)</span></label>
            <select id="ilce" name="ilce"<?php echo count($formIlceleri) == 0 ? ' disabled' : ''; ?>
                    data-metin-merkez="İl merkezi" data-metin-bos="— Önce il seçin —">
                <option value=""><?php echo count($formIlceleri) == 0 ? '— Önce il seçin —' : 'İl merkezi'; ?></option>
                <?php foreach ($formIlceleri as $kod => $ad): ?>
                    <option value="<?php echo $kod; ?>" <?php echo ($form['ilce'] == $kod) ? 'selected' : ''; ?> translate="no"><?php echo e($ad); ?></option>
                <?php endforeach; ?>
            </select>
            <small>Boş bırakılırsa il merkezinin hava durumu takip edilir.</small>
            <?php alanHatasi($hatalar, 'ilce'); ?>
        </div>

        <!-- Takip adı -->
        <div class="alan <?php echo isset($hatalar['baslik']) ? 'hatali' : ''; ?>">
            <label for="baslik">Takip adı <span class="zorunlu">*</span></label>
            <input type="text" id="baslik" name="baslik" maxlength="60"
                   placeholder="ör. Ev, Okul, Annemler"
                   value="<?php echo e($form['baslik']); ?>">
            <small>2–40 karakter.</small>
            <?php alanHatasi($hatalar, 'baslik'); ?>
        </div>

        <div class="alan-satir">
            <!-- Kategori -->
            <div class="alan <?php echo isset($hatalar['kategori']) ? 'hatali' : ''; ?>">
                <label for="kategori">Kategori <span class="zorunlu">*</span></label>
                <select id="kategori" name="kategori">
                    <option value="">— Kategori seçin —</option>
                    <?php foreach ($kategoriler as $kod => $ad): ?>
                        <option value="<?php echo $kod; ?>" <?php echo ($form['kategori'] == $kod) ? 'selected' : ''; ?>>
                            <?php echo e($ad); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php alanHatasi($hatalar, 'kategori'); ?>
            </div>

            <!-- Durum -->
            <div class="alan <?php echo isset($hatalar['durum']) ? 'hatali' : ''; ?>">
                <span class="etiket">Durum <span class="zorunlu">*</span></span>
                <div class="secenekler">
                    <?php foreach ($durumlar as $kod => $ad): ?>
                        <label class="secenek">
                            <input type="radio" name="durum" value="<?php echo $kod; ?>"
                                   <?php echo ($form['durum'] == $kod) ? 'checked' : ''; ?>>
                            <?php echo e($ad); ?>
                        </label>
                    <?php endforeach; ?>
                </div>
                <?php alanHatasi($hatalar, 'durum'); ?>
            </div>
        </div>

        <!-- Uyarılar -->
        <div class="alan <?php echo isset($hatalar['uyarilar']) ? 'hatali' : ''; ?>">
            <span class="etiket">Hangi durumlarda uyarılmak istersiniz?</span>
            <div class="secenekler">
                <?php foreach ($uyariTurleri as $kod => $ad): ?>
                    <label class="secenek">
                        <!-- name="uyarilar[]" : seçilen kutular PHP'ye dizi olarak gelir -->
                        <input type="checkbox" name="uyarilar[]" value="<?php echo $kod; ?>"
                               <?php echo in_array($kod, $form['uyarilar']) ? 'checked' : ''; ?>>
                        <?php echo e($ad); ?>
                    </label>
                <?php endforeach; ?>
            </div>
            <small>Aktif takiplerde en az bir uyarı seçilmelidir.</small>
            <?php alanHatasi($hatalar, 'uyarilar'); ?>
        </div>

        <!-- Yağış eşiği -->
        <div class="alan kisa <?php echo isset($hatalar['yagisEsigi']) ? 'hatali' : ''; ?>">
            <label for="yagisEsigi">Yağış uyarı eşiği (%)</label>
            <input type="number" id="yagisEsigi" name="yagisEsigi" min="10" max="100" step="5"
                   value="<?php echo e($form['yagisEsigi']); ?>">
            <small>Yağış ihtimali bu değeri geçerse uyarılırsınız (10–100). Yalnızca "Yağış" seçiliyse kullanılır.</small>
            <?php alanHatasi($hatalar, 'yagisEsigi'); ?>
        </div>

        <!-- Açıklama -->
        <div class="alan <?php echo isset($hatalar['aciklama']) ? 'hatali' : ''; ?>">
            <label for="aciklama">Açıklama <span class="istege-bagli">(isteğe bağlı)</span></label>
            <textarea id="aciklama" name="aciklama" rows="3"
                      placeholder="ör. Sabah işe çıkmadan önce bakıyorum."><?php echo e($form['aciklama']); ?></textarea>
            <small>En fazla 200 karakter.</small>
            <?php alanHatasi($hatalar, 'aciklama'); ?>
        </div>

        <div class="form-butonlar">
            <button type="submit" class="buton-ana"><?php echo $duzenle ? 'Değişiklikleri kaydet' : 'Takibe ekle'; ?></button>
            <a href="takip.php" class="buton-ikincil">Vazgeç</a>
        </div>
    </form>
</main>

<?php
// İl değişince ilçe listesini yenilemek için tüm illerin ilçeleri: {"ankara": [["cankaya","Çankaya"], ...]}
$ilceVerisi = [];
foreach ($ilceler as $ilKodu => $liste) {
    foreach (ilceListesi($ilceler, $ilKodu) as $kod => $ad) {
        if ($kod != 'merkez') {
            $ilceVerisi[$ilKodu][] = [$kod, $ad];
        }
    }
}
?>
<script>
// İl seçilince ilçe listesini o ilin ilçeleriyle doldurur (JavaScript kapalıysa liste
// sayfa yüklenirken PHP ile doldurulur ve PHP yine ilçenin o ile ait olduğunu kontrol eder).
(function () {
    var ilceler = <?php echo json_encode($ilceVerisi, JSON_UNESCAPED_UNICODE); ?>;
    var il = document.getElementById('il'), ilce = document.getElementById('ilce');
    il.addEventListener('change', function () {
        var liste = ilceler[il.value] || [];
        ilce.innerHTML = '';
        var ilk = document.createElement('option');
        ilk.value = '';
        ilk.textContent = liste.length ? ilce.dataset.metinMerkez : ilce.dataset.metinBos;
        ilce.appendChild(ilk);
        liste.forEach(function (x) {
            var o = document.createElement('option');
            o.value = x[0];
            o.textContent = x[1];
            ilce.appendChild(o);
        });
        ilce.disabled = liste.length == 0;
    });
})();
</script>

<?php require 'footer.php'; ?>
