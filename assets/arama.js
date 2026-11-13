/*
 * assets/arama.js — Ana sayfadaki arama kutusu
 *
 *  1) Öneri kutusu: yazdıkça 81 il ve 973 ilçe içinde arar, en uygun 8 sonucu simgeli satırlar hâlinde
 *     gösterir (yazılan kısım vurgulanır). "İl: ilçe" biçiminde de yazılabilir (ör. "tokat: er").
 *     Klavye: ↓ ↑ ile satırlar arasında gezilir, Enter seçili satırı açar, Esc kutuyu kapatır.
 *  2) × düğmesi: kutuyu temizler.
 *  3) Konumum: tarayıcıdan konum alınır, en yakın il / ilçe bulunur ve o yerin hava durumu sayfası açılır.
 *
 * Veri, index.php'deki <script id="aramaVerisi"> etiketinden okunur:
 *   [tür (0 il, 1 ilçe), ad, üst bilgi, il kodu, ilçe kodu, enlem, boylam]
 * JavaScript kapalıysa form normal GET araması yapar (index.php?ara=...).
 */
(function () {
    var form = document.querySelector('form.arama');
    var veriEtiketi = document.getElementById('aramaVerisi');
    if (!form || !veriEtiketi) {
        return;
    }
    var girdi = form.querySelector('input[name="ara"]');
    var kutu = form.querySelector('.oneri-kutu');
    var temizle = form.querySelector('.arama-temizle');
    var konumDugme = form.querySelector('.konum-buton');
    var durum = document.querySelector('.konum-durum');
    var metin = form.dataset;
    var veri = JSON.parse(veriEtiketi.textContent);
    var EN_FAZLA = 8;

    // Türkçe harfleri sadeleştirir; harf sayısı değişmez (vurgulama için konumlar korunur)
    function sade(m) {
        return m.toLocaleLowerCase('tr').replace(/ı/g, 'i').replace(/ş/g, 's').replace(/ğ/g, 'g')
                .replace(/ü/g, 'u').replace(/ö/g, 'o').replace(/ç/g, 'c').replace(/[âà]/g, 'a').replace(/[îì]/g, 'i').replace(/[ûù]/g, 'u');
    }
    function guvenli(m) {
        return String(m).replace(/[&<>"']/g, function (h) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[h];
        });
    }
    veri.forEach(function (v) {
        v.sAd = sade(v[1]);
        v.sUst = sade(v[2]);
    });
    function adres(v) {
        return v[0] === 0 ? 'il.php?il=' + v[3] : 'il.php?il=' + v[3] + '&ilce=' + v[4];
    }
    function vurgula(ad, aranan) {
        var i = aranan ? sade(ad).indexOf(aranan) : -1;
        if (i < 0) {
            return guvenli(ad);
        }
        return guvenli(ad.slice(0, i)) + '<mark>' + guvenli(ad.slice(i, i + aranan.length)) + '</mark>' + guvenli(ad.slice(i + aranan.length));
    }

    var SIMGE_IL = '<svg viewBox="0 0 24 24"><path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/></svg>';
    var SIMGE_ILCE = '<svg viewBox="0 0 24 24"><path d="M12 20s-5-4.4-5-8.2a5 5 0 0 1 10 0C17 15.6 12 20 12 20z"/><circle cx="12" cy="11.8" r="1.6"/></svg>';
    var SIMGE_TUM = '<svg viewBox="0 0 24 24"><rect x="4" y="4" width="6.5" height="6.5" rx="1.5"/><rect x="13.5" y="4" width="6.5" height="6.5" rx="1.5"/><rect x="4" y="13.5" width="6.5" height="6.5" rx="1.5"/><rect x="13.5" y="13.5" width="6.5" height="6.5" rx="1.5"/></svg>';

    var satirlar = [];
    var secili = -1;

    function ac() {
        kutu.hidden = false;
        girdi.setAttribute('aria-expanded', 'true');
        form.classList.add('oneri-acik');
    }
    function kapat() {
        kutu.hidden = true;
        girdi.setAttribute('aria-expanded', 'false');
        girdi.removeAttribute('aria-activedescendant');
        form.classList.remove('oneri-acik');
        secili = -1;
    }

    function ara() {
        var ham = girdi.value.trim();
        temizle.hidden = ham === '';
        var q = sade(ham);
        if (q === '') {
            kapat();
            return;
        }
        var ilAranan = q, ilceAranan = null;
        if (q.indexOf(':') !== -1) {
            ilAranan = q.split(':')[0].trim();
            ilceAranan = q.split(':').slice(1).join(':').trim();
        }

        // Puan: küçük olan önce (baştan eşleşen il → baştan eşleşen ilçe → içinde geçen il → içinde geçen ilçe)
        var bulunan = [];
        veri.forEach(function (v) {
            var puan = -1;
            if (ilceAranan !== null) {
                if (v[0] === 1 && v.sUst.indexOf(ilAranan) === 0) {
                    var j = v.sAd.indexOf(ilceAranan);
                    if (ilceAranan === '' || j === 0) { puan = 0; } else if (j > 0) { puan = 1; }
                }
            } else {
                var i = v.sAd.indexOf(q);
                if (i === 0) { puan = v[0] === 0 ? 0 : 1; } else if (i > 0) { puan = v[0] === 0 ? 2 : 3; }
            }
            if (puan >= 0) {
                bulunan.push([puan, v]);
            }
        });
        bulunan.sort(function (a, b) { return a[0] - b[0] || a[1][1].localeCompare(b[1][1], 'tr'); });

        satirlar = bulunan.slice(0, EN_FAZLA).map(function (x) { return { v: x[1] }; });
        // İlk sonuç bir il ise hemen altına "İl: tüm ilçeler" satırı eklenir
        if (ilceAranan === null && satirlar.length && satirlar[0].v[0] === 0) {
            satirlar.splice(1, 0, { tum: satirlar[0].v });
        }
        ciz(ilceAranan !== null ? ilceAranan : q);
    }

    function ciz(aranan) {
        if (satirlar.length === 0) {
            kutu.innerHTML = '<div class="oneri-bos">' + guvenli(metin.metinBos) + '</div>';
            ac();
            return;
        }
        kutu.innerHTML = satirlar.map(function (s, i) {
            if (s.tum) {
                return '<a class="oneri oneri-tum" role="option" id="oneri-' + i + '" href="il.php?il=' + s.tum[3] + '&amp;ilceler=1">'
                     + '<span class="oneri-ikon">' + SIMGE_TUM + '</span>'
                     + '<span class="oneri-metin"><strong>' + guvenli(s.tum[1]) + ': ' + guvenli(metin.metinTum) + '</strong>'
                     + '<small>' + guvenli(metin.metinTumalt) + '</small></span></a>';
            }
            var v = s.v;
            return '<a class="oneri ' + (v[0] === 0 ? 'oneri-il' : 'oneri-ilce') + '" role="option" id="oneri-' + i + '" href="' + guvenli(adres(v)) + '">'
                 + '<span class="oneri-ikon">' + (v[0] === 0 ? SIMGE_IL : SIMGE_ILCE) + '</span>'
                 + '<span class="oneri-metin"><strong>' + vurgula(v[1], aranan) + '</strong>'
                 + '<small>' + guvenli(v[2]) + '</small></span>'
                 + '<span class="oneri-tur">' + guvenli(v[0] === 0 ? metin.metinIl : metin.metinIlce) + '</span></a>';
        }).join('');
        secili = -1;
        ac();
    }

    function sec(yeni) {
        var liste = kutu.querySelectorAll('.oneri');
        if (!liste.length) {
            return;
        }
        if (secili >= 0 && liste[secili]) {
            liste[secili].classList.remove('aktif');
        }
        secili = (yeni + liste.length) % liste.length;
        liste[secili].classList.add('aktif');
        liste[secili].scrollIntoView({ block: 'nearest' });
        girdi.setAttribute('aria-activedescendant', liste[secili].id);
    }

    girdi.addEventListener('input', ara);
    girdi.addEventListener('focus', function () {
        if (girdi.value.trim() !== '') {
            ara();
        }
    });
    girdi.addEventListener('keydown', function (olay) {
        if (olay.key === 'ArrowDown') {
            olay.preventDefault();
            if (kutu.hidden) { ara(); }
            sec(secili + 1);
        } else if (olay.key === 'ArrowUp') {
            olay.preventDefault();
            sec(secili - 1);
        } else if (olay.key === 'Enter' && !kutu.hidden && secili >= 0) {
            olay.preventDefault();
            window.location.href = kutu.querySelectorAll('.oneri')[secili].href;
        } else if (olay.key === 'Escape') {
            kapat();
        }
    });
    // Satıra tıklarken kutu kapanmasın (bağlantı açılsın)
    kutu.addEventListener('mousedown', function (olay) { olay.preventDefault(); });
    document.addEventListener('click', function (olay) {
        if (!form.contains(olay.target)) {
            kapat();
        }
    });
    temizle.addEventListener('click', function () {
        girdi.value = '';
        temizle.hidden = true;
        kapat();
        girdi.focus();
    });
    temizle.hidden = girdi.value.trim() === '';

    /* ---------- Konumum ---------- */
    function durumYaz(yazi, tur) {
        if (!durum) {
            return;
        }
        durum.hidden = false;
        durum.className = 'konum-durum ' + (tur || '');
        durum.innerHTML = yazi;
    }
    if (konumDugme && navigator.geolocation) {
        konumDugme.hidden = false;
        konumDugme.addEventListener('click', function () {
            konumDugme.classList.add('yukleniyor');
            konumDugme.disabled = true;
            durumYaz(guvenli(metin.metinKonumbekle), 'bekle');
            navigator.geolocation.getCurrentPosition(function (konum) {
                var lat = konum.coords.latitude, lon = konum.coords.longitude;
                var enYakin = null, enKisa = Infinity;
                veri.forEach(function (v) {
                    if (v[5] === null) {
                        return;
                    }
                    var x = (v[6] - lon) * Math.cos((v[5] + lat) / 2 * Math.PI / 180);
                    var y = v[5] - lat;
                    var d = x * x + y * y;
                    if (d < enKisa) {
                        enKisa = d;
                        enYakin = v;
                    }
                });
                if (!enYakin) {
                    konumDugme.classList.remove('yukleniyor');
                    konumDugme.disabled = false;
                    durumYaz(guvenli(metin.metinKonumhata), 'hata');
                    return;
                }
                var ad = enYakin[0] === 0 ? enYakin[1] : enYakin[1] + ', ' + enYakin[2];
                durumYaz(guvenli(metin.metinKonumbulundu) + ' <strong>' + guvenli(ad) + '</strong>', 'tamam');
                setTimeout(function () { window.location.href = adres(enYakin); }, 700);
            }, function (hata) {
                konumDugme.classList.remove('yukleniyor');
                konumDugme.disabled = false;
                durumYaz(guvenli(hata.code === 1 ? metin.metinKonumred : metin.metinKonumhata), 'hata');
            }, { enableHighAccuracy: false, timeout: 12000, maximumAge: 300000 });
        });
    } else if (konumDugme) {
        konumDugme.remove();
    }
})();
