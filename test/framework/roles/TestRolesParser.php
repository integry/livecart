<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';
ClassLoader::import('framework.roles.RolesParser');

class TestRolesParser extends UnitTestCase 
{
    public function __construct()
    {
        parent::__construct('Roles parser tests');
    }
    
    private function clearDirectory($dirPath)
    {
        if (is_dir($dirPath)) {
		    if ($dir = opendir($dirPath)) {
		        while (($file = readdir($dir)) !== false) {
		            if(in_array($file, array('.', '..'))) continue;
		            
		            unlink($dirPath . DIRECTORY_SEPARATOR . $file);
		        }
		        
		        closedir($dir);
		    }
		}
    }
	
	public function setUp()
	{
        $this->clearDirectory(ClassLoader::getRealPath('test.framework.roles.cache'));
	}
    
    public function testParseControllerClass()
    {
        $rolesParser = new RolesParser(
            ClassLoader::getRealPath("test.framework.roles.controllers.DumpController") . ".php", 
            ClassLoader::getRealPath("test.framework.roles.cache.DumpControllerRoles") . ".php"
        );
    }
    
    public function testGetRoleByAction()
    {
        $dumpControllerRoles = new RolesParser(
            ClassLoader::getRealPath("test.framework.roles.controllers.DumpController") . ".php", 
            ClassLoader::getRealPath("test.framework.roles.cache.DumpControllerRoles") . ".php"
        );
        
        $this->assertEqual($dumpControllerRoles->getRole('test'), 'test.subtest');
    }
}

?>