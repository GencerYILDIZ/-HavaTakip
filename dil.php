<?php
/*
 * dil.php — Türkçe / İngilizce arasında geçiş yapar.
 *
 * dil.php?dil=en → İngilizce, dil.php?dil=tr → Türkçe
 * Seçim bir çerezde 1 yıl saklanır (tema.php ile aynı yöntem).
 * İşlem bitince kullanıcı geldiği sayfaya geri gönderilir.
 */

require_once 'baslat.php';   // guvenliDonus() fonksiyonu için

$dil = $_GET['dil'] ?? 'tr';
if (!isset(DILLER[$dil])) {
    $dil = 'tr';
}

// setcookie(ad, değer, bitiş zamanı, yol)
setcookie('dil', $dil, time() + 60 * 60 * 24 * 365, '/');

/* ---------- Geri dönülecek sayfa (yalnızca kendi sayfalarımız) ---------- */
$geri = 'index.php';
if (isset($_SERVER['HTTP_REFERER'])) {
    $parcalar = parse_url($_SERVER['HTTP_REFERER']);
    $adres    = basename($parcalar['path'] ?? '') . (isset($parcalar['query']) ? '?' . $parcalar['query'] : '');
    $geri     = guvenliDonus($adres);
}

header('Location: ' . $geri);
exit;
