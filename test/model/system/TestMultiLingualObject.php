<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import('application.model.category.Category');

/**
 * MultiLingualObject test
 *
 * Multi-lingual field values are stored as serialized arrays, which may contain all sorts of characters,
 * which may garble the serialization, characters may not be escaped properly in queries, etc.
 *
 * @author Integry Systems
 * @package test.model.system
 */
class TestMultiLingualObject extends UnitTest
{	  
	function setUp()
	{
		ActiveRecordModel::beginTransaction();	  	  	
	}
	
	function tearDown()
	{
		ActiveRecordModel::rollback();	  	  	
	}

	function testSerializingValuesWithQuotes()
	{
		// two quotes
		$testValue = 'This is a value with "quotes" :)';
		
		$root = Category::getInstanceByID(1);
		$new = Category::getNewInstance($root);		
		$new->setValueByLang('name', 'en', $testValue);
		$new->save();
		
		ActiveRecordModel::removeFromPool($new);
		$restored = Category::getInstanceByID($new->getID(), Category::LOAD_DATA);
		$array = $restored->toArray();
	
		$this->assertEqual($testValue, $array['name']);
		
		// one quote
		$testValue = 'NX9420 C2D T7400 17" WSXGA+ WVA BRIGHT VIEW 1024MB 120GB DVD+/-RW DL ATI MOBILITY RADEON X1600 256MB WLAN BT TPM XPPKeyb En';
		
		$restored->setValueByLang('name', 'en', $testValue);
		$restored->save();		
		ActiveRecordModel::removeFromPool($restored);
		
		$restored->totalProductCount->set(333);
		
		$another = Category::getInstanceByID($restored->getID(), Category::LOAD_DATA);
		$array = $another->toArray();
	
		$this->assertEqual($testValue, $array['name']);
	}

	function testSerializingAll_ASCII_Characters()
	{
		$testValue = '';

		for ($k = 0; $k <= 255; $k++)
		{
			$testValue .= chr($k);  
		}

		$testValue = 'x' . $testValue;

		$root = Category::getInstanceByID(1);
		$new = Category::getNewInstance($root);		
		$new->setValueByLang('name', 'en', $testValue);
		$new->save();
		
		ActiveRecordModel::removeFromPool($new);
		$restored = Category::getInstanceByID($new->getID(), Category::LOAD_DATA);
		$array = $restored->toArray();

		$this->assertEqual($testValue, $array['name']);
	}
}

?>