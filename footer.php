<footer>
    <p>Hava durumu verileri: <a href="https://open-meteo.com/" target="_blank" rel="noopener">Open-Meteo.com</a> (CC BY 4.0)</p>
    <p>İnternet Programcılığı I — PHP Proje Ödevi · <a href="ikonlar.php">Hava simgeleri</a></p>
    <!-- Mevsim teması yönetim panelinden seçilir (otomatik ya da sabit) -->
    <p class="mevsim-secici">
        Tema: <strong><?php echo MEVSIMLER[$mevsim]; ?></strong>
        (<?php echo (mevsimAyari() == 'oto') ? 'aya göre otomatik' : 'yönetici seçimi'; ?>)
        <?php if (adminMi()): ?>· <a href="yonetim.php#site-temasi">Değiştir</a><?php endif; ?>
    </p>
    <p class="telif">© <?php echo date('Y'); ?> HavaTakip · Tüm hakları saklıdır.</p>
</footer>

<script>
/* Navbar:
 *  - Sayfa aşağı kaydırılınca navbar koyulaşır ve gölgelenir (.kaydirildi). Navbar'ın yüksekliği DEĞİŞMEZ:
 *    yükseklik değişseydi sayfa sayfanın en üstüne yakınken kayar, sınıf açılıp kapanır ve navbar titrerdi.
 *    Ayrıca açma ve kapatma için farklı eşikler (24 px / 4 px) kullanılır.
 *  - Bilgisayarda menüdeki koyu, dolu seçim kutusu, fare hangi bağlantının üzerindeyse kayarak oraya gider;
 *    kutunun altındaki bağlantının yazısı beyaza döner (.gosterge-ustu);
 *    fare menüden çıkınca seçili sayfanın bağlantısına geri döner. JavaScript kapalıysa seçili
 *    bağlantı yine vurgulu görünür (CSS). */
(function () {
    var navbar = document.querySelector('.navbar');
    var menu = document.querySelector('nav.menu');
    if (!navbar || !menu) {
        return;
    }
    function kaydirma() {
        var y = window.scrollY;
        if (y > 24) {
            navbar.classList.add('kaydirildi');
        } else if (y < 4) {
            navbar.classList.remove('kaydirildi');
        }
    }
    window.addEventListener('scroll', kaydirma, { passive: true });
    kaydirma();

    var gosterge = menu.querySelector('.menu-gosterge');
    function git(baglanti, anlik) {
        menu.querySelectorAll('a.gosterge-ustu').forEach(function (a) { a.classList.remove('gosterge-ustu'); });
        if (!baglanti) {
            menu.classList.remove('gosterge-var');
            return;
        }
        baglanti.classList.add('gosterge-ustu');
        if (anlik) {
            gosterge.style.transition = 'none';
        }
        gosterge.style.width = baglanti.offsetWidth + 'px';
        gosterge.style.transform = 'translateX(' + baglanti.offsetLeft + 'px)';
        menu.classList.add('gosterge-var');
        if (anlik) {
            gosterge.offsetWidth;            // tarayıcı yeni konumu çizsin, sonra geçiş geri açılsın
            gosterge.style.transition = '';
        }
    }
    var aktif = menu.querySelector('a.aktif');
    menu.querySelectorAll('a').forEach(function (a) {
        a.addEventListener('mouseenter', function () { git(a, !menu.classList.contains('gosterge-var')); });
    });
    menu.addEventListener('mouseleave', function () { git(aktif, false); });
    window.addEventListener('resize', function () { git(aktif, true); });
    if (document.fonts && document.fonts.ready) {
        document.fonts.ready.then(function () { git(aktif, true); });
    }
    git(aktif, true);
    menu.classList.add('js-hazir');
})();

/* Navbar'ın yanındaki tarih ve saat kartları: her saniye Türkiye saatine göre güncellenir.
 * Analog kadrandaki ibreler döner, dijital saat ve saniye yazılır. Gün değişince takvim yaprağı
 * (ay, gün, haftanın günü) sayfanın diline göre (TR / EN) yenilenir. */
