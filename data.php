<?php
/*
 * data.php
 * Türkiye'nin 7 coğrafi bölgesi ve 81 ili.
 *
 * Yapı: iç içe ilişkisel dizi
 *   $bolgeler['karadeniz']['iller']['trabzon'] = ['ad' => 'Trabzon', 'lat' => ..., 'lon' => ...]
 *
 * Anahtarlar (karadeniz, trabzon...) adres çubuğunda kullanılır:
 *   index.php?bolge=karadeniz&il=trabzon
 * Bu yüzden Türkçe karakter ve boşluk içermezler.
 *
 * lat = enlem, lon = boylam (il merkezinin yaklaşık koordinatı).
 * Hava durumu verisi bu koordinatlarla Open-Meteo'dan istenir.
 */

$bolgeler = [

    'marmara' => [
        'ad'       => 'Marmara',
        'aciklama' => 'Ilıman ve nemli; kışlar serin ve yağışlı, yazlar sıcak.',
        'iller'    => [
            'balikesir'  => ['ad' => 'Balıkesir',  'lat' => 39.65, 'lon' => 27.88],
            'bilecik'    => ['ad' => 'Bilecik',    'lat' => 40.14, 'lon' => 29.98],
            'bursa'      => ['ad' => 'Bursa',      'lat' => 40.19, 'lon' => 29.06],
            'canakkale'  => ['ad' => 'Çanakkale',  'lat' => 40.15, 'lon' => 26.41],
            'edirne'     => ['ad' => 'Edirne',     'lat' => 41.68, 'lon' => 26.56],
            'istanbul'   => ['ad' => 'İstanbul',   'lat' => 41.01, 'lon' => 28.98],
            'kirklareli' => ['ad' => 'Kırklareli', 'lat' => 41.73, 'lon' => 27.22],
            'kocaeli'    => ['ad' => 'Kocaeli',    'lat' => 40.77, 'lon' => 29.92],
            'sakarya'    => ['ad' => 'Sakarya',    'lat' => 40.78, 'lon' => 30.40],
            'tekirdag'   => ['ad' => 'Tekirdağ',   'lat' => 40.98, 'lon' => 27.51],
            'yalova'     => ['ad' => 'Yalova',     'lat' => 40.65, 'lon' => 29.27],
        ],
    ],

    'ege' => [
        'ad'       => 'Ege',
        'aciklama' => 'Kıyıda Akdeniz iklimi; içeride daha sert kışlar.',
        'iller'    => [
            'afyonkarahisar' => ['ad' => 'Afyonkarahisar', 'lat' => 38.76, 'lon' => 30.54],
            'aydin'          => ['ad' => 'Aydın',          'lat' => 37.85, 'lon' => 27.85],
            'denizli'        => ['ad' => 'Denizli',        'lat' => 37.78, 'lon' => 29.09],
            'izmir'          => ['ad' => 'İzmir',          'lat' => 38.42, 'lon' => 27.14],
            'kutahya'        => ['ad' => 'Kütahya',        'lat' => 39.42, 'lon' => 29.98],
            'manisa'         => ['ad' => 'Manisa',         'lat' => 38.61, 'lon' => 27.43],
            'mugla'          => ['ad' => 'Muğla',          'lat' => 37.22, 'lon' => 28.36],
            'usak'           => ['ad' => 'Uşak',           'lat' => 38.68, 'lon' => 29.41],
        ],
    ],

    'akdeniz' => [
        'ad'       => 'Akdeniz',
        'aciklama' => 'Sıcak ve kurak yazlar, ılık ve yağışlı kışlar.',
        'iller'    => [
            'adana'         => ['ad' => 'Adana',         'lat' => 37.00, 'lon' => 35.32],
            'antalya'       => ['ad' => 'Antalya',       'lat' => 36.89, 'lon' => 30.71],
            'burdur'        => ['ad' => 'Burdur',        'lat' => 37.72, 'lon' => 30.29],
            'hatay'         => ['ad' => 'Hatay',         'lat' => 36.20, 'lon' => 36.16],
            'isparta'       => ['ad' => 'Isparta',       'lat' => 37.76, 'lon' => 30.55],
            'kahramanmaras' => ['ad' => 'Kahramanmaraş', 'lat' => 37.58, 'lon' => 36.94],
            'mersin'        => ['ad' => 'Mersin',        'lat' => 36.81, 'lon' => 34.64],
            'osmaniye'      => ['ad' => 'Osmaniye',      'lat' => 37.07, 'lon' => 36.25],
        ],
    ],

    'icanadolu' => [
        'ad'       => 'İç Anadolu',
        'aciklama' => 'Karasal iklim; yazlar kurak, kışlar soğuk ve karlı.',
        'iller'    => [
            'aksaray'   => ['ad' => 'Aksaray',   'lat' => 38.37, 'lon' => 34.03],
            'ankara'    => ['ad' => 'Ankara',    'lat' => 39.93, 'lon' => 32.86],
            'cankiri'   => ['ad' => 'Çankırı',   'lat' => 40.60, 'lon' => 33.61],
            'eskisehir' => ['ad' => 'Eskişehir', 'lat' => 39.78, 'lon' => 30.52],
            'karaman'   => ['ad' => 'Karaman',   'lat' => 37.18, 'lon' => 33.22],
            'kayseri'   => ['ad' => 'Kayseri',   'lat' => 38.73, 'lon' => 35.49],
            'kirikkale' => ['ad' => 'Kırıkkale', 'lat' => 39.85, 'lon' => 33.51],
            'kirsehir'  => ['ad' => 'Kırşehir',  'lat' => 39.15, 'lon' => 34.16],
            'konya'     => ['ad' => 'Konya',     'lat' => 37.87, 'lon' => 32.48],
            'nevsehir'  => ['ad' => 'Nevşehir',  'lat' => 38.62, 'lon' => 34.71],
            'nigde'     => ['ad' => 'Niğde',     'lat' => 37.97, 'lon' => 34.68],
            'sivas'     => ['ad' => 'Sivas',     'lat' => 39.75, 'lon' => 37.02],
            'yozgat'    => ['ad' => 'Yozgat',    'lat' => 39.82, 'lon' => 34.81],
        ],
    ],

    'karadeniz' => [
        'ad'       => 'Karadeniz',
        'aciklama' => 'Her mevsim yağışlı; yazlar serin, kışlar ılık.',
        'iller'    => [
            'amasya'    => ['ad' => 'Amasya',    'lat' => 40.65, 'lon' => 35.83],
            'artvin'    => ['ad' => 'Artvin',    'lat' => 41.18, 'lon' => 41.82],
            'bartin'    => ['ad' => 'Bartın',    'lat' => 41.64, 'lon' => 32.34],
            'bayburt'   => ['ad' => 'Bayburt',   'lat' => 40.26, 'lon' => 40.23],
            'bolu'      => ['ad' => 'Bolu',      'lat' => 40.74, 'lon' => 31.61],
            'corum'     => ['ad' => 'Çorum',     'lat' => 40.55, 'lon' => 34.95],
            'duzce'     => ['ad' => 'Düzce',     'lat' => 40.84, 'lon' => 31.16],
            'giresun'   => ['ad' => 'Giresun',   'lat' => 40.91, 'lon' => 38.39],
            'gumushane' => ['ad' => 'Gümüşhane', 'lat' => 40.46, 'lon' => 39.48],
            'karabuk'   => ['ad' => 'Karabük',   'lat' => 41.20, 'lon' => 32.62],
            'kastamonu' => ['ad' => 'Kastamonu', 'lat' => 41.38, 'lon' => 33.78],
            'ordu'      => ['ad' => 'Ordu',      'lat' => 40.98, 'lon' => 37.88],
            'rize'      => ['ad' => 'Rize',      'lat' => 41.02, 'lon' => 40.52],
            'samsun'    => ['ad' => 'Samsun',    'lat' => 41.29, 'lon' => 36.33],
            'sinop'     => ['ad' => 'Sinop',     'lat' => 42.03, 'lon' => 35.15],
            'tokat'     => ['ad' => 'Tokat',     'lat' => 40.31, 'lon' => 36.55],
            'trabzon'   => ['ad' => 'Trabzon',   'lat' => 41.00, 'lon' => 39.72],
            'zonguldak' => ['ad' => 'Zonguldak', 'lat' => 41.45, 'lon' => 31.79],
        ],
    ],

    'doguanadolu' => [
        'ad'       => 'Doğu Anadolu',
        'aciklama' => 'Türkiye\'nin en soğuk bölgesi; uzun ve karlı kışlar.',
        'iller'    => [
            'agri'     => ['ad' => 'Ağrı',     'lat' => 39.72, 'lon' => 43.05],
            'ardahan'  => ['ad' => 'Ardahan',  'lat' => 41.11, 'lon' => 42.70],
            'bingol'   => ['ad' => 'Bingöl',   'lat' => 38.88, 'lon' => 40.50],
            'bitlis'   => ['ad' => 'Bitlis',   'lat' => 38.40, 'lon' => 42.11],
            'elazig'   => ['ad' => 'Elazığ',   'lat' => 38.68, 'lon' => 39.22],
            'erzincan' => ['ad' => 'Erzincan', 'lat' => 39.75, 'lon' => 39.49],
            'erzurum'  => ['ad' => 'Erzurum',  'lat' => 39.90, 'lon' => 41.27],
            'hakkari'  => ['ad' => 'Hakkari',  'lat' => 37.58, 'lon' => 43.74],
            'igdir'    => ['ad' => 'Iğdır',    'lat' => 39.92, 'lon' => 44.04],
            'kars'     => ['ad' => 'Kars',     'lat' => 40.60, 'lon' => 43.10],
            'malatya'  => ['ad' => 'Malatya',  'lat' => 38.35, 'lon' => 38.31],
            'mus'      => ['ad' => 'Muş',      'lat' => 38.75, 'lon' => 41.49],
            'tunceli'  => ['ad' => 'Tunceli',  'lat' => 39.11, 'lon' => 39.55],
            'van'      => ['ad' => 'Van',      'lat' => 38.49, 'lon' => 43.38],
        ],
    ],

    'guneydoguanadolu' => [
        'ad'       => 'Güneydoğu Anadolu',
        'aciklama' => 'Yazları çok sıcak ve kurak, en az yağış alan bölge.',
        'iller'    => [
            'adiyaman'   => ['ad' => 'Adıyaman',   'lat' => 37.76, 'lon' => 38.28],
            'batman'     => ['ad' => 'Batman',     'lat' => 37.88, 'lon' => 41.13],
            'diyarbakir' => ['ad' => 'Diyarbakır', 'lat' => 37.91, 'lon' => 40.23],
            'gaziantep'  => ['ad' => 'Gaziantep',  'lat' => 37.07, 'lon' => 37.38],
            'kilis'      => ['ad' => 'Kilis',      'lat' => 36.72, 'lon' => 37.12],
            'mardin'     => ['ad' => 'Mardin',     'lat' => 37.31, 'lon' => 40.74],
            'siirt'      => ['ad' => 'Siirt',      'lat' => 37.93, 'lon' => 41.94],
            'sanliurfa'  => ['ad' => 'Şanlıurfa',  'lat' => 37.16, 'lon' => 38.79],
            'sirnak'     => ['ad' => 'Şırnak',     'lat' => 37.52, 'lon' => 42.46],
        ],
    ],
];

