<?php

/**
 *  Part of the LiveCart front controller that is only used when URL rewriting is not available
 *
 *  @author Integry Systems
 *  @package application 
 */

// Indicates that URL rewriting is disabled
$_GET['noRewrite'] = true;

// Apache: index.php/route
if (preg_match('/^Apache/', $_SERVER['SERVER_SOFTWARE']))
{
	$_GET['route'] = isset($_SERVER['PATH_INFO']) ? substr($_SERVER['PATH_INFO'], 1) : '';
	$_SERVER['virtualBaseDir'] = $_SERVER['SCRIPT_NAME'] . '/';
}

// IIS, etc.: index.php?route=
else
{
	$_SERVER['virtualBaseDir'] = $_SERVER['SCRIPT_NAME'] . '?route=';
}

$_SERVER['baseDir'] = dirname($_SERVER['SCRIPT_NAME']) . '/public/';

include 'public/index.php';

?>
