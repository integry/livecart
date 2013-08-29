<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';

/**
 * @author Integry Systems
 * @package test.framework.roles
 */ 
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
					
					@unlink($dirPath . DIRECTORY_SEPARATOR . $file);
				}
				
				closedir($dir);
			}
		}
	}
	
	public function setUp()
	{
		$this->clearDirectory($this->config->getPath('test.framework.roles.cache'));
	}

	public function testGetRoleByAction()
	{
		$dumpControllerRoles = new RolesParser(
			$this->config->getPath("test.framework.roles.controllers.DumpController") . ".php",
			$this->config->getPath("test.framework.roles.cache.DumpControllerRoles") . ".php"
		);
		
		$this->assertEqual($dumpControllerRoles->getRole('test'), 'test.subtest');
	}
	
	public function testGerRoleNames()
	{
		$dumpControllerRoles = new RolesParser(
			$this->config->getPath("test.framework.roles.controllers.DumpController") . ".php",
			$this->config->getPath("test.framework.roles.cache.DumpControllerRoles") . ".php"
		);
		$roleNames = $dumpControllerRoles->getRolesNames();
		$this->assertEqual(count($roleNames), 4);
		$this->assertTrue(in_array('test', $roleNames));
		$this->assertTrue(in_array('test.subtest', $roleNames));
		$this->assertTrue(in_array('another', $roleNames));
		$this->assertTrue(in_array('another.another', $roleNames));
	}
}

?>