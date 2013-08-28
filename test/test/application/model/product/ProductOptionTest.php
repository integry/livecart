<?php
if(!defined('TEST_SUITE')) require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import("application.model.user.*");
ClassLoader::import("application.model.product.*");
ClassLoader::import("application.model.category.Category");

/**
 *  @author Integry Systems
 *  @package test.model.product
 */
class ProductOptionTest extends LiveCartTest
{
	private $product;
	private $option;
	private $choices = array();

	public function getUsedSchemas()
	{
		return array(
			'Product',
			'ProductOption',
			'ProductOptionChoice',
		);
	}

	public function setUp()
	{
		parent::setUp();

		$this->product = Product::getNewInstance(Category::getRootNode(), 'test');
		$this->product->save();

		$this->option = ProductOption::getNewInstance($this->product);
		$this->option->type->set(ProductOption::TYPE_SELECT);
		$this->option->save();

		for ($k = 0; $k <= 1; $k++)
		{
			$choice = ProductOptionChoice::getNewInstance($this->option);
			$choice->priceDiff->set(10 + $k);
			$choice->save();
			$this->choices[] = $choice;
		}
	}

	public function testClone()
	{
		$cloned = clone $this->option;
		$cloned->save();

		$this->assertNotEquals($cloned->getID(), $this->option->getID());
		$this->assertSame($cloned->product, $cloned->getField('productID'));
		$this->assertEquals($cloned->getChoiceSet()->size(), count($this->choices));

		foreach ($cloned->getChoiceSet() as $key => $choice)
		{
			$this->assertNotEquals($choice->getID(), $this->choices[$key]->getID());
			$this->assertEquals($choice->priceDiff, 10 + $key);
		}
	}
}

?>