<?php


/**
 *
 * @package application/model/category
 * @author Integry Systems <http://integry.com>
 */
class CategoryRelationship extends ActiveRecordModel
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName($className);

		public $ID;
		public $relatedCategoryID;
		public $categoryID;
		public $position;
	}

	/*####################  Static method implementations ####################*/

	public static function getNewInstance(Category $category, Category $relatedCategory)
	{
		$instance = parent::getNewInstance(__CLASS__);
		$instance->category = $category);
		$instance->relatedCategory = $relatedCategory);
		return $instance;
	}

	/*####################  Saving ####################*/

	protected function insert()
	{
		$this->setLastPosition('category');

		return parent::insert();
	}
}

?>