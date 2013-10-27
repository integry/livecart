<?php

namespace category;

/**
 * Assigns a product to additional category
 *
 * @package application/model/product
 * @author Integry Systems <http://integry.com>
 */
class ProductCategory extends \ActiveRecordModel
{
	public $categoryID;
	public $productID;

	public function initialize()
	{
		$this->belongsTo('categoryID', 'category\Category', 'ID', array('foreignKey' => true, 'alias' => 'Category'));
		$this->belongsTo('productID', 'product\Product', 'ID', array('foreignKey' => true, 'alias' => 'Product'));
	}

	/**
	 * Creates a new related product
	 *
	 * @param Product $product
	 * @param Category $category
	 *
	 * @return ProductCategory
	 */
	public static function getNewInstance(\product\Product $product, Category $category)
	{
		$instance = new self();
		$instance->productID = $product->getID();
		$instance->categoryID = $category->getID();

		return $instance;
	}

/*
	public function delete()
	{
		$this->product->updateCategoryCounters($this->product->getCountUpdateFilter(true), $this->category);
		parent::delete();
	}

	public function beforeCreate()
	{
		$this->product->updateCategoryCounters($this->product->getCountUpdateFilter(), $this->category);
		$this->product->registerAdditionalCategory($this->category);
		$insertResult = parent::insert();
		Category::updateCategoryIntervals($this->product);
		return $insertResult;
	}
*/
}

?>
