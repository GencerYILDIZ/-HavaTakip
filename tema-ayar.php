<?php
/*
 * tema-ayar.php — Site mevsim temasını kaydeder (yalnızca admin, Yönetim panelinden)
 *
 * Gelen alan (POST): mevsim = 'oto' | 'ilkbahar' | 'yaz' | 'sonbahar' | 'kis', csrf
 * Seçim veri/ayarlar.json dosyasına yazılır ve tüm ziyaretçiler için geçerli olur.
 * 'oto' seçilirse tema bugünün ayına göre kendiliğinden değişir.
 */

require_once 'baslat.php';
adminGerekli();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $secim = $_POST['mevsim'] ?? '';

    if (!csrfGecerliMi()) {
        mesajEkle('hata', 'İstek doğrulanamadı. Lütfen sayfayı yenileyip tekrar deneyin.');
    } elseif ($secim != 'oto' && !isset(MEVSIMLER[$secim])) {
        mesajEkle('hata', 'Geçersiz bir tema seçildi.');
    } else {
        $ayarlar = ayarlariOku();
        $ayarlar['mevsim'] = $secim;
        if (ayarlariKaydet($ayarlar)) {
            $yeni = ($secim == 'oto') ? ayaGoreMevsim((int) date('n')) : $secim;
            mesajEkle('basari', 'Site teması güncellendi: ' . MEVSIMLER[$yeni]
                . ($secim == 'oto' ? ' (aya göre otomatik).' : ' (sabit).'));
        } else {
            mesajEkle('hata', 'Tema kaydedilemedi. veri klasörüne yazma izni olduğundan emin olun.');
        }
    }
}

header('Location: yonetim.php#site-temasi');
exit;
