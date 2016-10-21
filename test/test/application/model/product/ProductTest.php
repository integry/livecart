<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import("application.model.product.Product");
ClassLoader::import("application.model.category.*");
ClassLoader::import("application.model.product.*");

/**
 *	Test Product and Product Specification model for the following scenarios:
 *
 *	  * Create a new product and assign specification attributes
 *	  * Load a product from a database, read and modify specification attributes
 *
 *  @author Integry Systems
 *  @package test.model.product
 */
class ProductTest extends LiveCartTest
{
	/**
	 * @var Product
	 */
	private $product = null;
	/**
	 * @var Category
	 */
	private $productCategory = null;

	public function __construct()
	{
		parent::__construct('Test Product class');
		ActiveRecordModel::beginTransaction();
	}

	public function getUsedSchemas()
	{
		return array(
			'Category',
			'Product',
			'ProductRelationship',
			'ProductRelationshipGroup',
			'ProductFile',
			'ProductFileGroup',
			'ProductVariation',
			'ProductVariationType',
			'ProductVariationValue',
		);
	}

	public function setUp()
	{
		parent::setUp();

		// create a new category
		$this->productCategory = Category::getNewInstance(Category::getRootNode());
		$this->productCategory->setValueByLang("name", "en", "Demo category branch");
		$this->productCategory->save();

		// create a product without attributes
		$this->product = Product::getNewInstance($this->productCategory, 'test');
		$this->product->setValueByLang("name", "en", "Test product...");
		$this->product->setValueByLang("name", "lt", "Bandomasis produktas");
		$this->product->setFieldValue("isEnabled", true);
		$this->product->save();

		$this->usd = ActiveRecordModel::getNewInstance('Currency');
		$this->usd->setID('ZZZ');
		$this->usd->save(ActiveRecord::PERFORM_INSERT);
	}

	/**
	 *  Disabled product, with 0 stock - the numbers shouldn't change
	 */
	public function testCategoryCountsWhenDisabledProductWithNoStockIsAdded()
	{
		$secondCategory = Category::getNewInstance($this->productCategory);
		$secondCategory->save();

		$product = Product::getNewInstance($secondCategory);
		$product->isEnabled->set(0);
		$product->stockCount->set(0);
		$product->save();

		ActiveRecordModel::removeFromPool($secondCategory);
		$sameCategory = Category::getInstanceByID($secondCategory->getID(), true);
		$this->assertEqual((int)$secondCategory->totalProductCount->get() + 1, (int)$sameCategory->totalProductCount->get());
		$this->assertEqual((int)$secondCategory->activeProductCount->get(), (int)$sameCategory->activeProductCount->get());
		$this->assertEqual((int)$secondCategory->availableProductCount->get(), (int)$sameCategory->availableProductCount->get());
	}

	/**
	 *  Disabled product, with some stock - the numbers shouldn't change again
	 */
	public function testCategoryCountsWhenDisabledProductWithSomeStockIsAdded()
	{
		$secondCategory = Category::getNewInstance($this->productCategory);
		$secondCategory->save();

		$product = Product::getNewInstance($secondCategory);
		$product->isEnabled->set(0);
		$product->stockCount->set(5);
		$product->save();

		ActiveRecordModel::removeFromPool($secondCategory);
		$sameCategory = Category::getInstanceByID($secondCategory->getID(), true);
		$this->assertEqual((int)$secondCategory->activeProductCount->get(), (int)$sameCategory->activeProductCount->get());
		$this->assertEqual((int)$secondCategory->availableProductCount->get(), (int)$sameCategory->availableProductCount->get());

		// enable the product, so available and enabled product counts should INCREASE by one
		$product->isEnabled->set(1);
		$product->save();
		ActiveRecordModel::removeFromPool($sameCategory);
		$sameCategory = Category::getInstanceByID($secondCategory->getID(), true);
		$this->assertEqual((int)$secondCategory->availableProductCount->get() + 1, (int)$sameCategory->availableProductCount->get());
		$this->assertEqual((int)$secondCategory->activeProductCount->get() + 1, (int)$sameCategory->activeProductCount->get());

		// disable the product, so available and enabled product counts should DECREASE by one
		$product->isEnabled->set(0);
		$product->save();
		ActiveRecordModel::removeFromPool($sameCategory);
		$sameCategory = Category::getInstanceByID($secondCategory->getID(), true);
		$this->assertEqual((int)$secondCategory->availableProductCount->get(), (int)$sameCategory->availableProductCount->get());
		$this->assertEqual((int)$secondCategory->activeProductCount->get(), (int)$sameCategory->activeProductCount->get());
	}

