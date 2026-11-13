<?php
/*
 * fonksiyonlar/dil.php
 * Türkçe / İngilizce dil desteği.
 *
 * Site Türkçe yazılmıştır. İngilizce seçildiğinde (çerez: dil=en) sayfa tarayıcıya
 * gönderilmeden hemen önce çıktı tamponu (ob_start) ile yakalanır ve içindeki Türkçe
 * metinler dil/en.php sözlüğündeki İngilizce karşılıklarıyla değiştirilir.
 * Böylece her sayfanın kodu tek tek değiştirilmeden tüm site çevrilmiş olur.
 *
 * Yalnızca görünen metinler çevrilir: etiketler arasındaki yazılar ve
 * placeholder / title / aria-label / alt öznitelikleri. Bağlantı adresleri, form
 * alanlarının adları ve kodlar değişmez. Şehir adları gibi özel adlar sözlükte
 * olmadığı için olduğu gibi kalır.
 *
 * Sözlükteki "{}" işaretleri değişen kısımları (sayı, ad, tarih) temsil eder:
 *   'Bugün yağış ihtimali %{} (eşiğiniz %{}).' => 'Chance of rain today is {1}% (your threshold {2}%).'
 * İngilizce tarafta {1} yazılırsa değişen kısım da çevrilir (ör. "Ege" → "Aegean"),
 * {!1} yazılırsa olduğu gibi bırakılır (şehir / kişi adları için).
 *
 * translate="no" özniteliği taşıyan etiketlerin içi çevrilmez (ör. ilçe adları:
 * "Pazar" ilçesi "Sunday", "Akdeniz" ilçesi "Mediterranean" olmasın diye).
 */

const DILLER = ['tr' => 'Türkçe', 'en' => 'English'];

/**
 * Seçili dil: çerezde 'en' varsa İngilizce, yoksa Türkçe.
 */
function aktifDil()
{
    return (($_COOKIE['dil'] ?? '') == 'en') ? 'en' : 'tr';
}

/**
 * Sözlüğü bir kez yükler ve çeviri için hazırlar:
 *   tam    : cümlenin tamamı birebir eşleşenler
 *   kalip  : "{}" içerenler (düzenli ifadeye çevrilir)
 *   kelime : tek kelimeler (ay ve gün adları gibi), kelime sınırına göre değiştirilir
 */
function sozluk()
{
    static $hazir = null;
    if ($hazir !== null) {
        return $hazir;
    }
    $ham = require __DIR__ . '/../dil/en.php';
    $hazir = ['tam' => [], 'kalip' => [], 'kelime' => $ham['kelimeler'] ?? []];

    foreach ($ham['metinler'] as $tr => $en) {
        if (strpos($tr, '{}') === false) {
            $hazir['tam'][$tr] = $en;
            continue;
        }
        $parcalar = explode('{}', $tr);
        $desen    = '/^' . implode('(.+?)', array_map(function ($p) { return preg_quote($p, '/'); }, $parcalar)) . '$/u';
        $hazir['kalip'][] = [$desen, $en];
    }
    // Uzun kalıplar önce denensin (daha özel olanlar)
    usort($hazir['kalip'], function ($a, $b) { return strlen($b[0]) - strlen($a[0]); });
    return $hazir;
}

/**
 * Tek bir metni çevirir. Önce tamamı, sonra cümle cümle, en son kelime kelime denenir.
 */
function cevir($metin)
{
    $s = sozluk();
    // Satır sonları / girintiler tek boşluğa indirilir (HTML'de görünüm değişmez)
    $bosluk = preg_replace('/\s+/u', ' ', trim($metin));
    if ($bosluk === '' || !preg_match('/\p{L}/u', $bosluk)) {
        return $metin;   // yalnızca sayı / işaret
    }

    $sonuc = cumleCevir($bosluk, $s);
    if ($sonuc === null) {
        // Cümlelere bölerek dene: "Yağmur ihtimali var (%40). Çantanızda şemsiye bulunsun."
        $cumleler = preg_split('/(?<=[.!?])\s+/u', $bosluk);
        if (count($cumleler) > 1) {
            $parcalar = [];
            foreach ($cumleler as $c) {
                $parcalar[] = cumleCevir($c, $s) ?? kelimeCevir($c, $s);
            }
            $sonuc = implode(' ', $parcalar);
        } else {
            $sonuc = kelimeCevir($bosluk, $s);
        }
    }
    // Baştaki / sondaki boşluklar korunur (HTML düzeni bozulmasın)
    preg_match('/^\s*/u', $metin, $bas);
    preg_match('/\s*$/u', $metin, $son);
    return $bas[0] . $sonuc . $son[0];
}

