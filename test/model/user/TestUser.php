<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import("application.model.user.UserGroup");
ClassLoader::import("application.model.user.User");

class TestUser extends UnitTest
{
    /**
     * User group
     *
     * @var UserGroup
     */
    private $group = null;

    public function __construct()
    {
        parent::__construct('shiping service tests');
    }
    
    public function getUsedSchemas()
    {
        return array(
			'User',
			'UserGroup'
        );
    }
    
    public function setUp()
    {
        parent::setUp();
        
        $this->group = UserGroup::getNewInstance('test', 'test');
        $this->group->save();
    }
    
    public function testCreateNewTaxRate()
    {
        $dateCreated = new ARSerializableDateTime();
        $user = User::getNewInstance('_tester@tester.com', 'tester', $this->group);
        
        $user->firstName->set('Yuri');
        $user->lastName->set('Gagarin');
        $user->companyName->set('Integry Systams');
        $user->isEnabled->set(true);
        $user->isAdmin->set(true);
        
        $user->save();
        $user->markAsNotLoaded();
        $user->load();
        
        $this->assertEqual($user->firstName->get(), 'Yuri');
        $this->assertEqual($user->lastName->get(), 'Gagarin');
        $this->assertEqual($user->password->get(), md5('tester'));
        $this->assertEqual($user->companyName->get(), 'Integry Systams');
        $this->assertTrue($user->isEnabled->get());
        $this->assertTrue($user->isAdmin->get());
        $this->assertReference($user->userGroup->get(), $this->group);
        
        $this->assertIdentical($dateCreated->format('Y-m-d H:i:s'), $user->dateCreated->get()->format('Y-m-d H:i:s'));
    }
    
    public function testGetUsersByGroup()
    {
        $userWithGroup = User::getNewInstance('_tester@tester.com', 'tester', $this->group);
        $userWithGroup->save();
        
        $userWithoutGroup = User::getNewInstance('_tester1@tester.com', 'tester');
        $userWithoutGroup->save();
        
        $usersWithGroup = User::getRecordSetByGroup($this->group);
        $usersWithoutGroup = User::getRecordSetByGroup(null);
        
        $this->assertReference($usersWithGroup->get($usersWithGroup->getTotalRecordCount() - 1), $userWithGroup);
        $this->assertReference($usersWithoutGroup->get($usersWithoutGroup->getTotalRecordCount() - 1), $userWithoutGroup);
    }
}
?>