/* =====================================================================
 * 2. HAFTA — TAKİP LİSTESİ
 * Kullanıcının kendi takip ettiği şehirler. Her kayıt:
 *   id         : benzersiz numara
 *   il         : il kodu ($bolgeler dizisindeki anahtar, ör. 'istanbul')
 *   baslik     : kullanıcının verdiği ad (ör. "Ev", "Annemler")
 *   kategori   : $kategoriler dizisindeki anahtarlardan biri
 *   durum      : 'aktif' veya 'pasif'
 *   uyarilar   : hangi durumlarda uyarılmak istediği (dizi)
 *   yagisEsigi : yağış ihtimali yüzde kaçı geçerse uyarı verilsin
 *   aciklama   : isteğe bağlı kısa not
 *   eklenme    : eklendiği tarih ve saat
 * =================================================================== */

// Formdaki açılır listeler ve doğrulama için izin verilen değerler
$kategoriler = [
    'ev'      => 'Ev',
    'is'      => 'İş / Okul',
    'aile'    => 'Aile',
    'seyahat' => 'Seyahat',
    'diger'   => 'Diğer',
];

$durumlar = [
    'aktif' => 'Aktif',
    'pasif' => 'Duraklatıldı',
];

$uyariTurleri = [
    'yagis'  => 'Yağış',
    'don'    => 'Don / buzlanma',
    'ruzgar' => 'Kuvvetli rüzgâr',
    'sicak'  => 'Aşırı sıcak',
];

