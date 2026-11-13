<?php
/*
 * hesap-duzenle.php — Hesap ekleme ve düzenleme (yalnızca admin)
 *
 * hesap-duzenle.php          → CREATE: yeni hesap formu
 * hesap-duzenle.php?k=ayse   → UPDATE: "ayse" hesabının ad soyad, e-posta, rol ve parolası
 *
 * Kurallar:
 *  - Kullanıcı adı yalnızca yeni hesapta seçilir, sonra değiştirilemez.
 *  - Düzenlemede parola alanı boş bırakılırsa parola değişmez.
 *  - Sistemde en az bir yönetici kalmalıdır (son yöneticinin rolü değiştirilemez).
 */

require_once 'baslat.php';
adminGerekli();

$hedef = (string) ($_GET['k'] ?? $_POST['k'] ?? '');
$yeni  = ($hedef === '');

if (!$yeni && !isset($kullanicilar[$hedef])) {
    mesajEkle('hata', 'Düzenlenecek hesap bulunamadı. Silinmiş olabilir.');
    header('Location: yonetim.php#hesaplar');
    exit;
}

$hatalar = [];
$form = $yeni
    ? ['kullaniciAdi' => '', 'adSoyad' => '', 'eposta' => '', 'rol' => 'kullanici']
    : ['kullaniciAdi' => $hedef,
       'adSoyad'      => $kullanicilar[$hedef]['adSoyad'],
       'eposta'       => $kullanicilar[$hedef]['eposta'] ?? '',
       'rol'          => $kullanicilar[$hedef]['rol']];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sonuc = hesapDogrula($_POST, $kullanicilar, $yeni ? null : $hedef, true);
    if (!csrfGecerliMi()) {
        $sonuc['hatalar']['genel'] = 'Form süresi dolmuş olabilir. Lütfen tekrar gönderin.';
    }

    if (count($sonuc['hatalar']) == 0) {
        $veri = $sonuc['veri'];
        if ($yeni) {
            $kaydedildi = hesapEkle($kullanicilar, $veri);
            $metin      = '"' . $veri['kullaniciAdi'] . '" hesabı oluşturuldu.';
        } else {
            $kaydedildi = hesapGuncelle($kullanicilar, $hedef, $veri, true);
            $metin      = '"' . $hedef . '" hesabı güncellendi.';
        }

        if ($kaydedildi) {
            mesajEkle('basari', $metin);
            // Admin kendi rolünü "kullanıcı" yaptıysa artık Yönetim'i göremez → Hesabım'a gönder
            $kendiRolunuBirakti = (!$yeni && $hedef === aktifKullanici()['kullaniciAdi'] && $veri['rol'] != 'admin');
            header('Location: ' . ($kendiRolunuBirakti ? 'hesabim.php' : 'yonetim.php#hesaplar'));
        } else {
            mesajEkle('hata', 'Hesap kaydedilemedi. veri klasörüne yazma izni olduğundan emin olun.');
            header('Location: yonetim.php#hesaplar');
        }
        exit;
    }

    // BAŞARISIZ: hatalar gösterilir, girilen değerler formda kalır (parola hariç)
    $hatalar = $sonuc['hatalar'];
    $form = [
        'kullaniciAdi' => $yeni ? ($_POST['kullaniciAdi'] ?? '') : $hedef,
        'adSoyad'      => $_POST['adSoyad'] ?? '',
        'eposta'       => $_POST['eposta'] ?? '',
        'rol'          => $_POST['rol'] ?? 'kullanici',
    ];
}

$sayfaBasligi = $yeni ? 'Yeni Hesap' : 'Hesabı Düzenle';
$aktifMenu    = 'yonetim';
require 'header.php';
?>

