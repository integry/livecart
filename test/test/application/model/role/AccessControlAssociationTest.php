<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import("application.model.role.Role");
ClassLoader::import("application.model.role.AccessControlAssociation");
ClassLoader::import("application.model.user.UserGroup");

/**
 *  @author Integry Systems
 *  @package test.model.role
 */
class AccessControlAssociationTest extends LiveCartTest
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
		$this->role->save();

		$this->userGroup = UserGroup::getNewInstance('Any random group name');
		$this->userGroup->save();
	}

	public function testCreateNewAssociation()
	{
		$assoc = AccessControlAssociation::getNewInstance($this->userGroup, $this->role);
		$assoc->save();

		$assoc->reload();

		$this->assertSame($this->userGroup, $assoc->userGroup->get());
		$this->assertSame($this->role, $assoc->role->get());
	}

	public function testGetAssociationsByGroup()
	{
		$assoc = AccessControlAssociation::getNewInstance($this->userGroup, $this->role);
		$assoc->save();

		$associations = AccessControlAssociation::getRecordSetByUserGroup($this->userGroup, new ARSelectFilter());

		$this->assertEqual($associations->getTotalRecordCount(), 1);
		$this->assertSame($associations->get(0)->role->get(), $this->role);
	}

	public function testGetAssociationsByRole()
	{
		$assoc = AccessControlAssociation::getNewInstance($this->userGroup, $this->role);
		$assoc->save();

		$associations = AccessControlAssociation::getRecordSetByRole($this->role, new ARSelectFilter());

		$this->assertEqual($associations->getTotalRecordCount(), 1);
		$this->assertSame($associations->get(0)->userGroup->get(), $this->userGroup);
	}
}
?>