<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import("application.model.category.*");
ClassLoader::import("application.model.product.*");
ClassLoader::import("application.model.product.Product");

/**
 *	Test Product and Product Specification model for the following scenarios:
 *	
 *	  * Create a new product and assign specification attributes
 *	  * Load a product from a database, read and modify specification attributes
 *  
 */
class TestProduct extends UnitTest 
{
	private $product = null;
	private $productCategory = null;
	
	function __construct()
	{
		parent::__construct('Test Product class');
		
		ActiveRecordModel::beginTransaction();		
		
		// create a new category
		$this->productCategory = Category::getNewInstance(Category::getRootNode());
		$this->productCategory->setValueByLang("name", "en", "Demo category branch");
		$this->productCategory->save();
		
		// create a product without attributes
		$this->product = Product::getNewInstance($this->productCategory);
		$this->product->setValueByLang("name", "en", "Test product...");
		$this->product->setValueByLang("name", "lt", "Bandomasis produktas");
		$this->product->setFieldValue("isEnabled", true);
		$this->product->save();			
	}
	
	function testSimpleValues()
	{
		// create some simple value attributes
		$numField = SpecField::getNewInstance($this->productCategory, SpecField::DATATYPE_NUMBERS, SpecField::TYPE_NUMBERS_SIMPLE);
		$numField->handle->set('numeric.field');
		$numField->setValueByLang('name', 'en', 'This would be a numeric field');
		$numField->setValueByLang('name', 'lt', 'Cia galima rasyt tik skaicius');
		$numField->save();
		
		$textField = SpecField::getNewInstance($this->productCategory, SpecField::DATATYPE_TEXT, SpecField::TYPE_TEXT_SIMPLE);
		$textField->handle->set('text.field');
		$textField->setValueByLang('name', 'en', 'Here goes some free text');
		$textField->setValueByLang('name', 'lt', 'Cia bet ka galima irasyt');
		$textField->save();
		
		$this->product->setAttributeValue($numField, $numValue = 666);
		$this->product->setAttributeValue($textField, array('en' => $textValue = 'We`re testing here'));
		
		// assign attribute values for product
		$this->product->save();
		
		$array = $this->product->toArray();
		$this->assertEqual("Bandomasis produktas", $array['name_lt']);
		$this->assertEqual($textValue, $array['attributes'][$textField->getID()]['value_en']);
		$this->assertEqual($numValue, $array['attributes'][$numField->getID()]['value']);
			
		// modify an attribute
		$this->product->setAttributeValue($numField, $numValue = 777);
		$this->product->save();		
		$array = $this->product->toArray();
		$this->assertEqual($numValue, $array['attributes'][$numField->getID()]['value']);
			
		// remove the textfield attribute
		$this->product->removeAttribute($textField);
		$array = $this->product->toArray();		
		$this->assertFalse(isset($array['attributes'][$textField->getID()]));			
	}
	
	function testSingleSelectValues()
	{			
		// create a single value select attribute
		$singleSel = SpecField::getNewInstance($this->productCategory, SpecField::DATATYPE_NUMBERS, SpecField::TYPE_NUMBERS_SELECTOR);
		$singleSel->handle->set('single.sel');
		$singleSel->setValueByLang('name', 'en', 'Select one value');
		$singleSel->setValueByLang('name', 'lt', 'Pasirinkite viena reiksme');
		$singleSel->save();
		
		// create some numeric values for the select
		$value1 = SpecFieldValue::getNewInstance($singleSel);
		$value1->setValueByLang('value', 'en', $firstValue = '20');
		$value1->save();
		
		$value2 = SpecFieldValue::getNewInstance($singleSel);
		$value2->setValueByLang('value', 'en', $secValue = '30');
		$value2->save();

		// assign the select value to product
		$this->product->setAttributeValue($singleSel, $value1);
		$this->product->save();

		$array = $this->product->toArray();
		$this->assertEqual($firstValue, $array['attributes'][$singleSel->getID()]['value_en']);
		
		// assign a different select value
		$this->product->setAttributeValue($singleSel, $value2);
		$this->product->save();		
				
		$array = $this->product->toArray();
		$this->assertEqual($secValue, $array['attributes'][$singleSel->getID()]['value_en']);
		
		// check for the number of SpecificationItem instances matching this field/product in database.
		// basically, we need to make sure that the old value has been deleted
		$query = 'SELECT COUNT(*) FROM SpecificationItem WHERE productID=' . $this->product->getID() . ' AND specFieldID=' . $singleSel->getID();
		$data = ActiveRecord::getDataBySQL($query);
		$this->assertEqual(1, array_shift(array_shift($data)));
		
		// create yet another single value select attribute
		$anotherSel = SpecField::getNewInstance($this->productCategory, SpecField::DATATYPE_NUMBERS, SpecField::TYPE_NUMBERS_SELECTOR);
		$anotherSel->setValueByLang('name', 'en', 'Select another value');
		$anotherSel->setValueByLang('name', 'lt', 'Pasirinkite kita reiksme');
		$anotherSel->save();
		
		// create some numeric values for the select
		$avalue1 = SpecFieldValue::getNewInstance($anotherSel);
		$avalue1->setValueByLang('value', 'en', '20');
		$avalue1->save();
		
		$this->avalue1 = $avalue1;
		
		// attempt to assign second selectors value to the first selector
		try
		{
			$this->product->setAttributeValue($singleSel, $avalue1);  
			$this->assertTrue(0);
		}
		catch (Exception $e)
		{
			$this->assertTrue(1);
		}		
		
	}
	
