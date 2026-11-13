🌤️ HavaTakip
> **İnternet Programcılığı I** — 3 Haftalık Bireysel PHP Proje Ödevi (3. hafta: teslim sürümü)\
> **Öğrenci:** Gencer Yıldız · **Öğrenci No:** 254309026 · **Son teslim tarihi:** 15 Kasım 2026\
> **Geliştirme ortamı:** XAMPP (Apache + PHP 8), saf PHP (framework kullanılmamıştır), HTML5, CSS3\
> *(Tüm işlevler PHP ile çalışır; birkaç küçük JavaScript yalnızca kullanım kolaylığı sağlar; JavaScript kapalı olsa da site çalışır.)*
🧾 Bilgi	
👤 Öğrenci	Ad Soyad: Gencer Yıldız / Öğrenci No: 254309026
🎯 Proje konusu	Kişisel hava durumu takip sistemi
🛠️ Teknolojiler	PHP 8, HTML5, CSS3; isteğe bağlı küçük JavaScript eklentileri (bkz. Ek C, teknik kurallar). Veritabanı yok: takip kayıtları `$_SESSION`'da, hesaplar ve ayarlar `veri/` klasöründeki JSON dosyalarında
🌐 Veri kaynağı	Open-Meteo Forecast, Air Quality ve Geocoding API'leri (ücretsiz, API anahtarı gerekmez)
📦 Teslim dosyası	`OgrenciNo_Ad_Soyad_PHP_Proje.zip`
📑 İçindekiler
📌 Projenin adı ve amacı
🚀 Projede bulunan özellikler
📂 Klasör yapısı
⚙️ XAMPP üzerinde projeyi çalıştırma adımları
⚠️ Bilinen eksikler ve sonraki aşama planları
🖼️ Ekran görüntüleri ve tanıtım videosu
🤖 Yapay zekâ kullanımı: araçlar, amaçlar, örnek istem, öğrencinin geliştirmeleri
📎 Ekler: kayıt alanları, fonksiyonlar, ödev maddeleri, güvenlik, testler, kaynaklar ve özelliklerin ayrıntıları
---
<a id="bolum-1"></a>
📌 1. Projenin adı ve amacı
1.1. Projenin adı
HavaTakip — Kişisel Hava Durumu Takip Sistemi
1.2. Projenin amacı ve kullanıcıya sağladığı faydalar
HavaTakip, Türkiye'nin 7 bölgesindeki 81 il ve 973 ilçe için anlık hava durumunu, 3 ve 7 günlük tahmini,
saat saat yağış bilgisini ve hava kalitesini gösteren bir web uygulamasıdır.
Giriş yapan kullanıcı kendi takip listesini oluşturur: bir ili veya ilçeyi kendi verdiği bir adla
("Ev", "Okul", "Annemler" …) kaydeder, kategori ve durum seçer, hangi durumlarda (yağış, don, rüzgâr, sıcak)
uyarılmak istediğini belirtir. Liste sayfasında her kayıt için canlı hava durumu ve kullanıcının seçtiği koşullara göre
kişisel uyarılar gösterilir; uyarılar ayrıca navbar'daki bildirim zili ile bildirilir.
Ziyaretçi kendi hesabını Kayıt Ol sayfasından açabilir; kayıtlarını sonradan düzenleyebilir, duraklatabilir
ya da silebilir. Ana sayfadaki Konumum düğmesi kullanıcının bulunduğu yere en yakın ilçenin havasını tek tıkla açar.
Kullanıcıya sağladığı temel faydalar:
📍 Önemli yerler tek ekranda: Ev, okul, iş yeri ya da ailenin yaşadığı şehir gibi yerlerin havası tek tek
aranmaz; hepsi Takip Listem sayfasında kendi verdiği adlarla yan yana görünür.
🔔 Kişisel uyarılar: Kullanıcı yalnızca kendisini ilgilendiren durumları seçer (ör. "yağış ihtimali %50'yi
geçerse"). Koşul gerçekleşince kartta uyarı çıkar, bildirim sesi çalar ve zilde kırmızı sayı belirir.
☂️ Ne giymeli, ne almalı: Uyarılar yalnızca "yağmur var" demez; şemsiye, mont, güneş gözlüğü gibi animasyonlu
simgelerle ne yapılması gerektiğini de söyler.
🗺️ Türkiye'nin her yeri: 7 bölge, 81 il ve 973 ilçe; arama kutusu Türkçe karakter bilmeden de bulur
("sanliurfa"), Konumum düğmesi en yakın ilçeyi kendisi seçer.
🍂 Keyifli ve anlaşılır görünüm: Site mevsime göre renk değiştirir (dört mevsim teması), il sayfasında havaya
göre yağmur, kar ya da güneş efekti oynar; açık / koyu tema ve Türkçe / İngilizce dil seçeneği vardır.
---
<a id="bolum-2"></a>
🚀 2. Projede bulunan özellikler
2.1. 🌍 Herkese açık
Ana sayfa: uygulamanın adı ve açıklaması, ortada şehir arama kutusu; arama kutusunun solunda İstanbul,
sağında Ankara için anlık hava kartı (simge, sıcaklık, en yüksek / en düşük, yağış ihtimali; iki şehir tek
istekte alınır, dar ekranda kartlar aramanın altında yan yana durur). 7 bölge kartı (il sayılarıyla). Her kartın
sağ üstünde o bölgeyi anlatan, elle çizilmiş hareketli bir simge vardır (ör. Marmara'da boğaz köprüsü, Karadeniz'de
bulut ve yağmur, Doğu Anadolu'da karlı dağlar; `bolgeSimgesi()`, fonksiyonlar/gorunum.php).
Bölge seçimi: bölgeye tıklanınca illeri listelenir; her il için anlık sıcaklık ve 3 günlük tahmin.
Arama: Türkçe karakter ve büyük/küçük harf duyarsız ("şanlı", "IZMIR"); "Tokat", "Erbaa", "İstanbul: Beşiktaş"
ya da "İstanbul: tüm ilçeler" yazılabilir. Aynı adlı ilçeler (ör. Ereğli) seçenek olarak gösterilir.
Yazarken kutunun altında öneri listesi açılır (`assets/arama.js`): il ve ilçeler simgeli satırlar hâlinde, yazılan
kısım vurgulu, il / ilçe etiketiyle gösterilir; ilk sonuç bir ilse altında "İl: tüm ilçeler" satırı çıkar.
↓ ↑ ile satırlar arasında gezilir, Enter seçili yeri açar, Esc listeyi kapatır; × düğmesi kutuyu temizler.
JavaScript kapalıysa form normal arama yapar.
Konumum: arama kutusundaki düğmeye basınca tarayıcı konum izni ister; izin verilirse size en yakın ilçe
(973 ilçenin hazır koordinatlarıyla) bulunur ve o yerin hava durumu sayfası açılır. İzin verilmezse ne yapılacağı
yazılır. Konum bilgisi sunucuya gönderilmez, hesaplama tarayıcıda yapılır.
İl / ilçe sayfası: anlık durum (tarih, gün, saat, hissedilen, nem, rüzgâr, yağış ihtimali, gün doğumu/batımı),
yağış beklenen saat aralıkları, günlük giyim ve şemsiye tavsiyeleri (simgeli), 7 günlük tahmin, bir güne tıklanınca
24 saatin tamamı. "İlçe seç" ile 973 ilçenin her birinin hava durumu görülebilir.
Hava kalitesi: Avrupa Hava Kalitesi İndeksi (AQI), renkli ölçek, tavsiye ve PM2.5, PM10, NO₂, O₃, SO₂, CO değerleri.
Hava efektleri: şehir açılınca ekranın ortasında havanın büyük, animasyonlu simgesi; kenarlardan bulut, yağmur,
kar, sis, şimşek, rüzgâr efektleri; gündüz güneş, gece ay ve yıldızlar.
Dört mevsim teması: sitenin renkleri ve ekrana düşen parçacıklar (kar tanesi, akçaağaç yaprağı, kiraz çiçeği,
gün ışığı) mevsime göre değişir.
28 animasyonlu simge (`ikonlar.php`): 17 hava simgesi ve 11 uyarı / tavsiye simgesi (şemsiye, yağmurluk, mont,
atkı-bere, hırka, bot, fırtına, rüzgâr, aşırı sıcak, güneş gözlüğü, güzel hava). Sayfanın üstündeki Tümü / Hava
simgeleri / Uyarı ve tavsiye simgeleri sekmeleri (JavaScript'siz) her grubu kaç animasyon olduğuyla gösterir; bildirim
zilinde de kullanılan uyarı simgeleri "Bildirimde" etiketiyle işaretlidir.
Footer: veri kaynağı, ödev bilgisi, tema bilgisi ve "© 2026 HavaTakip · Tüm hakları saklıdır." satırı
(yıl kendiliğinden güncellenir).
Koyu / açık tema ve Türkçe / İngilizce dil seçimi (navbar'ın en sağında); seçimler çerezde saklanır.
Telefon, tablet ve bilgisayarda uyumlu (responsive) görünüm; ayrıntı ve testler: 2.4. 📱 Responsive tasarım.
Modern navbar: ekranın iki kenarına kadar uzanmaz; sayfa içeriğiyle aynı hizada, köşeleri yuvarlatılmış yüzen bir
şerittir. Alt kenarında mevsim renginde kayan ışık çizgisi vardır; kaydırınca yalnızca gölgesi koyulaşır (yüksekliği
değişmez, bu yüzden sayfanın en üstünde titremez). Bağlantılar şeridin tam ortasında, açık renkli bir kutuda,
büyük harfle ve eşit aralıklı yazı tipiyle (JetBrains Mono) yazılıdır; navbar'daki düğme yazıları da aynı yazı tipindedir;
seçili sayfa koyu, dolu bir kutuyla işaretlidir ve fare hangi bağlantının üzerine gelirse bu kutu kayarak oraya gider,
fare çıkınca seçili sayfaya döner. Düğmeler köşeleri hafif yuvarlatılmış dikdörtgendir.
Tarih ve saat: bilgisayar ekranlarında navbar şeridinin solunda takvim yaprağı görünümünde bugünün tarihi
(ay, gün, haftanın günü), sağında ibreleri dönen analog kadran ve saniyesi işleyen dijital saat (Türkiye saati; dil
İngilizce seçilince tarih de İngilizce yazılır). 1680 pikselden dar ekranlarda (ör. 1280–1600 px dizüstü) şerit biraz
daralır ve kartlar sadeleşir (yalnızca takvim yaprağı ve saat); telefon ve tablette gizlenir.
Giriş ve kayıt ekranı: mevsim renginde tanıtım bölümü (süzülen animasyonlu hava simgeleri) ve form. Kayıt
sayfasında tanıtım solda, form sağda; giriş sayfasında tam tersi. Üstteki Giriş Yap / Kayıt Ol sekmeleriyle iki sayfa
arasında geçerken tanıtım bölümü kayarak yer değiştirir. Simgeli giriş kutuları ve parolayı göster düğmesi vardır.
Giriş yapılmamışken navbar'da Giriş Yap ve Kayıt Ol düğmeleri bulunur; ikisi de renklidir ve üzerine gelince
hafifçe kalkar.
Kayıt olma (`kayit.php`): ad soyad, kullanıcı adı, e-posta, parola + tekrarı ve onay kutusu; PHP tarafında
doğrulama (admin'in yeni hesap formuyla aynı kurallar), alan bazında hata mesajları, parola gücü çubuğu.
Kayıttan sonra kullanıcı otomatik giriş yapar ve boş Takip Listem sayfasına gider. Yeni hesap her zaman
"kullanıcı" rolündedir.
2.2. 👤 Giriş yapan kullanıcı
Takip Listem: yönetim paneliyle aynı tasarımda; üstte karşılama bölümü, simgeli sayaç kartları (toplam, aktif,
duraklatılmış, bugün uyarı veren), filtre kutusu ve sırayla beliren kayıt kartları. Her kartta canlı hava, kişisel
uyarılar (renkli, animasyonlu simgelerle) ve kartın üstünde durumu gösteren renkli şerit vardır.
Yeni kayıt ekleme (`ekle.php`): POST formu, il seçildikten sonra isteğe bağlı ilçe seçimi, PHP tarafında
doğrulama, alan bazında hata mesajları.
Kayıt düzenleme (`ekle.php?id=…`): her kartta Sil düğmesinin üstündeki ⚙ Düzenle ile kaydın ili / ilçesi, adı,
kategorisi, durumu, uyarıları, yağış eşiği ve açıklaması değiştirilir. Aynı form ve aynı doğrulama kullanılır;
kaydın numarası ve eklenme tarihi korunur (`takipGuncelle()`), bildirimler hemen yeni bilgilere göre güncellenir.
Hesap ayarları: navbar'da kullanıcı adı ile Çıkış düğmesinin arasındaki ⚙ dişli Hesabım sayfasını açar
(admin ve kullanıcı için aynı).
Kayıt silme (`sil.php`): POST ile, onay penceresiyle.
Durum değiştirme (`durum.php`): her kartın altındaki Aktif / Duraklatıldı düğmeleriyle takip duraklatılır ya da
yeniden aktif edilir; karttaki durum etiketi, sayaçlar ve bildirimler hemen güncellenir.
Filtreleme: kategoriye ve/veya duruma göre (GET), hızlı kategori düğmeleri.
Bildirim zili: seçilen uyarılar bugün veya yarın gerçekleşecekse sesli bildirim; okunmamış sayısı, tek tek ya da
toplu silme, bildirime tıklayınca o yerin hava durumu.
Hesabım: ad soyad, e-posta ve parola güncelleme, hesabı silme.
Ana sayfada özet: "Takip ettikleriniz" kartları. Giriş yapılmamışsa takip listesi hiçbir yerde görünmez.
2.3. 🛡️ Yönetici (admin)
Yönetim paneli (`yonetim.php`): üstte sayaç kartları ve bölüm sekmeleri; kullanıcı hesaplarını ekleme, düzenleme,
silme (CRUD); tüm takip kayıtlarını görme, kullanıcıya göre süzme ve silme.
İl ve ilçe koordinatları (Yönetim › Koordinatlar): 81 il bölgelere göre kart olarak listelenir, her kartta ilin
enlem/boylamı yazar. İle tıklanınca o ilin ilçelerinin koordinatları ve kaynağı (il merkezi / önbellek / hazır koordinat)
açılır; her satırdan ilçenin hava durumuna ya da haritadaki yerine (OpenStreetMap) gidilebilir. Tüm ilçeleri aç
düğmesi 81 ilin hepsini açar ve 973 ilçenin koordinatı birlikte görünür. Arama kutusu il ve ilçe adında arar
(ör. "Çankaya" yazınca Ankara açılır ve yalnızca Çankaya satırı görünür).
Bu liste API'ye istek atmaz.
Site teması: mevsim temasını otomatik (aya göre) ya da sabit bir mevsim olarak seçme.
İlçe kontrolü (`ilce-kontrol.php`): "Merkez" dışındaki 922 ilçenin koordinatını Geocoding API ile 50'şer 50'şer
toplu kontrol etme ve nereden geldiğini görme.
Kullanıcılar tablosu: her satırda aynı boyda, simgeli Kayıtları · Düzenle · Sil düğmeleri (kendi satırında
Sil yerine Hesabım).
Her özelliğin ayrıntısı ve test senaryoları Ekler bölümündedir.
2.4. 📱 Responsive tasarım (telefon / tablet / bilgisayar)
Site her ekran genişliğine uyum sağlar; telefonda ve tablette sayfa bozulmaz, yana kaydırma (yatay kaydırma çubuğu)
oluşmaz. Bunun için `header.php`'de `<meta name="viewport" content="width=device-width, initial-scale=1.0">` etiketi
ve `assets/style.css` içinde 40'tan fazla `@media` kuralı vardır. Hazır bir CSS kütüphanesi kullanılmamıştır;
düzen CSS Grid ve Flexbox ile elle yazılmıştır.
Ekran	Genişlik	Görünüm
📱 Telefon	≤ 560 px	Navbar'da logo, bildirim zili ve ☰ düğmesi; menü ☰ ile açılır (JavaScript'siz). Kartlar tek sütuna iner, formlar tam genişlikte, İstanbul / Ankara kartları aramanın altında yan yana
📱 Büyük telefon / küçük tablet	≤ 760 px	Geniş tablolar karta dönüşür ya da kendi içinde yana kayar; sayaç ve özet kartları sığdığı kadar sütuna dizilir
📟 Tablet	≤ 900 px	Giriş / kayıt ekranında tanıtım bölümü formun üstüne geçer
📟 Tablet (yatay)	≤ 1200 px	Menü yine ☰ ile açılır; tarih / saat kartları gizlenir
💻 Dizüstü / masaüstü	≥ 1201 px	Tam navbar (ortada menü); yanlarda tarih ve saat kartları, 1680 px'ten itibaren tam boy
Efektler başka cihazlarda da görünür. Mevsim parçacıkları (yapraklar, kar taneleri, çiçekler), il sayfasındaki
yağmur / kar / güneş efektleri, animasyonlu hava ve uyarı simgeleri ve bölge simgeleri tamamen CSS ve SVG ile
yapıldığından telefon, tablet ve bilgisayarda, güncel tarayıcılarda (Chrome, Edge, Firefox, Safari) oynar; ek bir
eklenti veya dosya gerekmez. Cihazda "hareketi azalt" (erişilebilirlik) ayarı açıksa bazı animasyonlar bilerek durdurulur.
Test edilen cihazlar (Chromium tarayıcısının cihaz öykünmesiyle): iPhone SE (320 px), iPhone 13 (390 px),
Galaxy S9+ (320 px), iPad Mini dikey (768 px) ve yatay (1024 px), iPad Pro 11 yatay (1194 px); ayrıca 360, 390, 768,
1024, 1366 ve 1920 px genişlikte 12 sayfa. Hiçbir sayfada yatay kaydırma oluşmamış, efektler her cihazda çalışmıştır
(ör. ana sayfada aynı anda 94 animasyonlu öğe).
---
<a id="bolum-3"></a>
📂 3. Klasör yapısı
```
hava-takip-3hafta/
├── index.php            Ana sayfa: arama (öneri listesi, Konumum), İstanbul / Ankara kartları, bölgeler, takip özeti
├── il.php               İl / ilçe sayfası: anlık durum, hava efekti, tavsiyeler, hava kalitesi, 7 gün, saatlik ayrıntı
├── ilce-secim.php       İl sayfasındaki ilçe listesi (il.php dahil eder)
├── giris.php            Giriş formu (form solda, tanıtım sağda)
├── kayit.php            Kayıt ol formu (yeni kullanıcı hesabı)
├── kimlik-yan.php       Giriş ve kayıt sayfalarındaki tanıtım bölümü (süzülen hava simgeleri)
├── cikis.php            Çıkış (POST)
├── takip.php            Takip Listem: sayaçlar, filtre, kayıt kartları, durum / Düzenle / Sil düğmeleri
├── ekle.php             Takip ekleme ve düzenleme formu (il + ilçe; ekle.php?id=3 → düzenleme)
├── sil.php              Kayıt silme (POST)
├── durum.php            Kaydın durumunu değiştirme: Aktif ⇄ Duraklatıldı (POST)
├── bildirim.php         Bildirim işlemleri: tek tek / toplu silme, okundu (POST)
├── hesabim.php          Kullanıcının kendi hesabı: görüntüleme, güncelleme, silme
├── hesap-duzenle.php    Admin: yeni hesap açma ve hesap düzenleme
├── hesap-sil.php        Admin: hesap silme (POST)
├── yonetim.php          Yönetim paneli (yalnızca admin): kullanıcılar, site teması, tüm kayıtlar, il / ilçe koordinatları
├── tema-ayar.php        Admin: mevsim temasını kaydetme (POST)
├── ilce-kontrol.php     Admin: ilçe koordinatlarını toplu kontrol etme aracı
├── ikonlar.php          Hava ve uyarı simgeleri (17 + 11 animasyonlu simge, sekmeli)
├── tema.php             Koyu / açık tema geçişi (çerez)
├── dil.php              Türkçe / İngilizce dil geçişi (çerez)
├── baslat.php           Her sayfanın başında: oturum, veri, fonksiyonlar, tema, dil
├── header.php           Ortak üst kısım: navbar, logo, tarih / saat kartları, bildirim zili, hesap, dil ve tema düğmeleri
├── footer.php           Ortak alt kısım (telif satırı) ve küçük JavaScript: navbar göstergesi, saat, bildirim paneli, parola göster
├── data.php             7 bölge, 81 il, 973 ilçe, kategoriler, durumlar, örnek kayıtlar, başlangıç hesapları
├── functions.php        Fonksiyon dosyalarını dahil eder
├── fonksiyonlar/
│   ├── genel.php        Metin, arama, tarih, güvenli çıktı, dişli simgesi
│   ├── hava.php         Open-Meteo API, hava kodları, simgeler, tavsiyeler, hava kalitesi
│   ├── takip.php        Takip listesi: ekle, düzenle, sil, durum değiştir, doğrula, filtrele, say; kaydın yeri (il / ilçe)
│   ├── oturum.php       Giriş / çıkış, yetki, CSRF, tek seferlik mesajlar
│   ├── hesap.php        Hesap CRUD (veri/kullanicilar.json)
│   ├── bildirim.php     Bildirim üretme, okundu, silme
│   ├── gorunum.php      Mevsim teması, mevsim parçacıkları, il sayfası hava efektleri, bölge simgeleri
│   ├── ilce.php         İlçe listesi, ilçe koordinatı (API'li ve API'siz), "il: ilçe" araması, harita bağlantısı
│   └── dil.php          Türkçe / İngilizce çeviri (sayfa çıktısını çevirir)
├── dil/
│   └── en.php           İngilizce sözlük (Türkçe → İngilizce)
├── veri/
│   ├── .htaccess        Klasörün tarayıcıdan açılmasını engeller
│   ├── kullanicilar.json  Kullanıcı hesapları (parolalar özet olarak)
│   ├── ilce-merkezleri.json  "Merkez" dışındaki 922 ilçenin hazır koordinatı (API bulamazsa ve Konumum'da kullanılır)
│   ├── ilce-konumlari.json   API'den bulunan ilçe koordinatları (site kullanılırken otomatik oluşur)
│   └── ayarlar.json     Admin'in seçtiği site teması (tema kaydedilince otomatik oluşur)
├── assets/
│   ├── style.css        Tüm stiller (açık / koyu tema, mevsimler, efektler, mobil)
│   ├── arama.js         Ana sayfadaki arama öneri listesi ve Konumum düğmesi
│   ├── bildirim.wav     Bildirim sesi
│   ├── ikonlar/         17 animasyonlu hava simgesi (SVG)
│   ├── tavsiye/         11 animasyonlu uyarı / tavsiye simgesi: mont, hırka, yağmurluk, şemsiye, rüzgâr … (SVG)
│   ├── mevsim/          Mevsim parçacıkları: kar tanesi, akçaağaç yaprakları, kiraz çiçeği, gün ışığı (SVG)
│   └── sahne/           Hava efektlerindeki bulutlar (SVG)
│   ├── Ana Sayfa/       6 görüntü: mevsim açılışı, ana sayfa, koyu tema, bölgeler, giriş
│   ├── Kullanıcı/       8 görüntü: kullanıcı ana sayfası, Hesabım, Takip Listem, ekleme, bildirim, duraklatma
│   └── Admin/           4 görüntü: yönetici Hesabım ve Yönetim paneli
├── README.md            Bu açıklama dosyası
└── README.docx          Aynı açıklamanın Word biçimi
```
---
<a id="bolum-4"></a>
⚙️ 4. XAMPP üzerinde projeyi çalıştırma adımları
XAMPP'ı kurun ve XAMPP Control Panel'den Apache'yi başlatın (PHP 8 veya üzeri).
MySQL gerekmez.
ZIP dosyasını açın ve `hava-takip-3hafta` klasörünü `C:\xampp\htdocs\` içine kopyalayın.
Tarayıcıda (Chrome, Edge veya Firefox) `http://localhost/hava-takip-3hafta/` adresini açın.
PHP dosyalarına çift tıklayarak açmayın; sayfalar mutlaka `localhost` üzerinden açılmalıdır.
Giriş yapmak için aşağıdaki test hesaplarından birini kullanın ya da Kayıt Ol ile yeni bir hesap açın.
Konumum düğmesi de `localhost` üzerinden açıldığında çalışır (tarayıcı konum izni ister).
Hava durumu, hava kalitesi ve ilçe koordinatı verileri internetten alındığı için bilgisayarın internete bağlı olması
gerekir. "Hava durumu verisi alınamadı" mesajı görülürse `C:\xampp\php\php.ini` dosyasında:
`allow_url_fopen=On` olmalı,
`extension=openssl` satırının başında `;` olmamalı,
ardından Apache yeniden başlatılmalıdır. İnternet olmasa da site çökmez; kayıtlar listelenir ve efektler oynar,
yalnızca hava bilgisi yerine uyarı mesajı görünür. Sayfa eski görünürse Ctrl+F5 ile yenileyin.
4.1. 🔑 Test hesapları
Rol	Kullanıcı adı	Parola	Yetkiler
Yönetici	`admin`	`Admin123`	Kendi takip listesi + Yönetim paneli (hesaplar, site teması, tüm kayıtlar, il / ilçe koordinatları)
Kullanıcı	`kullanici`	`Kullanici123`	Kendi takip listesini görme, ekleme, düzenleme, silme, filtreleme; Hesabım
Yeni bir hesap da navbar'daki Kayıt Ol düğmesiyle açılabilir (rolü her zaman "kullanıcı" olur; boş takip listesiyle başlar).
Parolalar açık metin olarak değil, `password_hash()` ile üretilmiş özet (hash) olarak `veri/kullanicilar.json`
dosyasında saklanır; giriş sırasında `password_verify()` ile kontrol edilir.
Her hesap ilk girişte örnek kayıtlarla başlar (`kullanici`: 5 kayıt, `admin`: 2 kayıt).
Takip listesi sayfasının altındaki "Örnek kayıtlara dön" düğmesiyle liste sıfırlanabilir.
4.2. 📍 İlçe koordinatları
İlçe adları `data.php` içindedir. Bir ilçenin koordinatı iki yoldan gelir:
API'den (Geocoding): İlçe ilk açıldığında adı Open-Meteo Geocoding API
(veriler GeoNames) ile sorulur, ör. "Erbaa, Tokat". Gelen enlem/boylam (genelde ilçenin şehir merkezi)
`veri/ilce-konumlari.json` dosyasına kaydedilir; sonraki açılışlarda API'ye sorulmadan dosyadan okunur.
Hazır koordinat (yedek): API ilçeyi bulamazsa ya da aynı adlı başka bir yeri bulursa
`veri/ilce-merkezleri.json` dosyasındaki hazır koordinat kullanılır. Bu dosyadaki koordinatlar da Open-Meteo
Geocoding'den (GeoNames) bir kez toplanıp kaydedilmiştir (ayrıntı: Ek J).
"Merkez" ilçesi için il merkezinin koordinatı (`data.php`) kullanılır.
İsteğe bağlı: admin olarak giriş yapıp `ilce-kontrol.php` sayfasında "Sonraki 50 ilçe" ile ilerlenirse tüm ilçeler
önceden bulunur ve bulunamayan ilçe varsa listelenir.
---
<a id="bolum-5"></a>
⚠️ 5. Bilinen eksikler ve sonraki aşama planları
E-posta doğrulaması yok. Kayıt olurken e-posta adresinin biçimi ve benzersizliği kontrol edilir, ancak adrese
doğrulama e-postası gönderilmez. → Sonraki aşamada doğrulama bağlantısı.
Veritabanı yok. Takip kayıtları `$_SESSION` içinde tutulur: tarayıcı kapanınca veya oturum süresi dolunca
sıfırlanır; yönetim paneli yalnızca aynı tarayıcıda giriş yapılmış hesapların kayıtlarını görür.
Hesaplar ve ayarlar `veri/` klasöründeki JSON dosyalarında kalıcıdır. → Sonraki aşamada MySQL ile kalıcı ve ortak veri.
Bildirimler yalnızca site açıkken oluşur ve görünür; e-posta veya telefon bildirimi yok.
→ Zamanlanmış görev (cron) ile e-posta bildirimi.
Şehrin içindeki yeni ilçeler (ör. Çukurova, Karesi, Ortahisar, Haliliye) için API'de ilçe adıyla bir yer
bulunmadığından hazır koordinat olarak il merkezi kullanılır; bu ilçelerde hava durumu il merkezininkiyle aynıdır.
İngilizce çeviri sözlükle yapılır: sözlüğe eklenmemiş yeni bir metin Türkçe görünür; kullanıcının yazdığı
takip adları ve şehir adları çevrilmez.
Bildirim sesi tarayıcı izin vermezse ilk sayfada değil, kullanıcının ilk tıklamasında çalar.
Konumum yalnızca güvenli bağlantıda çalışır: `http://localhost` ve `https://` adreslerinde tarayıcı konum izni
ister; başka bir bilgisayardan `http://192.168…` gibi bir adresle açılırsa tarayıcı konum vermez.
Giriş ↔ kayıt geçişi iki ayrı PHP sayfası arasında yapıldığı için sayfa yenilenir; kayma animasyonu yeni sayfa
açıldıktan sonra oynar. → Sonraki aşamada iki form tek sayfada, JavaScript ile yenilemeden geçiş.
Tarih ve saat kartları navbar'ın yanında yer gerektirdiği için yalnızca 1201 pikselden geniş ekranlarda görünür.
Ödev kapsamı gereği sunucuda ölçek / performans testi yapılmamıştır.
---
<a id="bolum-6"></a>
🖼️ 6. Ekran görüntüleri ve tanıtım videosu
> 📁 **Ekran görüntüleri proje klasöründe değil, Google Drive'dadır.** Drive bağlantıları (proje yedeği; tanıtım
> videosu ve ekran görüntüleri) ödev teslim mesajında GitHub bağlantısıyla birlikte paylaşılmıştır.
Drive'daki klasörde AnaSayfa, Kullanıcı ve Admin olmak üzere üç alt klasör vardır; görüntüler sıra
numaralıdır (toplam 22 görüntü: Ana Sayfa 6, Kullanıcı 11, Admin 5). Görüntüler XAMPP üzerinde gerçek hava verisiyle,
3. haftanın son tasarımıyla alınmıştır. Kullanıcı görüntüleri bir takip kaydının baştan sona yolculuğunu sırayla
gösterir: ekleme → bildirim → duraklatma → düzenleme → yeniden aktif etme → yeni bildirim.
6.1. ✅ Ödevde istenen ekran görüntüleri
Ödevde istenen	Ekran görüntüsü
Ana sayfa	`AnaSayfa/2.Ana Sayfa.png`
Kayıt listeleme ekranı	`Kullanıcı/4.Kullanıcı TakipListem.png`
Yeni kayıt ekleme ekranı	`Kullanıcı/5.Kullanıcı Yeni Takip Ekle.png`
Filtreleme veya kayıt silme işlemi	`Kullanıcı/4.Kullanıcı TakipListem.png` (filtre alanı ve Sil düğmeleri), `Admin/4.Admin Yönetim.png` (kayıt silme)
Kayıt düzenleme (ek)	`Kullanıcı/9.Kullanıcı Edilen Takip Düzenlemesi.png`
6.2. 🏠 Ana Sayfa (`AnaSayfa/`)
Dosya	İçerik
`AnaSayfa/1.Mevsim Tema Ana Sayfa ilk Açılış.png`	Ana sayfanın ilk açılışı: sonbahar teması, ekrana düşen akçaağaç yaprakları, ortada mevsim simgesi ve "Sonbahar" yazısı
`AnaSayfa/2.Ana Sayfa.png`	Ana sayfa (açık tema): navbar ve yanındaki tarih / saat kartları, İstanbul ve Ankara hava kartları, Konumum düğmeli arama kutusu, örnek aramalar, bölge kartları
`AnaSayfa/3.KoyuTema AnaSayfa.png`	Ana sayfa (koyu tema)
`AnaSayfa/4.Bölgeler.png`	7 coğrafi bölge kartı: il sayıları ve sağ üstte bölgeyi anlatan hareketli simge; altta footer ve "© 2026 HavaTakip · Tüm hakları saklıdır." yazısı
`AnaSayfa/5.Bölgeler Tıkladığımızda.png`	Bir bölgeye (Marmara) tıklanınca illerin anlık sıcaklığı ve 3 günlük tahmini
`AnaSayfa/6.Giriş Ekranı.png`	Giriş ekranı: solda form (Giriş Yap / Kayıt Ol sekmeleri, parolayı göster düğmesi), sağda mevsim renginde, hava simgeleri süzülen tanıtım bölümü

6.3. 👤 Kullanıcı (`Kullanıcı/`)
Dosya	İçerik
`Kullanıcı/1.Kullanıcı AnaSayfa.png`	Giriş yapan kullanıcının ana sayfası: "Takip ettikleriniz" özet kartları; navbar'da Takip Listem sayacı, bildirim zili, kullanıcı adı, ⚙ dişli ve Çıkış
`Kullanıcı/2.Kullanıcı Ayarları.png`	Hesabım (navbar'daki ⚙ dişliyle açılır): hesap bilgileri ve bilgileri güncelleme formu
`Kullanıcı/3.Kullanıcı Ayarları.png`	Hesabım: parola değiştirme ve hesabı silme (onay için parola ve onay kutusu)
`Kullanıcı/4.Kullanıcı TakipListem.png`	Takip Listem: karşılama bölümü, simgeli sayaç kartları (toplam / aktif / duraklatıldı / bugün uyaran), kategori hızlı filtresi, kategori / durum filtresi ve kayıt kartları
`Kullanıcı/5.Kullanıcı Yeni Takip Ekle.png`	Yeni takip ekleme formu: il, ilçe (isteğe bağlı), takip adı, kategori, durum ve uyarı seçimi
`Kullanıcı/6.Kullanıcı Yeni Takip Takip Listesinde Gormesi Bildirim.png`	Yeni kayıt (ev · Çanakkale) eklendikten sonra listenin başında vurgulu kart; bildirim sesi çalar ve zilde kırmızı okunmamış sayısı çıkar (panel kendiliğinden açılmaz)
`Kullanıcı/7.Kullanıcı Yeni Takip Takip Listesinde Gormesi Bildirim.png`	Zile tıklanınca açılan bildirim paneli: animasyonlu şemsiye simgesiyle yağış uyarıları, "Tümünü temizle" ve tek tek kapatma (×) düğmeleri
`Kullanıcı/8.Kullanıcı Yeni Takip Takip Listesinde Duraklatması.png`	Kaydın Duraklatıldı düğmesiyle duraklatılması; kart soluklaşır ve uyarı vermez
`Kullanıcı/9.Kullanıcı Edilen Takip Düzenlemesi.png`	Kartın ⚙ Düzenle düğmesiyle açılan "Takibi düzenle" formu: il, ilçe, ad, kategori, durum ve uyarılar önceki değerleriyle dolu gelir
`Kullanıcı/10.Kullanıcı Yeni Takip Takip Listesinde Aktif Etmesi.png`	Düzenlenen kaydın yeniden aktif edilmesi: kartta animasyonlu şemsiye simgeli uyarı ("Bugün yağış ihtimali %13 (eşiğiniz %10)") ve zilde yeni bildirim sayısı
`Kullanıcı/11.Kullanıcı Yeni Takip Takip Listesinde Düzenledikten ve Aktif Ettikten Sonra Bildirim.png`	Düzenleme ve aktif etme sonrası bildirim panelinde yeni uyarı

6.4. 🛡️ Admin (`Admin/`)
Dosya	İçerik
`Admin/1.Admin Ayarlar.png`	Yöneticinin Hesabım sayfası; navbar'da yöneticiye özel Yönetim bağlantısı
`Admin/2.Admin Ayarlar2.png`	Parola değiştirme; sistemdeki tek yönetici olduğu için hesap silme yerine uyarı
`Admin/3.Admin Yönetim.png`	Yönetim paneli: karşılama bölümü (Yeni hesap, Koordinatlar, İlçe kontrolü), bölüm sekmeleri, sayaç kartları ve site teması (dört mevsim) seçimi
`Admin/4.Admin Yönetim.png`	Kullanıcılar tablosu (simgeli Kayıtları / Düzenle / Hesabım / Sil düğmeleri) ve tüm kullanıcıların takip kayıtları tablosu (kullanıcıya göre süzme, Sil)
`Admin/5.Admin Yönetim.png`	İl ve ilçe koordinatları: bölgelere göre 81 il kartı, il / ilçe arama kutusu, "Tüm ilçeleri aç" düğmesi ve koordinat kaynağı sayaçları
6.5. 🎬 Tanıtım videosu
▶️ Uygulamanın amacını anlatan ve temel özelliklerini çalışır hâlde gösteren yaklaşık 4 dakikalık tanıtım videosu,
ekran görüntüleriyle birlikte Google Drive klasöründedir (bağlantı teslim mesajındadır).
---
<a id="bolum-7"></a>
🤖 7. Yapay zekâ kullanımı
Bu bölüm, ödev yönergesinin 7. maddesindeki Kural 4 — Yapay zekâ kullanımını belirtmelisiniz gereği hazırlanmıştır.
Kural 4'ün istediği dört bilgi aşağıda sırasıyla verilmiştir.
7.1. 🧰 Kullanılan yapay zekâ araçları
Araç	Geliştirici
Claude	Anthropic
7.2. 🎯 Bu araçlardan hangi amaçlarla yararlanıldığı
Yönergede sayılan amaçlara göre:
Yönergedeki amaç	Bu projede
Proje konusu araştırma	Veri kaynaklarının (Open-Meteo hava durumu, hava kalitesi ve geocoding API'leri) uygunluğunun araştırılması
PHP kodu oluşturma	Sayfaların, formların, oturum / giriş, takip listesi, bildirim, hesap ve dil işlemlerinin kodlanması
Fonksiyon yazma	`fonksiyonlar/` klasöründeki kullanıcı tanımlı fonksiyonlar (ör. `takipDogrula()`, `tavsiyeler()`, `bildirimleriGuncelle()`)
Kullanıcı arayüzü tasarlama	CSS, animasyonlu SVG hava ve uyarı simgeleri, mevsim parçacıkları, hava efektleri, logo, navbar, giriş / kayıt ekranı, arama öneri kutusu, responsive (telefon / tablet) düzen
Kodlardaki hataları bulma	Hataların bulunması ve sayfaların farklı senaryolarla (farklı hava durumları, telefon görünümü, İngilizce) test edilmesi
Kodları açıklama	Kodun satır satır açıklanması; proje çalışma raporunda her işin hangi dosyanın hangi satırında olduğunun gösterilmesi
Uygulama geliştirme konusunda öneriler alma	Form doğrulama, güvenlik (XSS, CSRF, parola özeti), Post/Redirect/Get, veri saklama yöntemleri
Ayrıca: İngilizce sözlüğün hazırlanması, 922 ilçenin hazır koordinatının Open-Meteo Geocoding'den toplanması ve
bu README'nin yazımında yardım alınmıştır.
7.3. 💬 Kullanılan örnek bir istem (prompt)
> "Admin panelinde 4 mevsim temasını ben değiştirebilmeliyim ve otomatik yapabilmeliyim tekrardan diye düşündüm.
> Sayfa açılışında hangi mevsim varsa, mesela kış kar taneleri düşsün ekrana, sonbaharsa yaprak düşsün gibi
> 4 mevsimi böyle optimize edelim."
Diğer örnekler:
> "Takip listesinde silme ve ekleme işlemini yapabilmeliyiz. Admin ve kullanıcı olarak birer hesap ver bana.
> Takip listesi giriş yapmazsak ana ekranda gözükmemeli."
> "Takip eklede ilçede seçebilmeliyiz il seçtikten sonra."
> "Bazı ilçelerin enlem boylamları alınamamış galiba, en basit Adana Çukurova'nın verisi yok. Verisi olmayanları belirle
> ve onların da koordinatlarını bul."
> "Yönetim yerinde hem ilçe hem il koordinatlarını görebilmeliyim; il koordinatı ve ilçe koordinatları aynı yerde olsun,
> önce il koordinatını görsün, üstüne tıkladığında o ilin ilçe koordinatlarını görsün."
> "Arama yerimiz siyah bir şekilde, modern olmayan bir görünüm oluyor; Tokat yazdığım örnekteki gibi gözükmeli.
> Konumum özelliği de getir."
7.4. ✍️ Öğrencinin kendi yaptığı geliştirmeler
Proje fikri, özellikler ve tasarım kararları öğrenciye aittir. Yapay zekâ bu fikirleri koda dökmek için kullanılmış;
her değişiklik öğrenci tarafından kendi bilgisayarında (XAMPP) denenmiş, beğenilmeyen veya hatalı bulunan kısımlar
öğrencinin geri bildirimiyle değiştirilmiştir.
Proje fikri ve kapsam
Proje konusunun seçilmesi; polen takibi fikrinden, polen verisinin Türkiye için yetersiz olduğu fark edilince
kişisel hava durumu takibine geçilmesi kararı.
Open-Meteo veri kaynağının bulunması; Türkiye'yi 7 bölge, 81 il ve ilçeler üzerinden ele alma; şehir bazında arama fikri.
Her haftanın ayrı klasörde saklanması (`hava-takip-1hafta`, `hava-takip-2hafta`, `hava-takip-3hafta`).
Özellik fikirleri
7 günlük tahmin, saat saat ayrıntı, yağışın hangi saatlerde beklendiğinin gösterilmesi, tarih ve gün bilgisi.
"Şemsiye alın / sıkı giyinin" gibi günlük tavsiyeler.
Admin ve kullanıcı rolleri; takip listesinin yalnızca giriş yapınca görünmesi. 2. haftada da giriş, veri ekleme ve
silme olması (hoca açıp göstermemi isterse gösterebilmek için).
Hesap CRUD: kullanıcının adını, e-postasını değiştirebilmesi; hem admin'in hem kullanıcının hesap silebilmesi.
Bildirim zili: yalnızca giriş yapan kullanıcıda (3. hafta), sesli bildirim, tek tek ve toplu silme. Zile bakınca
sayının kaybolması, sayfada boş bir yere tıklayınca panelin kapanması, bildirime tıklayınca o yerin havasının
ekranın ortasında gösterilmesi.
Hava kalitesi verisinin il sayfasına eklenmesi.
Dört mevsim teması: yılın aylarına göre otomatik değişmesi ve admin panelinden mevsimin seçilebilmesi / tekrar
otomatiğe alınabilmesi.
Türkçe / İngilizce dil seçeneği.
Takip eklerken il seçildikten sonra ilçe de seçilebilmesi.
Takip kayıtlarının sonradan düzenlenebilmesi (kartta Sil'in üstünde ⚙ "Düzenle" düğmesi).
Ziyaretçinin kendi hesabını açabilmesi için kayıt ol sayfası; navbar'da Giriş Yap ve Kayıt Ol düğmeleri.
Yönetim panelinde il ve ilçe koordinatlarının aynı yerde görünmesi: önce il, tıklanınca o ilin ilçeleri.
Yeni bildirimde panelin kendiliğinden açılmaması; yalnızca sesin çalması ve zilde kırmızı okunmamış sayısının
(99'dan fazlaysa "99+") görünmesi, paneli kullanıcının kendisinin açması.
Ana sayfada Konumum özelliği ve arama kutusunun yazarken öneri listesi göstermesi.
Ana sayfada arama kutusunun solunda İstanbul, sağında Ankara hava kartları.
Navbar'ın iki yanındaki boşluğa tarih ve saat; footer'a "Tüm hakları saklıdır" satırı.
Simgeler sayfasında uyarı animasyonlarının da kaç tane olduklarıyla birlikte gösterilmesi.
Tasarım fikirleri
Lacivert / deniz mavisi / gri renk paleti, geniş navbar, sayfanın ortasında arama kutusu, koyu / açık tema.
Tema düğmesinin ve dil seçiminin (TR / EN) navbar'ın en sağında durması.
Bütün sayfaların %90 ölçekte daha iyi göründüğünün fark edilmesi; tarayıcı %100'deyken ve telefonda da bu görünümün korunması.

hafta yeniden tasarım: ikonların renk paleti çok değişmeden daha estetik ve animasyonlu hâle getirilmesi;
16 hava simgesi ve gece için ay simgesi.
Şehir sayfası açılınca havaya göre sağdan ve soldan görsel efektler gelmesi; şehre tıklayınca o şehrin havasının
ekranın tam ortasında büyük ve animasyonlu görünmesi (bulut ileri geri gider, güneş hafif parlayıp söner ve
nefes alır gibi büyüyüp küçülür).
Sayfa açılışında mevsime göre ekrana düşen parçacıklar: kışın kar tanesi, sonbaharda yaprak (öğrencinin
verdiği görsele göre akçaağaç yaprağı), ilkbaharda kiraz çiçeği, yazın gün ışığı.
Navbar'ın ekranın iki kenarına kadar uzanmaması (yanlarının "kesilmesi"); bağlantıların gönderilen örnekteki gibi
açık renkli bir kutuda, büyük harfle ve kayan koyu seçim kutusuyla gösterilmesi, menünün tam ortada durması,
navbar'daki tüm yazıların aynı yazı tipinde olması; düğmelerin hap değil köşeleri hafif yuvarlatılmış dikdörtgen
olması; kullanıcı adı ile Çıkış arasına ⚙ dişli (hesap ayarları).
Bölge kartlarının boş sağ üst köşesine bir şey eklenmesi (→ bölgeyi anlatan, elle çizilmiş hareketli bölge simgesi).
Takip Listem ve Yönetim sayfalarının daha modern ve animasyonlu olması; bildirim panelinde ve uyarılarda
"yağmurluğunu al" gibi animasyonlu simgeler.
Giriş ve kayıt ekranının sitenin konseptine göre modern tasarlanması; kayıtta görselin solda, girişte sağda olması.
Tarih ve saatin daha modern görünmesi (→ takvim yaprağı ve ibreleri dönen saat).
"Bugün için tavsiyeler" bölümüne mont, hırka, yağmurluk, şemsiye, rüzgâr gibi simgeler.
Navbar amblemi: önce dönen vektörel dünya, ardından güneşin ortada durduğu, dünyanın güneşin etrafında dolaşıp
kendi ekseni etrafında döndüğü logo; güneş ışınlarının dönmemesi.
Bildirim zilinin beyaz olması.
Site teması seçilirken yalnızca seçilen mevsimin vurgulanması (eski seçimin vurgusunun kalkması).
Gündüz güneş, gece ay gösterilmesi.
Test ederek bulunan hatalar ve eksikler
%98 yağışta "hırka alın" tavsiyesinin mantıksız olduğunun fark edilmesi ve tavsiye kurallarının düzeltilmesinin istenmesi.
Efektlerin bilgisayarda görünmediğinin fark edilmesi (Windows'un animasyon ayarı nedeniyle) ve her bilgisayarda
görünecek şekilde düzeltilmesi; diğer bilgisayarlarda da çalıştığının kontrol ettirilmesi.
Mevsim parçacıklarının ekrana düşmediğinin fark edilmesi.
Adana / Çukurova gibi bazı ilçelerin hava verisinin gelmediğinin fark edilmesi; verisi olmayan ilçelerin belirlenip
koordinatlarının bulunmasının istenmesi; koordinatların nereden geldiğinin sorgulanması ve README'de belirtilmesi.
Bildirim sayısının okunduktan sonra kaybolmaması, panelin dışarı tıklayınca kapanmaması gibi kullanım sorunlarının bulunması.
Projenin ve README'nin ödevin teslim biçimine (Madde 10) ve yapay zekâ kurallarına (Madde 7) göre kontrol ettirilmesi.
Telefonda ve tablette görünümün kötü olduğunun fark edilmesi (navbar'ın üç satıra taşması, sıkışan tablolar)
ve responsive düzenin istenmesi (☰ menü, karta dönüşen tablolar).
Navbar sayfanın en üstüne tam oturmadığında sürekli yanıp söndüğünün fark edilmesi (kaydırınca navbar'ın
yüksekliği değişiyor, bu da sayfayı kaydırıp sınıfı tekrar değiştiriyordu; yükseklik sabitlenerek düzeltildi).
Tarih ve saatin dizüstü bilgisayarda (Windows %125 ölçek) görünmediğinin fark edilmesi; orta genişlikteki
ekranlar için sadeleştirilmiş kartlar eklendi.
Ankara sayfasında PHP uyarıları çıktığının fark edilmesi: navbar'a eklenen bir değişkenin adı il sayfasındaki
değişkenle aynıydı ve onu eziyordu; değişken adları değiştirildi, tüm sayfalar uyarı için tek tek tarandı.
Ege bölgesinde Çanakkale'nin olmadığının sorulması; bölge ve ilçe verilerinin resmî listeyle karşılaştırılması
(Çanakkale resmî olarak Marmara Bölgesi'ndedir; 81 il ve 973 ilçe doğru bölgelerde).
---
<a id="bolum-8"></a>
📎 8. Ekler
🗃️ Ek A. Bir takip kaydının alanları
Alan	Açıklama	Doğrulama
`il`	Takip edilen il	Zorunlu, 81 il listesinde olmalı
`ilce`	Takip edilen ilçe (boşsa il merkezi)	İsteğe bağlı; seçildiyse o ilin ilçelerinden biri olmalı
`baslik`	Kullanıcının verdiği ad	Zorunlu, 2–40 karakter, aynı yerde (il + ilçe) aynı ad tekrar edemez
`kategori`	Ev, İş/Okul, Aile, Seyahat, Diğer	Zorunlu, listedeki değerlerden biri
`durum`	Aktif / Duraklatıldı	Zorunlu, listedeki değerlerden biri; sonradan kartın altındaki düğmelerle değiştirilebilir (aktif olması için en az bir uyarı seçili olmalı)
`uyarilar`	Yağış, Don, Rüzgâr, Sıcak (çoklu seçim)	Aktif kayıtta en az biri seçilmeli
`yagisEsigi`	Yağış uyarısı için yüzde	"Yağış" seçiliyse 10–100 arası tam sayı
`aciklama`	Kısa not	İsteğe bağlı, en fazla 200 karakter
`eklenme`	Eklenme zamanı	Otomatik
🧩 Ek B. Kullanıcı tanımlı fonksiyonlar (seçmeler)
Projede toplam 103 kullanıcı tanımlı fonksiyon vardır; konularına göre `fonksiyonlar/` klasöründeki 9 dosyaya
ayrılmıştır ve `functions.php` hepsini dahil eder. Önemli olanlar:
Fonksiyon	Dosya	Görevi
`takipDogrula()`	fonksiyonlar/takip.php	Form verisini doğrular, alan bazında hata listesi döndürür
`takipEkle()` / `takipGuncelle()` / `takipSil()`	fonksiyonlar/takip.php	Oturumdaki listeye kayıt ekler / kaydı düzenler / siler
`takipDurumDegistir()`	fonksiyonlar/takip.php	Kaydı duraklatır ya da yeniden aktif eder (aktif kayıtta en az bir uyarı olmalı)
`takipFiltrele()`	fonksiyonlar/takip.php	Kayıtları kategori ve duruma göre süzer
`takipSayaclari()`	fonksiyonlar/takip.php	Toplam, duruma ve kategoriye göre sayıları hesaplar
`takipUyarilari()`	fonksiyonlar/takip.php	Kullanıcının seçtiği koşullara göre bugünün uyarılarını bulur
`girisYap()` / `girisGerekli()` / `adminGerekli()`	fonksiyonlar/oturum.php	Giriş ve sayfa erişim kontrolü
`ilAra()` / `slugYap()`	fonksiyonlar/genel.php	Türkçe karakterden bağımsız il araması
`bolgeHavaGetir()`	fonksiyonlar/hava.php	Birden çok ilin hava durumunu tek istekle alır
`yagisAraliklari()`	fonksiyonlar/hava.php	Ardışık yağışlı saatleri "15:00–19:00" gibi aralıklara çevirir
`tavsiyeler()`	fonksiyonlar/hava.php	Sıcaklık, hissedilen sıcaklık, yağış, rüzgâr ve UV'yi birlikte değerlendirerek giyim, şemsiye ve güvenlik tavsiyesi üretir (ör. serin + yağmurlu → "hırka yetmez, yağmurluk giyin")
`e()`	fonksiyonlar/genel.php	`htmlspecialchars()` kısayolu (güvenli çıktı)
`hesapDogrula()` / `hesapEkle()` / `hesapGuncelle()` / `hesapSil()`	fonksiyonlar/hesap.php	Hesap CRUD işlemleri (`veri/kullanicilar.json`)
`bildirimleriGuncelle()` / `bildirimPaneli()`	fonksiyonlar/bildirim.php	Takip listesindeki uyarılara göre bildirim üretir, zil panelini hazırlar
`aktifMevsim()` / `sahneHtml()`	fonksiyonlar/gorunum.php	Mevsim teması ve il sayfasındaki hava efekti
`bolgeSimgesi($kod)`	fonksiyonlar/gorunum.php	Bölge kartının sağ üstündeki elle çizilmiş, hareketli bölge simgesi (SVG)
`havaKalitesiGetir()` / `havaKalitesiDurumu()`	fonksiyonlar/hava.php	Hava kalitesi (AQI) verisi ve kategorisi
`ilceKonumu()` / `takipKonumu()`	fonksiyonlar/ilce.php, takip.php	İlçe koordinatı (API, önbellek ya da hazır koordinat)
`sayfayiCevir()`	fonksiyonlar/dil.php	İngilizce seçiliyse sayfa çıktısını sözlükle çevirir
`ilceKonumuApisiz()` / `haritaAdresi()`	fonksiyonlar/ilce.php	Yönetim › Koordinatlar için API'ye sormadan koordinat ve kaynağı; OpenStreetMap bağlantısı
`uyariIkonu()`	fonksiyonlar/hava.php	Uyarı türüne göre animasyonlu simge (bildirim paneli ve Takip Listem kartları)
`disliSimgesi()`	fonksiyonlar/genel.php	Navbar'daki hesap ayarları ve "Düzenle" düğmesindeki dişli simgesi
☑️ Ek C. Ödevdeki zorunlu özelliklerin karşılığı
Ödev maddesi	Projede
3.1 Ana sayfa	`index.php`: ad, açıklama, gezinme menüsü
3.2 Listeleme (döngü, ≥4 alan)	`takip.php`, `index.php`, `il.php`: `foreach` ile; kayıtlarda 9 alan
3.3 Yeni kayıt (POST)	`ekle.php`, ardından listeye yönlendirme (Post/Redirect/Get)
3.4 Doğrulama	`takipDogrula()`; hata özeti + alan altında mesaj; girilen değerler korunur
3.5 Silme	`sil.php` (POST), listeden kaldırılır, mesaj gösterilir
3.6 Filtreleme	`takip.php?kategori=…&durum=…` (GET)
3.7 Özet bilgiler	`takipSayaclari()`: toplam, aktif, duraklatılmış, uyarı veren sayaçları; durum değiştirilince hemen güncellenir
3.8 Güvenli çıktı	Tüm kullanıcı girdileri `e()` → `htmlspecialchars()` ile yazdırılır
2. hafta: `$_SESSION`	Kayıtlar oturumda; sayfa yenilenince ve çıkış/giriş sonrası korunur
3. hafta: durum değiştirme	`durum.php` (POST): kartlardaki Aktif / Duraklatıldı düğmeleri, `takipDurumDegistir()`
3. hafta: kayıt düzenleme	`ekle.php?id=…` (POST): kartlardaki ⚙ Düzenle; aynı doğrulama, `takipGuncelle()`
3. hafta: kayıt olma	`kayit.php` (POST): `hesapDogrula()` + `hesapEkle()`, ardından otomatik giriş
3. hafta: tasarım ve ek özellikler	Dört mevsim teması, animasyonlu simgeler ve hava efektleri, hava kalitesi, bildirimler, hesap CRUD, TR/EN, ilçe takibi (Ek F–J)
Ödevdeki teknik kuralların karşılığı (5. Teknik kurallar):
Teknik kural	Projede
PHP, HTML ve CSS ile geliştirme; PHP kodları gerçekten çalışmalı	Bütün sayfalar PHP ile üretilir: listeleme, ekleme, düzenleme, silme, filtreleme, giriş / kayıt, hesap yönetimi, hava durumu verisinin alınması ve uyarıların hesaplanması sunucuda (PHP) yapılır
XAMPP	Proje XAMPP (Apache + PHP 8) üzerinde geliştirilip test edilmiştir (bölüm 4)
Veriler PHP dizileri ve `$_SESSION` ile	Takip kayıtları `$_SESSION`'da; bölge / il / ilçe listeleri `data.php`'deki PHP dizilerinde; hesaplar `veri/` klasöründeki JSON dosyalarında
MySQL zorunlu değil	MySQL kullanılmamıştır
Ekleme ve silme POST ile	`ekle.php`, `sil.php`, `durum.php`, `kayit.php`, hesap işlemleri: hepsi `method="post"` ve CSRF anahtarıyla
Filtreleme GET ile	`takip.php?kategori=…&durum=…`, arama `index.php?ara=…`
En az bir `foreach`	Projede 100'den fazla `foreach` döngüsü vardır (kayıt kartları, il / ilçe listeleri, tahmin günleri, uyarılar …)
Koşul yapıları	`if / elseif / else` ve `? :` (üçlü koşul) yapıları: form doğrulama, uyarı koşulları, yetki kontrolleri, durum ve filtre seçimleri
En az iki kullanıcı tanımlı fonksiyon	`fonksiyonlar/` klasöründe 103 kullanıcı tanımlı fonksiyon (Ek B)
Hazır framework ve hazır yönetim paneli yok	Laravel vb. kullanılmamıştır; yönetim paneli (`yonetim.php`) proje için sıfırdan yazılmıştır
CSS kütüphaneleri isteğe bağlı	CSS kütüphanesi kullanılmamıştır; tüm stiller `assets/style.css` içinde elle yazılmıştır
JavaScript hakkında: Ödevin zorunlu işlevlerinin hiçbiri JavaScript'e bağlı değildir. Projedeki küçük JavaScript
parçaları yalnızca kullanım kolaylığı içindir: arama kutusundaki öneri listesi ve Konumum düğmesi (`assets/arama.js`),
navbar'daki kayan seçim kutusu ve canlı saat, bildirim panelinin dışarı tıklayınca kapanması ve bildirim sesi,
parolayı göster düğmesi, silmeden önce "emin misiniz?" onayı, ekleme formunda ilçe listesinin il değişince yenilenmesi
(`footer.php`, `ekle.php`, `yonetim.php`). Tarayıcıda JavaScript kapatılarak yapılan testte giriş, kayıt ekleme,
filtreleme, silme ve arama sorunsuz çalışmıştır; JavaScript kapalıyken bu parçaların yerini normal PHP formları alır.
🔒 Ek D. Güvenlik önlemleri
XSS: kullanıcı girdileri `htmlspecialchars()` ile yazdırılır (`<script>` düz metin görünür).
Parolalar: `password_hash()` / `password_verify()`; hatalı girişte hangi bilginin yanlış olduğu söylenmez.
Kaba kuvvet: 5 hatalı girişten sonra 60 saniye bekleme.
Oturum: girişte ve çıkışta `session_regenerate_id()`; oturum çerezi `httponly`.
CSRF: ekleme, düzenleme, silme, durum değiştirme, kayıt olma, çıkış, sıfırlama, hesap, tema ve bildirim formlarında
oturuma özel gizli anahtar.
Veri klasörü: `veri/.htaccess` (`Require all denied`) hesap ve ayar dosyalarının tarayıcıdan açılmasını engeller.
Yetki: takip sayfaları giriş, yönetim paneli admin rolü gerektirir; normal kullanıcı başkasının kaydını silemez ve durumunu değiştiremez.
Yönlendirme: `?geri=` ile yalnızca kendi sayfalarımıza dönülür; dış siteye yönlendirme engellenir.
Veri değiştiren işlemler (ekleme, düzenleme, silme, durum değiştirme, kayıt olma, çıkış) yalnızca POST ile yapılır.
Kayıt ol: CSRF anahtarı, admin'in hesap formuyla aynı doğrulama; formdan "rol" gönderilse bile yeni hesap her zaman
"kullanıcı" olur (kayıt formundan yönetici açılamaz).
Düzenleme: `ekle.php?id=…` yalnızca kullanıcının kendi listesindeki kaydı açar; başka bir numara verilirse
"kayıt bulunamadı" denir.
Konumum: konum bilgisi sunucuya gönderilmez; en yakın yer tarayıcıda hesaplanır.
🧪 Ek E. Test senaryoları
Bu bölümde ve diğer eklerdeki tablolarda toplam 112 test senaryosu vardır.
#	Senaryo	Beklenen sonuç	Durum
1	Giriş yapmadan ana sayfa	"Takip Listem" menüde ve ana sayfada görünmez	✅
2	Giriş yapmadan `takip.php`, `ekle.php`, `yonetim.php`	Giriş sayfasına yönlendirilir, bilgi mesajı	✅
3	Boş giriş formu	"Kullanıcı adı / Parola boş bırakılamaz"	✅
4	Yanlış parola / olmayan kullanıcı	"Kullanıcı adı veya parola hatalı", kalan hak	✅
5	5 hatalı deneme	60 saniye kilit; doğru parola da kabul edilmez	✅
6	İl sayfasından "Takip etmek için giriş yapın"	Girişten sonra il seçili ekleme formu açılır	✅
7	`kullanici` girişi	5 örnek kayıt, sayaçlar 5 / 4 / 1	✅
8	Boş ekleme formu	4 alan hatası	✅
9	1 karakterlik ad, eşik 5, 230 karakter açıklama	3 hata, girilen değerler korunur	✅
10	Aynı ilde aynı ad	"zaten bir takip kaydınız var"	✅
11	Geçerli kayıt	Listeye eklenir, en üstte vurgulu, sayaç artar	✅
12	Kayıttan sonra F5	Kayıt tekrar eklenmez	✅
13	Başlığa `<script>` yazmak	Kod çalışmaz, düz metin görünür	✅
14	Filtre: Seyahat / Duraklatıldı / ikisi birlikte	Doğru kayıtlar, "x / y kayıt gösteriliyor"	✅
15	Sonuçsuz filtre	"Bu filtreye uyan kayıt yok"	✅
16	`?kategori=xyz`	Filtre yok sayılır, uyarı mesajı	✅
17	Filtreliyken silme	Kayıt silinir, filtre korunur, mesaj gösterilir	✅
18	Olmayan kaydı / CSRF anahtarı olmadan silme	Hata mesajı, hiçbir şey silinmez	✅
19	`kullanici` ile `yonetim.php`	"yalnızca yöneticiler erişebilir"	✅
20	`admin` ile yönetimden başka kullanıcının kaydını silme	Silinir; `kullanici` tekrar girince kayıt yok	✅
21	Çıkış ve tekrar giriş	Kayıtlar korunur	✅
22	`giris.php?geri=https://baska-site`	Giriş sonrası `takip.php`'ye gidilir	✅
23	İnternet yokken	Sayfa çökmez, "veri alınamadı" mesajı	✅
24	Koyu tema ve mobil görünüm (390 px)	Düzgün görüntülenir	✅
25	Saat 14:30'da yağışlı gün	Kutuda "Şu an–16:00, 21:00–24:00"; ızgarada 24 saat, geçmiş saatler soluk, 06–09 aralığı "geçti"	✅
26	Yağışsız gün	"Bugün kalan saatlerde yağış beklenmiyor"	✅
27	16°, %98 yağış, gök gürültülü	Fırtına uyarısı + "hırka yetmez, su geçirmez mont/yağmurluk" + şemsiye ve yağış saatleri; çelişkili tavsiye yok	✅
28	"İstanbul: Beşiktaş", "beşiktaş", "erbaa" araması	Doğrudan ilçe sayfası açılır	✅
29	"ereğli" / "rize:" araması	Aynı adlı 2 ilçe / Rize'nin 12 ilçesi listelenir	✅
30	İl sayfasında "İlçe seç" → Erbaa	İlçe listesi açılır; Erbaa'nın hava durumu, yol: Tokat › Erbaa; 7 gün ve saatlik bağlantılar ilçeyle çalışır	✅
31	Olmayan / bulunamayan ilçe	Uyarı mesajı, il merkezinin verisi gösterilir	✅
32	"İstanbul: tüm ilçeler", "konya: Tüm İlçeler", "istanbul:"	İstanbul'un 39 / Konya'nın 31 ilçesi listelenir; tıklanınca ilçe sayfası açılır	✅
33	Duraklatılmış kayıtta Aktif düğmesi	Kayıt aktif olur: etiket "Aktif", kart solukluğu kalkar, sayaçlar değişir, "yeniden aktif edildi" mesajı	✅
34	Aktif kayıtta Duraklatıldı düğmesi	Etiket "Duraklatıldı", kart soluklaşır, uyarılar ve bildirimler durur	✅
35	Filtreliyken durum değiştirme	Filtre korunur, sayfa aynı karta döner	✅
36	CSRF anahtarı olmadan durum değiştirme isteği	"İstek doğrulanamadı", durum değişmez	✅
37	Kartta ⚙ Düzenle → adı ve açıklamayı değiştir	Form kaydın bilgileriyle dolu gelir; kayıt aynı numarayla güncellenir, "kaydı güncellendi" mesajı	✅
38	Düzenlemede adı boş bırak / `ekle.php?id=999`	"Kayıt güncellenemedi" ve alan hatası / "kayıt bulunamadı"	✅
39	Boş kayıt formu	Ad soyad, kullanıcı adı, e-posta, parola ve onay kutusu hataları birlikte	✅
40	Kayıt: kullanıcı adı "admin", parolalar farklı	"kullanıcı adı alınmış", "Parolalar birbiriyle eşleşmiyor"; girilenler korunur, parola alanları boş	✅
41	Geçerli kayıt	Hesap "kullanıcı" rolüyle açılır, otomatik giriş, boş Takip Listem; çıkıp yeniden giriş yapılabilir	✅
42	Arama kutusuna "er" / "istanbul: bes" yazmak	Öneri listesi (il önce, yazılan kısım vurgulu) / yalnızca Beşiktaş; ↓ + Enter ilçe sayfasını açar	✅
43	"xyzq" yazmak, × düğmesi	"Sonuç bulunamadı"; × kutuyu temizler ve listeyi kapatır	✅
44	Konumum (konum: Erbaa civarı)	"Size en yakın yer: Erbaa, Tokat", Erbaa sayfası açılır	✅
45	Konum izni verilmeden Konumum	İzin verilmediği ve nasıl verileceği yazılır	✅
46	Sayfanın en üstüne yakın küçük kaydırmalar	Navbar titremez (yükseklik sabit, sınıf yalnızca 24 px / 4 px eşiklerinde değişir)	✅
47	1234 / 1440 / 1920 px genişlik	Tarih ve saat kartları görünür, saat her saniye ilerler; navbar tek satır, yatay kaydırma yok	✅
48	Giriş yapmadan navbar	Menü şeridin tam ortasında; Giriş Yap ve Kayıt Ol renkli, üzerine gelince kalkar	✅
49	Kayıt sayfasından "Giriş Yap" sekmesi	Giriş sayfası açılır, tanıtım bölümü soldan sağa kayar; form solda	✅
50	Simgeler sayfası sekmeleri	Tümü 28 / Hava 17 / Uyarı 11 kart; `?sekme=uyari` uyarı sekmesiyle açılır	✅
51	Tüm sayfalar (giriş yapmadan ve admin ile, 23 sayfa)	Hiçbir sayfada PHP uyarısı (Warning / Notice) yok	✅
52	360 / 768 / 1024 px, tüm sayfalar	Yatay kaydırma yok; telefonda ☰ menü, tablolar karta dönüşür ya da kayar	✅
53	iPhone SE, iPhone 13, Galaxy S9+, iPad Mini (dikey / yatay), iPad Pro 11 öykünmesi: ana sayfa, giriş, kayıt, il sayfası, simgeler	Yatay kaydırma yok; mevsim parçacıkları, hava efektleri ve animasyonlu simgeler oynar	✅
54	JavaScript kapalı tarayıcı: giriş, kayıt ekleme, filtreleme, silme, arama	Hepsi PHP formlarıyla çalışır	✅
🔔 Ek F. Bildirim zili
Giriş yapan kullanıcının navbar'ında bir zil simgesi bulunur. Bildirimler, takip listesindeki
aktif kayıtlarda kullanıcının seçtiği uyarılara göre bugün ve yarın için oluşturulur:
Kayıttaki uyarı	Bildirim koşulu
Yağış	Yağış ihtimali ≥ kayıttaki eşik (%)
Don / buzlanma	En düşük sıcaklık ≤ 0°
Kuvvetli rüzgâr	En yüksek rüzgâr ≥ 40 km/s
Aşırı sıcak	En yüksek sıcaklık ≥ 32°
Yeni bildirim gelince panel açılmaz: `assets/bildirim.wav` sesi çalar, zil sallanır ve zilin köşesinde
kırmızı sayı (okunmamış bildirim sayısı) belirir; 99'dan fazla bildirim varsa 99+ yazar. Paneli kullanıcı
zile tıklayarak kendisi açar. Sayfa yenilendiğinde ses tekrar çalmaz.
Panelde her uyarı kendi renkli, animasyonlu simgesiyle gösterilir (yağış → şemsiye, don → atkı-bere,
rüzgâr → rüzgâr, aşırı sıcak → güneş). Simgeler "Bugün için tavsiyeler" kutularındakilerle aynıdır (`assets/tavsiye/`,
`uyariIkonu()`). Okunmamış bildirimler mavi zeminle gösterilir.
Tarayıcı, siteye hiç tıklanmadan açılan sayfada sesi engellerse ses kullanıcının ilk tıklamasında çalar
(footer.php'deki küçük JavaScript).
Her bildirimin sağındaki × düğmesi o bildirimi, Tümünü temizle düğmesi hepsini kaldırır.
Kaldırılan bildirim aynı gün için tekrar gelmez.
Bildirime tıklanınca ilgili il / ilçenin o günkü saatlik ayrıntısı açılır ve hava durumu animasyonlu simgesiyle
ekranın ortasında gösterilir (`il.php?il=...&ilce=...&gun=...&efekt=1`).
Zil beyazdır. Zilin üstündeki kırmızı sayı yalnızca okunmamış bildirimleri gösterir: kullanıcı zile tıklayıp
paneli açınca bildirimler okundu olur ve sayı kaybolur; sayfa yenilense de geri gelmez.
Yeni bir bildirim gelirse sayı yeniden görünür. (`bildirim.php`, `islem=okundu`; bildirimlerde `okundu` bilgisi)
Panel açıkken sayfada boş bir yere tıklanınca panel kapanır.
Kayıt silinir veya duraklatılırsa (kartın altındaki Duraklatıldı düğmesi) ona ait bildirimler kalkar; yeniden aktif edilince
uyarı koşulları beklemeden tekrar kontrol edilir. Tahmin değişip koşul ortadan kalkarsa
(ör. yağış ihtimali eşiğin altına düşerse) bildirim de kalkar.
Hava servisine en fazla 15 dakikada bir sorulur. Takip listesi değişince (kayıt ekleme/silme) beklemeden
yeniden kontrol edilir.
Her kullanıcının bildirimleri ayrıdır: `$_SESSION['bildirimMerkezi'][kullaniciAdi]`.
(`$_SESSION['bildirim']` tek seferlik mesajlar için kullanıldığından ad farklı seçildi.)
Panelin açılıp kapanması `<details>` etiketiyle yapılır. Dışarı tıklayınca kapanma ve okundu bilgisini arka planda
gönderme footer.php'deki küçük bir JavaScript ile yapılır (JavaScript kapalıysa panel yine çalışır). × ve "Tümünü temizle" `bildirim.php`'ye
CSRF anahtarlı POST formlarıyla gönderilir. İşlemden sonra aynı sayfaya dönülür ve panel açık kalır.
Ses hakkında: Tarayıcılar, kullanıcı siteye hiç tıklamadan açılan ilk sayfada otomatik sesi
engelleyebilir. Bu durumda ses, kullanıcının sayfadaki ilk tıklamasında çalar.
İlgili dosyalar:
Dosya	Görevi
`fonksiyonlar/bildirim.php`	`bildirimleriGuncelle()`, `gunUyarilari()`, `takipImzasi()`, `bildirimPaneli()`, `bildirimSil()`, `bildirimleriTemizle()`, `bildirimMerkeziBos()`
`bildirim.php`	×, "Tümünü temizle" ve okundu işlemleri (yalnızca POST, giriş ve CSRF kontrolü)
`footer.php`	Dışarı tıklayınca paneli kapatma, okundu bilgisi, engellenen sesi ilk tıklamada çalma (JavaScript)
`header.php`	Navbar'daki zil, sayı rozeti ve açılır panel
`assets/style.css`	Dosyanın sonundaki "BİLDİRİM ZİLİ ve PANELİ" bölümü (açık/koyu tema ve mobil)
`assets/bildirim.wav`	Bildirim sesi
Test senaryoları:
#	Senaryo	Beklenen sonuç	Sonuç
B1	Giriş yapmadan ana sayfa	Zil görünmez	✅
B2	`kullanici` ile ilk giriş (örnek kayıtlar)	Panel kapalı kalır, ses çalar, zilde kırmızı bildirim sayısı görünür	✅
B3	Sayfayı yenile	Panel kapalı, ses yok, bildirimler duruyor	✅
B4	İl sayfasında bir bildirimin × düğmesi	O bildirim kalkar, aynı il sayfasına dönülür, panel açık	✅
B5	Uyarı seçili yeni bir kayıt ekle	Panel açılmaz, ses çalar, kırmızı sayı artar; zile tıklayınca yeni bildirim en üstte	✅
B6	Bildirimi olan "Ev" kaydını sil	"Ev" kaydına ait bildirimler kalkar	✅
B7	Yanlış CSRF anahtarıyla temizleme isteği	"İstek doğrulanamadı" mesajı, bildirimler değişmez	✅
B8	120 okunmamış bildirim (birim testi)	Zilde "99+" yazar	✅
B8	`donus` alanına başka bir site adresi yazılması	Kendi sayfamıza (`index.php`) dönülür	✅
B9	`admin` ile giriş, "Tümünü temizle"	Yalnızca admin'in kendi bildirimleri görünür ve temizlenir	✅
B10	Çıkış yapıp tekrar `kullanici` ile giriş	Kullanıcının kalan bildirimleri durur, admin'in temizlemesi etkilemez	✅
B11	Mobil görünüm (390 px)	Panel ekran genişliğinde açılır, yatay kaydırma yok	✅
Fikir: öğrenci (navbar'da sesli bildirim, tek tek × ile ve toplu temizleme). Kodlama yapay zekâ desteğiyle
yapılmıştır (bkz. 7. bölüm).
👥 Ek G. Hesap yönetimi (CRUD)
Hesaplar üzerinde dört temel işlem yapılabilir:
İşlem	Kullanıcı (kendi hesabı)	Admin (tüm hesaplar)
Create (ekleme)	Navbar › Kayıt Ol (`kayit.php`), rol her zaman "kullanıcı"	Yönetim › + Yeni hesap (`hesap-duzenle.php`)
Read (görüntüleme)	Navbar'daki ⚙ dişli → Hesabım (`hesabim.php`)	Yönetim › Kullanıcılar tablosu (ad, e-posta, rol, kayıt sayıları)
Update (güncelleme)	Ad soyad, e-posta; mevcut parolayı girerek parola değiştirme	Düzenle: ad soyad, e-posta, rol; isteğe bağlı yeni parola
Delete (silme)	Parola + onay kutusuyla kendi hesabını silme	Sil düğmesi (`hesap-sil.php`, POST)
Hesaplar `veri/kullanicilar.json` dosyasında saklanır, bu yüzden değişiklikler tarayıcı kapansa da kalır.
Proje bu dosya, içinde 2 başlangıç hesabıyla gelir. Dosya açılınca parolaların özet (hash) olarak saklandığı görülür.
Dosya silinirse `data.php`'deki başlangıç hesapları kullanılır. `veri/.htaccess` bu klasörün tarayıcıdan açılmasını engeller.
Parolalar her zaman `password_hash()` özeti olarak saklanır.
Kullanıcı adı değiştirilemez, çünkü takip listesi bu ada bağlıdır.
Hesap silinince o hesabın takip listesi ve bildirimleri de silinir. Silinen hesap başka bir tarayıcıda açıksa
oturumu bir sonraki sayfada kapatılır.
Sistemde en az bir yönetici kalır: son yönetici silinemez ve rolü "kullanıcı" yapılamaz.
Admin kendi hesabını Yönetim'den silemez; bunun için Hesabım sayfası kullanılır.
Tüm formlar POST ile gönderilir ve CSRF anahtarı taşır. Hatalı girişte alanların altında açıklayıcı mesaj çıkar.
Başlangıç hesaplarının e-postaları: `admin@ornek.com`, `kullanici@ornek.com`.
İlgili dosyalar: `hesabim.php`, `hesap-duzenle.php`, `hesap-sil.php`, `fonksiyonlar/hesap.php`
(`hesaplariOku`, `hesaplariKaydet`, `hesapDogrula`, `parolaKontrol`, `hesapEkle`, `hesapGuncelle`, `hesapSil`,
`adminSayisi`, `oturumuTazele`, `hesapAlanHatasi`), `yonetim.php` (Kullanıcılar tablosu), `veri/.htaccess`.
Test senaryoları:
#	Senaryo	Beklenen sonuç	Sonuç
H1	Giriş yapmadan `hesabim.php`	Giriş sayfasına yönlendirilir	✅
H2	`kullanici` ile Hesabım	Kullanıcı adı, e-posta, rol, kayıt tarihi ve takip sayısı görünür	✅
H3	Ad soyad "A", e-posta "yanlis"	İki alanın altında hata mesajı	✅
H4	Başka hesabın e-postası	"Bu e-posta adresi başka bir hesapta kullanılıyor."	✅
H5	Ad soyad ve e-posta güncelleme	"Bilgileriniz güncellendi.", navbar'daki harf değişir	✅
H6	Yanlış mevcut parola ile parola değiştirme	"Mevcut parolanız hatalı."	✅
H7	Parola değiştirip çıkış, eski parolayla giriş	Giriş reddedilir; yeni parolayla giriş yapılır	✅
H8	Kullanıcı `hesap-duzenle.php` açar	"Bu sayfaya yalnızca yöneticiler erişebilir."	✅
H9	Admin hatalı bilgilerle yeni hesap	Kullanıcı adı, ad soyad, e-posta ve parola hataları	✅
H10	Admin geçerli yeni hesap açar	Hesap tabloda görünür, yeni hesapla giriş yapılabilir	✅
H11	Admin hesabı düzenler (ad, rol)	"… hesabı güncellendi.", tablo güncellenir	✅
H12	CSRF anahtarı olmadan hesap silme isteği	"İstek doğrulanamadı.", hesap silinmez	✅
H13	Son yöneticinin rolünü "kullanıcı" yapma	Hata mesajı, rol değişmez	✅
H14	Tek yönetici Hesabım'dan silmeye çalışır	Silme formu yerine uyarı gösterilir	✅
H15	Kullanıcı yanlış parola / onaysız hesap silme	İki alanda hata mesajı	✅
H16	Kullanıcı kendi hesabını siler	Çıkış yapılır, aynı bilgilerle giriş reddedilir	✅
H17	Admin, başka tarayıcıda açık olan hesabı siler	O tarayıcıda oturum kapanır: "Hesabınız silindiği için oturumunuz kapatıldı."	✅
H18	Mobil görünüm (390 px)	Hesabım ve Yönetim sayfalarında yatay kaydırma yok	✅
🎨 Ek H. Tasarım: dört mevsim teması, animasyonlu simgeler, hava efektleri ve hava kalitesi
Dört mevsim teması. Sitenin renkleri, başlık alanının degradesi, sayfa arka planındaki ton ve süzülen
parçacıklar bulunulan aya göre otomatik değişir:
Mevsim	Aylar	Renkler	Atmosfer
İlkbahar	Mart–Mayıs	Yeşil, pembe	Pembe-beyaz çiçek yaprakları
Yaz	Haziran–Ağustos	Sarı, turkuaz	Yükselen ışık noktaları, güneş parıltısı
Sonbahar	Eylül–Kasım	Turuncu, kızıl	Düşen yapraklar
Kış	Aralık–Şubat	Buz mavisi, beyaz	Kar taneleri
Tema Yönetim › Site teması bölümünden seçilir (yalnızca admin): Otomatik (aya göre) ya da sabit bir mevsim.
Seçim `veri/ayarlar.json` dosyasında saklanır ve tüm ziyaretçiler için geçerlidir (`tema-ayar.php`, POST + CSRF).
Mevsim `aktifMevsim()` ile bulunur ve `<body data-mevsim="...">` etiketine yazılır; renkler CSS'te
`body[data-mevsim="..."]` kurallarıyla değişir.
Mevsim açılış efekti. Ana sayfa her açıldığında (diğer sayfalarda oturumda bir kez ve tema değiştiğinde) birkaç saniye
boyunca ekrana mevsimin parçacıkları yağar, ortada büyük mevsim simgesi ve mevsimin adı belirir: kışın kar taneleri,
sonbaharda turuncu-kızıl-sarı akçaağaç yaprakları, ilkbaharda kiraz çiçekleri, yazın ışık hüzmeleri ve düşen gün ışığı. Sonrasında sayfa boyunca
az sayıda parçacık yavaşça süzülmeye devam eder. Hava efektinin oynadığı il sayfasında mevsim efekti gösterilmez.
Görseller `assets/mevsim/` klasöründedir; kod: `mevsimParcaciklari()`, `mevsimGirisiGoster()` (fonksiyonlar/gorunum.php).
Animasyonlu hava simgeleri. `assets/ikonlar/` klasöründe 16 simge ve bir dolunay vardır (SVG, içinde CSS animasyonu):
güneşli, parçalı bulutlu, bulutlu, kapalı, hafif yağmur, sağanak, gök gürültülü, kar, yoğun kar, sis, rüzgârlı,
gün doğumu, gün batımı, açık gece, bulutlu gece, ay ve yıldızlar, dolunay. `assets/tavsiye/` klasöründe de 11 uyarı /
tavsiye simgesi vardır (şemsiye, yağmurluk, mont, atkı-bere, hırka, bot, fırtına, rüzgâr, aşırı sıcak, güneş gözlüğü,
güzel hava). Hepsi `ikonlar.php` sayfasında sekmelerle görülebilir (alt bilgideki "Hava simgeleri" bağlantısı). `havaSimgesi($kod, $gunduz)` hava koduna göre doğru dosyayı seçer;
Open-Meteo'nun `is_day` alanı 0 ise (gece) güneş yerine ay ve yıldızlı simgeler kullanılır.
İl sayfasında hava efekti. Bir il veya ilçe açıldığında o anki havaya göre birkaç saniyelik bir açılış efekti oynar,
sonra anlık durum kartının arkasında sakin hâli sürer:
Hava	Efekt
Güneşli	Güneş sağ üstten doğar, ışık yayılır
Bulutlu / kapalı (gündüz)	Bulutlar soldan ve sağdan girer; güneş bulutların arkasında görünür
Yağmurlu	Gri bulutlar ve eğik yağmur damlaları
Gök gürültülü	Koyu bulutlar, sağanak ve şimşek çakması
Karlı	Bulutlar ve salınarak düşen kar taneleri
Sisli	Kenarlardan kayan sis bantları
Rüzgârlı (≥ 30 km/s)	Rüzgâr çizgileri ve savrulan yapraklar
Gece	Güneş yerine dolunay ve parlayan yıldızlar (bulutluysa bulutlarla)
Açılışta ekranın tam ortasında o anki havanın büyük, animasyonlu simgesi, şehrin adı, hava durumu ve sıcaklık belirir
(~3,5 sn): güneş nefes alır gibi büyüyüp küçülür ve parlayıp söner, bulutlar ileri geri gider, fırtınada simge sarsılır,
gece ay parlar. Efektler yalnızca CSS animasyonudur. Şehir her açıldığında oynar; aynı sayfada gün seçerken
(`gun=`) ya da ilçe listesini açarken tekrar etmez (tekrar görmek için adrese `&efekt=1` eklenebilir).
İlgili kod: `havaSahnesi()` (fonksiyonlar/hava.php), `sahneHtml()` ve `sahneParcaciklari()` (fonksiyonlar/gorunum.php).
Logo. Navbar'daki amblemde güneş ortadadır; dünya güneşin etrafında yörüngede dolaşır ve kendi ekseni etrafında
döner. Dünya yörüngenin arka yarısında güneşin arkasında, ön yarısında önünde görünür (SVG içinde, JavaScript yok).
Görünüm. Tüm sayfalar %90 ölçekte tasarlanmıştır; tarayıcı %100'deyken de bu görünüm korunur (telefonda da).
Navbar. Ekranın iki kenarına kadar uzanmayan, köşeleri yuvarlatılmış yüzen bir şerittir; alt kenarında mevsim
renginde kayan ince bir ışık çizgisi vardır. Bağlantılar şeridin tam ortasında, açık renkli bir kutuda büyük harfle
yazılıdır (JetBrains Mono). Seçili sayfa koyu, dolu bir kutuyla işaretlidir; fare başka bir bağlantının üzerine gelince
bu kutu kayarak oraya gider (footer.php'deki küçük JavaScript; JavaScript kapalıysa seçili bağlantı yine koyu kutudadır).
Kaydırınca yalnızca gölge koyulaşır, yükseklik değişmez. Geniş ekranda şeridin solunda takvim yaprağı (ay, gün,
haftanın günü), sağında ibreleri dönen analog saat ve dijital saat bulunur. Telefon ve tablette (≤ 1200 px) bağlantılar
☰ düğmesiyle açılan menüye toplanır (JavaScript'siz, gizli onay kutusuyla).
Sayfa tasarımları. Takip Listem ve Yönetim paneli aynı tasarım dilini kullanır: üstte mevsim renginde karşılama
bölümü ve yavaş dönen süs halkaları, simgeli sayaç kartları, sırayla beliren kartlar. Bölge kartlarının sağ üstünde
bölgeyi anlatan hareketli simgeler (`bolgeSimgesi()`), giriş ve kayıt sayfalarında süzülen hava
simgeli tanıtım bölümü vardır. Animasyonların hepsi CSS ile yapılır; tarayıcıda "hareketi azalt" ayarı açıksa giriş /
kayıt geçiş animasyonu oynamaz.
Hava kalitesi. İl sayfasında Open-Meteo Hava Kalitesi API'sinden (Copernicus CAMS) anlık Avrupa Hava Kalitesi
İndeksi (AQI), bugünün beklenen en yüksek değeri, renkli ölçek ve tavsiye gösterilir. Ayrıca PM2.5, PM10, NO₂, O₃, SO₂
ve CO değerleri (µg/m³) listelenir. Kategoriler: 0–20 İyi · 20–40 Makul · 40–60 Orta · 60–80 Kötü · 80–100 Çok kötü
· 100+ Son derece kötü. Veri alınamazsa sayfa yine çalışır ve uyarı gösterilir.
İlgili kod: `havaKalitesiGetir()` ve `havaKalitesiDurumu()` (fonksiyonlar/hava.php).
Test senaryoları:
#	Senaryo	Beklenen sonuç	Sonuç
T1	Kasım ayında ilk açılış	Sonbahar teması, ekrana yapraklar yağar ve "Sonbahar" yazısı belirir; yenileyince tekrar etmez	✅
T2	Admin Yönetim'den Kış / İlkbahar / Yaz seçer	Tüm ziyaretçilerde tema değişir, açılış efekti bir kez oynar; "Otomatik" aya döner	✅
T2b	Kullanıcı veya CSRF anahtarı olmadan tema değiştirme	Reddedilir, tema değişmez	✅
T3	Hava kodu 0, gündüz	Güneş efekti, güneşli simge	✅
T3b	Gündüz bulutlu / kapalı (kod 2–3)	Güneş bulutların arkasında görünür; gece aynı havada güneş yerine ay ve yıldızlar	✅
T4	Hava kodu 3 / 63 / 73 / 95 / 45	Bulut / yağmur / kar / fırtına / sis efektleri	✅
T5	Rüzgâr 48 km/s, yağış yok	Rüzgâr çizgileri ve yapraklar	✅
T6	Gece (is_day = 0)	Dolunay ve yıldızlar; saatlik listede gece saatlerinde ay simgesi	✅
T7	Aynı ilde gün değiştirme	Açılış efekti tekrar oynamaz	✅
T8	Açılış efektinden ~4,6 sn sonra	Katman kaybolur, sayfa kullanılabilir (tıklamaları hiç engellemez)	✅
T9	Hava kalitesi AQI 34 / 67	"Makul" yeşil / "Kötü" turuncu gösterim, ölçekte işaret	✅
T10	Koyu tema ve mobil (390 px)	Efektler ve kartlar düzgün, yatay kaydırma yok	✅
T11	Bildirimler ve hesap işlemleri	Önceki testlerin hepsi yeni tasarımla geçer	✅
🌐 Ek I. Dil seçimi: Türkçe / İngilizce
Navbar'ın en sağındaki TR / EN düğmesiyle site Türkçe ya da İngilizce görüntülenir. Seçim `dil` çerezinde
1 yıl saklanır; düğmeye basınca aynı sayfaya geri dönülür (adres değişmez, form ve oturum bilgileri korunur).
Nasıl çalışır? Site Türkçe yazılmıştır. İngilizce seçiliyse `baslat.php` sayfanın çıktısını `ob_start('sayfayiCevir')`
ile tamponlar; sayfa tarayıcıya gönderilmeden hemen önce içindeki Türkçe metinler `dil/en.php` sözlüğündeki
İngilizce karşılıklarıyla değiştirilir. Böylece sayfaların kodunu tek tek değiştirmeden tüm site çevrilir.
Yalnızca görünen metinler çevrilir: etiketler arasındaki yazılar, `placeholder` / `title` / `aria-label` / `alt`
öznitelikleri ve silme onay pencereleri. Bağlantı adresleri, form alanlarının adları ve `<style>`/`<script>` içleri değişmez.
Sayı, ad ve tarih içeren cümleler sözlükte `{}` ile yazılır:
`'Bugün yağış ihtimali %{} (eşiğiniz %{}).' => 'Chance of rain today is {1}% (your threshold {2}%).'`
`{1}` değişen kısmı da çevirir (ör. hava durumu adı), `{!1}` olduğu gibi bırakır (şehir, kullanıcı adı).
Ay ve gün adları kelime olarak çevrilir: "6 Kasım 2026, Cuma" → "6 November 2026, Friday".
Şehir ve ilçe adları çevrilmez. Gün ya da bölge adıyla aynı olan ilçeler ("Pazar", "Akdeniz") `translate="no"`
ile işaretlenmiştir; böylece "Sunday" ya da "Mediterranean" olmazlar.
Kullanıcının yazdığı takip adları ve açıklamaları çevrilmez (yalnızca data.php'deki örnek kayıtlar sözlükte vardır).
`<html lang="tr">` İngilizcede `lang="en"` olur (ekran okuyucular ve tarayıcı için).
İlgili kod: `fonksiyonlar/dil.php` (`aktifDil()`, `cevir()`, `sayfayiCevir()`), `dil.php`, `dil/en.php`, header.php'deki `.dil-secici`.
Yeni metin eklerken: Türkçe metni sayfaya yazın, `dil/en.php` içindeki `'metinler'` dizisine
`'Türkçe metin' => 'English text',` satırını ekleyin. Sözlükte olmayan metin Türkçe görünmeye devam eder (hata vermez).
#	Senaryo	Beklenen sonuç	Sonuç
D1	EN düğmesine basmak	Aynı sayfa İngilizce açılır, EN düğmesi beyaz (seçili) olur	✅
D2	Tüm sayfaları İngilizce gezmek (ana sayfa, bölgeler, arama, il, gün, ilçe, giriş, takip, ekle, yönetim, hesabım, hesap düzenle, ilçe kontrolü, simgeler)	Türkçe metin kalmaz; yalnızca yer adları ve kullanıcının yazdıkları Türkçe kalır	✅
D3	İngilizcede hatalı form gönderme, kayıt ekleme / silme, tema değiştirme	Hata ve başarı mesajları İngilizce	✅
D4	Farklı hava durumları ve tavsiyeler (sıcak, soğuk, yağmurlu, rüzgârlı, hava kalitesi 6 kategori)	Tavsiyeler tam cümle İngilizce	✅
D5	TR düğmesine basmak	Site Türkçeye döner; Türkçe sayfalar dil özelliği eklenmeden öncekiyle birebir aynı	✅
D6	Telefon (390 px)	TR / EN düğmesi navbar'ın sağında, taşma yok	✅
📍 Ek J. İlçe takibi ve ilçe koordinatları
Yeni takip eklerken il seçildikten sonra ilçe de seçilebilir (isteğe bağlı; boş bırakılırsa il merkezi takip edilir).
İl değiştirilince ilçe listesi o ilin ilçeleriyle yenilenir (ekle.php'deki küçük JavaScript). JavaScript kapalıysa liste
sayfa yüklenirken seçili ilin ilçeleriyle PHP tarafından doldurulur.
PHP, seçilen ilçenin gerçekten o ile ait olduğunu kontrol eder (`takipDogrula()`); değilse
"Seçilen ilçe bu ile ait değil" hatası verir. Aynı yer (il + ilçe) için aynı adla ikinci kayıt eklenemez.
Kayıtta `'ilce' => 'cankaya'` olarak saklanır. Takip listesi, ana sayfa özeti, yönetim paneli ve bildirimlerde yer
"Çankaya, Ankara" diye yazar; bağlantılar ilçenin sayfasına (`il.php?il=ankara&ilce=cankaya`) gider.
Hava durumu ve uyarılar ilçenin koordinatıyla hesaplanır (`takipKonumu()`; ilçe konumu bulunamazsa il merkezi kullanılır).
Bir ilçenin sayfasındaki + Takip listeme ekle düğmesi formu il ve ilçe seçili olarak açar
(`ekle.php?il=istanbul&ilce=besiktas`).
İlgili kod: `takipYerAdi()`, `takipAdresi()`, `takipKonumu()` (fonksiyonlar/takip.php), ekle.php.
#	Senaryo	Beklenen sonuç	Sonuç
İ1	Ekle formunda il seçmeden	İlçe listesi pasif: "— Önce il seçin —"	✅
İ2	Ankara seçilir	İlçe listesi "İl merkezi" + 25 ilçe ile dolar	✅
İ3	Ankara / Çankaya ile kayıt	Listede "Çankaya, Ankara · İç Anadolu", bağlantı ilçe sayfasına gider	✅
İ4	İzmir + Çankaya (kurcalanmış form)	"Seçilen ilçe bu ile ait değil" hatası	✅
İ5	Çankaya kaydı için bildirime tıklama	Çankaya'nın o günkü sayfası açılır, hava ekranın ortasında gösterilir	✅
İ6	Zile tıklayıp sayfayı yenileme	Zilin üstündeki sayı kaybolur, geri gelmez	✅
İ7	Panel açıkken boş yere tıklama	Panel kapanır; panelin içine tıklayınca açık kalır	✅
İlçe koordinatları: hiçbir ilçe verisiz kalmaz
Bazı ilçeler (ör. Adana / Çukurova) Open-Meteo Geocoding'de bulunamıyordu; bu ilçelerin sayfası il merkezinin
havasını gösteriyordu. Artık `veri/ilce-merkezleri.json` dosyasında 922 ilçenin tamamı (Merkez ilçeler hariç)
için hazır bir koordinat var.
Hazır koordinatlar nasıl üretildi? Sitenin zaten kullandığı Open-Meteo Geocoding API'den (verisi GeoNames,
CC BY 4.0) bir kez toplanıp dosyaya yazıldı; başka bir harita ya da veri seti kullanılmadı:
922 ilçenin her biri için API'ye ilçe adıyla soruldu (`countryCode=TR`, `language=tr`).
Gelen sonuçlardan ili (admin1) tutanlar alındı; adı ilçeyle aynı olan, bağlı olduğu ilçe (admin2) tutan ve
türü "ilçe merkezi" (PPLA2) olan sonuç seçildi.
Şehrin içinde kalan yeni ilçelerde (ör. Çukurova, Karesi, Ortahisar, Haliliye) API'de ilçe adıyla bir yer yoktur;
bu ilçelere il merkezinin koordinatı yazıldı. Merkezi farklı adlı bir yerde olan ilçeler o yerin adıyla arandı
(ör. Kahramankazan → Kazan, Aziziye → Ilıca, Üzümlü → Cimin).
API'nin hiç bulamadığı 2 ilçenin (Pülümür, Bahçe) merkez koordinatı elle girildi.
Sonuçlar `data.php`'deki ilçe listesiyle eşleştirildi (922 / 922) ve `veri/ilce-merkezleri.json` dosyasına yazıldı.
Hazır koordinat ilçenin merkez yerleşimidir. Önce yine API denenir, hazır koordinat yedektir.
`ilceKonumu()` sırası: Merkez → il koordinatı · önbellek · Geocoding API · bulunamazsa hazır koordinat.
Geocoding aynı adlı başka bir yeri bulursa (hazır koordinattan 50 km'den uzaksa) o sonuç kullanılmaz.
`ilce-kontrol.php` sayfasındaki "Kaynak" sütunu koordinatın nereden geldiğini gösterir:
Kaynak	Anlamı
`il merkezi`	"Merkez" ilçesi: `data.php`'deki il koordinatı
`geocoding`	Bu açılışta Open-Meteo Geocoding API'den çekildi ve `veri/ilce-konumlari.json`'a kaydedildi
`önbellek`	Daha önce API'den çekilmişti; `veri/ilce-konumlari.json` dosyasından okundu (API'ye sorulmadı)
`hazır koordinat`	API bulamadı ya da yanlış yer buldu; `veri/ilce-merkezleri.json`'daki hesaplanmış koordinat kullanıldı
Yönetim › Koordinatlar bölümü aynı bilgiyi API'ye hiç sormadan gösterir (`ilceKonumuApisiz()`,
fonksiyonlar/ilce.php): önce il listesi, ile tıklanınca o ilin ilçeleri. Bu yüzden 81 il ve 973 ilçe sayfa açılırken
bekletmeden listelenir; Geocoding ile toplu kontrol için yine `ilce-kontrol.php` kullanılır.
#	Senaryo	Beklenen sonuç	Sonuç
İ8	Adana / Çukurova sayfası (Geocoding sonuç vermezken)	Çukurova'nın kendi koordinatıyla hava durumu, uyarı mesajı yok	✅
İ9	ilce-kontrol.php ile 922 ilçe	Bulunamayan: 0	✅
📚 Ek K. Kaynaklar ve lisans
Hava durumu verileri: Open-Meteo.com, CC BY 4.0. Ticari olmayan kullanım ücretsizdir.
Hava kalitesi: Open-Meteo Air Quality API (Copernicus CAMS verisi).
Yazı tipi: Inter (Google Fonts). İnternet yoksa sistem yazı tipi kullanılır.
Simgeler (hava simgeleri, uyarı simgeleri ve bölge kartlarındaki bölge simgeleri) projede SVG olarak elle
çizilmiştir; harita verisi kullanılmamıştır.
İlçe koordinatları: Open-Meteo Geocoding API (veriler GeoNames, CC BY 4.0).
Hazır ilçe koordinatları (`veri/ilce-merkezleri.json`) da aynı kaynaktan bir kez toplanmıştır.
