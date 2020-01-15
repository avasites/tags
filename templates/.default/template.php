
<?php foreach($arResult['TAGS'] as $tags): ?>
    <div class="tags">
        <div class="tag_label">
            <?= $tags['NAME'] ?>:
        </div>
        <div class="tag_list">

            <?php foreach($tags['VALUE'] as $key=> $arItem): ?>
            
                <a href="<?= $arItem['URL'] ?>"><?= $arItem['NAME'] ?></a>

            <?php endforeach; ?>

        </div>
    </div>
<?php endforeach; ?>
