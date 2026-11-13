<?php
/*
 * hesabim.php — Hesabım (giriş yapan her kullanıcı kendi hesabını yönetir)
 *
 * READ   : hesap bilgileri (kullanıcı adı, ad soyad, e-posta, rol, kayıt tarihi, takip sayısı)
 * UPDATE : islem=bilgi  → ad soyad ve e-posta değiştirilir
 *          islem=parola → mevcut parola doğrulanır, yeni parola kaydedilir
 * DELETE : islem=sil    → parola ve onay kutusuyla hesap ve takip listesi silinir
 *
 * Tüm formlar POST ile gönderilir ve CSRF anahtarı taşır.
 * Başarılı işlemden sonra sayfaya yönlendirilir (Post/Redirect/Get), mesaj bir kez gösterilir.
 * Kullanıcı adı değiştirilemez; takip listesi bu ada bağlıdır.
 */

require_once 'baslat.php';
girisGerekli();

$kullaniciAdi = aktifKullanici()['kullaniciAdi'];
$hesap        = $kullanicilar[$kullaniciAdi];

// Her formun hataları ayrı tutulur (hangi formda hata olduğu belli olsun)
$hatalar = ['bilgi' => [], 'parola' => [], 'sil' => []];
$form    = ['adSoyad' => $hesap['adSoyad'], 'eposta' => $hesap['eposta'] ?? ''];

// Sistemdeki tek yönetici hesabını silemez (yönetimsiz site kalmasın)
$silinemez = adminMi() && adminSayisi($kullanicilar) <= 1;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!csrfGecerliMi()) {
        mesajEkle('hata', 'İstek doğrulanamadı. Lütfen sayfayı yenileyip tekrar deneyin.');
        header('Location: hesabim.php');
        exit;
    }

    $islem = $_POST['islem'] ?? '';

    /* ---------- UPDATE: ad soyad ve e-posta ---------- */
    if ($islem == 'bilgi') {
        $girdi = ['adSoyad' => $_POST['adSoyad'] ?? '', 'eposta' => $_POST['eposta'] ?? ''];
        $sonuc = hesapDogrula($girdi, $kullanicilar, $kullaniciAdi);   // parola boş → değişmez

        if (count($sonuc['hatalar']) == 0) {
            if (hesapGuncelle($kullanicilar, $kullaniciAdi, $sonuc['veri'])) {
                mesajEkle('basari', 'Bilgileriniz güncellendi.');
            } else {
                mesajEkle('hata', 'Bilgiler kaydedilemedi. veri klasörüne yazma izni olduğundan emin olun.');
            }
            header('Location: hesabim.php');
            exit;
        }
        $hatalar['bilgi'] = $sonuc['hatalar'];
        $form = ['adSoyad' => $girdi['adSoyad'], 'eposta' => $girdi['eposta']];   // girilenler kaybolmasın

    /* ---------- UPDATE: parola ---------- */
    } elseif ($islem == 'parola') {
        $mevcut = $_POST['mevcutParola'] ?? '';
        $yeni   = $_POST['yeniParola'] ?? '';
        $tekrar = $_POST['yeniParolaTekrar'] ?? '';

        if (!password_verify($mevcut, $hesap['parola'])) {
            $hatalar['parola']['mevcutParola'] = 'Mevcut parolanız hatalı.';
        }
        $parolaHatasi = parolaKontrol($yeni, $kullaniciAdi);
        if ($parolaHatasi !== null) {
            $hatalar['parola']['yeniParola'] = $parolaHatasi;
        } elseif ($yeni !== $tekrar) {
            $hatalar['parola']['yeniParolaTekrar'] = 'Parolalar birbiriyle eşleşmiyor.';
        } elseif ($yeni === $mevcut) {
            $hatalar['parola']['yeniParola'] = 'Yeni parola mevcut parolayla aynı olamaz.';
        }

        if (count($hatalar['parola']) == 0) {
            $veri = ['adSoyad' => $hesap['adSoyad'], 'eposta' => $hesap['eposta'] ?? '', 'parola' => $yeni];
            if (hesapGuncelle($kullanicilar, $kullaniciAdi, $veri)) {
                mesajEkle('basari', 'Parolanız değiştirildi.');
            } else {
                mesajEkle('hata', 'Parola kaydedilemedi. veri klasörüne yazma izni olduğundan emin olun.');
            }
            header('Location: hesabim.php');
            exit;
        }

    /* ---------- DELETE: hesabı sil ---------- */
    } elseif ($islem == 'sil') {
        if ($silinemez) {
            $hatalar['sil']['genel'] = 'Sistemdeki tek yönetici olduğunuz için hesabınızı silemezsiniz.';
        } else {
            if (!password_verify($_POST['silParola'] ?? '', $hesap['parola'])) {
                $hatalar['sil']['silParola'] = 'Parolanız hatalı.';
            }
            if (!isset($_POST['silOnay'])) {
                $hatalar['sil']['silOnay'] = 'Devam etmek için kutucuğu işaretleyin.';
            }
        }

        if (count($hatalar['sil']) == 0) {
            if (hesapSil($kullanicilar, $kullaniciAdi)) {
                cikisYap();
                mesajEkle('bilgi', 'Hesabınız ve takip listeniz silindi. Görüşmek üzere!');
                header('Location: giris.php');
            } else {
                mesajEkle('hata', 'Hesap silinemedi. veri klasörüne yazma izni olduğundan emin olun.');
                header('Location: hesabim.php');
            }
            exit;
        }
    }
}