<main class="dar">
    <p class="yol"><a href="index.php">Ana Sayfa</a> › <a href="yonetim.php">Yönetim</a> › <?php echo $yeni ? 'Yeni hesap' : 'Hesabı düzenle'; ?></p>
    <h1 class="sayfa-baslik"><?php echo $yeni ? 'Yeni hesap oluştur' : '"' . e($hedef) . '" hesabını düzenle'; ?></h1>
    <p class="sayfa-aciklama">
        <?php echo $yeni ? 'Yeni bir kullanıcı veya yönetici hesabı açın.' : 'Ad soyad, e-posta, rol ve parolayı değiştirebilirsiniz.'; ?>
    </p>

    <?php if (count($hatalar) > 0): ?>
        <p class="mesaj hata">
            <?php echo isset($hatalar['genel']) ? e($hatalar['genel']) : 'Hesap kaydedilemedi. Lütfen işaretli ' . count($hatalar) . ' alanı düzeltin.'; ?>
        </p>
    <?php endif; ?>

    <form action="hesap-duzenle.php<?php echo $yeni ? '' : '?k=' . urlencode($hedef); ?>" method="post" class="form" novalidate>
        <?php echo csrfAlani(); ?>

        <div class="alan-satir">
            <div class="alan <?php echo isset($hatalar['kullaniciAdi']) ? 'hatali' : ''; ?>">
                <label for="kullaniciAdi">Kullanıcı adı</label>
                <?php if ($yeni): ?>
                    <input type="text" id="kullaniciAdi" name="kullaniciAdi" maxlength="20" autocomplete="off"
                           value="<?php echo e($form['kullaniciAdi']); ?>">
                    <small>3–20 karakter: a-z, 0-9 ve _ (sonradan değiştirilemez)</small>
                <?php else: ?>
                    <input type="text" id="kullaniciAdi" value="<?php echo e($hedef); ?>" disabled>
                    <small>Kullanıcı adı değiştirilemez.</small>
                <?php endif; ?>
                <?php hesapAlanHatasi($hatalar, 'kullaniciAdi'); ?>
            </div>
            <div class="alan <?php echo isset($hatalar['adSoyad']) ? 'hatali' : ''; ?>">
                <label for="adSoyad">Ad soyad</label>
                <input type="text" id="adSoyad" name="adSoyad" maxlength="60" value="<?php echo e($form['adSoyad']); ?>">
                <?php hesapAlanHatasi($hatalar, 'adSoyad'); ?>
            </div>
        </div>

        <div class="alan-satir">
            <div class="alan <?php echo isset($hatalar['eposta']) ? 'hatali' : ''; ?>">
                <label for="eposta">E-posta</label>
                <input type="email" id="eposta" name="eposta" maxlength="100" value="<?php echo e($form['eposta']); ?>">
                <?php hesapAlanHatasi($hatalar, 'eposta'); ?>
            </div>
            <div class="alan <?php echo isset($hatalar['rol']) ? 'hatali' : ''; ?>">
                <label for="rol">Rol</label>
                <select id="rol" name="rol">
                    <option value="kullanici" <?php echo ($form['rol'] == 'kullanici') ? 'selected' : ''; ?>>Kullanıcı</option>
                    <option value="admin" <?php echo ($form['rol'] == 'admin') ? 'selected' : ''; ?>>Yönetici</option>
                </select>
                <?php hesapAlanHatasi($hatalar, 'rol'); ?>
            </div>
        </div>

        <div class="alan-satir">
            <div class="alan <?php echo isset($hatalar['parola']) ? 'hatali' : ''; ?>">
                <label for="parola"><?php echo $yeni ? 'Parola' : 'Yeni parola'; ?>
                    <?php if (!$yeni): ?><span class="istege-bagli">(isteğe bağlı)</span><?php endif; ?></label>
                <input type="password" id="parola" name="parola" autocomplete="new-password">
                <small><?php echo $yeni ? 'En az 8 karakter; en az bir harf ve bir rakam.' : 'Boş bırakılırsa parola değişmez.'; ?></small>
                <?php hesapAlanHatasi($hatalar, 'parola'); ?>
            </div>
            <div class="alan <?php echo isset($hatalar['parolaTekrar']) ? 'hatali' : ''; ?>">
                <label for="parolaTekrar"><?php echo $yeni ? 'Parola' : 'Yeni parola'; ?> (tekrar)</label>
                <input type="password" id="parolaTekrar" name="parolaTekrar" autocomplete="new-password">
                <?php hesapAlanHatasi($hatalar, 'parolaTekrar'); ?>
            </div>
        </div>

        <div class="form-butonlar">
            <button type="submit" class="buton-ana"><?php echo $yeni ? 'Hesabı oluştur' : 'Değişiklikleri kaydet'; ?></button>
            <a href="yonetim.php#hesaplar" class="buton-ikincil">Vazgeç</a>
        </div>
    </form>
</main>

<?php require 'footer.php'; ?>
