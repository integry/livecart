<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../../Initialize.php';

ClassLoader::import("application.controller.backend.EavFieldController");

/**
 *
 * @package test.controller.backend
 * @author Integry Systems
 */
class EavFieldControllerTest extends UnitTest implements ControllerTestCase
{
	private $controller;

	public function getUsedSchemas()
	{
		return array(
			'EavField',
		);
	}

	public function setUp()
	{
		parent::setUp();
		$this->controller = new EavFieldController(self::getApplication());
		ActiveRecordModel::executeUpdate('DELETE FROM EavField');
		ActiveRecordModel::executeUpdate('DELETE FROM EavFieldGroup');
	}

	public function testIndex()
	{
		$class = 'Category';
		$this->request->set('id', EavField::getClassID($class));

		$response = $this->controller->index();
		$this->assertIsA($response, 'ActionResponse');

		// field list should be empty
		$this->assertEqual(0, count($response->get('specFieldsWithGroups')));

		$field = EavField::getNewInstance($class);
		$field->save();

		// one new field expected
		$response = $this->controller->index();
		$this->assertEqual(1, count($response->get('specFieldsWithGroups')));

		// the field should not show up in different category
		$this->request->set('id', EavField::getClassID('User'));
		$response = $this->controller->index();
		$this->assertEqual(0, count($response->get('specFieldsWithGroups')));

		// test creating a group
		$group = EavFieldGroup::getNewInstance($class);
		$group->save();
		$this->request->set('id', EavField::getClassID($class));
		$response = $this->controller->index();
	}
}

?>