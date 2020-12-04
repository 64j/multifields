<?php
/**
 * custom tv multifields
 * @author 64j
 */

if (!defined('MODX_BASE_PATH')) {
    die('HACK???');
}

require_once dirname(dirname(__DIR__)) . '/plugins/multifields/core/MultiFields.php';

echo \MF2\MultiFields::getInstance(isset($content['id']) ? $content['id'] : 0, $row)->renderData();