	/**
	 *  Enabled product, with some stock - the numbers should increase by one
	 */
	public function testCategoryCountsWhenEnabledProductWithSomeStockIsAdded()
	{
		$secondCategory = Category::getNewInstance($this->productCategory);
		$secondCategory->save();

		$product = Product::getNewInstance($secondCategory);
		$product->isEnabled->set(1);
		$product->stockCount->set(5);
		$product->save();

		ActiveRecordModel::removeFromPool($secondCategory);
		$sameCategory = Category::getInstanceByID($secondCategory->getID(), true);
		$this->assertEqual((int)$secondCategory->activeProductCount->get() + 1, (int)$sameCategory->activeProductCount->get());
		$this->assertEqual((int)$secondCategory->availableProductCount->get() + 1, (int)$sameCategory->availableProductCount->get());
	}

	/**
	 *  Enabled product, with some stock - the numbers should increase by one
	 */
	public function testCategoryCountsWhenEnabledProductWithNoStockIsAdded()
	{
		$secondCategory = Category::getNewInstance($this->productCategory);
		$secondCategory->save();

		$product = Product::getNewInstance($secondCategory);
		$product->isEnabled->set(1);
		$product->stockCount->set(0);
		$product->save();

		ActiveRecordModel::removeFromPool($secondCategory);
		$sameCategory = Category::getInstanceByID($secondCategory->getID(), true);
		$this->assertEqual((int)$secondCategory->activeProductCount->get() + 1, (int)$sameCategory->activeProductCount->get());
		$this->assertEqual((int)$secondCategory->availableProductCount->get(), (int)$sameCategory->availableProductCount->get());

		// now add some stock, so available product count should increase by one
		$product->stockCount->set(5);
		$product->save();
		ActiveRecordModel::removeFromPool($sameCategory);
		$sameCategory = Category::getInstanceByID($secondCategory->getID(), true);
		$this->assertEqual((int)$secondCategory->availableProductCount->get() + 1, (int)$sameCategory->availableProductCount->get());
	}

	public function testSimpleValues()
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

