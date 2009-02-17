<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import("application.model.category.Category");
ClassLoader::import("application.model.product.Product");
ClassLoader::import("application.model.product.ProductPricing");

/**
 *  @author Integry Systems
 *  @package test.model.product
 */
class ProductPricingTest extends LiveCartTest
{
	/**
	 * Root category
	 *
	 * @var Category
	 */
	private $category;

	/**
	 * Some product
	 *
	 * @var Product
	 */
	private $product;

	public function __construct()
	{
		parent::__construct('Product pricings');

		// Get root category
		$this->category = Category::getInstanceByID(Category::ROOT_ID);
	}

	public function getUsedSchemas()
	{
		return array(
			'Product'
		);
	}

	public function setUp()
	{
		parent::setUp();

   		// create a product without attributes
		$this->product = Product::getNewInstance($this->category, 'test');
		$this->product->setValueByLang("name", "en", "TEST_PRODUCT");
		$this->product->save();
		$this->productAutoIncrementNumber = $this->product->getID();

		for ($k = 1; $k < 4; $k++)
		{
			$currency = Currency::getNewInstance($k . 'zz');
			$currency->save();
		}
	}

	public function testSave()
	{
		// Create new prices
		$prices = array();
		foreach(self::getApplication()->getCurrencyArray() as $currency) $this->product->setPrice($currency, $prices[$currency] = (rand(1, 1000) + rand(1, 100) / 100));
		$this->product->save();

		// reload product
		$this->product->reload();


		// Load pricing and check if prices are stored in database
		$this->product->loadSpecification();
		$pricing = $this->product->getPricingHandler();
		$this->assertEqual($pricing->toArray(ProductPricing::CALCULATED), $prices);
   	}

   	public function testEdit()
   	{
		$prices = array();
		$pricing = $this->product->getPricingHandler();

		// Create new prices
		foreach(self::getApplication()->getCurrencyArray() as $currency) $this->product->setPrice($currency, $prices[$currency] = (rand(1, 1000) + rand(1, 100) / 100));
		$this->product->save();

		// Reload product
		$this->product->reload();

		// Edit prices
		$this->product->loadSpecification();
		foreach(self::getApplication()->getCurrencyArray() as $currency) $this->product->setPrice($currency, $prices[$currency] = (rand(1, 1000) + rand(1, 100) / 100));
		$this->product->save();

		// Prices should change (also note that to make prices change you should reload specifications too)
		$this->product->loadSpecification();
		$pricing = $this->product->getPricingHandler();
		$this->assertEqual($pricing->toArray(ProductPricing::CALCULATED), $prices);
   	}


   	public function testCalculatePrices()
   	{
		// Create new prices
		$this->product->setPrice(self::getApplication()->getDefaultCurrencyCode(), $defaultPrice = 1);
		$this->product->save();

		// Just check that prices for other currencies are generated
		$this->product->loadSpecification();
		$pricing = $this->product->getPricingHandler();

		$prices = $pricing->toArray(ProductPricing::BOTH);
		foreach(self::getApplication()->getCurrencyArray(!LiveCart::INCLUDE_DEFAULT) as $currencyCode)
		{
			$this->assertTrue(isset($prices[ProductPricing::CALCULATED][$currencyCode]) && $prices[ProductPricing::CALCULATED][$currencyCode] > 0);
			$this->assertFalse(isset($prices[ProductPricing::DEFINED][$currencyCode]));
		}
   	}
}

?>