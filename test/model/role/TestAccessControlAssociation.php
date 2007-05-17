<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import("application.model.role.Role");
ClassLoader::import("application.model.role.AccessControlAssociation");
ClassLoader::import("application.model.user.UserGroup");

class TestAccessControlAssociation extends UnitTest
{
    /**
     * @var Role
     */
    private $role;
    
    /**
     * @var UserGroup
     */
    private $userGroup;
    
    public function __construct()
    {
        parent::__construct('Test roles association with user groups');
    }
    
    public function getUsedSchemas()
    {
        return array(
            'Role',
            'UserGroup',
            'AccessControlAssociation'
        );
    }
    
    public function setUp()
    {
        parent::setUp();
        
        $this->role = Role::getNewInstance('__testrole__');
        $this->role->save;
        
        $this->userGroup = UserGroup::getNewInstance('Any random group name');
        $this->userGroup->save();
    }
    
    public function testCreateNewAssociation()
    {
        $assoc = AccessControlAssociation::getNewInstance($this->userGroup, $this->role);
        $assoc->save();
        
        $assoc->reload();
        
        $this->assertReference($this->userGroup, $assoc->userGroup->get());
        $this->assertReference($this->role, $assoc->role->get());
    }

    public function xtestGetRoleByName()
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