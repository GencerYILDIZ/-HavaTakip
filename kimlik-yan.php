<?php
/*
 * kimlik-yan.php — Giriş ve kayıt sayfalarının sol tarafındaki tanıtım bölümü.
 * giris.php ve kayit.php tarafından dahil edilir; tek başına açılmaz.
 *
 * Mevsim renginde degrade zemin, yavaşça süzülen animasyonlu hava simgeleri
 * (assets/ikonlar/) ve sitenin öne çıkan özellikleri.
 */
if (!isset($mevsim)) {
    header('Location: giris.php');
    exit;
}
?>
<aside class="kimlik-yan" aria-hidden="true">
    <!-- Süzülen hava simgeleri (yalnızca süs) -->
    <img class="ky-simge s1" src="assets/ikonlar/gunesli.svg" alt="">
    <img class="ky-simge s2" src="assets/ikonlar/parcali-bulutlu.svg" alt="">
    <img class="ky-simge s3" src="assets/ikonlar/hafif-yagmur.svg" alt="">
    <img class="ky-simge s4" src="assets/ikonlar/kar.svg" alt="">

    <div class="ky-icerik">
        <span class="ky-etiket">HavaTakip</span>
        <h2>Şehrinizin havası, sizin uyarılarınızla.</h2>
        <p>Takip ettiğiniz il ve ilçeler için yağış, don, rüzgâr ve sıcak uyarılarını tek ekranda görün.</p>
        <ul class="ky-ozellikler">
            <li><span>✓</span> 81 il ve 973 ilçe için anlık hava ve 7 günlük tahmin</li>
            <li><span>✓</span> Kişisel takip listesi ve bildirim zili</li>
            <li><span>✓</span> Hava kalitesi, giyim ve şemsiye tavsiyeleri</li>
        </ul>
    </div>
</aside>