(function () {
    var saat = document.getElementById('navSaat');
    if (!saat || !window.Intl) {
        return;
    }
    var saniye = document.getElementById('navSaniye');
    var ibreler = {
        s: document.getElementById('ibreSaat'),
        d: document.getElementById('ibreDakika'),
        n: document.getElementById('ibreSaniye')
    };
    var dil = document.documentElement.lang === 'en' ? 'en-GB' : 'tr-TR';
    var bolge = { timeZone: 'Europe/Istanbul' };
    var sonGun = null;
    function parca(tarih, ayar) {
        return tarih.toLocaleString(dil, Object.assign({}, bolge, ayar));
    }
    function yaz() {
        var simdi = new Date();
        var hms = simdi.toLocaleTimeString('en-GB', Object.assign({ hour12: false }, bolge)).split(':');
        var h = +hms[0], m = +hms[1], sn = +hms[2];
        saat.innerHTML = hms[0] + '<span class="ns-nokta">:</span>' + hms[1];
        saniye.textContent = hms[2];
        ibreler.s.setAttribute('transform', 'rotate(' + ((h % 12) * 30 + m * 0.5) + ' 20 20)');
        ibreler.d.setAttribute('transform', 'rotate(' + (m * 6 + sn * 0.1) + ' 20 20)');
        ibreler.n.setAttribute('transform', 'rotate(' + (sn * 6) + ' 20 20)');

        var gun = parca(simdi, { day: 'numeric' });
        if (sonGun !== null && gun !== sonGun) {
            document.getElementById('navGun').textContent = gun;
            document.getElementById('navAy').textContent = parca(simdi, { month: 'short' }).replace('.', '').slice(0, 3).toLocaleUpperCase(dil);
            var hg = parca(simdi, { weekday: 'long' });
            document.getElementById('navHaftaGunu').textContent = hg.charAt(0).toLocaleUpperCase(dil) + hg.slice(1);
            document.getElementById('navAyYil').textContent = parca(simdi, { month: 'long', year: 'numeric' });
        }
        sonGun = gun;
    }
    yaz();
    setInterval(yaz, 1000);
})();
</script>

<script>
/* Giriş / kayıt formları:
 *  - Göz düğmesi parolayı gösterir / gizler (JavaScript kapalıysa düğme hiç görünmez).
 *  - Kayıt formunda parola yazıldıkça altındaki 4 parçalı çubuk parolanın gücünü gösterir
 *    (yalnızca yol gösterir; asıl kontrol PHP'de, hesapDogrula() içinde yapılır). */
(function () {
    document.querySelectorAll('.parola-goster').forEach(function (dugme) {
        var girdi = dugme.parentNode.querySelector('input');
        dugme.hidden = false;
        dugme.addEventListener('click', function () {
            var gizli = girdi.type === 'password';
            girdi.type = gizli ? 'text' : 'password';
            dugme.classList.toggle('acik', gizli);
            girdi.focus();
        });
    });
    var guc = document.querySelector('.parola-guc');
    var parola = document.getElementById('parola');
    if (guc && parola) {
        guc.hidden = false;
        parola.addEventListener('input', function () {
            var p = parola.value, puan = 0;
            if (p.length >= 8) { puan++; }
            if (/[A-Za-zÇĞİÖŞÜçğıöşü]/.test(p) && /[0-9]/.test(p)) { puan++; }
            if (/[A-ZÇĞİÖŞÜ]/.test(p) && /[a-zçğıöşü]/.test(p)) { puan++; }
            if (p.length >= 12 || /[^A-Za-z0-9ÇĞİÖŞÜçğıöşü]/.test(p)) { puan++; }
            guc.dataset.guc = p.length ? puan : 0;
        });
    }
})();
</script>

<?php if (girisYapildiMi()): ?>
<script>
/* Bildirim paneli:
 *  - Sayfada panelin dışında bir yere tıklanınca panel kapanır.
 *  - Panel açılınca (kullanıcı zile tıklayınca) zilin köşesindeki
 *    sayı kaldırılır ve bildirimler arka planda "okundu" yapılır (bildirim.php, islem=okundu).
 *    Sayfa yenilendiğinde de sayı tekrar görünmez; yalnızca yeni bir bildirim gelirse görünür. */
(function () {
    var menu = document.querySelector('.bildirim-menu');
    if (!menu) {
        return;
    }
    function okunduYap() {
        var sayi = menu.querySelector('.zil-sayi');
        if (sayi) {
            sayi.remove();
        }
        if (menu.dataset.okunmamis !== '0') {
            menu.dataset.okunmamis = '0';
            fetch('bildirim.php', { method: 'POST', body: new FormData(menu.querySelector('.bildirim-okundu-form')) });
        }
    }
    menu.addEventListener('toggle', function () {
        if (menu.open) {
            okunduYap();
        }
    });
    if (menu.open) {
        okunduYap();
    }
    document.addEventListener('click', function (olay) {
        if (menu.open && !menu.contains(olay.target)) {
            menu.open = false;
        }
    });

    // Yeni bildirim sesi: tarayıcı otomatik çalmayı engellediyse ilk tıklamada / tuşa basışta çalınır
    var ses = document.getElementById('bildirimSesi');
    if (ses && ses.play) {
        var bekleyen = ses.play();
        if (bekleyen && bekleyen.catch) {
            bekleyen.catch(function () {
                var cal = function () {
                    ses.play().catch(function () {});
                    document.removeEventListener('pointerdown', cal);
                    document.removeEventListener('keydown', cal);
                };
                document.addEventListener('pointerdown', cal);
                document.addEventListener('keydown', cal);
            });
        }
    }
})();
</script>
<?php endif; ?>

</body>
</html>
