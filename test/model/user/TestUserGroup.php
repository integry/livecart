<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import("application.model.user.UserGroup");
ClassLoader::import("application.model.user.User");

class TestUserGroup extends UnitTest
{
    public function __construct()
    {
        parent::__construct('user group tests');
    }
    
    public function getUsedSchemas()
    {
        return array(
			'UserGroup',
        );
    }
    
    public function testCreateNewUserGroup()
    {
        $group = UserGroup::getNewInstance('testing', 'testing');
        $group->save();
        
        $group->reload();
        
        $this->assertEqual($group->name->get(), 'testing');
        $this->assertEqual($group->description->get(), 'testing');
    }

    public function testGetAllGroups()
    {
        $groupCount = UserGroup::getRecordSet(new ARSelectFilter())->getTotalRecordCount();
        
        $group = UserGroup::getNewInstance('testing', 'testing');
        $group->save();
        
        $groups = UserGroup::getRecordSet(new ARSelectFilter());
        $this->assertEqual($groups->getTotalRecordCount(), $groupCount + 1);
        $this->assertEqual($groups->get($groupCount), $group);
    }
    
    public function testGetGroupUsers()
    {
        $group = UserGroup::getNewInstance('testing', 'testing');
        $group->save();
        
        $user = User::getNewInstance('_tester@tester.com', 'tester', $group);
        $user->save();
        
        $groupUsers = $group->getUsersRecordSet();
        
        $this->assertReference($groupUsers->get($groupUsers->getTotalRecordCount() - 1), $user);
    }   

    public function testGetRolesRecordSet()
    {
        $role = Role::getNewInstance('__testrole__');
        $role->save();
        
        $userGroup = UserGroup::getNewInstance('Any random group name');
        $userGroup->save();
        
        $assoc = AccessControlAssociation::getNewInstance($userGroup, $role);
        $assoc->save();
        
        $rolesRecordSet = $userGroup->getRolesRecordSet();
        $this->assertEqual($rolesRecordSet->getTotalRecordCount(), 1);
        $this->assertReference($rolesRecordSet->get(0), $role);
    }
}
?>