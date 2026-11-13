<?php
/*
 * hesap-sil.php — Bir hesabı silme (yalnızca admin, Yönetim panelinden)
 *
 * Gelen alan (POST): k = silinecek hesabın kullanıcı adı, csrf = form güvenlik anahtarı
 *
 * - Hesap ve bu oturumdaki takip listesi silinir.
 * - Admin kendi hesabını buradan silemez; bunun için "Hesabım" sayfası kullanılır.
 * - Sistemdeki son yönetici silinemez.
 */

require_once 'baslat.php';
adminGerekli();

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: yonetim.php#hesaplar');
    exit;
}

$hedef = (string) ($_POST['k'] ?? '');

if (!csrfGecerliMi()) {
    mesajEkle('hata', 'İstek doğrulanamadı. Lütfen sayfayı yenileyip tekrar deneyin.');
} elseif (!isset($kullanicilar[$hedef])) {
    mesajEkle('hata', 'Silinecek hesap bulunamadı. Daha önce silinmiş olabilir.');
} elseif ($hedef === aktifKullanici()['kullaniciAdi']) {
    mesajEkle('uyari', 'Kendi hesabınızı "Hesabım" sayfasından silebilirsiniz.');
} elseif ($kullanicilar[$hedef]['rol'] == 'admin' && adminSayisi($kullanicilar) <= 1) {
    mesajEkle('hata', 'Sistemdeki son yönetici hesabı silinemez.');
} elseif (hesapSil($kullanicilar, $hedef)) {
    mesajEkle('basari', '"' . $hedef . '" hesabı ve takip listesi silindi.');
} else {
    mesajEkle('hata', 'Hesap silinemedi. veri klasörüne yazma izni olduğundan emin olun.');
}

header('Location: yonetim.php#hesaplar');
exit;