	function testMultipleSelectValues()
	{
		// create a multiple value select attribute
		$multiSel = SpecField::getNewInstance($this->productCategory, SpecField::DATATYPE_NUMBERS, SpecField::TYPE_NUMBERS_SELECTOR);
		$multiSel->isMultiValue->set(true);
		$multiSel->setValueByLang('name', 'en', 'Select multiple values');
		$multiSel->setValueByLang('name', 'lt', 'Pasirinkite kelias reiksmes');
		$multiSel->save();
		
		$values = array();
		for ($k = 0; $k < 5; $k++)
		{
		  	$inst = SpecFieldValue::getNewInstance($multiSel);
			$inst->setValueByLang('value', 'en', $k);
			$inst->setValueByLang('value', 'lt', 'Blaah');
			$inst->save();
			$values[] = $inst;
		}
		
		// assign the multiselect values
		$this->product->setAttributeValue($multiSel, $values[1]);  
		$this->product->setAttributeValue($multiSel, $values[3]); 
		$this->product->save();		
		$array = $this->product->toArray();
		$this->assertEqual(2, count($array['attributes'][$multiSel->getID()]['values']));
		
		// assign one more multiselect value
		$this->product->setAttributeValue($multiSel, $values[2]); 
		$this->product->save();		
		$array = $this->product->toArray();
		$this->assertEqual(3, count($array['attributes'][$multiSel->getID()]['values']));

		// remove the first multiselect value
		$this->product->removeAttributeValue($multiSel, $values[1]); 
		$this->product->save();
		$array = $this->product->toArray();
		$this->assertEqual(2, count($array['attributes'][$multiSel->getID()]['values']));
		
		// check for the number of SpecificationItem instances matching this field/product in database
		$query = 'SELECT COUNT(*) FROM SpecificationItem WHERE productID=' . $this->product->getID() . ' AND specFieldID=' . $multiSel->getID();
		$data = ActiveRecord::getDataBySQL($query);
		$this->assertEqual(2, array_shift(array_shift($data)));		

		// try to assign a value from a different selector
		try
		{
			$this->product->setAttributeValue($multiSel, $this->avalue1); 
			$this->assertTrue(0);
		}
		catch (Exception $e)
		{
			$this->assertTrue(1);
		}

		// remove the multiselect value altogether
		$this->product->removeAttribute($multiSel);
		$this->product->save();

		// check for the number of SpecificationItem instances matching this field/product in database.
		// shouldn't be any after the value removal
		$query = 'SELECT COUNT(*) FROM SpecificationItem WHERE productID=' . $this->product->getID() . ' AND specFieldID=' . $multiSel->getID();
		$data = ActiveRecord::getDataBySQL($query);
		$this->assertEqual(0, array_shift(array_shift($data)));		
		
		// set the values back, so we could test how the data is restored from DB
		$this->product->setAttributeValue($multiSel, $values[1]); 
		$this->product->setAttributeValue($multiSel, $values[2]); 

		// set prices
		foreach (Store::getInstance()->getCurrencyArray() as $currency)
		{
			$this->product->setPrice($currency, 111);
		}
		
		$this->product->save();				
	}
	
	function testLoadSpecification()
	{	
		ActiveRecord::removeFromPool($this->product);

		$this->product = Product::getInstanceByID($this->product->getID(), true);
		$this->product->loadSpecification();

		// save as soon as the specification is loaded to make sure all associated objects are marked as existing.
		// and won't be re-inserted in database
		try
		{
			$this->product->save();
			$this->assertTrue(1);	
		}
		catch(Exception $e)
		{
			$this->assertTrue(0);
			throw $e;
		}		
		
		$arr = $this->product->toArray();
		foreach (Store::getInstance()->getCurrencyArray() as $currency)
		{
			$this->assertEqual($arr['price_' . $currency], 111);
		}

		// re-run all the previous tests on the restored object
		$this->testSimpleValues();
		$this->testSingleSelectValues();
		$this->testMultipleSelectValues();

		$arr = $this->product->toArray();
		
		ActiveRecordModel::rollback();
	}

	
}

?>