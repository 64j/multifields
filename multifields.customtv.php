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
      Multifields.lastImageCtrl = null;
      Multifields.lastFileCtrl = null;

      Multifields.OpenServerBrowser = function(url, width, height) {
        var iLeft = (screen.width - width) / 2;
        var iTop = (screen.height - height) / 2;

        var sOptions = 'toolbar=no,status=no,resizable=yes,dependent=yes';
        sOptions += ',width=' + width;
        sOptions += ',height=' + height;
        sOptions += ',left=' + iLeft;
        sOptions += ',top=' + iTop;

        var oWindow = window.open(url, 'FCKBrowseWindow', sOptions);

        var mf_setCallback = setInterval(function() {
          if (typeof window.KCFinder !== 'undefined') {
            clearInterval(mf_setCallback);
            window.KCFinder.callBack = Multifields.SetUrl;
            window.KCFinder.callBackMultiple = function(files) {
              Multifields.SetUrl(files[0]);
//              for (var i = 1; i < files.length; i++) {
//                // callBackMultiple
//              }
            };
          }
        }, 100);
      };

      Multifields.BrowseServer = function(ctrl) {
        Multifields.lastImageCtrl = ctrl;
        var w = screen.width * 0.5;
        var h = screen.height * 0.5;
        Multifields.OpenServerBrowser(
            '<?= MODX_MANAGER_URL ?>media/browser/<?= $modx->config['which_browser'] ?>/browser.php?type=images', w,
            h);
      };

      Multifields.BrowseFileServer = function(ctrl) {
        Multifields.lastFileCtrl = ctrl;
        var w = screen.width * 0.5;
        var h = screen.height * 0.5;
        Multifields.OpenServerBrowser(
            '<?= MODX_MANAGER_URL ?>media/browser/<?= $modx->config['which_browser'] ?>/browser.php?type=files', w,
            h);
      };

      Multifields.SetUrlChange = function(el) {
        if ('createEvent' in document) {
          var evt = document.createEvent('HTMLEvents');
          evt.initEvent('change', false, true);
          el.dispatchEvent(evt);
        } else {
          el.fireEvent('onchange');
        }
      };

      Multifields.SetUrl = function(url) {
        var c;
        if (Multifields.lastFileCtrl) {
          c = typeof Multifields.lastFileCtrl === 'object' ? Multifields.lastFileCtrl : document.getElementById(
              Multifields.lastFileCtrl);
          if (c && c.value !== url) {
            c.value = url;
            Multifields.SetUrlChange(c);
          }
          Multifields.lastFileCtrl = '';
        } else if (Multifields.lastImageCtrl) {
          c = typeof Multifields.lastImageCtrl === 'object' ? Multifields.lastImageCtrl : document.getElementById(
              Multifields.lastImageCtrl);
          if (c && c.value !== url) {
            c.value = url;
            Multifields.SetUrlChange(c);
          }
          Multifields.lastImageCtrl = '';
        } else {

        }
      };

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