/**
 * Bir cümleyi tam eşleşme ya da kalıpla çevirir; bulunamazsa null.
 */
function cumleCevir($cumle, $s)
{
    if (isset($s['tam'][$cumle])) {
        return $s['tam'][$cumle];
    }
    foreach ($s['kalip'] as [$desen, $en]) {
        if (preg_match($desen, $cumle, $m)) {
            $cikti = $en;
            for ($i = count($m) - 1; $i >= 1; $i--) {
                $parca = $m[$i];
                // {!1}: ad olduğu gibi kalır; {1}: değişen kısım da çevrilir (ör. hava durumu, bölge adı)
                $cikti = str_replace('{!' . $i . '}', $parca, $cikti);
                if (strpos($cikti, '{' . $i . '}') !== false) {
                    $ceviri = cumleCevir($parca, $s) ?? kelimeCevir($parca, $s);
                    $cikti  = str_replace('{' . $i . '}', $ceviri, $cikti);
                }
            }
            return $cikti;
        }
    }
    return null;
}

/**
 * Kelime sözlüğündeki kelimeleri (ay, gün adları…) metin içinde değiştirir.
 */
function kelimeCevir($metin, $s)
{
    foreach ($s['kelime'] as $tr => $en) {
        $metin = preg_replace('/(?<![\p{L}])' . preg_quote($tr, '/') . '(?![\p{L}])/u', $en, $metin);
    }
    return $metin;
}

/**
 * ob_start() geri çağırma fonksiyonu: sayfanın tüm HTML'ini çevirir.
 */
function sayfayiCevir($html)
{
    // <style> ve <script> içleri ile HTML yorumları atlanır
    $parcalar = preg_split('/(<style\b.*?<\/style>|<script\b.*?<\/script>|<!--.*?-->)/su', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
    foreach ($parcalar as $i => $parca) {
        if ($i % 2 == 1) {
            continue;
        }
        // 1) Etiketler arasındaki metinler (translate="no" etiketinin hemen içi hariç)
        $parca = preg_replace_callback('/(<[^<>]*>)([^<>]+)(?=<)/u', function ($m) {
            if (strpos($m[1], 'translate="no"') !== false) {
                return $m[0];
            }
            $duz = html_entity_decode($m[2], ENT_QUOTES, 'UTF-8');
            $cev = cevir($duz);
            return $m[1] . ($cev === $duz ? $m[2] : htmlspecialchars($cev, ENT_NOQUOTES, 'UTF-8'));
        }, $parca);
        // 2) Görünen öznitelikler
        $parca = preg_replace_callback('/\b(placeholder|title|aria-label|alt|data-label|data-metin-[a-z]+)="([^"]*)"/u', function ($m) {
            $duz = html_entity_decode($m[2], ENT_QUOTES, 'UTF-8');
            $cev = cevir($duz);
            return $m[1] . '="' . ($cev === $duz ? $m[2] : htmlspecialchars($cev, ENT_QUOTES, 'UTF-8')) . '"';
        }, $parca);
        // 3) Silme onay pencereleri: onsubmit="return confirm('...');"
        $parca = preg_replace_callback("/confirm\('((?:[^'\\\\]|\\\\.)*)'\)/u", function ($m) {
            $duz = html_entity_decode($m[1], ENT_QUOTES, 'UTF-8');
            $cev = cevir($duz);
            return "confirm('" . ($cev === $duz ? $m[1] : htmlspecialchars(addslashes($cev), ENT_QUOTES, 'UTF-8')) . "')";
        }, $parca);
        $parcalar[$i] = $parca;
    }
    $html = implode('', $parcalar);
    return str_replace('<html lang="tr"', '<html lang="en"', $html);
}

/**
 * Dil değiştirme bağlantısının adresi.
 */
function dilAdresi($dil)
{
    return 'dil.php?dil=' . $dil;
}
