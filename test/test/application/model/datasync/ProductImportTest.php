<?php

if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';


/**
 * @author Integry Systems
 * @package test.model.tax
 */
class ProductImportTest extends LiveCartTest
{
	public function getUsedSchemas()
	{
		return array(
			'Product',
			'Category',
		);
	}

	public function testSimpleImport()
	{
		$lv = ActiveRecordModel::getNewInstance('Language');
		$lv->setID('xx');
		$lv->save();

		$profile = new CsvImportProfile('Product');
		$profile->setField(0, 'Product.sku');
		$profile->setField(1, 'Product.name', array('language' => 'en'));
		$profile->setField(2, 'Product.name', array('language' => 'xx'));
		$profile->setField(3, 'Product.shippingWeight');
		$profile->setParam('delimiter', ';');

		$csvFile = ClassLoader::getRealPath('cache.') . 'testDataImport.csv';
		file_put_contents($csvFile, 'test; "Test Product"; "Parbaudes Produkts"; 15' . "\n" .
									'another; "Another Test"; "Vel Viens"; 12.44');

		$import = new ProductImport($this->getApplication());
		$csv = $profile->getCsvFile($csvFile);
		$cnt = $import->importFile($csv, $profile);

		$this->assertEquals($cnt, 2);

		$test = Product::getInstanceBySKU('test');
		$this->assertTrue($test instanceof Product);
		$this->assertEquals($test->shippingWeight->get(), '15');
		$this->assertEquals($test->getValueByLang('name', 'en'), 'Test Product');

		$another = Product::getInstanceBySKU('another');
		$this->assertEquals($another->getValueByLang('name', 'xx'), 'Vel Viens');

		unlink($csvFile);
	}
}
?>