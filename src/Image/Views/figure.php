<figure class="Figure">
    <div class="Figure__Image">
        <?= $image->drawPicture($sizeGroup) ?>
    </div>
    <?php if ($caption && $captionEnabled) : ?>
        <figcaption class="Figure__Caption">
            <?= $caption ?>
        </figcaption>
    <?php endif ?>
</figure>
