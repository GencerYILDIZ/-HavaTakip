<?php
/*
 * fonksiyonlar/hesap.php
 * Hesap yönetimi (CRUD): hesap ekleme, görüntüleme, güncelleme ve silme.
 *
 *   Kullanıcı : "Hesabım" sayfasında (hesabim.php) ad soyad, e-posta ve parolasını
 *               değiştirebilir, kendi hesabını silebilir.
 *   Admin     : Yönetim panelinden yeni hesap açabilir (hesap-duzenle.php),
 *               her hesabı düzenleyebilir ve silebilir (hesap-sil.php).
 *
 * Hesaplar veri/kullanicilar.json dosyasında saklanır; böylece değişiklikler
 * tarayıcı kapansa da kaybolmaz. Proje bu dosya, içinde 2 başlangıç hesabıyla gelir.
 * Dosya silinirse data.php'deki başlangıç hesapları kullanılır ve ilk değişiklikte dosya yeniden oluşur.
 * veri/.htaccess dosyası bu klasörün tarayıcıdan açılmasını engeller.
 *
 * Dosyadaki biçim:
 * {
 *   "kullanici": {
 *     "adSoyad": "Deneme Kullanıcısı", "eposta": "kullanici@ornek.com", "rol": "kullanici",
 *     "parola": "$2y$10$...",                 ← password_hash() özeti, parolanın kendisi değil
 *     "kayitTarihi": "2026-10-19 09:00"
 *   }
 * }
 */

// veriYolu(): veri/ klasörüne yazılamayan bilgisayarlarda geçici klasördeki kopyayı kullanır (bkz. genel.php)
define('HESAP_DOSYASI', veriYolu('kullanicilar.json'));

// Yeni hesap açılırken alınamayacak kullanıcı adları
const AYRILMIS_ADLAR = ['yonetici', 'root', 'sistem', 'destek'];

/**
 * Hesapları dosyadan okur. Dosya yoksa (ör. silindiyse)
 * data.php'deki başlangıç hesaplarını döndürür.
 */
function hesaplariOku($baslangicHesaplari)
{
    if (!file_exists(HESAP_DOSYASI)) {
        return $baslangicHesaplari;
    }
    $veri = json_decode(file_get_contents(HESAP_DOSYASI), true);
    return is_array($veri) ? $veri : $baslangicHesaplari;
}

/**
 * Hesapların tamamını dosyaya yazar. Başarılıysa true döner.
 * LOCK_EX: aynı anda iki işlem yazarsa dosyanın bozulmasını önler.
 */
function hesaplariKaydet($kullanicilar)
{
    $klasor = dirname(HESAP_DOSYASI);
    if (!is_dir($klasor)) {
        @mkdir($klasor, 0775, true);
    }
    $sonuc = @file_put_contents(
        HESAP_DOSYASI,
        json_encode($kullanicilar, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT),
        LOCK_EX
    );
    return $sonuc !== false;
}

/**
 * Kaç admin hesabı var? (Son admin silinemez ve rolü değiştirilemez.)
 */
function adminSayisi($kullanicilar)
{
    $sayi = 0;
    foreach ($kullanicilar as $hesap) {
        if ($hesap['rol'] == 'admin') {
            $sayi++;
        }
    }
    return $sayi;
}

/**
 * Hesap formunu doğrular.
 *
 * $girdi        : formdan gelen veri ($_POST)
 * $kullanicilar : mevcut hesaplar (benzersizlik kontrolü için)
 * $hedef        : düzenlenen hesabın kullanıcı adı; yeni hesapta null
 * $adminFormu   : true ise rol alanı da kontrol edilir (yalnızca admin)
 *
 * Dönüş: ['hatalar' => [alan => mesaj], 'veri' => temizlenmiş değerler]
 */
