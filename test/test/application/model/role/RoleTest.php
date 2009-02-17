<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import("application.model.role.Role");

/**
 *  @author Integry Systems
 *  @package test.model.role
 */
class RoleTest extends LiveCartTest
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

		$role->reload();

		$this->assertEqual($role->name->get(), 'testing');
	}

	public function testGetRoleByName()
	{
		$newRole = Role::getNewInstance('testing');
		$newRole->save();

		$role = Role::getInstanceByName('testing');
		$this->assertSame($role, $newRole);

		$role = Role::getInstanceByName('unknown');
		$this->assertNull($role, null);
	}
}
?>