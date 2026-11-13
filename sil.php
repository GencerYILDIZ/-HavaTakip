<?php
/*
 * sil.php — Takip kaydı silme
 *
 * Yalnızca POST ile çalışır. Silme gibi veriyi değiştiren işlemler GET ile
 * (bir bağlantıyla) yapılmaz; aksi hâlde bir bağlantıya tıklamak veya
 * tarayıcının sayfayı önceden yüklemesi kaydı silebilirdi.
 *
 * Gelen alanlar:
 *   id        : silinecek kaydın numarası
 *   kullanici : (yalnızca admin) başka bir kullanıcının kaydını silmek için
 *   donus     : 'takip' veya 'yonetim' — işlemden sonra hangi sayfaya dönülecek
 *   kategori, durum : listedeki filtreler korunsun diye
 */

require_once 'baslat.php';
girisGerekli();

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: takip.php');
    exit;
}

/* ---------- Dönüş adresi (filtreler korunur) ---------- */
$donus = ($_POST['donus'] ?? '') == 'yonetim' && adminMi() ? 'yonetim.php' : 'takip.php';
$filtre = [];
foreach (['kategori', 'durum', 'kullanici'] as $alan) {
    if (!empty($_POST['filtre_' . $alan])) {
        $filtre[$alan] = $_POST['filtre_' . $alan];
    }
}
$donusAdresi = $donus . (count($filtre) > 0 ? '?' . http_build_query($filtre) : '');

/* ---------- Kontroller ---------- */
if (!csrfGecerliMi()) {
    mesajEkle('hata', 'İstek doğrulanamadı. Lütfen sayfayı yenileyip tekrar deneyin.');
    header('Location: ' . $donusAdresi);
    exit;
}

$id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);

// Normal kullanıcı yalnızca kendi kaydını silebilir; admin başkasınınkini de silebilir
$hedefKullanici = aktifKullanici()['kullaniciAdi'];
if (adminMi() && isset($_POST['kullanici']) && isset($kullanicilar[$_POST['kullanici']])) {
    $hedefKullanici = $_POST['kullanici'];
}

if ($id === false) {
    mesajEkle('hata', 'Geçersiz kayıt numarası.');
} else {
    $silinen = takipSil($hedefKullanici, $id);
    if ($silinen === null) {
        mesajEkle('hata', 'Silinecek kayıt bulunamadı. Daha önce silinmiş olabilir.');
    } else {
        mesajEkle('basari', '"' . $silinen['baslik'] . '" takip listesinden silindi.');
    }
}

header('Location: ' . $donusAdresi);
exit;
