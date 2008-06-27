<?php

require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import('application.model.eav.EavObject');
ClassLoader::import('application.model.eav.EavField');
ClassLoader::import('application.model.tax.Tax');
ClassLoader::import('application.model.product.Manufacturer');
ClassLoader::import('application.model.user.User');

/**
 *
 * @package test.model.eav
 * @author Integry Systems
 */
class EavObjectTest extends UnitTest
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

		$this->assertEqual($object->manufacturer->get(), $manufacturer);
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

		$user = ActiveRecordModel::getInstanceByID('User', $id, ActiveRecordModel::LOAD_DATA);
		$spec = $user->getSpecification();
		$attr = $spec->getAttribute($field);
		$this->assertEqual($testValue, $attr->getValueByLang('value', 'en'));
	}


}

?>