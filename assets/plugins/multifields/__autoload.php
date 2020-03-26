<?php
spl_autoload_register(function ($class) {
    $className = basename(str_replace('\\', '/', $class));
    $class = dirname(strtolower(str_replace('\\', '/', $class)));
    if (basename($class) == 'elements') {
        $class .=  '/' . strtolower($className);
    }
    $class .=  '/' . $className;
    $file = dirname(__DIR__) . '/' . $class . '.php';
    if (is_file($file) && is_readable($file)) {
        require $file;
    }
});
