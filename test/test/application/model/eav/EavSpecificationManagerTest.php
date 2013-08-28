<?php

require_once dirname(__FILE__) . '/../../Initialize.php';


/**
 *
 * @package test.model.eav
 * @author Integry Systems
 */
class EavSpecificationManagerTest extends LiveCartTest
{
	public function getUsedSchemas()
	{
		return array(
			'EavField',
			'Manufacturer',
		);
	}

	public function setUp()
	{
		parent::setUp();
		ActiveRecordModel::executeUpdate('DELETE FROM EavField');
		ActiveRecordModel::executeUpdate('DELETE FROM EavObject');
		ActiveRecordModel::executeUpdate('DELETE FROM Manufacturer');
	}

	public function testClone()
	{
		$text = EavField::getNewInstance('User', EavField::DATATYPE_TEXT, EavField::TYPE_TEXT_SIMPLE);
		$text->save();

		$singleSel = EavField::getNewInstance('User', EavField::DATATYPE_NUMBERS, EavField::TYPE_NUMBERS_SELECTOR);
		$singleSel->handle->set('single.sel');
		$singleSel->setValueByLang('name', 'en', 'Select one value');
		$singleSel->save();

		$value1 = EavValue::getNewInstance($singleSel);
		$value1->setValueByLang('value', 'en', $firstValue = '20');
		$value1->save();

		$value2 = EavValue::getNewInstance($singleSel);
		$value2->setValueByLang('value', 'en', $secValue = '30');
		$value2->save();

		$user = User::getNewInstance('someuser@eavclonetest.com');
		$user->save();

		$spec = $user->getSpecification();
		$spec->setAttributeValueByLang($text, 'en', 'text');
		$spec->setAttributeValue($singleSel, $value1);
		$user->save();

		$cloned = clone $user;
		$cloned->email->set('cloneduser@test.com');
		$cloned->save();
		$this->assertNotSame($cloned->getSpecification(), $user->getSpecification());
		$this->assertEquals($cloned->getSpecification()->getAttribute($text)->getValueByLang('value', 'en'), 'text');

		ActiveRecordModel::clearPool();
		$reloaded = ActiveRecordModel::getInstanceByID('User', $cloned->getID(), true);
		$this->assertEquals($reloaded->getSpecification()->getAttribute($text)->getValueByLang('value', 'en'), 'text');
		$this->assertEquals($reloaded->getSpecification()->getAttribute($singleSel)->getValue()->get()->getID(), $value1->getID());
	}
}

?>