<?php
/*
 * ilce-kontrol.php — İlçe koordinatlarını toplu bulma ve kontrol aracı (yalnızca admin)
 *
 * Neden var?
 *   İlçe koordinatları ilk kez açıldıklarında Open-Meteo Geocoding API'den bulunur.
 *   Bu sayfa 973 ilçeyi 50'şerli gruplar hâlinde sorar, bulunanları
 *   veri/ilce-konumlari.json dosyasına kaydeder ve bulunamayanları listeler.
 *   Bir kez çalıştırıldıktan sonra ilçe sayfaları API'ye adres sormadan açılır.
 *
 * Kullanım: admin olarak giriş yapıp ilce-kontrol.php adresini açın,
 *           "Sonraki 50 ilçe" bağlantısıyla sonuna kadar ilerleyin.
 */

require_once 'baslat.php';
adminGerekli();
set_time_limit(120);   // 50 istek birkaç saniye sürebilir

const GRUP = 50;

/* ---------- Tüm ilçeleri tek listede topla (Merkez hariç: il koordinatı kullanılır) ---------- */
$hepsi = [];
foreach ($ilceler as $ilKodu => $adlar) {
    foreach (ilceListesi($ilceler, $ilKodu) as $ilceKodu => $ad) {
        if ($ilceKodu != 'merkez') {
            $hepsi[] = ['il' => $ilKodu, 'ilce' => $ilceKodu, 'ad' => $ad];
        }
    }
}

$bas = max(0, (int) ($_GET['bas'] ?? 0));
$grup = array_slice($hepsi, $bas, GRUP);

/* ---------- Bu gruptaki ilçeleri sor ---------- */
$sonuclar = [];
foreach ($grup as $s) {
    $konum = ilceKonumu($s['il'], $s['ilce'], $bolgeler, $ilceler);
    $sonuclar[] = $s + ['konum' => $konum];
}

$onbellek    = ilceOnbellekOku();
$bulunamayan = 0;
foreach ($sonuclar as $s) {
    if ($s['konum'] === null) {
        $bulunamayan++;
    }
}

$sayfaBasligi = 'İlçe kontrolü';
$aktifMenu    = 'yonetim';
require 'header.php';
?>

<main>
    <p class="yol"><a href="index.php">Ana Sayfa</a> › <a href="yonetim.php">Yönetim</a> › İlçe kontrolü</p>
    <h1 class="sayfa-baslik">İlçe koordinatları</h1>
    <p class="sayfa-aciklama">
        Önbellekte <strong><?php echo count($onbellek); ?></strong> / <?php echo count($hepsi); ?> ilçe var.
        Bu sayfa <?php echo $bas + 1; ?>–<?php echo min($bas + GRUP, count($hepsi)); ?>. ilçeleri kontrol etti;
        <strong><?php echo $bulunamayan; ?></strong> tanesi bulunamadı.
    </p>

    <div class="form-butonlar">
        <?php if ($bas > 0): ?>
            <a class="buton-ikincil" href="ilce-kontrol.php?bas=<?php echo max(0, $bas - GRUP); ?>">← Önceki 50</a>
        <?php endif; ?>
        <?php if ($bas + GRUP < count($hepsi)): ?>
            <a class="buton-ana" href="ilce-kontrol.php?bas=<?php echo $bas + GRUP; ?>">Sonraki 50 ilçe →</a>
        <?php else: ?>
            <span class="mesaj basari">Tüm ilçeler kontrol edildi.</span>
        <?php endif; ?>
    </div>
    <br>

    <div class="tablo-kutu">
        <table class="tablo">
            <thead><tr><th>#</th><th>İl</th><th>İlçe</th><th>Enlem</th><th>Boylam</th><th>Kaynak</th></tr></thead>
            <tbody>
                <?php foreach ($sonuclar as $i => $s): ?>
                    <tr>
                        <td><?php echo $bas + $i + 1; ?></td>
                        <td><?php echo e(ilBul($bolgeler, $s['il'])['il']['ad']); ?></td>
                        <td><a href="il.php?il=<?php echo $s['il']; ?>&ilce=<?php echo $s['ilce']; ?>" translate="no"><?php echo e($s['ad']); ?></a></td>
                        <?php if ($s['konum'] === null): ?>
                            <td colspan="3"><span class="etiket-durum durum-pasif">Bulunamadı</span></td>
                        <?php else: ?>
                            <td><?php echo $s['konum']['lat']; ?></td>
                            <td><?php echo $s['konum']['lon']; ?></td>
                            <td class="soluk"><?php echo e($s['konum']['kaynak']); ?></td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<?php require 'footer.php'; ?>
