<?php
/**
 * custom tv multifields
 * @author 64j
 */

if (!defined('MODX_BASE_PATH')) {
    die('HACK???');
}

$baseDir = str_replace(dirname(__DIR__) . DIRECTORY_SEPARATOR, '', __DIR__);
if (!class_exists('multifields')) {
    include 'class.multifields.php';
    ?>
    <link rel="stylesheet" type="text/css" href="../assets/tvs/<?= $baseDir ?>/css/style.css">
    <script src="../assets/tvs/<?= $baseDir ?>/js/multifields.js"></script>
    <script>
      Multifields.urlBrowseServer = '<?= MODX_MANAGER_URL ?>media/browser/<?= $modx->config['which_browser'] ?>/browser.php';
    </script>
    <?php
}
$value = $row['value'];
$row['value'] = !empty($value) ? json_decode($value, true) : [];

if (!empty($row['elements'])) {
    $row['templates'] = json_decode($row['elements'], true);
} elseif (file_exists(MODX_BASE_PATH . 'assets/tvs/' . $baseDir . '/configs/' . $row['name'] . '.config.inc.php')) {
    $row['templates'] = include_once MODX_BASE_PATH . 'assets/tvs/' . $baseDir . '/configs/' . $row['name'] . '.config.inc.php';
} else {
    return;
}

$mf = new multifields($row);
echo $mf->run();
?>
<textarea name="tv<?= $row['id'] ?>" id="tv<?= $row['id'] ?>" style="display: none;height: 500px;"><?= $value ?></textarea>
<script>
  new Multifields({
    name: '<?= $row['name'] ?>',
    id: '<?= $row['id'] ?>'
  });
</script>