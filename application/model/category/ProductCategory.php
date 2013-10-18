<?php


/**
 * Assigns a product to additional category
 *
 * @package application/model/product
 * @author Integry Systems <http://integry.com>
 */
class ProductCategory extends ActiveRecordModel
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName($className);

		$schema->registerField(new ARPrimaryForeignKeyField('categoryID', 'Category', 'ID', 'Category;
		$schema->registerField(new ARPrimaryForeignKeyField('productID', 'Product', 'ID', 'Product;
	}

	/*####################  Static method implementations ####################*/

	/**
	 * Creates a new related product
	 *
	 * @param Product $product
	 * @param Category $category
	 *
	 * @return ProductCategory
	 */
	public static function getNewInstance(Product $product, Category $category)
	{
		$instance = new self();
		$instance->product = $product;
		$instance->category = $category;

		return $instance;
	}

	/*####################  Saving ####################*/

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

}

?>