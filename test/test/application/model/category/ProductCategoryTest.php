<?php

require_once dirname(__FILE__) . '/../../Initialize.php';

ClassLoader::import('application.model.category.ProductCategory');
ClassLoader::import('application.model.category.Category');

/**
 *  @author Integry Systems
 *  @package test.model.category
 */
class ProductCategoryTest extends LiveCartTest
{
	private $product;
	private $categories;
	private $user;
	private $root;
	private $secondCategory;

	public function getUsedSchemas()
	{
		return array(
			'Product',
			'Category',
		);
	}

	public function setUp()
	{
		parent::setUp();

		Category::recalculateProductsCount();

		$this->root = Category::getNewInstance(Category::getRootNode());
		$this->root->save();

		for ($k = 1; $k <= 2; $k++)
		{
			$cat = Category::getNewInstance($this->root);
			$cat->save();
			$this->categories[$k] = $cat;
		}

		$this->product = Product::getNewInstance($this->categories[1]);
		$this->product->save();

		$this->secondCategory = ProductCategory::getNewInstance($this->product, $this->categories[2]);
		$this->secondCategory->save();
	}

	public function testCategoryCount()
	{
		$this->reloadCategories();
		$this->assertEquals(1, $this->categories[2]->totalProductCount->get());
		$this->assertEquals(2, $this->root->totalProductCount->get());

		$this->secondCategory->delete();
		$this->reloadCategories();
		$this->assertEquals(0, $this->categories[2]->totalProductCount->get());
		$this->assertEquals(1, $this->root->totalProductCount->get());
	}

	public function testCountRecalculate()
	{
		$this->root->totalProductCount->set(0);
		$this->categories[1]->totalProductCount->set(0);
		$this->categories[2]->totalProductCount->set(0);
		Category::recalculateProductsCount();
		$this->reloadCategories();

		$this->assertEquals(1, $this->categories[1]->totalProductCount->get());
		$this->assertEquals(1, $this->categories[2]->totalProductCount->get());
		$this->assertEquals(1, $this->root->totalProductCount->get());
	}

	public function testDeleteProduct()
	{
		$this->product->delete();
		$this->reloadCategories();

		$this->assertEquals(0, $this->categories[1]->totalProductCount->get());
		$this->assertEquals(0, $this->categories[2]->totalProductCount->get());
		$this->assertEquals(0, $this->root->totalProductCount->get());
	}

	private function reloadCategories()
	{
		$this->categories[1]->reload();
		$this->categories[2]->reload();
		$this->root->reload();
	}
}
?>