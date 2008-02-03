<?php

ClassLoader::import('application.model.product.Product');
ClassLoader::import('application.model.category.Category');
ClassLoader::import('application.model.system.MultilingualObject');

/**
 * One of the main entities of the system - defines and handles product related logic.
 * This class allows to assign or change product attribute values, product files, images, related products, etc.
 *
 * @package application.model.product
 * @author Integry Systems <http://integry.com>
 */
class ProductOptionChoice extends MultilingualObject
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("ProductOptionChoice");

		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("optionID", "ProductOption", "ID", null, ARInteger::instance()));

		$schema->registerField(new ARField("priceDiff", ARFloat::instance(4)));
		$schema->registerField(new ARField("hasImage", ARBool::instance()));
		$schema->registerField(new ARField("position", ARInteger::instance(4)));
		$schema->registerField(new ARField("name", ARArray::instance()));
	}

	/**
	 * Creates a new option instance that is assigned to a category
	 *
	 * @param Category $category
	 *
	 * @return Product
	 */
	public static function getNewInstance(ProductOption $option)
	{
		$choice = parent::getNewInstance(__CLASS__);
		$choice->option->set($option);

		return $choice;
	}

	/**
	 * Get ActiveRecord instance
	 *
	 * @param mixed $recordID
	 * @param bool $loadRecordData
	 * @param bool $loadReferencedRecords
	 *
	 * @return Product
	 */
	public static function getInstanceByID($recordID, $loadRecordData = false, $loadReferencedRecords = false)
	{
		return parent::getInstanceByID(__CLASS__, $recordID, $loadRecordData, $loadReferencedRecords);
	}

	/*####################  Value retrieval and manipulation ####################*/

	/*####################  Saving ####################*/

	/**
	 * Removes an option choice from database
	 *
	 * @param int $recordID
	 * @return bool
	 * @throws Exception
	 */
	public static function deleteByID($recordID)
	{
		return parent::deleteByID(__CLASS__, $recordID);
	}


	/*####################  Data array transformation ####################*/

	public function toArray()
	{
	  	$array = parent::toArray();

		$this->setArrayData($array);

	  	return $array;
	}

	public static function transformArray($array, ARSchema $schema)
	{
		$array = parent::transformArray($array, $schema);

		return $array;
	}

	/*####################  Get related objects ####################*/

		/**
	 * Count products in category
	 *
	 * @param Category $category Category active record
	 * @return integer
	 */
	public static function countItems(Category $category)
	{
		return $category->getProductSet(new ARSelectFilter(), false)->getTotalRecordCount();
	}

}

?>