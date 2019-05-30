<div class="mf-table<?= $class ?>" data-type="table" data-name="<?= $name ?>"<?= (!empty($display) ? ' data-display="' . $display . '" style="height: calc((1.75rem * ' . $display . ') + 6.75rem);"' : '') ?>>
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