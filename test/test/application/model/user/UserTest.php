<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';


/**
 * @author Integry Systems
 * @package test.model.user
 */
class UserTest extends LiveCartTest
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

	public function testCreateNewUser()
	{
		$dateCreated = new ARSerializableDateTime();
		$user = User::getNewInstance('_tester@tester.com', 'tester', $this->group);

		$user->firstName->set('Yuri');
		$user->lastName->set('Gagarin');
		$user->companyName->set('Integry Systams');
		$user->isEnabled->set(true);

		$user->save();

		$user->reload();

		$this->assertEqual($user->firstName, 'Yuri');
		$this->assertEqual($user->lastName, 'Gagarin');
		$this->assertEqual(array_shift(explode(':', $user->password)), md5('tester' . array_pop(explode(':', $user->password))));
		$this->assertEqual($user->companyName, 'Integry Systams');
		$this->assertTrue((bool)$user->isEnabled);
		$this->assertSame($user->userGroup, $this->group);

		$this->assertSame($dateCreated->format('Y-m-d H:i:s'), $user->dateCreated->format('Y-m-d H:i:s'));
	}

	public function ___testGetUsersByGroup()
	{
		$userWithGroup = User::getNewInstance('_tester@tester.com', 'tester', $this->group);
		$userWithGroup->save();

		$userWithoutGroup = User::getNewInstance('_tester1@tester.com', 'tester');
		$userWithoutGroup->save();

		$usersWithGroup = User::getRecordSetByGroup($this->group);
		$usersWithoutGroup = User::getRecordSetByGroup(null);

		$this->assertSame($usersWithGroup->get($usersWithGroup->getTotalRecordCount() - 1), $userWithGroup);
		$this->assertSame($usersWithoutGroup->get($usersWithoutGroup->getTotalRecordCount() - 1), $userWithoutGroup);
	}

	public function testPreferences()
	{
		$user = User::getNewInstance('_tester@tester.com', 'tester', $this->group);
		$user->setPreference('test', 'value');
		$user->save();
		$this->assertEqual($user->getPreference('test'), 'value');

		// update preferences
		$user->setPreference('another', 'check');
		$user->save();

		ActiveRecordModel::clearPool();
		$reloaded = User::getInstanceByID($user->getID(), true);
		$this->assertNotSame($user, $reloaded);
		$this->assertEqual($reloaded->getPreference('test'), 'value');
		$this->assertEqual($reloaded->getPreference('another'), 'check');
	}
}

?>