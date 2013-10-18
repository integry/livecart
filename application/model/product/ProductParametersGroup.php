<?php


/**
 * A generic class for grouping assigned product entities (files, related products, etc.)
 *
 * @package application/model/product
 * @author Integry Systems <http://integry.com>
 */
abstract class ProductParametersGroup extends MultilingualObject
{
	private static $nextPosition = false;

	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);


		public $ID;
		public $productID", "Product", "ID", null, ARInteger::instance()));
		public $position;

		return $schema;
	}

	public static function mergeGroupsWithFields($className, $groups, $fields)
	{
		return ActiveRecordGroup::mergeGroupsWithFields($className, $groups, $fields);
	}

	public function setNextPosition()
	{
		$className = get_class($this);

		if(!is_integer(self::$nextPosition))
		{
			$filter = new ARSelectFilter();
			$filter->setCondition(new EqualsCond(new ARFieldHandle($className, 'productID'), $this->product->getID()));
			$filter->order(new ARFieldHandle($className, 'position'), ARSelectFilter::ORDER_DESC);
			$filter->limit(1);

			self::$nextPosition = 0;
			foreach(ActiveRecord::getRecordSet($className, $filter) as $relatedProductGroup)
			{
				self::$nextPosition = $relatedProductGroup->position;
			}
		}

		$this->position = ++self::$nextPosition);
	}

	public function save($forceOperation = false)
	{
		if(!$this->isExistingRecord()) $this->setNextPosition();

		parent::save($forceOperation);
	}
}

?>