/*
 * Her kullanıcının ilk girişinde oturuma yüklenen örnek kayıtlar.
 * Anahtar = kullanıcı adı. ('kullanici' hesabında en az 5 kayıt var.)
 */
$ornekTakipler = [
    'kullanici' => [
        [
            'id' => 1, 'il' => 'istanbul', 'baslik' => 'Ev', 'kategori' => 'ev', 'durum' => 'aktif',
            'uyarilar' => ['yagis', 'ruzgar'], 'yagisEsigi' => 60,
            'aciklama' => 'Sabah işe çıkmadan önce bakıyorum.', 'eklenme' => '2026-10-20 08:15',
        ],
        [
            'id' => 2, 'il' => 'ankara', 'baslik' => 'Okul', 'kategori' => 'is', 'durum' => 'aktif',
            'uyarilar' => ['don', 'yagis'], 'yagisEsigi' => 50,
            'aciklama' => 'Kampüs yolu kışın buz tutuyor.', 'eklenme' => '2026-10-21 19:40',
        ],
        [
            'id' => 3, 'il' => 'trabzon', 'baslik' => 'Annemler', 'kategori' => 'aile', 'durum' => 'aktif',
            'uyarilar' => ['yagis'], 'yagisEsigi' => 70,
            'aciklama' => '', 'eklenme' => '2026-10-22 12:05',
        ],
        [
            'id' => 4, 'il' => 'antalya', 'baslik' => 'Yaz tatili', 'kategori' => 'seyahat', 'durum' => 'pasif',
            'uyarilar' => ['sicak'], 'yagisEsigi' => 50,
            'aciklama' => 'Temmuzda gideceğiz, şimdilik duraklattım.', 'eklenme' => '2026-10-23 21:30',
        ],
        [
            'id' => 5, 'il' => 'erzurum', 'baslik' => 'Kayak hafta sonu', 'kategori' => 'seyahat', 'durum' => 'aktif',
            'uyarilar' => ['don', 'ruzgar'], 'yagisEsigi' => 50,
            'aciklama' => 'Palandöken için kar ve rüzgâr durumu.', 'eklenme' => '2026-10-24 10:00',
        ],
    ],

    'admin' => [
        [
            'id' => 1, 'il' => 'ankara', 'baslik' => 'Merkez ofis', 'kategori' => 'is', 'durum' => 'aktif',
            'uyarilar' => ['don', 'yagis'], 'yagisEsigi' => 60,
            'aciklama' => 'Sunucu odası klima kontrolü için.', 'eklenme' => '2026-10-19 09:00',
        ],
        [
            'id' => 2, 'il' => 'izmir', 'baslik' => 'Şube', 'kategori' => 'is', 'durum' => 'aktif',
            'uyarilar' => ['ruzgar', 'sicak'], 'yagisEsigi' => 50,
            'aciklama' => '', 'eklenme' => '2026-10-19 09:05',
        ],
    ],
];

