<picture>
    <?php foreach ($sources as $source) : ?>
        <?php if (array_key_exists('srcset', $source) && $source['srcset']) : ?>
            <source
                <?php if (array_key_exists('media', $source) && $source['media']) : ?>
                    media="<?= $source['media'] ?>"
                <?php endif; ?>
                <?php if (array_key_exists('sizes', $source) && $source['sizes']) : ?>
                    sizes="<?= $source['sizes'] ?>"
                <?php endif; ?>
                <?php if (array_key_exists('type', $source) && $source['type']) : ?>
                    type="<?= $source['type'] ?>"
                <?php endif; ?>
                <?php if (array_key_exists('srcset', $source) && $source['srcset']) : ?>
                    media="<?=  implode(' ', $source['srcset']) ?>"
                <?php endif; ?>
            >
        <?php endif; ?>
    <?php endforeach ?>
    <?= $image->drawImage($sizeGroup) ?>
</picture>
