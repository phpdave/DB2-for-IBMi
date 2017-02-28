<?php
spl_autoload_register('myAutoloader');
function myAutoloader($className)
{
    $path = __DIR__."/";
    include $path.$className.'.php';
}
