<?php
spl_autoload_register(function ($class) {
    if (stripos($class, '\\Elements\\') !== false) {
        $class .= '\\' . basename(str_replace('\\', '/', $class));
    }
    $file = dirname(__DIR__) . '/' . strtolower(str_replace('\\', '/', $class));
    $file = dirname($file) . '/' . ucfirst(basename($file)) . '.php';
    if (is_file($file) && is_readable($file)) {
        require $file;
    }
});
