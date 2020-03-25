<?php
spl_autoload_register(function ($class) {
    if (stripos($class, '\\Elements\\') !== false) {
        $class .= '\\' . basename($class);
    }
    $file = dirname(__DIR__) . DIRECTORY_SEPARATOR . preg_replace('{\\\\|_(?!.*\\\\)}', DIRECTORY_SEPARATOR, ltrim($class, '\\')) . '.php';
    if (is_file($file) && is_readable($file)) {
        require $file;
    }
});
