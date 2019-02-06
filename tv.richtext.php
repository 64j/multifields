<?php
define('MODX_API_MODE', true);
define('IN_MANAGER_MODE', true);

include_once('../../../index.php');

$modx->db->connect();

if (empty($modx->config)) {
    $modx->getSettings();
}

if (!isset($_SESSION['mgrValidated'])/* || !isset($_SERVER['HTTP_X_REQUESTED_WITH']) || (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') || ($_SERVER['REQUEST_METHOD'] != 'POST')*/) {
    $modx->sendErrorPage();
}

//$modx->sid = session_id();
//$modx->loadExtension("ManagerAPI");

$_lang = [];
include_once MODX_MANAGER_PATH . '/includes/lang/english.inc.php';
if ($modx->config['manager_language'] != 'english') {
    include_once MODX_MANAGER_PATH . '/includes/lang/' . $modx->config['manager_language'] . '.inc.php';
}
include_once MODX_MANAGER_PATH . '/media/style/' . $modx->config['manager_theme'] . '/style.php';
$mxla = $modx_lang_attribute ? $modx_lang_attribute : 'en';

$which_editor = $modx->config['which_editor'];
if (!empty($_POST['which_editor'])) {
    $which_editor = $_POST['which_editor'];
}
$which_editor_config = [
    'editor' => $which_editor,
    'elements' => ['ta'],
    'options' => [
        'ta' => [
            'theme' => 'introtext',
            'width' => '100%',
            'height' => '100%',
        ]
    ]
];
if (!empty($_REQUEST['options']) && is_scalar($_REQUEST['options'])) {
    $options = base64_decode($_REQUEST['options']);
    $options = json_decode($options, true);
    if (is_array($options)) {
        $which_editor_config['options']['ta'] = array_merge($which_editor_config['options']['ta'], $options);
    }
}
?>
<!doctype html>
<html lang="<?= $mxla ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>MultiFields:: RichText</title>
    <link rel="stylesheet" href="<?= MODX_BASE_URL . MGR_DIR ?>/media/style/<?= $modx->config['manager_theme'] ?>/style.css">
    <style>
        html, body, body > form { position: relative; margin: 0; padding: 0; height: 100%; }
        #table-layout { position: relative; width: 100%; height: 100%; padding: 0; border: none; border-collapse: collapse; }
        #table-layout > tfoot > tr { height: 2rem; }
        #table-layout > tbody > tr > td > .mce-tinymce { height: 100% !important; border: none !important; }
        #table-layout > tbody > tr > td > .mce-tinymce > .mce-container-body { display: table !important; width: 100%; height: 100%; }
        #table-layout > tbody > tr > td > .mce-tinymce > .mce-container-body > div { display: table-row !important; height: 1%; }
        #table-layout > tbody > tr > td > .mce-tinymce > .mce-container-body > div.mce-edit-area { height: auto }
        textarea, textarea + div { height: 100% !important; border: none !important; }
    </style>
    <script>
      if (!parent.modx) {
        parent.modx = {
          tree: {}
        };
      }
    </script>
</head>
<body>
<form id="ta_form" name="ta_form" method="post">
    <input type="hidden" name="editor" id="editor" value="">
    <div id="actions">
        <div class="btn-group">
            <!--<div class="btn-group dropdown">
                <? /*= $_lang['which_editor_title'] */ ?>
                <select class="form-control form-control-sm" size="1"
                    name="which_editor"
                    onchange="document.ta_form.editor.value = this.value; document.ta_form.submit();">
                    <option value="none"><? /*= $_lang['none'] */ ?></option>
                    <option value="Codemirror"<? /*= ($which_editor == 'Codemirror' ? ' selected="selected"' : '') */ ?>>Codemirror</option>
                    <?php
            /*                    // invoke OnRichTextEditorRegister event
                                $evtOut = $modx->invokeEvent("OnRichTextEditorRegister");
                                if (is_array($evtOut)) {
                                    for ($i = 0; $i < count($evtOut); $i++) {
                                        $editor = $evtOut[$i];
                                        echo '<option value="' . $editor . '"' . ($which_editor == $editor ? ' selected="selected"' : '') . '>' . $editor . "</option>";
                                    }
                                }
                                */ ?>
                </select>
            </div>-->
            <button type="submit" class="btn btn-success btn-sm"><?= $_lang['save'] ?></button>
        </div>
    </div>
    <table id="table-layout">
        <tbody>
        <tr>
            <td>
                <textarea name="ta" id="ta" cols="30" rows="10"></textarea>
            </td>
        </tr>
        </tbody>
    </table>
</form>
<?php
// invoke OnRichTextEditorInit event
$evtOut = $modx->invokeEvent('OnRichTextEditorInit', $which_editor_config);
if (is_array($evtOut)) {
    echo implode('', $evtOut);
}
?>
</body>
</html>