function hesapDogrula($girdi, $kullanicilar, $hedef = null, $adminFormu = false)
{
    $hatalar = [];
    $yeni    = ($hedef === null);

    $adSoyad      = trim($girdi['adSoyad'] ?? '');
    $eposta       = mb_strtolower(trim($girdi['eposta'] ?? ''));
    $kullaniciAdi = $yeni ? mb_strtolower(trim($girdi['kullaniciAdi'] ?? '')) : $hedef;
    $parola       = $girdi['parola'] ?? '';
    $parolaTekrar = $girdi['parolaTekrar'] ?? '';
    $rol          = $girdi['rol'] ?? 'kullanici';

    // 1) Ad soyad: zorunlu, 3–50 karakter, yalnızca harf ve boşluk
    if ($adSoyad === '') {
        $hatalar['adSoyad'] = 'Ad soyad boş bırakılamaz.';
    } elseif (mb_strlen($adSoyad) < 3 || mb_strlen($adSoyad) > 50) {
        $hatalar['adSoyad'] = 'Ad soyad 3–50 karakter olmalıdır.';
    } elseif (!preg_match('/^[\p{L} ]+$/u', $adSoyad)) {
        $hatalar['adSoyad'] = 'Ad soyad yalnızca harf ve boşluk içerebilir.';
    }

    // 2) Kullanıcı adı (yalnızca yeni hesapta): küçük harf/rakam/alt çizgi, benzersiz
    if ($yeni) {
        if ($kullaniciAdi === '') {
            $hatalar['kullaniciAdi'] = 'Kullanıcı adı boş bırakılamaz.';
        } elseif (!preg_match('/^[a-z0-9_]{3,20}$/', $kullaniciAdi)) {
            $hatalar['kullaniciAdi'] = 'Kullanıcı adı 3–20 karakter olmalı; yalnızca a-z, 0-9 ve _ kullanılabilir.';
        } elseif (in_array($kullaniciAdi, AYRILMIS_ADLAR) || isset($kullanicilar[$kullaniciAdi])) {
            $hatalar['kullaniciAdi'] = '"' . $kullaniciAdi . '" kullanıcı adı alınmış.';
        }
    }

    // 3) E-posta: zorunlu, geçerli biçim, başka bir hesapta olmamalı
    if ($eposta === '') {
        $hatalar['eposta'] = 'E-posta adresi boş bırakılamaz.';
    } elseif (filter_var($eposta, FILTER_VALIDATE_EMAIL) === false) {
        $hatalar['eposta'] = 'Geçerli bir e-posta adresi girin (ör. ad@ornek.com).';
    } else {
        foreach ($kullanicilar as $kadi => $hesap) {
            if ($kadi !== $hedef && ($hesap['eposta'] ?? '') === $eposta) {
                $hatalar['eposta'] = 'Bu e-posta adresi başka bir hesapta kullanılıyor.';
                break;
            }
        }
    }

    // 4) Parola: yeni hesapta zorunlu; düzenlemede boş bırakılırsa değişmez
    if ($parola !== '' || $yeni) {
        $parolaHatasi = parolaKontrol($parola, $kullaniciAdi);
        if ($parolaHatasi !== null) {
            $hatalar['parola'] = $parolaHatasi;
        } elseif ($parola !== $parolaTekrar) {
            $hatalar['parolaTekrar'] = 'Parolalar birbiriyle eşleşmiyor.';
        }
    }

    // 5) Rol (yalnızca admin formunda): son admin kullanıcıya çevrilemez
    if ($adminFormu) {
        if (!in_array($rol, ['admin', 'kullanici'])) {
            $hatalar['rol'] = 'Geçersiz bir rol seçildi.';
        } elseif (!$yeni && $rol == 'kullanici' && $kullanicilar[$hedef]['rol'] == 'admin'
                  && adminSayisi($kullanicilar) <= 1) {
            $hatalar['rol'] = 'Sistemde en az bir yönetici kalmalıdır; son yöneticinin rolü değiştirilemez.';
        }
    }

    return [
        'hatalar' => $hatalar,
        'veri'    => [
            'kullaniciAdi' => $kullaniciAdi,
            'adSoyad'      => $adSoyad,
            'eposta'       => $eposta,
            'parola'       => $parola,
            'rol'          => $rol,
        ],
    ];
}

