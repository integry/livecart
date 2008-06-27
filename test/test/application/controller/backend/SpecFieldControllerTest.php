<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../../Initialize.php';

ClassLoader::import("application.controller.backend.SpecFieldController");

/**
 *
 * @package test.model.category
 * @author Integry Systems
 */
class SpecFieldControllerTest extends UnitTest
{
	/**
	 * Root category
	 * @var Category
	 */
	private $controller;

	public function getUsedSchemas()
	{
		return array(
			'SpecField',
			'SpecFieldGroup',
			'SpecFieldGroup',
			'User',
			'UserGroup',
		);
	}

	public function setUp()
	{
		parent::setUp();

		ClassLoader::import('application.model.user.SessionUser');
		ClassLoader::import('application.model.user.UserGroup');

		// set up user
		$group = UserGroup::getNewInstance('Unit tester');
		$group->save();
		$group->setAllRoles();
		$group->save();
		$user = User::getNewInstance('unittest@test.com', null, $group);
		$user->save();
		SessionUser::setUser($user);

		$this->request = self::getApplication()->getRequest();

		$this->controller = new SpecFieldController(self::getApplication());
	}

	public function testIndex()
	{
		$category = Category::getNewInstance(Category::getRootNode());
		$category->save();

		$language = ActiveRecordModel::getNewInstance('Language', 'ee');
		$language->save();

		$field = SpecField::getNewInstance($category, SpecField::DATATYPE_TEXT, SpecField::TYPE_TEXT_SIMPLE);
		$field->handle->set('randomhandle');
		$field->save();

		$this->request->set('id', $category->getID());

		$response = $this->controller->index();
		$this->assertIsA($response, 'ActionResponse');

		$this->assertEqual($category->getID(), $response->get('categoryID'));
		$this->assertTrue(is_array($response->get('configuration')));
		$this->assertEqual(count($response->get('specFieldsWithGroups')), 1);

		$defaultValues = $response->get('specFieldsList');
		$this->assertEqual($defaultValues['ID'], $category->getID() . '_new');

		$group = SpecFieldGroup::getNewInstance($category);
		$group->save();
		$field->specFieldGroup->set($group);
		$field->save();

		$fields = array();
		foreach (range(1, 2) as $k)
		{
			$fields[$k] = SpecField::getNewInstance($category, SpecField::DATATYPE_TEXT, SpecField::TYPE_TEXT_SIMPLE);
			$fields[$k]->handle->set('randomhandle' . $k);
			$fields[$k]->save();
		}

		$fields[2]->specFieldGroup->set($group);
		$fields[2]->save();

		$response = $this->controller->index();
		$fieldArray = $response->get('specFieldsWithGroups');

		// first field should be without group
		$this->assertEqual($fieldArray[1]['ID'], $fields[1]->getID());

		// next one should be the first created field (with a group)
		$this->assertEqual($fieldArray[2]['ID'], $field->getID());
		$this->assertEqual($fieldArray[2]['SpecFieldGroup']['ID'], $group->getID());
	}

	public function testDelete()
	{
		$category = Category::getNewInstance(Category::getRootNode());
		$category->save();

		$field = SpecField::getNewInstance($category, SpecField::DATATYPE_TEXT, SpecField::TYPE_TEXT_SIMPLE);
		$field->handle->set('randomhandle');
		$field->save();

		$this->request->set('id', $field->getID());
		$response = $this->controller->delete();
		$this->assertIsA($response, 'JSONResponse');

		$value = $response->getValue();
		$this->assertEqual($value['status'], 'success');

		// already deleted
		$response = $this->controller->delete();
		$value = $response->getValue();
		$this->assertEqual($value['status'], 'failure');
	}
}

?>