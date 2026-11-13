<?php
/*
 * ilce-secim.php — İl sayfasındaki ilçe listesi (il.php tarafından dahil edilir).
 *
 * <details> etiketi tıklanınca açılıp kapanır; JavaScript gerekmez.
 * İlçe seçiliyse veya adreste ?ilceler=1 varsa açık gelir.
 *
 * Kullandığı değişkenler (il.php'de tanımlı):
 *   $ilKodu, $il, $ilceListe, $ilceKodu, $ilceAdi, $ilceSecimAcik
 */
?>
<details id="ilce-secim" class="ilce-secim" <?php echo $ilceSecimAcik ? 'open' : ''; ?>>
    <summary>
        <span class="ilce-secim-baslik">
            <?php echo e($il['ad']); ?> ilçeleri
            <span class="rozet"><?php echo count($ilceListe); ?></span>
        </span>
        <span class="ilce-secim-durum">
            <?php if ($ilceKodu !== ''): ?>
                Seçili: <strong translate="no"><?php echo e($ilceAdi); ?></strong>
            <?php else: ?>
                Bir ilçe seçin
            <?php endif; ?>
        </span>
    </summary>

    <div class="ilce-liste">
        <!-- İl merkezine dönüş -->
        <a href="il.php?il=<?php echo $ilKodu; ?>&ilceler=1#ilce-secim"
           class="<?php echo ($ilceKodu === '' || $ilceKodu == 'merkez') ? 'secili' : ''; ?>">
            <?php echo e($il['ad']); ?> <small>il merkezi</small>
        </a>

        <?php foreach ($ilceListe as $kod => $ad): ?>
            <?php if ($kod == 'merkez') continue; /* Merkez ilçe = il merkezi, yukarıda */ ?>
            <a href="il.php?il=<?php echo $ilKodu; ?>&ilce=<?php echo $kod; ?>"
               class="<?php echo ($kod == $ilceKodu) ? 'secili' : ''; ?>" translate="no">
                <?php echo e($ad); ?>
            </a>
        <?php endforeach; ?>
    </div>
</details>
