<?php
/*
 * fonksiyonlar/oturum.php
 * Kullanıcı girişi, yetki kontrolü, form güvenliği ve tek seferlik mesajlar.
 *
 * Giriş yapan kullanıcı oturumda şöyle tutulur:
 *   $_SESSION['kullanici'] = ['kullaniciAdi' => 'admin', 'adSoyad' => '...', 'rol' => 'admin']
 */

/* ------------------------------------------------------------
 * GİRİŞ / ÇIKIŞ
 * ---------------------------------------------------------- */

/**
 * Giriş yapmış kullanıcıyı döndürür; giriş yapılmamışsa null.
 */
function aktifKullanici()
{
    return $_SESSION['kullanici'] ?? null;
}

/**
 * Giriş yapılmış mı?
 */
function girisYapildiMi()
{
    return aktifKullanici() !== null;
}

/**
 * Giriş yapan kullanıcı admin mi?
 */
function adminMi()
{
    $k = aktifKullanici();
    return $k !== null && $k['rol'] == 'admin';
}

/**
 * Kullanıcı adı ve parolayı kontrol eder.
 * Doğruysa kullanıcıyı oturuma yazar ve true döner; yanlışsa false.
 *
 * Güvenlik notları:
 *  - Parola password_verify() ile özet (hash) üzerinden kontrol edilir.
 *  - Kullanıcı adı mı parola mı yanlış, ayrı ayrı söylenmez (tahmini zorlaştırır).
 *  - session_regenerate_id(): girişte oturum kimliği yenilenir (oturum sabitleme saldırısına karşı).
 */
function girisYap($kullaniciAdi, $parola, $kullanicilar)
{
    if (!isset($kullanicilar[$kullaniciAdi])) {
        return false;
    }
    if (!password_verify($parola, $kullanicilar[$kullaniciAdi]['parola'])) {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['kullanici'] = [
        'kullaniciAdi' => $kullaniciAdi,
        'adSoyad'      => $kullanicilar[$kullaniciAdi]['adSoyad'],
        'rol'          => $kullanicilar[$kullaniciAdi]['rol'],
    ];
    return true;
}

/**
 * Çıkış yapar. Takip listeleri oturumda kalır; aynı hesapla
 * tekrar giriş yapıldığında kayıtlar geri gelir.
 */
function cikisYap()
{
    unset($_SESSION['kullanici']);
    session_regenerate_id(true);
}

/* ------------------------------------------------------------
 * YETKİ KONTROLÜ (sayfaların en başında çağrılır)
 * ---------------------------------------------------------- */

/**
 * Giriş yapılmamışsa giriş sayfasına yönlendirir.
 * Girişten sonra kullanıcı bu sayfaya geri döner.
 */
function girisGerekli()
{
    if (!girisYapildiMi()) {
        mesajEkle('bilgi', 'Bu sayfayı görmek için giriş yapmalısınız.');
        $buSayfa = basename($_SERVER['PHP_SELF']);
        if (!empty($_SERVER['QUERY_STRING'])) {
            $buSayfa .= '?' . $_SERVER['QUERY_STRING'];
        }
        header('Location: giris.php?geri=' . urlencode($buSayfa));
        exit;
    }
}

/**
 * Admin değilse erişimi engeller.
 */
function adminGerekli()
{
    girisGerekli();
    if (!adminMi()) {
        mesajEkle('hata', 'Bu sayfaya yalnızca yöneticiler erişebilir.');
        header('Location: takip.php');
        exit;
    }
}

/**
 * Yönlendirme adresinin güvenli olup olmadığını kontrol eder.
 * Yalnızca kendi sayfalarımıza dönüş yapılır; başka siteye yönlendirme engellenir.
 * Güvenli değilse $varsayilan döner.
 */
function guvenliDonus($adres, $varsayilan = 'index.php')
{
    $izinli = ['index.php', 'il.php', 'takip.php', 'ekle.php', 'yonetim.php', 'ilce-kontrol.php', 'hesabim.php', 'hesap-duzenle.php', 'ikonlar.php'];

    $parcalar = parse_url($adres);
    if ($parcalar === false || isset($parcalar['host']) || isset($parcalar['scheme'])) {
        return $varsayilan;
    }
    $sayfa = basename($parcalar['path'] ?? '');
    if (!in_array($sayfa, $izinli)) {
        return $varsayilan;
    }
    return isset($parcalar['query']) ? $sayfa . '?' . $parcalar['query'] : $sayfa;
}

/* ------------------------------------------------------------
 * FORM GÜVENLİĞİ (CSRF)
 * Başka bir sitenin, kullanıcı farkında olmadan bizim formlarımızı
 * göndermesini (ör. kaydı silmesini) engellemek için her POST formuna
 * oturuma özel gizli bir anahtar eklenir.
 * ---------------------------------------------------------- */

function csrfAnahtari()
{
    if (!isset($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

/**
 * Formun içine konacak gizli alan.
 */
function csrfAlani()
{
    return '<input type="hidden" name="csrf" value="' . csrfAnahtari() . '">';
}

/**
 * Gelen anahtar doğru mu? hash_equals: güvenli karşılaştırma.
 */
function csrfGecerliMi()
{
    return isset($_POST['csrf']) && hash_equals(csrfAnahtari(), (string) $_POST['csrf']);
}

/* ------------------------------------------------------------
 * TEK SEFERLİK MESAJLAR
 * Bir sayfada oluşan mesaj (ör. "kayıt silindi") yönlendirmeden sonraki
 * sayfada bir kez gösterilir, sonra silinir.
 * ---------------------------------------------------------- */

/**
 * $tur: 'basari', 'hata', 'bilgi' veya 'uyari'
 */
function mesajEkle($tur, $metin)
{
    $_SESSION['bildirim'] = ['tur' => $tur, 'metin' => $metin];
}

/**
 * Mesajı alır ve oturumdan siler.
 */
function mesajAl()
{
    $mesaj = $_SESSION['bildirim'] ?? null;
    unset($_SESSION['bildirim']);
    return $mesaj;
}
