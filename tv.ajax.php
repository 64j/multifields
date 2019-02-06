<?php
/**
 * multifields ajax
 * @author 64j
 */

define('MODX_API_MODE', true);
define('IN_MANAGER_MODE', true);

include_once('../../../index.php');

$modx->db->connect();

if (empty ($modx->config)) {
    $modx->getSettings();
}

if (!isset($_SESSION['mgrValidated']) || !isset($_SERVER['HTTP_X_REQUESTED_WITH']) || (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') || ($_SERVER['REQUEST_METHOD'] != 'POST')) {
    $modx->sendErrorPage();
}

$_lang = array();
include_once MODX_MANAGER_PATH . '/includes/lang/english.inc.php';
if ($modx->config['manager_language'] != 'english') {
    include_once MODX_MANAGER_PATH . '/includes/lang/' . $modx->config['manager_language'] . '.inc.php';
}
include_once MODX_MANAGER_PATH . '/media/style/' . $modx->config['manager_theme'] . '/style.php';

if (isset($_REQUEST['template_name']) && is_scalar($_REQUEST['template_name']) && !empty($_REQUEST['field_name']) && is_scalar($_REQUEST['field_name']) && isset($_REQUEST['field_id'])) {
    $baseDir = str_replace(dirname(__DIR__) . DIRECTORY_SEPARATOR, '', __DIR__);
    $templates = array();
    $config = array();
    $config['id'] = $_REQUEST['field_id'];
    $config['name'] = $_REQUEST['field_name'];
    $config['template_name'] = $_REQUEST['template_name'];
    $config['render'] = false;

    if ($templates = $modx->db->getValue('SELECT elements FROM ' . $modx->getFullTableName('site_tmplvars') . ' WHERE id=' . $config['id'])) {
        $config['templates'] = json_decode($templates, true);
    } elseif (file_exists(MODX_BASE_PATH . 'assets/tvs/' . $baseDir . '/configs/' . $config['name'] . '.config.inc.php')) {
        $config['templates'] = include_once MODX_BASE_PATH . 'assets/tvs/' . $baseDir . '/configs/' . $config['name'] .
            '.config.inc.php';
    } else {
        return;
    }

    require_once(MODX_MANAGER_PATH . 'includes/tmplvars.inc.php');
    require_once(MODX_MANAGER_PATH . 'includes/tmplvars.commands.inc.php');
    require_once 'class.multifields.php';
    $controller = new multifields($config);
    echo $controller->getTpl();
}
