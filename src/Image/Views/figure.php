<figure class="Figure">
    <div class="Figure__Image">
        <?= $image->drawPicture($sizeGroup, $classes) ?>
    </div>
    <?php if ($image->caption) : ?>
    <figcaption class="Figure__Caption">
        <?= $image->caption?>
    </figcaption>
    <?php endif ?>
</figure>