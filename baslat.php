<?php
/*
 * baslat.php — Her sayfanın EN BAŞINDA dahil edilir.
 *
 * 1. Saat dilimini ayarlar.
 * 2. Oturumu (session) başlatır. session_start() sayfaya herhangi bir çıktı
 *    gönderilmeden önce çağrılmalıdır; bu yüzden en başta.
 * 3. Veri ve fonksiyon dosyalarını dahil eder.
 *
 * Takip kayıtları $_SESSION içinde, her kullanıcı için ayrı tutulur ve
 * kullanıcı ilk giriş yaptığında örnek kayıtlar yüklenir (giris.php).
 * Sayfa yenilendiğinde kayıtlar kaybolmaz; tarayıcı kapanınca veya oturum
 * süresi dolunca sıfırlanır. Kalıcı saklama sonraki dönemde MySQL ile yapılacak.
 */

date_default_timezone_set('Europe/Istanbul');

// Oturum çerezinin JavaScript ile okunmasını engelle
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();

require_once __DIR__ . '/data.php';
require_once __DIR__ . '/functions.php';

// Hesaplar: veri/kullanicilar.json varsa oradan, yoksa data.php'deki başlangıç hesaplarından okunur.
$kullanicilar = hesaplariOku($kullanicilar);
// Oturumdaki kullanıcı silinmişse çıkış yaptırılır; adı/rolü değiştiyse güncellenir.
oturumuTazele($kullanicilar);

// Dört mevsim teması: bugünün ayına göre (veya ?mevsim= önizlemesine göre) seçilir.
$mevsim = aktifMevsim();

// Dil: İngilizce seçildiyse sayfa çıktısı gönderilmeden önce çevrilir (bkz. fonksiyonlar/dil.php)
$dil = aktifDil();
if ($dil == 'en') {
    ob_start('sayfayiCevir');
}
