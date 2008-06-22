<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import("application.model.product.*");
ClassLoader::import("application.model.category.Category");

/**
 *  @author Integry Systems
 *  @package test.model.product
 */
class ProductRelationshipTest extends UnitTest
{
	/**
	 * @var Product
	 */
	private $product1 = null;

	/**
	 * @var Product
	 */
	private $product2 = null;

	/**
	 * @var Category
	 */
	private $rootCategory = null;

	/**
	 * @var ProductRelationshipGroup
	 */
	private $group = null;

	public function __construct()
	{
		parent::__construct('Related product tests');

		$this->rootCategory = Category::getInstanceByID(Category::ROOT_ID);
	}

	public function getUsedSchemas()
	{
		return array(
			'Product',
			'ProductRelationshipGroup',
			'ProductRelationship'
		);
	}

	public function setUp()
	{
		parent::setUp();

		// Create some product
		$this->product1 = Product::getNewInstance($this->rootCategory, 'test');
		$this->product1->save();
		$this->productAutoIncrementNumber = $this->product1->getID();

		// Create second product
		$this->product2 = Product::getNewInstance($this->rootCategory, 'test');
		$this->product2->save();

   		// create new group
		$this->group = ProductRelationshipGroup::getNewInstance($this->product1);
		$this->group->position->set(5);
		$this->group->save();
		$this->groupAutoIncrementNumber = $this->group->getID();
	}

	public function testInvalidRelationship()
	{
		// valid
		try {
			$relationship = ProductRelationship::getNewInstance($this->product1, $this->product2);
			$relationship->save();
			$this->pass();
		} catch(Exception $e) {
			$this->fail();
		}

		// invalid
		try {
			$relationship = ProductRelationship::getNewInstance($this->product1, $this->product1);
			$relationship->save();
			$this->fail();
		} catch(Exception $e) {
			$this->pass();
		}

		// two identical relationships are also invalid
		try {
			$relationship = ProductRelationship::getNewInstance($this->product1, $this->product2);
			$relationship->save();
			$this->fail();
		} catch(Exception $e) {
			$this->pass();
		}
	}

	public function testCreateNewRelationship()
	{
		// create
		$relationship = ProductRelationship::getNewInstance($this->product1, $this->product2);
		$relationship->save();

		// reloat
		$relationship->reload(array('RelatedProduct' => 'Product'));

		// Check if product and related products are not null
		$this->assertNotNull($relationship->product->get());
		$this->assertNotNull($relationship->relatedProduct->get());
		// Check group
		$this->assertNull($relationship->productRelationshipGroup->get());

		// Check if product is product and related product is related
		$this->assertTrue($relationship->product->get() === $this->product1);
		$this->assertTrue($relationship->relatedProduct->get() === $this->product2);

		// Check if related and main products are not the same
		$this->assertFalse($relationship->product->get() === $relationship->relatedProduct->get());
		$this->assertFalse($this->product1 === $this->product2);

		$relationship->productRelationshipGroup->set($this->group);
		$relationship->save();

		// reloat
		$relationship->reload();

		// Check group
		$this->assertTrue($relationship->productRelationshipGroup->get() === $this->group);
	}

	public function testDeleteRelationship()
	{
		$relationship = ProductRelationship::getNewInstance($this->product1, $this->product2);
		$relationship->save();
		$this->assertTrue($relationship->isExistingRecord());

		$relationship->delete();
		$this->assertFalse($relationship->isLoaded());
	}

	public function testGetRelatedProducts()
	{
		// new product
		$product = Product::getNewInstance($this->rootCategory, 'test');
		$product->save();

		// groups
		$groups = array(0 => null);
		foreach(range(1, 2) as $i)
		{
			$groups[$i] = ProductRelationshipGroup::getNewInstance($product);
			$groups[$i]->position->set($i);
			$groups[$i]->setValueByLang('name', 'en', 'TEST_GROUP_' . $i);
			$groups[$i]->save();
		}

		// related products
		$relatedProducts = array();
		$relationships = array();
		foreach(range(1, 9) as $i)
		{
			$relatedProducts[$i] = Product::getNewInstance($this->rootCategory, 'test');
			$relatedProducts[$i]->save();

			$relationships[$i] = ProductRelationship::getNewInstance($product, $relatedProducts[$i], $groups[floor(($i - 1) / 3)]);
			$relationships[$i]->position->set(9 - $i);
			$relationships[$i]->save();
		}

		// test order
		$groupPosition = -1;
		$productPosition = -1;
		foreach(ProductRelationship::getRelationships($product) as $relationship)
		{
			$currentGroupPosition = $relationship->productRelationshipGroup->get() ? $relationship->productRelationshipGroup->get()->position->get() : $groupPosition;
			$currentProductPosition = $relationship->position->get();

			$this->assertTrue($productPosition <= $currentProductPosition || $groupPosition <= $currentGroupPosition, "$productPosition <= $currentProductPosition || $groupPosition <= $currentGroupPosition");

			$groupPosition = $currentGroupPosition;
			$productPosition = $currentProductPosition;
		}
	}

	public function testHasRelationship()
	{
		$product = array();
		foreach(range(1, 3) as $i)
		{
			$product[$i] = Product::getNewInstance($this->rootCategory, 'test');
			$product[$i]->save();
		}

		$relationship = ProductRelationship::getNewInstance($product[1], $product[2]);

		// Check relationship
		$this->assertFalse(ProductRelationship::hasRelationship($product[1], $product[2]));
		$this->assertFalse(ProductRelationship::hasRelationship($product[1], $product[3]));

		// Double check relationship to be sure that it is not being created by previous test
		$this->assertFalse(ProductRelationship::hasRelationship($product[1], $product[3]));

		// Save and check again. Has relationship will return true if the record was set
		$relationship->save();

		$this->assertTrue(ProductRelationship::hasRelationship($product[1], $product[2]));
		$this->assertFalse(ProductRelationship::hasRelationship($product[1], $product[3]));
	}
}
?>