<?php

ClassLoader::import('application.model.ActiveRecordModel');
ClassLoader::import('application.model.category.Category');
ClassLoader::import('application.model.user.User');

/**
 *
 * @package application.model.product
 * @author Integry Systems <http://integry.com>
 */
class ProductRatingType extends ActiveRecordModel
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName($className);

		$schema->registerField(new ARPrimaryKeyField('ID', ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField('categoryID', 'Category', 'ID', null, ARInteger::instance()));
		$schema->registerField(new ARField('name', ARArray::instance()));
		$schema->registerField(new ARField('position', ARInteger::instance()));
	}

	/*####################  Static method implementations ####################*/

	public static function getNewInstance(Category $category)
	{
		$instance = parent::getNewInstance(__CLASS__);
		$instance->category->set($category);
		return $instance;
	}

	public static function getProductRatingTypes(Product $product)
	{
		return self::getRecordSet(__CLASS__, self::getRatingTypeFilter($product), array('Category'));
	}

	private function getRatingTypeFilter(Product $product)
	{
		$path = $product->category->get()->getPathNodeSet(Category::INCLUDE_ROOT_NODE);

		$filter = new ARSelectFilter();

		$filter->setOrder(new ARFieldHandle("Category", "lft"), 'ASC');
		$filter->setOrder(new ARFieldHandle(__CLASS__, "position"), 'ASC');

		$cond = new EqualsCond(new ARFieldHandle(__CLASS__, "categoryID"), $product->category->get()->getID());

		foreach ($path as $node)
		{
			$cond->addOR(new EqualsCond(new ARFieldHandle(__CLASS__, "categoryID"), $node->getID()));
		}

		$filter->setCondition($cond);

		return $filter;
	}

	/*####################  Saving ####################*/

	protected function insert()
	{
		// get max position
	  	$f = new ARSelectFilter(new EqualsCond(new ARFieldHandle(__CLASS__, 'categoryID'), $this->category->get()->getID()));
	  	$f->setOrder(new ARFieldHandle(get_class($this), 'position'), 'DESC');
	  	$f->setLimit(1);
	  	$rec = ActiveRecord::getRecordSetArray(get_class($this), $f);
		$position = (is_array($rec) && count($rec) > 0) ? $rec[0]['position'] + 1 : 1;

		$this->position->set($position);

		return parent::insert();
	}
}

?>