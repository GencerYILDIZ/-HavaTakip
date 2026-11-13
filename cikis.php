<?php
/*
 * cikis.php — Oturumu kapatır.
 * Yalnızca POST ile çalışır (navbar'daki "Çıkış" düğmesi bir formdur);
 * böylece bir bağlantıya tıklatılarak kullanıcının çıkışı yaptırılamaz.
 */

require_once 'baslat.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && csrfGecerliMi()) {
    cikisYap();
    mesajEkle('bilgi', 'Çıkış yaptınız. Tekrar görüşmek üzere!');
    header('Location: giris.php');
    exit;
}

header('Location: index.php');
exit;
