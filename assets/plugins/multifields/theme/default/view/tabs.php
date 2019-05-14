<div class="tab-pane col-12" id="tabs_<?= $tvId ?>_<?= $id ?>" data-type="<?= $type ?>" data-name="<?= $name ?>">
    <script>
      var tabs_<?= $tvId ?>_<?= $id ?> = new WebFXTabPane(document.getElementById('tabs_<?= $tvId ?>_<?= $id ?>'), false);
    </script>
    <?= $items ?>
    <?= $actions ?>
</div>