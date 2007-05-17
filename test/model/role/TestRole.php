<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import("application.model.role.Role");

class TestRole extends UnitTest
{
    public function __construct()
    {
        parent::__construct('Role tests');
    }
    
    public function getUsedSchemas()
    {
        return array(
            'Role'
        );
    }
    
    public function testCreateNewRole()
    {
        $role = Role::getNewInstance('testing');
        $role->save();
        
        $role->markAsNotLoaded();
        $role->load();
        
        $this->assertEqual($role->name->get(), 'testing');
    }

    public function testGetRoleByName()
    {
        $newRole = Role::getNewInstance('testing');
        $newRole->save();
        
        $role = Role::getInstanceByName('testing');
        $this->assertReference($role, $newRole);
        
        $role = Role::getInstanceByName('unknown');
        $this->assertNull($role, null);
    }
}
?>