<?php
/**
 * custom tv multifields
 * @author 64j
 */

if (!defined('MODX_BASE_PATH')) {
    die('HACK???');
}

echo MultiFields\Base\Core::getInstance()
    ->render($content['id'], $row);
