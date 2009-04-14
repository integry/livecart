<?php

function rglob($pattern='*', $flags = 0, $path='')
{
    $paths=glob($path.'*', GLOB_MARK|GLOB_ONLYDIR|GLOB_NOSORT);
    $files=glob($path.$pattern, $flags);
    foreach ($paths as $path) { $files=array_merge($files,rglob($pattern, $flags, $path)); }
    return $files;
}

error_reporting(E_ALL);
ini_set('display_errors', 'On');
include '../application/Initialize.php';
ClassLoader::import('application.LiveCart');
$application = new LiveCart();

chdir(ClassLoader::getRealPath('storage.language'));
foreach(rglob('*.php') as $file)
{
	touch($file, 0);
}

?>