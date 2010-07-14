<?php

/**
 *  Initialize framework, load main classes
 *  @package application
 *  @author Integry Systems
 */

if (isset($_REQUEST['stat']))
{
	function __autoload($className)
	{
		static $stat;
		if (!$stat)
		{
			$stat = $GLOBALS['stat'];
		}

		$start = microtime(true);
		ClassLoader::load($className);
		$elapsed = microtime(true) - $start;

		if (empty($GLOBALS['ClassLoaderTime']))
		{
			$GLOBALS['ClassLoaderTime'] = $GLOBALS['ClassLoaderCount'] = 0;
		}

		$GLOBALS['ClassLoaderTime'] += $elapsed;
		$GLOBALS['ClassLoaderCount']++;
	}
}

require_once(dirname(dirname(__file__)) . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'ClassLoader.php');

ClassLoader::mountPath('.', dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR);

if (isset($_REQUEST['stat']))
{
	ClassLoader::import('library.stat.Stat');
	$stat = new Stat(true);
	$GLOBALS['stat'] = $stat;
}

ClassLoader::import('framework.request.Request');
ClassLoader::import('framework.request.Router');
ClassLoader::import('framework.controller.*');
ClassLoader::import('framework.response.*');
ClassLoader::import('application.controller.*');
ClassLoader::import('application.model.*');
ClassLoader::import('application.model.system.*');
ClassLoader::import('application.LiveCart');

?>