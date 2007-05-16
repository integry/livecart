<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';
ClassLoader::import("framework.roles.RolesDirectoryParser");

class TestRolesDirectoryParser extends UnitTestCase 
{
    public function __construct()
    {
        parent::__construct('Roles directory parser tests');
    }
    
    public function testParseControllersDirectory()
    {
        $directoryParser = new RolesDirectoryParser(
	        ClassLoader::getRealPath('test.framework.roles.controllers'),  
	        ClassLoader::getRealPath('test.framework.roles.cache'),  
	        array(
				'/^\w+Controller\.php$/', // Match those files wich have "Controller.php" appended to them in the end
	            '/^\w*(?<!Base)Controller\.php$/' // Do not match "BaseController.php"
	        )
        );
    }
}
?>