/* =====================================================================
 * 3. HAFTA — KULLANICI HESAPLARI
 *
 * Parolalar açık metin olarak DEĞİL, password_hash() ile üretilmiş
 * özet (hash) olarak saklanır. Giriş sırasında password_verify() ile
 * kullanıcının yazdığı parola bu özetle karşılaştırılır.
 * Test hesaplarının kullanıcı adı ve parolaları README.md dosyasındadır.
 *
 * rol: 'admin'     → kendi listesine ek olarak Yönetim sayfasını görür,
 *                    tüm kullanıcıların kayıtlarını görebilir ve silebilir,
 *                    hesap açabilir, hesapları düzenleyip silebilir.
 *      'kullanici' → kendi takip listesini ve kendi hesabını yönetir.
 *
 * Bunlar BAŞLANGIÇ hesaplarıdır. Site hesapları veri/kullanicilar.json dosyasından
 * okur ve değişiklikleri (ad, e-posta, parola, yeni hesap, silme) oraya yazar. O dosya
 * silinirse bu hesaplar kullanılır (bkz. fonksiyonlar/hesap.php).
 * =================================================================== */
$kullanicilar = [
    'admin' => [
        'adSoyad'     => 'Site Yöneticisi',
        'eposta'      => 'admin@ornek.com',
        'rol'         => 'admin',
        'parola'      => '$2y$10$fp8ZwOGQ9f0JbN809cVDkeR77MiPktQMRJm4MXWz1OxPTM5O27NU.',
        'kayitTarihi' => '2026-10-19 09:00',
    ],
    'kullanici' => [
        'adSoyad'     => 'Deneme Kullanıcısı',
        'eposta'      => 'kullanici@ornek.com',
        'rol'         => 'kullanici',
        'parola'      => '$2y$10$/f6rUqTbvCrNGff57D6JBuGlY1m6NTZ1CA.Ci87eUn4vGOBS/jjt2',
        'kayitTarihi' => '2026-10-20 08:00',
    ],
];

/* =====================================================================
 * İLÇELER — 81 il, 973 ilçe
 *
 * Anahtar: il kodu ($bolgeler dizisindeki ile aynı). Değer: ilçe adları.
 * Büyükşehir olmayan 51 ilde "Merkez" ilçesi vardır; onun hava durumu için
 * il merkezinin koordinatı kullanılır. Diğer ilçelerin koordinatları
 * Open-Meteo Geocoding API'den bulunur ve veri/ilce-konumlari.json
 * dosyasında saklanır (bkz. fonksiyonlar/ilce.php).
 * =================================================================== */
