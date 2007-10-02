<?php

/**
 *
 * @package test
 * @author Integry Systems 
 */

error_reporting(E_ALL);
	
if (!defined('TEST_INITIALIZED'))
{
	// load classes and mount paths
	$cd = getcwd();
	
	chdir(dirname(__FILE__));
	chdir('..');
	
	include_once('application/Initialize.php');

	$arPath = realpath(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . 'activerecord' . DIRECTORY_SEPARATOR . 'ActiveRecord.php');
    include_once($arPath);
    ActiveRecord::setDSN('mysql://root@server/livecart_pre');
	
	// set unittest and simpletest library root directory
	$libDir = dirname(__FILE__) . '/_library/';
	ClassLoader::mountPath('simpletest', realpath($libDir . 'simpletest/'));
	ClassLoader::mountPath('unittest', realpath($libDir . 'unittest') . '/');
	ClassLoader::mountPath('testdir', dirname(__FILE__).'/');
	ClassLoader::import("simpletest.*");
	ClassLoader::import("unittest.*");
	ClassLoader::import("testdir.*");
	
	ClassLoader::load('unit_tester');
	ClassLoader::load('mock_objects');
	ClassLoader::load('reporter');
	
	ClassLoader::import('unittest.UnitTest');
	
	chdir($cd);
	
	define('TEST_INITIALIZED', true);
	
	ClassLoader::import('application.LiveCart');
	new LiveCart;
}

ClassLoader::import('application.system.*');
ClassLoader::import('library.locale.Locale');
ClassLoader::import('test.mock.Swift_Connection_Fake');

//Email::$connection = new Swift_Connection_Fake();

require_once('UTStandalone.php');

?>