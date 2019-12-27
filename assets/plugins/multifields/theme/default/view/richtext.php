<div class="mf-col mf-richtext input-group<?= $class ?>" data-type="<?= $type ?>" data-name="<?= $name ?>">
    <?= $element ?>
    <div class="btn" onclick="MultiFields.getRichText(this.previousElementSibling, '<?= $tvId ?>', '<?= $elements ?>')">
        <i class="fa fa-pencil"></i>
    </div>
</div>
