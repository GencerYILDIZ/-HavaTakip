<?php
/*
 * bildirim.php — Navbar'daki bildirim panelinin işlemleri
 *
 * Gelen alanlar (POST):
 *   islem : 'sil'     → tek bir bildirimi kaldırır (bildirimin sağındaki × düğmesi)
 *           'temizle' → tüm bildirimleri kaldırır ("Tümünü temizle" düğmesi)
 *           'okundu'  → tüm bildirimleri okundu yapar (zile tıklanınca sayfadaki küçük JavaScript
 *                       arka planda gönderir; sayfa yenilenmez, zilin üstündeki sayı kaybolur)
 *   id    : (yalnızca 'sil' için) kaldırılacak bildirimin numarası
 *   donus : işlemden sonra dönülecek sayfa (ör. il.php?il=ankara)
 *   csrf  : form güvenlik anahtarı
 *
 * Veriyi değiştiren bir işlem olduğu için yalnızca POST ile çalışır ve giriş gerektirir.
 * Her kullanıcı yalnızca kendi bildirimlerini kaldırabilir.
 * İşlemden sonra kullanıcı geldiği sayfaya döner ve bildirim paneli açık kalır.
 */

require_once 'baslat.php';
girisGerekli();

// guvenliDonus(): yalnızca kendi sayfalarımıza dönüşe izin verir
$geri = guvenliDonus($_POST['donus'] ?? '');

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: ' . $geri);
    exit;
}

if (!csrfGecerliMi()) {
    mesajEkle('hata', 'İstek doğrulanamadı. Lütfen sayfayı yenileyip tekrar deneyin.');
    header('Location: ' . $geri);
    exit;
}

$kullaniciAdi = aktifKullanici()['kullaniciAdi'];
if (!isset($_SESSION['bildirimMerkezi'][$kullaniciAdi])) {
    $_SESSION['bildirimMerkezi'][$kullaniciAdi] = bildirimMerkeziBos();
}

$islem = $_POST['islem'] ?? '';
if ($islem == 'sil') {
    bildirimSil($_SESSION['bildirimMerkezi'][$kullaniciAdi], (string) ($_POST['id'] ?? ''));
} elseif ($islem == 'temizle') {
    bildirimleriTemizle($_SESSION['bildirimMerkezi'][$kullaniciAdi]);
} elseif ($islem == 'okundu') {
    bildirimleriOkunduYap($_SESSION['bildirimMerkezi'][$kullaniciAdi]);
    http_response_code(204);   // arka plan isteği: yanıt gövdesi yok, yönlendirme yok
    exit;
}

// Dönülen sayfada panel açık gelsin (tek seferlik)
$_SESSION['bildirimMerkezi'][$kullaniciAdi]['panelAcik'] = true;

header('Location: ' . $geri);
exit;
