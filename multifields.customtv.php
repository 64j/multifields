<?php
if (!defined('MODX_BASE_PATH')) {
    die('HACK???');
}

if (!isset($modx->customTV['multifields'])) {
    $modx->customTV['multifields'] = array();
    require_once 'class.multifields.php';
    ?>

    <script src="../assets/tvs/multifields/js/multifields.js"></script>
    <link rel="stylesheet" type="text/css" href="../assets/tvs/multifields/css/style.css">
    <?php
    $size = '';
    if (!empty($modx->config['thumbWidth'])) {
        $size = $modx->config['thumbWidth'];
    }
    if (!empty($modx->config['thumbHeight'])) {
        $size = $modx->config['thumbHeight'];
    }
    if ($size) {
        ?>
        <style>
            .multifields .item-thumb { width: <?= $size ?>px; height: <?= $size ?>px;  }
        </style>
        <?php
    }

    if ($content['type'] != 'reference' || $modx->manager->action != '72') {
        ?>
        <script>
          var lastImageCtrl;
          var lastFileCtrl;

          function OpenServerBrowser(url, width, height)
          {
            var iLeft = (screen.width - width) / 2;
            var iTop = (screen.height - height) / 2;

            var sOptions = 'toolbar=no,status=no,resizable=yes,dependent=yes';
            sOptions += ',width=' + width;
            sOptions += ',height=' + height;
            sOptions += ',left=' + iLeft;
            sOptions += ',top=' + iTop;

            var oWindow = window.open(url, 'FCKBrowseWindow', sOptions);
          }

          function BrowseServer(ctrl)
          {
            lastImageCtrl = ctrl;
            var w = screen.width * 0.5;
            var h = screen.height * 0.5;
            OpenServerBrowser('<?= MODX_MANAGER_URL ?>media/browser/<?= $which_browser ?>/browser.php?Type=images', w, h);
          }

          function BrowseFileServer(ctrl)
          {
            lastFileCtrl = ctrl;
            var w = screen.width * 0.5;
            var h = screen.height * 0.5;
            OpenServerBrowser('<?= MODX_MANAGER_URL ?>media/browser/<?= $which_browser ?>/browser.php?Type=files', w, h);
          }

          function SetUrlChange(el)
          {
            if ('createEvent' in document) {
              var evt = document.createEvent('HTMLEvents');
              evt.initEvent('change', false, true);
              el.dispatchEvent(evt);
            } else {
              el.fireEvent('onchange');
            }
          }

          function SetUrl(url, width, height, alt)
          {
            if (lastFileCtrl) {
              var c = document.getElementById(lastFileCtrl);
              if (c && c.value !== url) {
                c.value = url;
                SetUrlChange(c);
              }
              lastFileCtrl = '';
            } else if (lastImageCtrl) {
              var c = document.getElementById(lastImageCtrl);
              if (c && c.value !== url) {
                c.value = url;
                SetUrlChange(c);
              }
              lastImageCtrl = '';
            } else {

            }
          }
        </script>
        <?php
    }
}

$config = array();
$config['field_id'] = $field_id;
$config['elements'] = !empty($field_elements) ? json_decode($field_elements, true) : array();
$config['templates'] = isset($config['elements']['templates']) ? $config['elements']['templates'] : array();
$config['value'] = !empty($field_value) ? json_decode($field_value, true) : array();
$config['title'] = isset($config['elements']['title']) ? $config['elements']['title'] : '';
$config['cols'] = isset($config['elements']['cols']) ? $config['elements']['cols'] : array();
$config['schema'] = isset($config['elements']['schema']) ? $config['elements']['schema'] : ''; // mm | mtv
$config['render'] = false;

$controller = new customTvMultifields($config, $modx);
echo $controller->run();
?>
<textarea name="tv<?= $field_id ?>" id="tv<?= $field_id ?>" style="display: none; height: 200px; margin-top: 1rem;"><?= $field_value ?></textarea>
<script>
  new Multifields('tv<?= $field_id ?>');
</script>