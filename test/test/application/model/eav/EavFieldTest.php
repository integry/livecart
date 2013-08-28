<?php

require_once dirname(__FILE__) . '/../../Initialize.php';


/**
 *
 * @package test.model.eav
 * @author Integry Systems
 */
class EavFieldTest extends LiveCartTest
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
	}

	/**
	 *	@expectedException ApplicationException
	 */
	/*
	public function testCreatingFieldForNonEavClass()
	{
		EavField::getNewInstance('DUMMYCLASS');
	}
	*/

	public function testCreatingField()
	{
		$field = EavField::getNewInstance('Manufacturer');
		$field->save();

		$otherField = EavField::getNewInstance('User');
		$otherField->save();

		$fields = EavField::getFieldsByClass('Manufacturer');
		$this->assertEqual($fields->size(), 1);
		$this->assertEqual($fields->get(0), $field);
	}
}

?>