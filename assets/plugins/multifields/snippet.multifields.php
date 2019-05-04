<?php
/**
 * Snippet multifields
 * @author 64j
 */

if (!defined('MODX_BASE_PATH')) {
    die('HACK???');
}

require_once MODX_BASE_PATH . 'assets/plugins/multifields/core/MultiFieldsFront.php';
return MultiFieldsFront::getInstance($params);
