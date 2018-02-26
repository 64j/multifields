<?php
define('MODX_API_MODE', true);
define('IN_MANAGER_MODE', true);

include_once("../../../index.php");

$modx->db->connect();

if (empty ($modx->config)) {
    $modx->getSettings();
}

if (!isset($_SESSION['mgrValidated']) || !isset($_SERVER['HTTP_X_REQUESTED_WITH']) || (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') || ($_SERVER['REQUEST_METHOD'] != 'POST')) {
    $modx->sendErrorPage();
}

//$modx->sid = session_id();
//$modx->loadExtension("ManagerAPI");

$_lang = array();
include_once MODX_MANAGER_PATH . '/includes/lang/english.inc.php';
if ($modx->config['manager_language'] != 'english') {
    include_once MODX_MANAGER_PATH . '/includes/lang/' . $modx->config['manager_language'] . '.inc.php';
}
include_once MODX_MANAGER_PATH . '/media/style/' . $modx->config['manager_theme'] . '/style.php';

if (!empty($_REQUEST['template_name']) && is_scalar($_REQUEST['template_name']) && !empty($_REQUEST['field_id'])) {
    $config = array();
    $config['field_id'] = (int) ltrim($_REQUEST['field_id'], 'tv');
    $config['template_name'] = (string) $_REQUEST['template_name'];
    $config['render'] = false;
    if($templates = $modx->db->getValue('SELECT elements FROM ' . $modx->getFullTableName('site_tmplvars') . ' WHERE id=' . $config['field_id'])) {
        $templates = json_decode($templates, true);
        $config['schema'] = isset($templates['schema']) ? $templates['schema'] : '';
        $config['templates'] = !empty($templates['templates']) ? $templates['templates'] : array();
        require_once 'class.multifields.php';
        $controller = new customTvMultifields($config, $modx);

        echo $controller->template();
    }
}
