<?php

require_once dirname(__FILE__) . '/../../Initialize.php';


/**
 *
 * @package test.model.eav
 * @author Integry Systems
 */
class EavObjectTest extends LiveCartTest
{
	public function getUsedSchemas()
	{
		return array(
			'EavField'
		);
	}

	public function setUp()
	{
		parent::setUp();
		ActiveRecordModel::executeUpdate('DELETE FROM EavField');
		ActiveRecordModel::executeUpdate('DELETE FROM EavObject');
		ActiveRecordModel::executeUpdate('DELETE FROM Manufacturer');
	}

	/**
	 *	@expectedException PHPUnit_Framework_Error
	 */
	public function testCreatingObjectForNonEavClass()
	{
		EavObject::getInstance(Tax::getInstanceByID(1));
	}

	public function testCreatingObject()
	{
		$field = EavField::getNewInstance('Manufacturer');
		$field->save();

		$manufacturer = Manufacturer::getNewInstance('Dummy Manufacturer');
		$manufacturer->save();

		$object = EavObject::getInstance($manufacturer);
		$object->save();

		$this->assertEqual($object->manufacturer, $manufacturer);
		$this->assertEqual($object, EavObject::getInstance($manufacturer));
	}

	public function testSettingValues()
	{
		$field = EavField::getNewInstance('User', EavField::DATATYPE_TEXT, EavField::TYPE_TEXT_SIMPLE);
		$field->save();

		$user = User::getNewInstance('someuser@eavtest.com');
		$user->save();

		$spec = $user->getSpecification();
		$this->assertIsA($spec, 'EavSpecificationManager');

		$testValue = 'something';
		$spec = $user->getSpecification();
		$this->assertSame($spec, $user->getSpecification());

		$spec->setAttributeValueByLang($field, 'en', $testValue);
		$attr = $spec->getAttribute($field);
		$this->assertEqual($testValue, $attr->getValueByLang('value', 'en'));

		$spec->save();
		$id = $user->getID();
		unset($user);
		ActiveRecordModel::clearPool();

		$user = User::getInstanceByID($id, ActiveRecordModel::LOAD_DATA);
		$spec = $user->getSpecification();
		$attr = $spec->getAttribute($field);
		$this->assertEqual($testValue, $attr->getValueByLang('value', 'en'));
	}

	public function testEavQueue()
	{
		// set up Manufacturer records
		$field = EavField::getNewInstance('Manufacturer', EavField::DATATYPE_TEXT, EavField::TYPE_TEXT_SIMPLE);
		$field->save();

		$data = array('first', 'second', 'third');
		foreach ($data as $value)
		{
			$manufacturer = Manufacturer::getNewInstance($value);
			$manufacturer->getSpecification()->setAttributeValueByLang($field, 'en', $value . ' test');
			$manufacturer->save();
		}

		ActiveRecordModel::clearPool();

		// fetch them from database
		$manufacturers = ActiveRecordModel::getRecordSetArray('Manufacturer', new ARSelectFilter());
		foreach ($manufacturers as &$entry)
		{
			ActiveRecordModel::addToEavQueue('Manufacturer', $entry);
		}

		// duplicate
		$manufacturers = array_merge($manufacturers, $manufacturers);

		// load EAV data
		ActiveRecordModel::loadEav();

		foreach ($manufacturers as $man)
		{
			$this->assertEqual($man['name'] . ' test', $man['attributes'][$field->getID()]['value_en']);
		}
	}
}

?>