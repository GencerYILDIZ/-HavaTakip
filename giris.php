<?php
/*
 * giris.php — Kullanıcı girişi
 *
 * GET : giriş formunu gösterir. giris.php?geri=takip.php → girişten sonra o sayfaya dönülür.
 * POST: kullanıcı adı ve parolayı kontrol eder.
 *       - Boş alan     → alanın altında hata mesajı
 *       - Hatalı bilgi → "kullanıcı adı veya parola hatalı" (hangisinin yanlış olduğu söylenmez)
 *       - 5 hatalı denemeden sonra 60 saniye bekleme
 *       - Başarılı     → kullanıcının takip listesi hazırlanır, geldiği sayfaya yönlendirilir
 */

require_once 'baslat.php';

$geri = guvenliDonus($_GET['geri'] ?? $_POST['geri'] ?? '', 'takip.php');

// Zaten giriş yapılmışsa formu gösterme
if (girisYapildiMi()) {
    header('Location: ' . $geri);
    exit;
}

$hatalar      = [];
$kullaniciAdi = '';

const EN_FAZLA_DENEME = 5;
const BEKLEME_SURESI  = 60; // saniye

// Kilit süresi devam ediyor mu?
$kalanSure = ($_SESSION['girisKilidi'] ?? 0) - time();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kullaniciAdi = trim($_POST['kullaniciAdi'] ?? '');
    $parola       = $_POST['parola'] ?? '';   // parolada boşluk olabilir, trim yapılmaz

    if ($kalanSure > 0) {
        $hatalar['genel'] = 'Çok fazla hatalı deneme yaptınız. Lütfen ' . $kalanSure . ' saniye sonra tekrar deneyin.';
    } else {
        // 1) Zorunlu alanlar
        if ($kullaniciAdi === '') {
            $hatalar['kullaniciAdi'] = 'Kullanıcı adı boş bırakılamaz.';
        }
        if ($parola === '') {
            $hatalar['parola'] = 'Parola boş bırakılamaz.';
        }

        // 2) Bilgiler doğru mu?
        if (count($hatalar) == 0) {
            if (girisYap($kullaniciAdi, $parola, $kullanicilar)) {
                // BAŞARILI
                unset($_SESSION['hataliGiris'], $_SESSION['girisKilidi']);
                ornekKayitlariYukle($kullaniciAdi, $ornekTakipler);  // ilk girişte örnek kayıtlar
                mesajEkle('basari', 'Hoş geldiniz, ' . aktifKullanici()['adSoyad'] . '.');
                header('Location: ' . $geri);
                exit;
            }

            // BAŞARISIZ: deneme sayısını artır
            $_SESSION['hataliGiris'] = ($_SESSION['hataliGiris'] ?? 0) + 1;
            $kalanHak = EN_FAZLA_DENEME - $_SESSION['hataliGiris'];

            if ($kalanHak <= 0) {
                $_SESSION['girisKilidi'] = time() + BEKLEME_SURESI;
                $_SESSION['hataliGiris'] = 0;
                $hatalar['genel'] = 'Çok fazla hatalı deneme yaptınız. Lütfen ' . BEKLEME_SURESI . ' saniye sonra tekrar deneyin.';
            } else {
                $hatalar['genel'] = 'Kullanıcı adı veya parola hatalı. (Kalan deneme hakkı: ' . $kalanHak . ')';
            }
        }
    }
}

$bildirim     = mesajAl();
$sayfaBasligi = 'Giriş Yap';
$aktifMenu    = 'giris';
require 'header.php';
?>

<!-- Giriş sayfasında form solda, görsel bölüm sağda (kayıt sayfasının tersi).
     Sekmeden gelindiyse (?gecis=1) görsel bölüm soldan sağa kayarak yerine gelir. -->
<main class="kimlik-sayfa">
    <div class="kimlik-kart ters<?php echo isset($_GET['gecis']) ? ' gecis' : ''; ?>">
        <?php require 'kimlik-yan.php'; ?>

        <section class="kimlik-form">
            <!-- Giriş / Kayıt sekmeleri -->
            <nav class="kimlik-sekme" aria-label="Hesap">
                <a href="giris.php" class="secili" aria-current="page">Giriş Yap</a>
                <a href="kayit.php?gecis=1">Kayıt Ol</a>
            </nav>

            <h1>Tekrar hoş geldiniz</h1>
            <p class="kimlik-aciklama">Takip listenizi görmek ve yönetmek için hesabınıza giriş yapın.</p>

            <?php if ($bildirim !== null): ?>
                <p class="mesaj <?php echo $bildirim['tur']; ?>"><?php echo e($bildirim['metin']); ?></p>
            <?php endif; ?>

            <?php if (isset($hatalar['genel'])): ?>
                <p class="mesaj hata"><?php echo e($hatalar['genel']); ?></p>
            <?php endif; ?>

            <form action="giris.php" method="post" class="form kimlik" novalidate>
                <input type="hidden" name="geri" value="<?php echo e($geri); ?>">

                <div class="alan <?php echo isset($hatalar['kullaniciAdi']) ? 'hatali' : ''; ?>">
                    <label for="kullaniciAdi">Kullanıcı adı</label>
                    <div class="girdi-ikon">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
                        <input type="text" id="kullaniciAdi" name="kullaniciAdi" autocomplete="username"
                               placeholder="ör. kullanici" value="<?php echo e($kullaniciAdi); ?>" autofocus>
                    </div>
                    <?php if (isset($hatalar['kullaniciAdi'])): ?>
                        <p class="alan-hata"><?php echo e($hatalar['kullaniciAdi']); ?></p>
                    <?php endif; ?>
                </div>

                <div class="alan <?php echo isset($hatalar['parola']) ? 'hatali' : ''; ?>">
                    <label for="parola">Parola</label>
                    <!-- Güvenlik için parola hatalı girişten sonra formda geri gösterilmez -->
                    <div class="girdi-ikon">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="10" width="16" height="11" rx="2.5"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
                        <input type="password" id="parola" name="parola" autocomplete="current-password" placeholder="••••••••">
                        <button type="button" class="parola-goster" title="Parolayı göster" aria-label="Parolayı göster" hidden>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                    <?php if (isset($hatalar['parola'])): ?>
                        <p class="alan-hata"><?php echo e($hatalar['parola']); ?></p>
                    <?php endif; ?>
                </div>

                <button type="submit" class="buton-ana tam">Giriş yap</button>
            </form>

            <p class="kimlik-alt">Hesabınız yok mu? <a href="kayit.php?gecis=1">Hemen kayıt olun →</a></p>
            <p class="giris-not">Test hesaplarının bilgileri proje klasöründeki <strong>README.md</strong> dosyasındadır.</p>
        </section>
    </div>
</main>

<?php require 'footer.php'; ?>
