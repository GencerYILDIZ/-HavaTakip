<?php
/*
 * durum.php — Takip kaydının durumunu değiştirme (Aktif ⇄ Duraklatıldı)
 *
 * Takip Listem'deki her kartın altında iki düğme vardır: "Aktif" ve "Duraklatıldı".
 * Kayıt o anki durumundayken o düğme seçili görünür; diğerine basınca bu sayfa çalışır.
 *
 * Yalnızca POST ile çalışır (veriyi değiştiren bir işlem olduğu için) ve giriş gerektirir.
 * Gelen alanlar:
 *   id    : kaydın numarası
 *   durum : yeni durum ('aktif' veya 'pasif')
 *   filtre_kategori, filtre_durum : listedeki filtreler korunsun diye
 *   csrf  : form güvenlik anahtarı
 * Her kullanıcı yalnızca kendi kaydının durumunu değiştirebilir.
 */

require_once 'baslat.php';
girisGerekli();

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: takip.php');
    exit;
}

/* ---------- Dönüş adresi: filtreler korunur, sayfa aynı karta kayar ---------- */
$filtre = [];
foreach (['kategori', 'durum'] as $alan) {
    if (!empty($_POST['filtre_' . $alan])) {
        $filtre[$alan] = $_POST['filtre_' . $alan];
    }
}
$id          = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
$donusAdresi = 'takip.php' . (count($filtre) > 0 ? '?' . http_build_query($filtre) : '')
             . ($id !== false ? '#takip-' . $id : '');

/* ---------- Kontroller ---------- */
if (!csrfGecerliMi()) {
    mesajEkle('hata', 'İstek doğrulanamadı. Lütfen sayfayı yenileyip tekrar deneyin.');
    header('Location: ' . $donusAdresi);
    exit;
}

$kullaniciAdi = aktifKullanici()['kullaniciAdi'];
$yeniDurum    = $_POST['durum'] ?? '';

if ($id === false) {
    mesajEkle('hata', 'Geçersiz kayıt numarası.');
} elseif (!isset($durumlar[$yeniDurum])) {
    mesajEkle('hata', 'Geçersiz bir durum seçildi.');
} else {
    $sonuc = takipDurumDegistir($kullaniciAdi, $id, $yeniDurum);
    if ($sonuc === null) {
        mesajEkle('hata', 'Kayıt bulunamadı. Silinmiş olabilir.');
    } elseif ($sonuc === 'uyari-yok') {
        mesajEkle('uyari', 'Bu kaydı aktif etmek için önce en az bir uyarı türü seçili olmalıdır.');
    } else {
        $baslik = $_SESSION['listeler'][$kullaniciAdi][$id]['baslik'];
        mesajEkle('basari', ($yeniDurum == 'aktif')
            ? '"' . $baslik . '" yeniden aktif edildi.'
            : '"' . $baslik . '" duraklatıldı.');
    }
}

header('Location: ' . $donusAdresi);
exit;
