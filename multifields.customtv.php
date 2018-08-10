<?php
if (!defined('MODX_BASE_PATH')) {
    die('HACK???');
}

$baseDir = str_replace(dirname(__DIR__) . DIRECTORY_SEPARATOR, '', __DIR__);
if (!class_exists('multifields')) {
    include 'class.multifields.php';
    ?>
    <script src="../assets/tvs/<?= $baseDir ?>/js/multifields.js"></script>
    <link rel="stylesheet" type="text/css" href="../assets/tvs/<?= $baseDir ?>/css/style.css">
    <script>
      if (typeof window.BrowseServer !== 'function' && typeof window.BrowseFileServer !== 'function') {
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
      }

      if (typeof window.SetUrlChange !== 'function') {
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
      }

      if (typeof window.SetUrl === 'function') {
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
          } else { }
        }
      }

    </script>
    <?php
}
$value = $row['value'];
$row['value'] = !empty($value) ? json_decode($value, true) : array();

if (!empty($row['elements'])) {
    $row['elements'] = json_decode($row['elements'], true);
} elseif (file_exists(MODX_BASE_PATH . 'assets/tvs/' . $baseDir . '/configs/' . $row['name'] . '.config.inc.php')) {
    $templates = array();
    include_once MODX_BASE_PATH . 'assets/tvs/' . $baseDir . '/configs/' . $row['name'] . '.config.inc.php';
    $row['elements'] = $templates;
} else {
    return;
}

$mf = new multifields($row);
echo $mf->run();
?>
<textarea name="tv<?= $row['id'] ?>" id="tv<?= $row['id'] ?>" style="display: block;height: 500px;"><?= $value ?></textarea>
<script>
  new Multifields({
    id: '<?= $row['id'] ?>',
    field_id: 'tv<?= $row['id'] ?>',
    field_name: '<?= $row['name'] ?>'
  });
</script>