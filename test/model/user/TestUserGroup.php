<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import("application.model.user.UserGroup");
ClassLoader::import("application.model.user.User");
ClassLoader::import("application.model.role.Role");
ClassLoader::import("application.model.role.AccessControlAssociation");


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
            'Role',
            'AccessControlAssociation'
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

    public function testApplyRoles()
    {
        $group = UserGroup::getNewInstance('testing', 'testing');
        $group->save();
        
        $role = Role::getNewInstance('testingweoufhyisuy387wh');
        $role->save();
        
        $group->applyRole($role);
        $group->save();
        
        $this->assertEqual($group->getRolesRecordSet()->getTotalRecordCount(), 1);
    }
    
    public function testCancelRoles()
    {
        $group = UserGroup::getNewInstance('testing', 'testing');
        $group->save();
        
        $role = Role::getNewInstance('testingweoufhyisuy387wh');
        $role->save();
        
        $group->applyRole($role);
        $group->save();
        $this->assertEqual($group->getRolesRecordSet()->getTotalRecordCount(), 1);
        
        $group->cancelRole($role);
        $group->save();
        $this->assertEqual($group->getRolesRecordSet()->getTotalRecordCount(), 0);
    }

    public function testHasMiscAccess()
    {
        $group = UserGroup::getNewInstance('testing1337', 'testing1337');
        $group->save();
        
        $role = Role::getNewInstance('testing1337');
        $role->save();
        
        $group->applyRole($role);
        $group->save();
        
        $this->assertTrue($group->hasAccess('testing1337'));
        $this->assertFalse($group->hasAccess('testing1337.update'));
    }

    public function testHasConcreteAccess()
    {
        $group = UserGroup::getNewInstance('testing1337', 'testing1337');
        $group->save();
        
        $role = Role::getNewInstance('testing1337.update');
        $role->save();
        
        $group->applyRole($role);
        $group->save();
        
        $this->assertFalse($group->hasAccess('testing1337'));
        $this->assertTrue($group->hasAccess('testing1337.update'));
        $this->assertFalse($group->hasAccess('testing1337.create'));
    }
}
?>