$bildirim     = mesajAl();
$takipSayisi  = count(kullaniciListesi($kullaniciAdi));
$sayfaBasligi = 'Hesabım';
$aktifMenu    = 'hesabim';
require 'header.php';
?>

<main class="dar hesap-sayfa">
    <p class="yol"><a href="index.php">Ana Sayfa</a> › Hesabım</p>
    <h1 class="sayfa-baslik">Hesabım</h1>
    <p class="sayfa-aciklama">Hesap bilgilerinizi görüntüleyin, güncelleyin veya hesabınızı silin.</p>

    <?php if ($bildirim !== null): ?>
        <p class="mesaj <?php echo $bildirim['tur']; ?>"><?php echo e($bildirim['metin']); ?></p>
    <?php endif; ?>

    <!-- ================= READ: hesap bilgileri ================= -->
    <section class="profil-kart">
        <span class="avatar buyuk" aria-hidden="true"><?php echo e(mb_strtoupper(mb_substr($hesap['adSoyad'], 0, 1))); ?></span>
        <div>
            <h2><?php echo e($hesap['adSoyad']); ?></h2>
            <dl class="bilgi-listesi">
                <dt>Kullanıcı adı</dt><dd><?php echo e($kullaniciAdi); ?></dd>
                <dt>E-posta</dt><dd><?php echo e($hesap['eposta'] ?? '—'); ?></dd>
                <dt>Rol</dt><dd><?php echo $hesap['rol'] == 'admin' ? 'Yönetici' : 'Kullanıcı'; ?></dd>
                <dt>Hesap oluşturma</dt><dd><?php echo isset($hesap['kayitTarihi']) ? eklenmeTarihi($hesap['kayitTarihi']) : '—'; ?></dd>
                <dt>Takip kaydı</dt><dd><a href="takip.php"><?php echo $takipSayisi; ?> kayıt</a></dd>
            </dl>
        </div>
    </section>

    <!-- ================= UPDATE: ad soyad ve e-posta ================= -->
    <h2 id="bilgiler">Bilgilerimi güncelle</h2>
    <form action="hesabim.php#bilgiler" method="post" class="form" novalidate>
        <?php echo csrfAlani(); ?>
        <input type="hidden" name="islem" value="bilgi">

        <div class="alan-satir">
            <div class="alan <?php echo isset($hatalar['bilgi']['adSoyad']) ? 'hatali' : ''; ?>">
                <label for="adSoyad">Ad soyad</label>
                <input type="text" id="adSoyad" name="adSoyad" maxlength="60" autocomplete="name"
                       value="<?php echo e($form['adSoyad']); ?>">
                <?php hesapAlanHatasi($hatalar['bilgi'], 'adSoyad'); ?>
            </div>
            <div class="alan <?php echo isset($hatalar['bilgi']['eposta']) ? 'hatali' : ''; ?>">
                <label for="eposta">E-posta</label>
                <input type="email" id="eposta" name="eposta" maxlength="100" autocomplete="email"
                       value="<?php echo e($form['eposta']); ?>">
                <?php hesapAlanHatasi($hatalar['bilgi'], 'eposta'); ?>
            </div>
        </div>
        <p class="form-not">Kullanıcı adı (<strong><?php echo e($kullaniciAdi); ?></strong>) değiştirilemez; takip listeniz bu ada bağlıdır.</p>
        <button type="submit" class="buton-ana">Bilgileri kaydet</button>
    </form>

    <!-- ================= UPDATE: parola ================= -->
    <h2 id="parola">Parolamı değiştir</h2>
    <form action="hesabim.php#parola" method="post" class="form" novalidate>
        <?php echo csrfAlani(); ?>
        <input type="hidden" name="islem" value="parola">

        <div class="alan <?php echo isset($hatalar['parola']['mevcutParola']) ? 'hatali' : ''; ?>">
            <label for="mevcutParola">Mevcut parola</label>
            <input type="password" id="mevcutParola" name="mevcutParola" autocomplete="current-password">
            <?php hesapAlanHatasi($hatalar['parola'], 'mevcutParola'); ?>
        </div>
        <div class="alan-satir">
            <div class="alan <?php echo isset($hatalar['parola']['yeniParola']) ? 'hatali' : ''; ?>">
                <label for="yeniParola">Yeni parola</label>
                <input type="password" id="yeniParola" name="yeniParola" autocomplete="new-password">
                <small>En az 8 karakter; en az bir harf ve bir rakam.</small>
                <?php hesapAlanHatasi($hatalar['parola'], 'yeniParola'); ?>
            </div>
            <div class="alan <?php echo isset($hatalar['parola']['yeniParolaTekrar']) ? 'hatali' : ''; ?>">
                <label for="yeniParolaTekrar">Yeni parola (tekrar)</label>
                <input type="password" id="yeniParolaTekrar" name="yeniParolaTekrar" autocomplete="new-password">
                <?php hesapAlanHatasi($hatalar['parola'], 'yeniParolaTekrar'); ?>
            </div>
        </div>
        <button type="submit" class="buton-ana">Parolayı değiştir</button>
    </form>

    <!-- ================= DELETE: hesabı sil ================= -->
    <h2 id="sil">Hesabı sil</h2>
    <form action="hesabim.php#sil" method="post" class="form tehlike-kutu" novalidate
          onsubmit="return confirm('Hesabınız ve takip listeniz kalıcı olarak silinecek. Emin misiniz?');">
        <?php echo csrfAlani(); ?>
        <input type="hidden" name="islem" value="sil">

        <p>Hesabınız ve takip listenizdeki <strong><?php echo $takipSayisi; ?> kayıt</strong> kalıcı olarak silinir. Bu işlem geri alınamaz.</p>

        <?php if (isset($hatalar['sil']['genel'])): ?>
            <p class="mesaj hata"><?php echo e($hatalar['sil']['genel']); ?></p>
        <?php endif; ?>

        <?php if ($silinemez): ?>
            <p class="mesaj uyari">Sistemdeki tek yönetici sizsiniz. Hesabınızı silmeden önce Yönetim panelinden başka bir hesabı yönetici yapın.</p>
        <?php else: ?>
            <div class="alan <?php echo isset($hatalar['sil']['silParola']) ? 'hatali' : ''; ?>">
                <label for="silParola">Onay için parolanız</label>
                <input type="password" id="silParola" name="silParola" autocomplete="current-password">
                <?php hesapAlanHatasi($hatalar['sil'], 'silParola'); ?>
            </div>
            <div class="alan <?php echo isset($hatalar['sil']['silOnay']) ? 'hatali' : ''; ?>">
                <label class="onay">
                    <input type="checkbox" name="silOnay" value="1">
                    <span>Hesabımın ve takip listemin kalıcı olarak silineceğini anlıyorum.</span>
                </label>
                <?php hesapAlanHatasi($hatalar['sil'], 'silOnay'); ?>
            </div>
            <button type="submit" class="buton-tehlike">Hesabımı sil</button>
        <?php endif; ?>
    </form>
</main>

<?php require 'footer.php'; ?>
