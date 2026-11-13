<?php
/*
 * kayit.php — Yeni hesap oluşturma (kayıt ol)
 *
 * GET : kayıt formunu gösterir.
 * POST: bilgiler hesapDogrula() ile kontrol edilir (fonksiyonlar/hesap.php; admin'in yeni hesap formuyla aynı kurallar):
 *       - ad soyad 3–50 karakter, yalnızca harf ve boşluk
 *       - kullanıcı adı 3–20 karakter (a-z, 0-9, _), benzersiz
 *       - e-posta geçerli ve başka hesapta kullanılmıyor
 *       - parola en az 8 karakter, en az bir harf ve bir rakam; tekrarıyla aynı
 *       Hata varsa form, girilen değerler (parola hariç) ve alan bazında hata mesajlarıyla tekrar gösterilir.
 *       Hata yoksa hesap "kullanıcı" rolüyle veri/kullanicilar.json'a eklenir, kullanıcı otomatik olarak
 *       giriş yapar ve Takip Listem sayfasına yönlendirilir.
 *
 * Kayıt formundan yönetici hesabı açılamaz; rol her zaman "kullanici"dır.
 */

require_once 'baslat.php';

// Zaten giriş yapılmışsa kayıt formu gösterilmez
if (girisYapildiMi()) {
    header('Location: takip.php');
    exit;
}

$hatalar = [];
$form    = ['adSoyad' => '', 'kullaniciAdi' => '', 'eposta' => ''];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $girdi        = $_POST;
    $girdi['rol'] = 'kullanici';   // formdan rol gelse bile dikkate alınmaz
    $sonuc   = hesapDogrula($girdi, $kullanicilar, null, false);
    $hatalar = $sonuc['hatalar'];

    if (!csrfGecerliMi()) {
        $hatalar['genel'] = 'Form süresi dolmuş olabilir. Lütfen tekrar gönderin.';
    }
    if (empty($_POST['kosul'])) {
        $hatalar['kosul'] = 'Devam etmek için bilgilerinizin bu sitede saklanmasını onaylayın.';
    }

    if (count($hatalar) == 0) {
        $veri        = $sonuc['veri'];
        $veri['rol'] = 'kullanici';
        if (hesapEkle($kullanicilar, $veri)) {
            // BAŞARILI: otomatik giriş, boş takip listesi, Takip Listem'e yönlendirme
            girisYap($veri['kullaniciAdi'], $veri['parola'], $kullanicilar);
            ornekKayitlariYukle($veri['kullaniciAdi'], $ornekTakipler);
            mesajEkle('basari', 'Hesabınız oluşturuldu. Hoş geldiniz, ' . $veri['adSoyad'] . '.');
            header('Location: takip.php');
            exit;
        }
        $hatalar['genel'] = 'Hesap kaydedilemedi. veri klasörüne yazma izni olduğundan emin olun.';
    }

    // BAŞARISIZ: girilen değerler formda kalır (parolalar hariç)
    $form = [
        'adSoyad'      => $_POST['adSoyad'] ?? '',
        'kullaniciAdi' => $_POST['kullaniciAdi'] ?? '',
        'eposta'       => $_POST['eposta'] ?? '',
    ];
}

$sayfaBasligi = 'Kayıt Ol';
$aktifMenu    = 'kayit';
require 'header.php';
?>

<!-- Kayıt sayfasında görsel bölüm solda, form sağda (giriş sayfasının tersi).
     Sekmeden gelindiyse (?gecis=1) görsel bölüm sağdan sola kayarak yerine gelir. -->
