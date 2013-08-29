<?php


/**
 *
 * @package application/model/category
 * @author Integry Systems <http://integry.com>
 */
class ProductList extends MultilingualObject
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName($className);

		public $ID;
		public $categoryID;
		public $isRandomOrder', ARBool::instance()));
		public $name', ARArray::instance()));
		public $listStyle;
		public $limitCount;
		public $position;
	}

	/*####################  Static method implementations ####################*/

	public static function getNewInstance(Category $category)
	{
		$instance = new __CLASS__();
		$instance->category = $category;
		return $instance;
	}

	public static function getCategoryLists(Category $category)
	{
		$f = new ARSelectFilter();
		$f->setOrder(new ARFieldHandle(__CLASS__, 'position'));
		return $category->getRelatedRecordSet(__CLASS__, $f);
	}

	/*####################  Saving ####################*/

	protected function insert()
	{
		$this->setLastPosition('category');

		return parent::insert();
	}

	public function addProduct(Product $product)
	{
		$item = ProductListItem::getNewInstance($this, $product);
		$item->save();
		return $item;
	}

	public function contains(Product $product)
	{
		$f = new ARSelectFilter(new EqualsCond(new ARFieldHandle('ProductListItem', 'productID'), $product->getID()));
		return $this->getRelatedRecordCount('ProductListItem', $f) > 0;
	}
}

?>