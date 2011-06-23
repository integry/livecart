+<?php

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

	$_SERVER["SCRIPT_FILENAME"] = dirname(__FILE__);

	chdir(dirname(dirname(__FILE__)));
	chdir('..');

	include_once('application/Initialize.php');

	$arPath = realpath(getcwd() . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . 'activerecord' . DIRECTORY_SEPARATOR . 'ActiveRecord.php');
	include_once($arPath);
	ActiveRecord::setDSN('mysql://root@server/livecart');

	// set unittest and simpletest library root directory
	$libDir = dirname(dirname(__FILE__)) . '/_library/';
	ClassLoader::mountPath('phpunit', realpath($libDir . 'phpunit/'));
	ClassLoader::mountPath('unittest', realpath($libDir . 'unittest') . '/');
	ClassLoader::mountPath('testdir', dirname(__FILE__).'/');
	ClassLoader::import("phpunit.*");
	ClassLoader::import("unittest.*");
	ClassLoader::import("testdir.*");

	ClassLoader::import('unittest.UnitTest');
	chdir($cd);

	define('TEST_INITIALIZED', true);

	ClassLoader::import('application.LiveCart');
	UnitTest::setApplication(new LiveCart);
	UnitTest::getApplication()->getConfig()->setAutoSave(false);
	UnitTest::getApplication()->getConfig()->set('EMAIL_METHOD', 'FAKE');
}

ClassLoader::import('application.system.*');
ClassLoader::import('library.locale.Locale');
ClassLoader::import('test.mock.Swift_Connection_Fake');

require_once('LiveCartTest.php');

?>