<main class="kimlik-sayfa">
    <div class="kimlik-kart<?php echo isset($_GET['gecis']) ? ' gecis' : ''; ?>">
        <?php require 'kimlik-yan.php'; ?>

        <section class="kimlik-form">
            <!-- Giriş / Kayıt sekmeleri -->
            <nav class="kimlik-sekme" aria-label="Hesap">
                <a href="giris.php?gecis=1">Giriş Yap</a>
                <a href="kayit.php" class="secili" aria-current="page">Kayıt Ol</a>
            </nav>

            <h1>Hesap oluşturun</h1>
            <p class="kimlik-aciklama">Birkaç bilgiyle hesabınızı açın; takip listenizi hemen oluşturmaya başlayın.</p>

            <?php if (isset($hatalar['genel'])): ?>
                <p class="mesaj hata"><?php echo e($hatalar['genel']); ?></p>
            <?php elseif (count($hatalar) > 0): ?>
                <p class="mesaj hata">Hesap oluşturulamadı. Lütfen işaretli <?php echo count($hatalar); ?> alanı düzeltin.</p>
            <?php endif; ?>

            <form action="kayit.php" method="post" class="form kimlik" novalidate>
                <?php echo csrfAlani(); ?>

                <div class="alan <?php echo isset($hatalar['adSoyad']) ? 'hatali' : ''; ?>">
                    <label for="adSoyad">Ad soyad</label>
                    <div class="girdi-ikon">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2.5"/><circle cx="9" cy="11" r="2.2"/><path d="M5.8 16a3.4 3.4 0 0 1 6.4 0M14 10h4M14 13.5h4"/></svg>
                        <input type="text" id="adSoyad" name="adSoyad" maxlength="50" autocomplete="name"
                               placeholder="ör. Ayşe Yılmaz" value="<?php echo e($form['adSoyad']); ?>" autofocus>
                    </div>
                    <?php hesapAlanHatasi($hatalar, 'adSoyad'); ?>
                </div>

                <div class="alan-satir">
                    <div class="alan <?php echo isset($hatalar['kullaniciAdi']) ? 'hatali' : ''; ?>">
                        <label for="kullaniciAdi">Kullanıcı adı</label>
                        <div class="girdi-ikon">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
                            <input type="text" id="kullaniciAdi" name="kullaniciAdi" maxlength="20" autocomplete="username"
                                   placeholder="ör. ayse_y" value="<?php echo e($form['kullaniciAdi']); ?>">
                        </div>
                        <small>3–20 karakter: a-z, 0-9 ve _</small>
                        <?php hesapAlanHatasi($hatalar, 'kullaniciAdi'); ?>
                    </div>

                    <div class="alan <?php echo isset($hatalar['eposta']) ? 'hatali' : ''; ?>">
                        <label for="eposta">E-posta</label>
                        <div class="girdi-ikon">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2.5"/><path d="M3.5 6.5l8.5 6.5 8.5-6.5"/></svg>
                            <input type="email" id="eposta" name="eposta" maxlength="80" autocomplete="email"
                                   placeholder="ad@ornek.com" value="<?php echo e($form['eposta']); ?>">
                        </div>
                        <?php hesapAlanHatasi($hatalar, 'eposta'); ?>
                    </div>
                </div>

                <div class="alan-satir">
                    <div class="alan <?php echo isset($hatalar['parola']) ? 'hatali' : ''; ?>">
                        <label for="parola">Parola</label>
                        <div class="girdi-ikon">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="10" width="16" height="11" rx="2.5"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
                            <input type="password" id="parola" name="parola" autocomplete="new-password" placeholder="En az 8 karakter">
                            <button type="button" class="parola-goster" title="Parolayı göster" aria-label="Parolayı göster" hidden>
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                        <!-- Parola gücü çubuğu (footer.php'deki küçük JavaScript doldurur; asıl kontrol PHP'dedir) -->
                        <div class="parola-guc" data-guc="0" hidden><span></span><span></span><span></span><span></span></div>
                        <small>En az 8 karakter; en az bir harf ve bir rakam.</small>
                        <?php hesapAlanHatasi($hatalar, 'parola'); ?>
                    </div>

                    <div class="alan <?php echo isset($hatalar['parolaTekrar']) ? 'hatali' : ''; ?>">
                        <label for="parolaTekrar">Parola (tekrar)</label>
                        <div class="girdi-ikon">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="10" width="16" height="11" rx="2.5"/><path d="M8 10V7a4 4 0 0 1 8 0v3M9.5 15.5l1.8 1.8 3.3-3.3"/></svg>
                            <input type="password" id="parolaTekrar" name="parolaTekrar" autocomplete="new-password" placeholder="Parolayı tekrar yazın">
                        </div>
                        <?php hesapAlanHatasi($hatalar, 'parolaTekrar'); ?>
                    </div>
                </div>

                <div class="alan <?php echo isset($hatalar['kosul']) ? 'hatali' : ''; ?>">
                    <label class="onay">
                        <input type="checkbox" name="kosul" value="1" <?php echo !empty($_POST['kosul']) ? 'checked' : ''; ?>>
                        <span>Bilgilerimin (ad soyad, e-posta, takip listem) bu sitede saklanmasını onaylıyorum.</span>
                    </label>
                    <?php hesapAlanHatasi($hatalar, 'kosul'); ?>
                </div>

                <button type="submit" class="buton-ana tam">Hesabımı oluştur</button>
            </form>

            <p class="kimlik-alt">Zaten hesabınız var mı? <a href="giris.php?gecis=1">Giriş yapın →</a></p>
        </section>
    </div>
</main>

<?php require 'footer.php'; ?>
