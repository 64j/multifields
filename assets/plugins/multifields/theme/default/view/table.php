<div class="mf-table<?= $class ?>" data-type="table" data-name="<?= $name ?>">
    <?php
    if (isset($value)) {
        ?>
        <div class="mf-title col-12">
            <input type="text" class="form-control form-control-sm" value="<?= $value ?>"<?= (isset($placeholder) ? ' placeholder="' . $placeholder . '"' : '') ?> data-value>
        </div>
        <?php
    }
    ?>
    <?= $actions ?>
    <?= $header ?>
    <?= $items ?>
</div>