$ilceler = [
    'adana' => [
        'Aladağ', 'Ceyhan', 'Çukurova', 'Feke', 'İmamoğlu', 'Karaisalı',
        'Karataş', 'Kozan', 'Pozantı', 'Saimbeyli', 'Sarıçam', 'Seyhan',
        'Tufanbeyli', 'Yumurtalık', 'Yüreğir',
    ],
    'adiyaman' => [
        'Merkez', 'Besni', 'Çelikhan', 'Gerger', 'Gölbaşı', 'Kahta',
        'Samsat', 'Sincik', 'Tut',
    ],
    'afyonkarahisar' => [
        'Merkez', 'Başmakçı', 'Bayat', 'Bolvadin', 'Çay', 'Çobanlar',
        'Dazkırı', 'Dinar', 'Emirdağ', 'Evciler', 'Hocalar', 'İhsaniye',
        'İscehisar', 'Kızılören', 'Sandıklı', 'Sinanpaşa', 'Sultandağı', 'Şuhut',
    ],
    'agri' => [
        'Merkez', 'Diyadin', 'Doğubayazıt', 'Eleşkirt', 'Hamur', 'Patnos',
        'Taşlıçay', 'Tutak',
    ],
    'aksaray' => [
        'Merkez', 'Ağaçören', 'Eskil', 'Gülağaç', 'Güzelyurt', 'Ortaköy',
        'Sarıyahşi', 'Sultanhanı',
    ],
    'amasya' => [
        'Merkez', 'Göynücek', 'Gümüşhacıköy', 'Hamamözü', 'Merzifon', 'Suluova',
        'Taşova',
    ],
    'ankara' => [
        'Akyurt', 'Altındağ', 'Ayaş', 'Bala', 'Beypazarı', 'Çamlıdere',
        'Çankaya', 'Çubuk', 'Elmadağ', 'Etimesgut', 'Evren', 'Gölbaşı',
        'Güdül', 'Haymana', 'Kahramankazan', 'Kalecik', 'Keçiören', 'Kızılcahamam',
        'Mamak', 'Nallıhan', 'Polatlı', 'Pursaklar', 'Sincan', 'Şereflikoçhisar',
        'Yenimahalle',
    ],
    'antalya' => [
        'Akseki', 'Aksu', 'Alanya', 'Demre', 'Döşemealtı', 'Elmalı',
        'Finike', 'Gazipaşa', 'Gündoğmuş', 'İbradı', 'Kaş', 'Kemer',
        'Kepez', 'Konyaaltı', 'Korkuteli', 'Kumluca', 'Manavgat', 'Muratpaşa',
        'Serik',
    ],
    'ardahan' => [
        'Merkez', 'Çıldır', 'Damal', 'Göle', 'Hanak', 'Posof',
    ],
    'artvin' => [
        'Merkez', 'Ardanuç', 'Arhavi', 'Borçka', 'Hopa', 'Kemalpaşa',
        'Murgul', 'Şavşat', 'Yusufeli',
    ],
    'aydin' => [
        'Bozdoğan', 'Buharkent', 'Çine', 'Didim', 'Efeler', 'Germencik',
        'İncirliova', 'Karacasu', 'Karpuzlu', 'Koçarlı', 'Köşk', 'Kuşadası',
        'Kuyucak', 'Nazilli', 'Söke', 'Sultanhisar', 'Yenipazar',
    ],
    'balikesir' => [
        'Altıeylül', 'Ayvalık', 'Balya', 'Bandırma', 'Bigadiç', 'Burhaniye',
        'Dursunbey', 'Edremit', 'Erdek', 'Gömeç', 'Gönen', 'Havran',
        'İvrindi', 'Karesi', 'Kepsut', 'Manyas', 'Marmara', 'Savaştepe',
        'Sındırgı', 'Susurluk',
    ],
    'bartin' => [
        'Merkez', 'Amasra', 'Kurucaşile', 'Ulus',
    ],
    'batman' => [
        'Merkez', 'Beşiri', 'Gercüş', 'Hasankeyf', 'Kozluk', 'Sason',
    ],
    'bayburt' => [
        'Merkez', 'Aydıntepe', 'Demirözü',
    ],
    'bilecik' => [
        'Merkez', 'Bozüyük', 'Gölpazarı', 'İnhisar', 'Osmaneli', 'Pazaryeri',
        'Söğüt', 'Yenipazar',
    ],
    'bingol' => [
        'Merkez', 'Adaklı', 'Genç', 'Karlıova', 'Kiğı', 'Solhan',
        'Yayladere', 'Yedisu',
    ],
    'bitlis' => [
        'Merkez', 'Adilcevaz', 'Ahlat', 'Güroymak', 'Hizan', 'Mutki',
        'Tatvan',
    ],
    'bolu' => [
        'Merkez', 'Dörtdivan', 'Gerede', 'Göynük', 'Kıbrıscık', 'Mengen',
        'Mudurnu', 'Seben', 'Yeniçağa',
    ],
    'burdur' => [
        'Merkez', 'Ağlasun', 'Altınyayla', 'Bucak', 'Çavdır', 'Çeltikçi',
        'Gölhisar', 'Karamanlı', 'Kemer', 'Tefenni', 'Yeşilova',
    ],
    'bursa' => [
        'Büyükorhan', 'Gemlik', 'Gürsu', 'Harmancık', 'İnegöl', 'İznik',
        'Karacabey', 'Keles', 'Kestel', 'Mudanya', 'Mustafakemalpaşa', 'Nilüfer',
        'Orhaneli', 'Orhangazi', 'Osmangazi', 'Yenişehir', 'Yıldırım',
    ],
    'canakkale' => [
        'Merkez', 'Ayvacık', 'Bayramiç', 'Biga', 'Bozcaada', 'Çan',
        'Eceabat', 'Ezine', 'Gelibolu', 'Gökçeada', 'Lapseki', 'Yenice',
    ],
    'cankiri' => [
        'Merkez', 'Atkaracalar', 'Bayramören', 'Çerkeş', 'Eldivan', 'Ilgaz',
        'Kızılırmak', 'Korgun', 'Kurşunlu', 'Orta', 'Şabanözü', 'Yapraklı',
    ],
    'corum' => [
        'Merkez', 'Alaca', 'Bayat', 'Boğazkale', 'Dodurga', 'İskilip',
        'Kargı', 'Laçin', 'Mecitözü', 'Oğuzlar', 'Ortaköy', 'Osmancık',
        'Sungurlu', 'Uğurludağ',
    ],
    'denizli' => [
        'Acıpayam', 'Babadağ', 'Baklan', 'Bekilli', 'Beyağaç', 'Bozkurt',
        'Buldan', 'Çal', 'Çameli', 'Çardak', 'Çivril', 'Güney',
        'Honaz', 'Kale', 'Merkezefendi', 'Pamukkale', 'Sarayköy', 'Serinhisar',
        'Tavas',
    ],
    'diyarbakir' => [
        'Bağlar', 'Bismil', 'Çermik', 'Çınar', 'Çüngüş', 'Dicle',
        'Eğil', 'Ergani', 'Hani', 'Hazro', 'Kayapınar', 'Kocaköy',
        'Kulp', 'Lice', 'Silvan', 'Sur', 'Yenişehir',
    ],
    'duzce' => [
        'Merkez', 'Akçakoca', 'Cumayeri', 'Çilimli', 'Gölyaka', 'Gümüşova',
        'Kaynaşlı', 'Yığılca',
    ],
    'edirne' => [
        'Merkez', 'Enez', 'Havsa', 'İpsala', 'Keşan', 'Lalapaşa',
        'Meriç', 'Süloğlu', 'Uzunköprü',
    ],
    'elazig' => [
        'Merkez', 'Ağın', 'Alacakaya', 'Arıcak', 'Baskil', 'Karakoçan',
        'Keban', 'Kovancılar', 'Maden', 'Palu', 'Sivrice',
    ],
    'erzincan' => [
        'Merkez', 'Çayırlı', 'İliç', 'Kemah', 'Kemaliye', 'Otlukbeli',
        'Refahiye', 'Tercan', 'Üzümlü',
    ],
    'erzurum' => [
        'Aşkale', 'Aziziye', 'Çat', 'Hınıs', 'Horasan', 'İspir',
        'Karaçoban', 'Karayazı', 'Köprüköy', 'Narman', 'Oltu', 'Olur',
        'Palandöken', 'Pasinler', 'Pazaryolu', 'Şenkaya', 'Tekman', 'Tortum',
        'Uzundere', 'Yakutiye',
    ],
    'eskisehir' => [
        'Alpu', 'Beylikova', 'Çifteler', 'Günyüzü', 'Han', 'İnönü',
        'Mahmudiye', 'Mihalgazi', 'Mihalıççık', 'Odunpazarı', 'Sarıcakaya', 'Seyitgazi',
        'Sivrihisar', 'Tepebaşı',
    ],
    'gaziantep' => [
        'Araban', 'İslahiye', 'Karkamış', 'Nizip', 'Nurdağı', 'Oğuzeli',
        'Şahinbey', 'Şehitkamil', 'Yavuzeli',
    ],
    'giresun' => [
        'Merkez', 'Alucra', 'Bulancak', 'Çamoluk', 'Çanakçı', 'Dereli',
        'Doğankent', 'Espiye', 'Eynesil', 'Görele', 'Güce', 'Keşap',
        'Piraziz', 'Şebinkarahisar', 'Tirebolu', 'Yağlıdere',
    ],
    'gumushane' => [
        'Merkez', 'Kelkit', 'Köse', 'Kürtün', 'Şiran', 'Torul',
    ],
    'hakkari' => [
        'Merkez', 'Çukurca', 'Derecik', 'Şemdinli', 'Yüksekova',
    ],
    'hatay' => [
        'Altınözü', 'Antakya', 'Arsuz', 'Belen', 'Defne', 'Dörtyol',
        'Erzin', 'Hassa', 'İskenderun', 'Kırıkhan', 'Kumlu', 'Payas',
        'Reyhanlı', 'Samandağ', 'Yayladağı',
    ],
    'igdir' => [
        'Merkez', 'Aralık', 'Karakoyunlu', 'Tuzluca',
    ],
    'isparta' => [
        'Merkez', 'Aksu', 'Atabey', 'Eğirdir', 'Gelendost', 'Gönen',
        'Keçiborlu', 'Senirkent', 'Sütçüler', 'Şarkikaraağaç', 'Uluborlu', 'Yalvaç',
        'Yenişarbademli',
    ],
    'istanbul' => [
        'Adalar', 'Arnavutköy', 'Ataşehir', 'Avcılar', 'Bağcılar', 'Bahçelievler',
        'Bakırköy', 'Başakşehir', 'Bayrampaşa', 'Beşiktaş', 'Beykoz', 'Beylikdüzü',
        'Beyoğlu', 'Büyükçekmece', 'Çatalca', 'Çekmeköy', 'Esenler', 'Esenyurt',
        'Eyüpsultan', 'Fatih', 'Gaziosmanpaşa', 'Güngören', 'Kadıköy', 'Kağıthane',
        'Kartal', 'Küçükçekmece', 'Maltepe', 'Pendik', 'Sancaktepe', 'Sarıyer',
        'Silivri', 'Sultanbeyli', 'Sultangazi', 'Şile', 'Şişli', 'Tuzla',
        'Ümraniye', 'Üsküdar', 'Zeytinburnu',
    ],
    'izmir' => [
        'Aliağa', 'Balçova', 'Bayındır', 'Bayraklı', 'Bergama', 'Beydağ',
        'Bornova', 'Buca', 'Çeşme', 'Çiğli', 'Dikili', 'Foça',
        'Gaziemir', 'Güzelbahçe', 'Karabağlar', 'Karaburun', 'Karşıyaka', 'Kemalpaşa',
        'Kınık', 'Kiraz', 'Konak', 'Menderes', 'Menemen', 'Narlıdere',
        'Ödemiş', 'Seferihisar', 'Selçuk', 'Tire', 'Torbalı', 'Urla',
    ],
    'kahramanmaras' => [
        'Afşin', 'Andırın', 'Çağlayancerit', 'Dulkadiroğlu', 'Ekinözü', 'Elbistan',
        'Göksun', 'Nurhak', 'Onikişubat', 'Pazarcık', 'Türkoğlu',
    ],
    'karabuk' => [
        'Merkez', 'Eflani', 'Eskipazar', 'Ovacık', 'Safranbolu', 'Yenice',
    ],
    'karaman' => [
        'Merkez', 'Ayrancı', 'Başyayla', 'Ermenek', 'Kazımkarabekir', 'Sarıveliler',
    ],
    'kars' => [
        'Merkez', 'Akyaka', 'Arpaçay', 'Digor', 'Kağızman', 'Sarıkamış',
        'Selim', 'Susuz',
    ],
    'kastamonu' => [
        'Merkez', 'Abana', 'Ağlı', 'Araç', 'Azdavay', 'Bozkurt',
        'Cide', 'Çatalzeytin', 'Daday', 'Devrekani', 'Doğanyurt', 'Hanönü',
        'İhsangazi', 'İnebolu', 'Küre', 'Pınarbaşı', 'Seydiler', 'Şenpazar',
        'Taşköprü', 'Tosya',
    ],
    'kayseri' => [
        'Akkışla', 'Bünyan', 'Develi', 'Felahiye', 'Hacılar', 'İncesu',
        'Kocasinan', 'Melikgazi', 'Özvatan', 'Pınarbaşı', 'Sarıoğlan', 'Sarız',
        'Talas', 'Tomarza', 'Yahyalı', 'Yeşilhisar',
    ],
    'kirikkale' => [
        'Merkez', 'Bahşılı', 'Balışeyh', 'Çelebi', 'Delice', 'Karakeçili',
        'Keskin', 'Sulakyurt', 'Yahşihan',
    ],
    'kirklareli' => [
        'Merkez', 'Babaeski', 'Demirköy', 'Kofçaz', 'Lüleburgaz', 'Pehlivanköy',
        'Pınarhisar', 'Vize',
    ],
    'kirsehir' => [
        'Merkez', 'Akçakent', 'Akpınar', 'Boztepe', 'Çiçekdağı', 'Kaman',
        'Mucur',
    ],
    'kilis' => [
        'Merkez', 'Elbeyli', 'Musabeyli', 'Polateli',
    ],
    'kocaeli' => [
        'Başiskele', 'Çayırova', 'Darıca', 'Derince', 'Dilovası', 'Gebze',
        'Gölcük', 'İzmit', 'Kandıra', 'Karamürsel', 'Kartepe', 'Körfez',
    ],
    'konya' => [
        'Ahırlı', 'Akören', 'Akşehir', 'Altınekin', 'Beyşehir', 'Bozkır',
        'Cihanbeyli', 'Çeltik', 'Çumra', 'Derbent', 'Derebucak', 'Doğanhisar',
        'Emirgazi', 'Ereğli', 'Güneysınır', 'Hadim', 'Halkapınar', 'Hüyük',
        'Ilgın', 'Kadınhanı', 'Karapınar', 'Karatay', 'Kulu', 'Meram',
        'Sarayönü', 'Selçuklu', 'Seydişehir', 'Taşkent', 'Tuzlukçu', 'Yalıhüyük',
        'Yunak',
    ],
    'kutahya' => [
        'Merkez', 'Altıntaş', 'Aslanapa', 'Çavdarhisar', 'Domaniç', 'Dumlupınar',
        'Emet', 'Gediz', 'Hisarcık', 'Pazarlar', 'Simav', 'Şaphane',
        'Tavşanlı',
    ],
    'malatya' => [
        'Akçadağ', 'Arapgir', 'Arguvan', 'Battalgazi', 'Darende', 'Doğanşehir',
        'Doğanyol', 'Hekimhan', 'Kale', 'Kuluncak', 'Pütürge', 'Yazıhan',
        'Yeşilyurt',
    ],
    'manisa' => [
        'Ahmetli', 'Akhisar', 'Alaşehir', 'Demirci', 'Gölmarmara', 'Gördes',
        'Kırkağaç', 'Köprübaşı', 'Kula', 'Salihli', 'Sarıgöl', 'Saruhanlı',
        'Selendi', 'Soma', 'Şehzadeler', 'Turgutlu', 'Yunusemre',
    ],
    'mardin' => [
        'Artuklu', 'Dargeçit', 'Derik', 'Kızıltepe', 'Mazıdağı', 'Midyat',
        'Nusaybin', 'Ömerli', 'Savur', 'Yeşilli',
    ],
    'mersin' => [
        'Akdeniz', 'Anamur', 'Aydıncık', 'Bozyazı', 'Çamlıyayla', 'Erdemli',
        'Gülnar', 'Mezitli', 'Mut', 'Silifke', 'Tarsus', 'Toroslar',
        'Yenişehir',
    ],
    'mugla' => [
        'Bodrum', 'Dalaman', 'Datça', 'Fethiye', 'Kavaklıdere', 'Köyceğiz',
        'Marmaris', 'Menteşe', 'Milas', 'Ortaca', 'Seydikemer', 'Ula',
        'Yatağan',
    ],
    'mus' => [
        'Merkez', 'Bulanık', 'Hasköy', 'Korkut', 'Malazgirt', 'Varto',
    ],
    'nevsehir' => [
        'Merkez', 'Acıgöl', 'Avanos', 'Derinkuyu', 'Gülşehir', 'Hacıbektaş',
        'Kozaklı', 'Ürgüp',
    ],
    'nigde' => [
        'Merkez', 'Altunhisar', 'Bor', 'Çamardı', 'Çiftlik', 'Ulukışla',
    ],
    'ordu' => [
        'Akkuş', 'Altınordu', 'Aybastı', 'Çamaş', 'Çatalpınar', 'Çaybaşı',
        'Fatsa', 'Gölköy', 'Gülyalı', 'Gürgentepe', 'İkizce', 'Kabadüz',
        'Kabataş', 'Korgan', 'Kumru', 'Mesudiye', 'Perşembe', 'Ulubey',
        'Ünye',
    ],
    'osmaniye' => [
        'Merkez', 'Bahçe', 'Düziçi', 'Hasanbeyli', 'Kadirli', 'Sumbas',
        'Toprakkale',
    ],
    'rize' => [
        'Merkez', 'Ardeşen', 'Çamlıhemşin', 'Çayeli', 'Derepazarı', 'Fındıklı',
        'Güneysu', 'Hemşin', 'İkizdere', 'İyidere', 'Kalkandere', 'Pazar',
    ],
    'sakarya' => [
        'Adapazarı', 'Akyazı', 'Arifiye', 'Erenler', 'Ferizli', 'Geyve',
        'Hendek', 'Karapürçek', 'Karasu', 'Kaynarca', 'Kocaali', 'Pamukova',
        'Sapanca', 'Serdivan', 'Söğütlü', 'Taraklı',
    ],
    'samsun' => [
        'Alaçam', 'Asarcık', 'Atakum', 'Ayvacık', 'Bafra', 'Canik',
        'Çarşamba', 'Havza', 'İlkadım', 'Kavak', 'Ladik', 'Ondokuzmayıs',
        'Salıpazarı', 'Tekkeköy', 'Terme', 'Vezirköprü', 'Yakakent',
    ],
    'siirt' => [
        'Merkez', 'Baykan', 'Eruh', 'Kurtalan', 'Pervari', 'Şirvan',
        'Tillo',
    ],
    'sinop' => [
        'Merkez', 'Ayancık', 'Boyabat', 'Dikmen', 'Durağan', 'Erfelek',
        'Gerze', 'Saraydüzü', 'Türkeli',
    ],
    'sivas' => [
        'Merkez', 'Akıncılar', 'Altınyayla', 'Divriği', 'Doğanşar', 'Gemerek',
        'Gölova', 'Gürün', 'Hafik', 'İmranlı', 'Kangal', 'Koyulhisar',
        'Suşehri', 'Şarkışla', 'Ulaş', 'Yıldızeli', 'Zara',
    ],
    'sanliurfa' => [
        'Akçakale', 'Birecik', 'Bozova', 'Ceylanpınar', 'Eyyübiye', 'Halfeti',
        'Haliliye', 'Harran', 'Hilvan', 'Karaköprü', 'Siverek', 'Suruç',
        'Viranşehir',
    ],
    'sirnak' => [
        'Merkez', 'Beytüşşebap', 'Cizre', 'Güçlükonak', 'İdil', 'Silopi',
        'Uludere',
    ],
    'tekirdag' => [
        'Çerkezköy', 'Çorlu', 'Ergene', 'Hayrabolu', 'Kapaklı', 'Malkara',
        'Marmaraereğlisi', 'Muratlı', 'Saray', 'Süleymanpaşa', 'Şarköy',
    ],
    'tokat' => [
        'Merkez', 'Almus', 'Artova', 'Başçiftlik', 'Erbaa', 'Niksar',
        'Pazar', 'Reşadiye', 'Sulusaray', 'Turhal', 'Yeşilyurt', 'Zile',
    ],
    'trabzon' => [
        'Akçaabat', 'Araklı', 'Arsin', 'Beşikdüzü', 'Çarşıbaşı', 'Çaykara',
        'Dernekpazarı', 'Düzköy', 'Hayrat', 'Köprübaşı', 'Maçka', 'Of',
        'Ortahisar', 'Sürmene', 'Şalpazarı', 'Tonya', 'Vakfıkebir', 'Yomra',
    ],
    'tunceli' => [
        'Merkez', 'Çemişgezek', 'Hozat', 'Mazgirt', 'Nazımiye', 'Ovacık',
        'Pertek', 'Pülümür',
    ],
    'usak' => [
        'Merkez', 'Banaz', 'Eşme', 'Karahallı', 'Sivaslı', 'Ulubey',
    ],
    'van' => [
        'Bahçesaray', 'Başkale', 'Çaldıran', 'Çatak', 'Edremit', 'Erciş',
        'Gevaş', 'Gürpınar', 'İpekyolu', 'Muradiye', 'Özalp', 'Saray',
        'Tuşba',
    ],
    'yalova' => [
        'Merkez', 'Altınova', 'Armutlu', 'Çınarcık', 'Çiftlikköy', 'Termal',
    ],
    'yozgat' => [
        'Merkez', 'Akdağmadeni', 'Aydıncık', 'Boğazlıyan', 'Çandır', 'Çayıralan',
        'Çekerek', 'Kadışehri', 'Saraykent', 'Sarıkaya', 'Sorgun', 'Şefaatli',
        'Yenifakılı', 'Yerköy',
    ],
    'zonguldak' => [
        'Merkez', 'Alaplı', 'Çaycuma', 'Devrek', 'Ereğli', 'Gökçebey',
        'Kilimli', 'Kozlu',
    ],
];
