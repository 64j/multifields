<div class="tab-page" id="tab_<?= $tvId ?>_<?= $id ?>" data-type="<?= $type ?>" data-name="<?= $name ?>">
    <h2 class="tab"><?= $title ?></h2>
    <script>window[document.getElementById('tab_<?= $tvId ?>_<?= $id ?>').closest('.tab-pane').id].addTabPage(document.getElementById('tab_<?= $tvId ?>_<?= $id ?>'));</script>
    <div class="tab-body">
        <?= $items ?>
    </div>
</div>