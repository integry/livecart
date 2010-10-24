<?php

$file = $_REQUEST['file'];

if (preg_match('/^[-a-zA-Z0-9]*\.css$/', $file))
{
	$file = 'cache/stylesheet/' . $file;
	header('Content-Type: text/css');
}
else if (preg_match('/^[-a-zA-Z0-9]*\.js$/', $file))
{
	$file = 'cache/javascript/' . $file;
	header('Content-Type: text/javascript');
}
else
{
	exit;
}

$realPath = $file;

if (!file_exists($file) && file_exists('appdir.php'))
{
	$realPath = (include('appdir.php')) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $file;
}

// check relative path to this file
if (!file_exists($realPath))
{
	$realPath = dirname(__file__) . DIRECTORY_SEPARATOR . $file;
}

// gzip.php is called from another directory via mod_rewrite
if (!file_exists($realPath) && !empty($_SERVER['REDIRECT_URL']))
{
	$root = $_SERVER['DOCUMENT_ROOT'] . $_SERVER['REDIRECT_URL'];
	$realPath = dirname($root) . DIRECTORY_SEPARATOR . $file;
}

if (isset($realPath) && file_exists($realPath))
{
	$file = $realPath;
}

if (!file_exists($file))
{
	exit;
}

if (!empty($_SERVER['HTTP_ACCEPT_ENCODING']) && (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false) && file_exists($file . '.gz') && !ini_get('zlib.output_compression'))
{
	$file = $file . '.gz';
	header('Content-Encoding: gzip');
}

header('Cache-Control: public; max-age=' . (3600 * 24 * 366));
header('Content-Length: ' . filesize($file));

echo file_get_contents($file);

?>