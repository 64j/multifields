<?php
/**
 * plugin multifields
 * @author 64j
 */

$e = &$modx->event;
switch ($e->name) {
    case 'OnDocFormPrerender':
        $theme = !empty($multifields_theme) ? $multifields_theme : 'default';
        $e->addOutput('<link rel="stylesheet" href="' . MODX_BASE_URL . 'assets/plugins/multifields/theme/' . $theme . '/css/MultiFields.css?v=1.3.0">');
        $e->addOutput('<script defer="true" src="' . MODX_BASE_URL . 'assets/plugins/multifields/theme/' . $theme . '/js/Sortable.min.js?v=1.9.0"></script>');
        $e->addOutput('<script defer="true" src="' . MODX_BASE_URL . 'assets/plugins/multifields/theme/' . $theme . '/js/MultiFields.js?v=1.3.0"></script>');
        $e->addOutput('<script>MultiFields_urlBrowseServer = \'' . MODX_MANAGER_URL . 'media/browser/' . $modx->config['which_browser'] . '/browser.php\';</script>');
        break;

    case 'OnDocFormSave':
        require_once MODX_BASE_PATH . 'assets/plugins/multifields/core/MultiFields.php';
        \MF2\MultiFields::getInstance($id)->saveData();
        break;

    case 'OnManagerPreFrameLoader':
        if (!empty($_REQUEST['mf-action'])) {
            require_once MODX_BASE_PATH . 'assets/plugins/multifields/core/MultiFields.php';
            switch ($_REQUEST['mf-action']) {
                case 'template':
                    \MF2\MultiFields::getInstance($id)->getTemplate();
                    break;

                case 'richtext':
                    \MF2\MultiFields::getInstance($id)->getRichText();
                    break;
            }
        }
        break;
}