	public function testSingleSelectValues()
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
			$this->fail();
		}
		catch (Exception $e)
		{
			$this->pass();
		}

	}

	public function testMultipleSelectValues()
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

		$this->product->save();
	}

	public function testLoadSpecification()
	{
		ActiveRecord::removeFromPool($this->product);

		$this->product = Product::getInstanceByID($this->product->getID(), true);
		$this->product->loadSpecification();

		// save as soon as the specification is loaded to make sure all associated objects are marked as existing.
		// and won't be re-inserted in database
		try
		{
			$this->product->save();
			$this->pass();
		}
		catch(Exception $e)
		{
			$this->fail();
		}

		// set prices
		foreach ($this->product->getApplication()->getCurrencyArray() as $currency)
		{
			$this->product->setPrice($currency, 111);
		}
		$this->product->save();

		$arr = $this->product->toArray();
		foreach ($this->product->getApplication()->getCurrencyArray() as $currency)
		{
			$this->assertEqual($arr['price_' . $currency], 111);
		}

		// re-run all the previous tests on the restored object
		$this->testSimpleValues();
		$this->testSingleSelectValues();
		$this->testMultipleSelectValues();

		$arr = $this->product->toArray();
	}

	public function testMultipleProductsWithPrices()
	{
		$usd = ActiveRecordModel::getNewInstance('Currency');
		$usd->setID('XXX');
		$usd->save(ActiveRecord::PERFORM_INSERT);

		for ($k = 0; $k < 5; $k++)
		{
			$product = Product::getNewInstance(Category::getRootNode());
			$product->setPrice('XXX', $k);
			$product->save();
			$this->assertEqual($product->getPricingHandler()->getPriceByCurrencyCode('XXX')->price->get(), $k);
		}
	}

	public function testAddRelatedProducts()
	{
		$otherProducts = array();
		foreach(range(1, 2) as $i)
		{
			$otherProducts[$i] = Product::getNewInstance($this->productCategory);
			$otherProducts[$i]->save();

			$this->product->addRelatedProduct($otherProducts[$i]);
		}

		$this->assertEqual(2, $this->product->getRelatedProducts()->getTotalRecordCount());
		foreach($this->product->getRelatedProducts() as $relatedProduct)
		{
			$this->assertIsA($relatedProduct, 'Product');
		}

		$this->product->reload();

		// All relationships will be lost unless product is saved
		$this->assertEqual(0, $this->product->getRelatedProducts()->getTotalRecordCount());

		foreach($otherProducts as $otherProduct) $this->product->addRelatedProduct($otherProduct);
		$this->product->save();

		// reload
		$this->product->reload();

		// all related products should be here
		$this->assertEqual(2, $this->product->getRelatedProducts()->getTotalRecordCount());
	}

	public function testGetRelationships()
	{
		$otherProducts = array();
		foreach(range(1, 2) as $i)
		{
			$otherProducts[$i] = Product::getNewInstance($this->productCategory);
			$otherProducts[$i]->save();

			$this->product->addRelatedProduct($otherProducts[$i]);
		}

		$this->product->save();

		$this->product->reload();

		$i = 1;
		$this->assertEqual(2, $this->product->getRelationships()->getTotalRecordCount());
		foreach($this->product->getRelationships() as $relationship)
		{
			$this->assertIsA($relationship, 'ProductRelationship');
			$this->assertTrue($relationship->relatedProduct->get() === $otherProducts[$i]);

			$i++;
		}
	}

	public function testGetRelatedProducts()
	{
		$otherProducts = array();
		foreach(range(1, 5) as $i)
		{
			$otherProducts[$i] = Product::getNewInstance($this->productCategory);
			$otherProducts[$i]->setValueByLang("name", "en", "Test");
			$otherProducts[$i]->save();

			$this->product->addRelatedProduct($otherProducts[$i]);
		}

		$i = 1;
		$this->assertEqual(5, $this->product->getRelatedProducts()->getTotalRecordCount());
		foreach($this->product->getRelatedProducts() as $relatedProduct)
		{
			$this->assertIsA($relatedProduct, 'Product');
			$this->assertTrue($relatedProduct === $otherProducts[$i]);

			$i++;
		}

		// Save and reload
		$this->product->save();
		ActiveRecord::removeClassFromPool('Product');
		$this->product->load();

		$i = 1;
		$this->assertEqual(5, $this->product->getRelatedProducts()->getTotalRecordCount());
		foreach($this->product->getRelatedProducts() as $relatedProduct)
		{
			$this->assertIsA($relatedProduct, 'Product');
			$this->assertTrue($relatedProduct === $otherProducts[$i]);

			$relatedProductName = $relatedProduct->name->get();
			$this->assertEqual($relatedProductName['en'], 'Test');

			$i++;
		}
	}

	public function testRemoveRelationship()
	{
		$otherProducts = array();
		foreach(range(1, 2) as $i)
		{
			$otherProducts[$i] = Product::getNewInstance($this->productCategory);
			$otherProducts[$i]->save();
			$this->product->addRelatedProduct($otherProducts[$i]);
		}
		$this->product->save();

		foreach($this->product->getRelatedProducts() as $relatedProduct)
		{
			$this->product->removeFromRelatedProducts($relatedProduct, ProductRelationship::TYPE_CROSS);
		}
		$this->assertEqual(0, $this->product->getRelatedProducts()->getTotalRecordCount());

		// Relationships are not removed from database unless the product is saved
		//$this->assertEqual(2, $this->product->getRelatedProducts()->getTotalRecordCount());

		$this->product->save();

		// Now they are removed
		//$this->product->loadRelationships(false, 0, true);
		$this->assertEqual(0, $this->product->getRelatedProducts()->getTotalRecordCount());
	}


	public function testIsRelatedTo()
	{
		$notRelatedProduct = Product::getNewInstance($this->productCategory);
		$notRelatedProduct->save();

		$relatedProduct = Product::getNewInstance($this->productCategory);
		$relatedProduct->save();

		$this->product->addRelatedProduct($relatedProduct);
		$this->product->save();

		$this->assertFalse($notRelatedProduct->isRelatedTo($this->product, ProductRelationship::TYPE_CROSS));
		$this->assertTrue($relatedProduct->isRelatedTo($this->product, ProductRelationship::TYPE_CROSS));

		// isRelatedTo provide one direction testing is related to means that
		// this product is in that product's related products list
		$this->assertFalse($this->product->isRelatedTo($relatedProduct, ProductRelationship::TYPE_CROSS));
	}

	public function testGetRelationshipGroups()
	{
		$this->assertEqual($this->product->getRelationshipGroups(ProductRelationship::TYPE_CROSS)->getTotalRecordCount(), 0);

		$relationship = ProductRelationshipGroup::getNewInstance($this->product, ProductRelationship::TYPE_CROSS);
		$this->assertEqual($this->product->getRelationshipGroups(ProductRelationship::TYPE_CROSS)->getTotalRecordCount(), 0);

		$relationship->save();
		$this->assertEqual($this->product->getRelationshipGroups(ProductRelationship::TYPE_CROSS)->getTotalRecordCount(), 1);
	}

	public function testGetFileGroups()
	{
		$this->assertEqual($this->product->getFileGroups()->getTotalRecordCount(), 0);

		$fileGroup = ProductFileGroup::getNewInstance($this->product);
		$this->assertEqual($this->product->getFileGroups()->getTotalRecordCount(), 0);

		$fileGroup->save();
		$this->assertEqual($this->product->getFileGroups()->getTotalRecordCount(), 1);
	}

	public function testGetFiles()
	{
		$productFiles = array();
		$productFilesO = array();
		$dir = ClassLoader::getRealPath('cache') . '/';
		foreach(range(1, 2) as $i)
		{
			file_put_contents($productFiles[$i] = $dir . md5($i), 'All Your Base Are Belong To Us');
			$productFilesO[$i] = ProductFile::getNewInstance($this->product, $productFiles[$i], 'test_file.txt');
			$productFilesO[$i]->save();
		}

		$this->assertEqual($this->product->getFiles()->getTotalRecordCount(), 2);

		foreach($productFilesO as $file)
		{
		   $file->delete();
		}
	}

	public function testMergeGroupsWithFIlters()
	{
		// create groups
		$productGroup = array();
		foreach(range(1, 3) as $i)
		{
			$productGroup[$i] = ProductFileGroup::getNewInstance($this->product);
			$productGroup[$i]->save();
		}

		// create files
		$productFile = array();
		$productFiles = array();
		$dir = ClassLoader::getRealPath('cache') . '/';
		foreach(range(1, 2) as $i)
		{
			file_put_contents($productFiles[$i] = $dir . md5($i), "file $i");
			$productFile[$i] = ProductFile::getNewInstance($this->product, $productFiles[$i], "test_file_$i.txt");
			$productFile[$i]->save();
		}

		$productFile[1]->productFileGroup->set($productGroup[2]);
		$productFile[1]->save();

		$this->assertEqual($this->product->getFileGroups()->getTotalRecordCount(), 3);
		$this->assertEqual($this->product->getFiles()->getTotalRecordCount(), 2);

		$filesMergedWithGroups = $this->product->getFilesMergedWithGroupsArray();

		$this->assertEqual(count($filesMergedWithGroups), 4);

		// Check files without group
		$this->assertTrue(isset($filesMergedWithGroups[1]['ID']));
		$this->assertEqual($filesMergedWithGroups[1]['ID'], $productFile[2]->getID());
		$this->assertFalse(isset($filesMergedWithGroups[1]['ProductFileGroup']['ID']));

		// Check first group
		$this->assertFalse(isset($filesMergedWithGroups[2]['ID']));
		$this->assertTrue(isset($filesMergedWithGroups[2]['ProductFileGroup']['ID']));
		$this->assertEqual($filesMergedWithGroups[2]['ProductFileGroup']['ID'], $productGroup[1]->getID());

		// Check second group
		$this->assertTrue(isset($filesMergedWithGroups[3]['ID']));
		$this->assertEqual($filesMergedWithGroups[3]['ID'], $productFile[1]->getID());
		$this->assertTrue(isset($filesMergedWithGroups[3]['ProductFileGroup']['ID']));
		$this->assertEqual($filesMergedWithGroups[3]['ProductFileGroup']['ID'], $productGroup[2]->getID());

		// Check second group
		$this->assertFalse(isset($filesMergedWithGroups[4]['ID']));
		$this->assertTrue(isset($filesMergedWithGroups[4]['ProductFileGroup']['ID']));
		$this->assertEqual($filesMergedWithGroups[4]['ProductFileGroup']['ID'], $productGroup[3]->getID());

		foreach($productFiles as $fileName) unlink($fileName);
		foreach($productFile as $file) $file->delete();
	}

	public function testAutoSku()
	{
		$this->assertEqual($this->product->sku->get(), 'SKU' . $this->product->getID());

		//reset sku
		$this->product->sku->set('CUSTOM');
		$this->product->save();

		ActiveRecordModel::clearPool();

		$product = Product::getInstanceByID($this->product->getID(), true);
		$product->isEnabled->set(true);
		$product->save();

		// SKU shouldn't be reset for products that are not loaded
		$this->assertNotEquals($this->product->sku->get(), 'SKU' . $product->getID());
	}

	public function testClone()
	{
		$image = ActiveRecordModel::getNewInstance('ProductImage');
		$image->product->set($this->product);
		$image->save();

		$this->assertSame($image, $this->product->defaultImage->get());

		$numField = SpecField::getNewInstance($this->productCategory, SpecField::DATATYPE_NUMBERS, SpecField::TYPE_NUMBERS_SIMPLE);
		$numField->save();
		$this->product->setAttributeValue($numField, 100);
		$this->product->save();

		$option = ProductOption::getNewInstance($this->product);
		$option->type->set(ProductOption::TYPE_SELECT);
		$option->setValueByLang('name', 'en', 'test');
		$option->save();

		$related = Product::getNewInstance($this->productCategory, 'related');
		$related->save();
		$relGroup = ProductRelationshipGroup::getNewInstance($this->product, ProductRelationship::TYPE_CROSS);
		$relGroup->save();
		$rel = ProductRelationship::getNewInstance($this->product, $related, $relGroup);
		$rel->save();

		$this->assertEquals(1, $this->product->getRelationships()->size());

		$cloned = clone $this->product;
		$this->assertEquals(100, $cloned->getSpecification()->getAttribute($numField)->value->get());

		$cloned->setAttributeValue($numField, 200);
		$cloned->setPrice($this->usd, 80);
		$cloned->save();

		$this->assertNotEquals($cloned->getID(), $this->product->getID());

		ActiveRecordModel::clearPool();
		$reloaded = Product::getInstanceByID($cloned->getID(), true);

		$reloaded->loadPricing();
		$this->assertEquals(80, $reloaded->getPrice($this->usd));

		$reloaded->loadSpecification();
		$this->assertEquals(200, $reloaded->getSpecification()->getAttribute($numField)->value->get());

		// related products
		$rel = $reloaded->getRelationships();
		$this->assertEquals(1, $rel->size());
		$this->assertSame($reloaded, $rel->get(0)->productRelationshipGroup->get()->product->get());

		// options
		$clonedOpts = ProductOption::getProductOptions($reloaded);
		$this->assertEquals(1, $clonedOpts->size());

		// image
		$this->assertTrue(is_object($reloaded->defaultImage->get()));
		$this->assertNotEquals($reloaded->defaultImage->get()->getID(), $this->product->defaultImage->get()->getID());
	}

	public function testChildProduct()
	{
		$this->product->setPrice($this->usd, 20);
		$this->product->shippingWeight->set(200);
		$this->product->save();

		$child = $this->product->createChildProduct();

		$root = Category::getRootNode();
		$root->reload();
		$productCount = $root->totalProductCount->get();

		// in array representation, parent product data is used where own data is not set
		$array = $child->toArray();
		$this->assertEquals($array['name_en'], $this->product->getValueByLang('name', 'en'));

		// auto-generated SKU is based on parent SKU
		$child->save();
		$this->assertEquals($child->sku->get(), $this->product->sku->get() . '-1');

		// category counters should not change
		$root->reload();
		$this->assertEquals($root->totalProductCount->get(), $productCount);

		// parent product price used if not defined
		$this->assertEquals($child->getPrice($this->usd), $this->product->getPrice($this->usd));

		// parent shipping weight used if not defined
		$this->assertEquals($child->getShippingWeight(), $this->product->getShippingWeight());

		// add/substract parent prices/shipping weights
		$child->setChildSetting('test', 'value');
		$this->assertEquals($child->getChildSetting('test'), 'value');

		// prices
		$child->setChildSetting('price', Product::CHILD_ADD);
		$child->setPrice($this->usd, 5);
		$this->assertEquals(20, $this->product->getPrice($this->usd));
		$this->assertEquals($child->getPrice($this->usd), $this->product->getPrice($this->usd) + 5);

		$child->setChildSetting('price', Product::CHILD_SUBSTRACT);
		$this->assertEquals($child->getPrice($this->usd), $this->product->getPrice($this->usd) - 5);

		$child->setChildSetting('price', Product::CHILD_OVERRIDE);
		$this->assertEquals($child->getPrice($this->usd), 5);

		// shipping weight
		$child->setChildSetting('weight', Product::CHILD_ADD);
		$child->shippingWeight->set(5);
		$this->assertEquals(200, $this->product->getShippingWeight());
		$this->assertEquals($child->getShippingWeight(), $this->product->getShippingWeight() + 5);

		$child->setChildSetting('weight', Product::CHILD_SUBSTRACT);
		$this->assertEquals($child->getShippingWeight(), $this->product->getShippingWeight() - 5);

		$child->setChildSetting('weight', Product::CHILD_OVERRIDE);
		$this->assertEquals($child->getShippingWeight(), 5);
	}

	public function testVariationMatrix()
	{
		$size = ProductVariationType::getNewInstance($this->product);
		$size->setValueByLang('name', 'en', 'Size');
		$size->save();

		$color = ProductVariationType::getNewInstance($this->product);
		$color->setValueByLang('name', 'en', 'Color');
		$color->save();

		$sizes = $colors = array();
		foreach (array('Small', 'Large') as $name)
		{
			$variation = ProductVariation::getNewInstance($size);
			$variation->setValueByLang('name', 'en', $name);
			$variation->save();
			$sizes[] = $variation;
		}

		foreach (array('Red', 'Green', 'Blue') as $name)
		{
			$variation = ProductVariation::getNewInstance($size);
			$variation->setValueByLang('name', 'en', $name);
			$variation->save();
			$colors[] = $variation;
		}

		// create product variations
		$variations = array();
		foreach ($sizes as $sizeVar)
		{
			foreach ($colors as $colorVar)
			{
				$child = $this->product->createVariation(array($sizeVar, $colorVar));
				$child->save();
				$variations[$sizeVar->getID()][$colorVar->getID()] = $child;
			}
		}

		$matrix = $this->product->getVariationMatrix();

		//var_dump($matrix);

	}

	public function testVariationInventory()
	{
		$child = $this->product->createChildProduct();
		$child->stockCount->set(10);
		$child->save();
		$this->assertEquals(10, $this->product->stockCount->get());

		$child->stockCount->set(20);
		$child->save();
		$this->assertEquals(20, $this->product->stockCount->get());
	}

    /**
     * Testing if getProductsPurchasedTogether() returns correct results when variations are purchased
     */
    public function testVariationsPurchasedTogether()
    {
        $this->initOrder();

        $parentProduct = $this->product;

        $childProduct = $parentProduct->createChildProduct();
        $childProduct->isEnabled->set(true);
        $childProduct->stockCount->set(1);
        $childProduct->save();

        $product = Product::getInstanceByID($childProduct->getID(), true);

        $this->order->addProduct($this->products[0], 1);
        $this->order->addProduct($this->products[1], 2);
        $this->order->addProduct($product, 1);
        $this->order->save();
        $this->order->finalize();

        /*
         * Because variations are added as child products, if an order contains a child product and regular products,
         * querying getProductsPurchasedTogether() of the parent product should return the regular products.
         * */
        $purchasedTogetherProducts = $parentProduct->getProductsPurchasedTogether(2);
        $this->assertEquals(2, count($purchasedTogetherProducts));
    }

	function test_SuiteTearDown()
	{
		ActiveRecordModel::rollback();
	}
}

?>
