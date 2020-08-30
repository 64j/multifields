<?php
spl_autoload_register(function ($class) {
    $path = str_replace('\\', '/', $class);
    $file = dirname(__DIR__) . '/' . strtolower(dirname($path)) . '/' . basename($path) . '.php';
    if (is_file($file) && is_readable($file)) {
        require $file;
    }
});

function mfc($params = [])
{
    return \Multifields\Base\Core::getInstance($params);
}

function mff($params = [])
{
    return \Multifields\Base\Front::getInstance($params);
}
