<?php
/**
 * custom tv multifields
 * @author 64j
 */

if (!defined('MODX_BASE_PATH')) {
    die('HACK???');
}

$id = isset($content['id']) ? $content['id'] : 0;
$row = isset($row) ? $row : [];

echo MultiFields\Base\Core::getInstance()
    ->render($id, $row);
