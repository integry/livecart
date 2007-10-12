<?php

/**
 *  Initialize framework, load main classes
 *  @package application
 *  @author Integry Systems
 */ 

require_once(dirname(dirname(__file__)) . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'ClassLoader.php');

ClassLoader::mountPath('.', dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR);

ClassLoader::import('library.stat.Stat');
$stat = new Stat(true);

// check writability of temporary directories
$writeFail = array();
foreach (array('cache', 'storage', 'public.cache', 'public.upload') as $dir)
{
	$file = ClassLoader::getRealPath($dir) . '/.writeTest';
	if (!file_exists($file))
	{
		if (!@file_put_contents($file, 'OK'))
		{
			$writeFail[] = $file;
		}
	}
	
	// do not check the public directories if cache and storage are OK
	if (('storage' == $dir) && !$writeFail)
	{
		break;
	}
}

if ($writeFail)
{
	echo '<h1>Some directories do not seem to be writable</h1>
	
		  <p>You\'re probably trying to set up LiveCart now.</p>
			
		  <p>Before the installation may continue, please make sure that the following directories are writable (chmod to 755 or 777):</p><ul>';
	
	foreach ($writeFail as $file)
	{
		echo '<li>' . dirname($file) . '</li>';
	}
	
	echo '</ul> <p>Please reload this page when the directory write permissions are fixed. Please <a href="http://support.livecart.com">contact the LiveCart support team</a> if any assistance is required.</p>';
	
	exit;
}

ClassLoader::import('framework.request.Request');
ClassLoader::import('framework.request.Router');
ClassLoader::import('framework.controller.*');
ClassLoader::import('framework.response.*');
ClassLoader::import('application.controller.*');
ClassLoader::import('application.model.*');
ClassLoader::import('application.model.system.*');

?>