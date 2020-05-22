<div class="multifields mf-wrap" data-tvid="<?= $tvId ?>" data-tvname="<?= $tvName ?>">
    <?= $toolbar ?>
    <?= $items ?>
</div>
<textarea name="<?= (!is_int($tvId) ? 'tv' : '') . $fieldname ?>" data-tvid="<?= $tvId ?>" data-tvname="<?= $tvName ?>" data-multifields rows="10"><?= $value ?></textarea>
