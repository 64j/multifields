<?php
/**
 * Snippet multifields
 * @author 64j
 */

if (!defined('MODX_BASE_PATH')) {
    die('HACK???');
}

echo \Multifields\Base\Front::getInstance()
    ->render($params);
