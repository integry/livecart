<?php
require_once "SomeClassWithPHPDocs.php";

if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';
ClassLoader::import("framework.roles.RolesClassParser");

class TestRolesParser extends UnitTestCase 
{
    public function __construct()
    {
        parent::__construct('Roles parser tests');
    }
    
    public function testParseControllerClass()
    {
        $rolesParser = new RolesClassParser(new ReflectionClass('SomeClassWithPHPDocs'));
        
        $this->assertEqual($rolesParser->getRoles(), array(
			    'SomeClassWithPHPDocs' => 'test',
			    'SomeClassWithPHPDocs::test' => 'test.subtest',
			    'SomeClassWithPHPDocs::noRole' => 'test'
			)
		);
    }
}

?>