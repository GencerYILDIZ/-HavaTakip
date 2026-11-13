<?php
/*
 * tema.php — Koyu / açık tema arasında geçiş yapar.
 *
 * Seçim bir çerezde (cookie) 1 yıl saklanır; böylece kullanıcı siteye
 * tekrar geldiğinde seçtiği tema hatırlanır.
 * İşlem bitince kullanıcı geldiği sayfaya geri gönderilir.
 */

require_once 'baslat.php';   // guvenliDonus() fonksiyonu için

$suAnki = $_COOKIE['tema'] ?? 'acik';
$yeni   = ($suAnki == 'koyu') ? 'acik' : 'koyu';

// setcookie(ad, değer, bitiş zamanı, yol)
setcookie('tema', $yeni, time() + 60 * 60 * 24 * 365, '/');

/* ---------- Geri dönülecek sayfa ----------
 * HTTP_REFERER = kullanıcının geldiği sayfanın adresi.
 * guvenliDonus() yalnızca kendi sayfalarımıza dönüşe izin verir.
 */
$geri = 'index.php';
if (isset($_SERVER['HTTP_REFERER'])) {
    $parcalar = parse_url($_SERVER['HTTP_REFERER']);
    $adres    = basename($parcalar['path'] ?? '') . (isset($parcalar['query']) ? '?' . $parcalar['query'] : '');
    $geri     = guvenliDonus($adres);
}

header('Location: ' . $geri);
exit;
