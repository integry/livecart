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

    chdir(dirname(dirname(__FILE__)));
    chdir('..');

    include_once('application/Initialize.php');

    $arPath = realpath(getcwd() . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . 'activerecord' . DIRECTORY_SEPARATOR . 'ActiveRecord.php');
    include_once($arPath);
    ActiveRecord::setDSN('mysql://testerer@localhost/livecart');

    // set unittest and simpletest library root directory
    $libDir = dirname(dirname(__FILE__)) . '/_library/';
    ClassLoader::mountPath('unittest', realpath($libDir . 'unittest') . '/');
    ClassLoader::mountPath('testdir', dirname(__FILE__).'/');
    ClassLoader::import("unittest.*");
    ClassLoader::import("testdir.*");

    //ClassLoader::load('unit_tester');
    //ClassLoader::load('mock_objects');
    //ClassLoader::load('reporter');

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
