<?php
/*
 * functions.php
 * Projede kullanılan tüm kendi yazdığımız (kullanıcı tanımlı) fonksiyonlar
 * konularına göre ayrı dosyalarda tutulur; bu dosya hepsini tek yerden dahil eder.
 *
 *   fonksiyonlar/genel.php  → metin, arama, tarih, güvenli çıktı (e)
 *   fonksiyonlar/hava.php   → Open-Meteo API, hava kodları, tavsiyeler
 *   fonksiyonlar/takip.php  → takip listesi: ekle, sil, durum değiştir, doğrula, filtrele, say
 *   fonksiyonlar/oturum.php → giriş/çıkış, yetki, form güvenliği, mesajlar
 *   fonksiyonlar/ilce.php   → ilçe listesi, ilçe koordinatı, il: ilçe araması
 *   fonksiyonlar/bildirim.php → bildirim zili: bildirim üretme, okundu, silme
 *   fonksiyonlar/hesap.php  → hesap CRUD (veri/kullanicilar.json)
 *   fonksiyonlar/gorunum.php → dört mevsim teması ve il sayfasındaki hava efektleri
 *   fonksiyonlar/dil.php    → Türkçe / İngilizce çeviri
 *
 * __DIR__ : bu dosyanın bulunduğu klasör. Dosya nereden çağrılırsa çağrılsın
 *           yollar doğru çalışsın diye kullanılır.
 */

require_once __DIR__ . '/fonksiyonlar/genel.php';
require_once __DIR__ . '/fonksiyonlar/hava.php';
require_once __DIR__ . '/fonksiyonlar/takip.php';
require_once __DIR__ . '/fonksiyonlar/oturum.php';
require_once __DIR__ . '/fonksiyonlar/ilce.php';
require_once __DIR__ . '/fonksiyonlar/bildirim.php';
require_once __DIR__ . '/fonksiyonlar/hesap.php';
require_once __DIR__ . '/fonksiyonlar/gorunum.php';
require_once __DIR__ . '/fonksiyonlar/dil.php';
