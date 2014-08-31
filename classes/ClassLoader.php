<?php
function Autoloader($className) {
    if(!class_exists($className)) {
        $className = explode('\\', $className);
        $class = array_pop($className);
        $namespace = implode(DIRECTORY_SEPARATOR, $className);
        $file = $namespace . DIRECTORY_SEPARATOR . str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';
        if(!file_exists("/www/classes/".$file)) {
            throw new \Exception("Class Loader Exception: Class File Not Found - $file");
        }
        require_once "/www/classes/".$file;
   }
}

spl_autoload_register('Autoloader');
?>