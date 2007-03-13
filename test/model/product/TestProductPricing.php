<?php
if(count(debug_backtrace()) == 0) require_once '../../Initialize.php';

ClassLoader::import("application.model.product.Product");
ClassLoader::import("application.model.product.ProductPricing");

/**
 *	Test Product and Product Specification model for the following scenarios:
 *	
 *	  * Create a new product and assign specification attributes
 *	  * Load a product from a database, read and modify specification attributes
 *  
 */
class TestProductPricing extends UnitTestCase
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
    
    /**
     * Product autoincrement (used to completely revert all changes done to the database during the tests)
     *
     * @var integer
     */
    private $productAutoIncrementNumber;
    
    /**
     * Creole database connection wrapper
     *
     * @var Connection
     */
    private $db;
    
    public function __construct()
	{
	    parent::__construct();
	    $this->db = ActiveRecord::getDBConnection();
	    
	    // Get root category
	    $this->category = Category::getInstanceByID(Category::ROOT_ID);
	}
    
    public function setUp()
	{
		echo "<pre>";
	    ActiveRecordModel::beginTransaction();	
		
   		// create a product without attributes
		$this->product = Product::getNewInstance($this->category);
		$this->product->setValueByLang("name", "en", "TEST_PRODUCT");
		$this->product->save();		
		$this->productAutoIncrementNumber = $this->product->getID();
	}
	
	public function tearDown()
	{
		echo "</pre>";
	    ActiveRecordModel::rollback();		
	    $this->db->executeUpdate("ALTER TABLE Product AUTO_INCREMENT=" . $this->productAutoIncrementNumber);
	}
	
	public function testSave()
	{        
	    // Create new prices
	    $prices = array();
		foreach(Store::getInstance()->getCurrencyArray() as $currency) $this->product->setPrice($currency, $prices[$currency] = (rand(1, 1000) + rand(1, 100) / 100));
        $this->product->save();       
        
        // reload product
        $this->product->markAsNotLoaded();
        $this->product->load();
        
        
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
		foreach(Store::getInstance()->getCurrencyArray() as $currency) $this->product->setPrice($currency, $prices[$currency] = (rand(1, 1000) + rand(1, 100) / 100));
		$this->product->save();

        // Reload product
        $this->product->markAsNotLoaded();
        $this->product->load();
  
        // Edit prices
        $this->product->loadSpecification();
		foreach(Store::getInstance()->getCurrencyArray() as $currency) $this->product->setPrice($currency, $prices[$currency] = (rand(1, 1000) + rand(1, 100) / 100));
		$this->product->save();
		
		// Prices should change (also note that to make prices change you should reload specifications too)
	    $this->product->loadSpecification();
        $pricing = $this->product->getPricingHandler();
		$this->assertEqual($pricing->toArray(ProductPricing::CALCULATED), $prices);
   	}
   	
   	
   	public function testCalculatePrices()
   	{        
        // Create new prices
		$this->product->setPrice(Store::getInstance()->getDefaultCurrencyCode(), $defaultPrice = 1);
		$this->product->save();
  		
		// Just check that prices for other currencies are generated
	    $this->product->loadSpecification();
        $pricing = $this->product->getPricingHandler();
        
        $prices = $pricing->toArray(ProductPricing::BOTH);
        foreach(Store::getInstance()->getCurrencyArray(!Store::INCLUDE_DEFAULT) as $currencyCode)
        {
            $this->assertTrue(isset($prices[ProductPricing::CALCULATED][$currencyCode]) && $prices[ProductPricing::CALCULATED][$currencyCode] > 0);
            $this->assertFalse(isset($prices[ProductPricing::DEFINED][$currencyCode]));
        }
   	}
}

?>