/**
 * Parola kuralları. Sorun yoksa null, varsa hata mesajı döndürür.
 */
function parolaKontrol($parola, $kullaniciAdi)
{
    if ($parola === '') {
        return 'Parola boş bırakılamaz.';
    } elseif (strlen($parola) < 8) {
        return 'Parola en az 8 karakter olmalıdır.';
    } elseif (strlen($parola) > 72) {
        return 'Parola en fazla 72 karakter olabilir.';
    } elseif (!preg_match('/\p{L}/u', $parola) || !preg_match('/[0-9]/', $parola)) {
        return 'Parola en az bir harf ve bir rakam içermelidir.';
    } elseif (mb_strtolower($parola) === $kullaniciAdi) {
        return 'Parola kullanıcı adıyla aynı olamaz.';
    }
    return null;
}

/**
 * CREATE — Yeni hesap ekler ve dosyaya kaydeder.
 */
function hesapEkle(&$kullanicilar, $veri)
{
    $kullanicilar[$veri['kullaniciAdi']] = [
        'adSoyad'     => $veri['adSoyad'],
        'eposta'      => $veri['eposta'],
        'rol'         => $veri['rol'],
        'parola'      => password_hash($veri['parola'], PASSWORD_DEFAULT),
        'kayitTarihi' => date('Y-m-d H:i'),
    ];
    return hesaplariKaydet($kullanicilar);
}

/**
 * UPDATE — Hesabın ad soyad, e-posta ve (verildiyse) parola / rol bilgisini günceller.
 * Kullanıcı adı değiştirilmez; takip listesi bu ada bağlıdır.
 */
function hesapGuncelle(&$kullanicilar, $kullaniciAdi, $veri, $rolDegissin = false)
{
    $kullanicilar[$kullaniciAdi]['adSoyad'] = $veri['adSoyad'];
    $kullanicilar[$kullaniciAdi]['eposta']  = $veri['eposta'];
    if ($veri['parola'] !== '') {
        $kullanicilar[$kullaniciAdi]['parola'] = password_hash($veri['parola'], PASSWORD_DEFAULT);
    }
    if ($rolDegissin) {
        $kullanicilar[$kullaniciAdi]['rol'] = $veri['rol'];
    }
    return hesaplariKaydet($kullanicilar);
}

/**
 * DELETE — Hesabı ve bu oturumdaki takip listesini / bildirimlerini siler.
 */
function hesapSil(&$kullanicilar, $kullaniciAdi)
{
    unset($kullanicilar[$kullaniciAdi]);
    unset($_SESSION['listeler'][$kullaniciAdi], $_SESSION['sonIdler'][$kullaniciAdi],
          $_SESSION['bildirimMerkezi'][$kullaniciAdi]);
    return hesaplariKaydet($kullanicilar);
}

/**
 * Oturumdaki kullanıcı bilgisini hesap dosyasıyla eşitler (her sayfada baslat.php çağırır).
 *  - Hesap silinmişse (ör. admin sildiyse) oturum kapatılır ve giriş sayfasına gidilir.
 *  - Ad soyad veya rol değiştiyse navbar'da hemen yeni bilgi görünür.
 */
function oturumuTazele($kullanicilar)
{
    $k = aktifKullanici();
    if ($k === null) {
        return;
    }
    if (!isset($kullanicilar[$k['kullaniciAdi']])) {
        cikisYap();
        mesajEkle('bilgi', 'Hesabınız silindiği için oturumunuz kapatıldı.');
        header('Location: giris.php');
        exit;
    }
    $_SESSION['kullanici']['adSoyad'] = $kullanicilar[$k['kullaniciAdi']]['adSoyad'];
    $_SESSION['kullanici']['rol']     = $kullanicilar[$k['kullaniciAdi']]['rol'];
}

/**
 * Formda bir alanın hata mesajını yazar (hesap formlarında kullanılır).
 */
function hesapAlanHatasi($hatalar, $alan)
{
    if (isset($hatalar[$alan])) {
        echo '<p class="alan-hata">' . e($hatalar[$alan]) . '</p>';
    }
}
