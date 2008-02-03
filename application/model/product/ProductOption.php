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
class ProductOption extends MultilingualObject
{
	const TYPE_BOOL = 0;

	const TYPE_SELECT = 1;

	const TYPE_TEXT = 2;

	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("ProductOption");

		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("productID", "Product", "ID", null, ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("categoryID", "Category", "ID", null, ARInteger::instance()));

		$schema->registerField(new ARField("name", ARArray::instance()));
		$schema->registerField(new ARField("type", ARInteger::instance(4)));
		$schema->registerField(new ARField("isRequired", ARBool::instance()));
		$schema->registerField(new ARField("isVisible", ARBool::instance()));
		$schema->registerField(new ARField("position", ARInteger::instance(4)));
	}

	/**
	 * Creates a new option instance that is assigned to a category
	 *
	 * @param Category $category
	 *
	 * @return Product
	 */
	public static function getNewInstanceByCategory(Category $category)
	{
		$option = parent::getNewInstance(__CLASS__);
		$option->category->set($category);

		return $option;
	}

	/**
	 * Creates a new option instance that is assigned to a product
	 *
	 * @param Category $category
	 *
	 * @return Product
	 */
	public static function getNewInstance(Product $product)
	{
		$option = parent::getNewInstance(__CLASS__);
		$option->product->set($product);

		return $option;
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

	/**
	 * Get products record set
	 *
	 * @param ARSelectFilter $filter
	 * @param bool $loadReferencedRecords
	 *
	 * @return ARSet
	 */
	public static function getRecordSet(ARSelectFilter $filter, $loadReferencedRecords = false)
	{
		return parent::getRecordSet(__CLASS__, $filter, $loadReferencedRecords);
	}

	/*####################  Value retrieval and manipulation ####################*/

	public function isBool()
	{
		return $this->type->get() == self::TYPE_BOOL;
	}

	public function isText()
	{
		return $this->type->get() == self::TYPE_TEXT;
	}

	public function isSelect()
	{
		return $this->type->get() == self::TYPE_SELECT;
	}

	public function addChoice(ProductOptionChoice $choice)
	{
		$relationship = ProductRelationship::getNewInstance($this, $product);
		$this->getRelationships()->add($relationship);
		$this->getRemovedRelationships()->removeRecord($relationship);
	}

	/*####################  Saving ####################*/

	protected function insert()
	{
	  	$this->setLastPosition();

		parent::insert();
	}

	public static function deleteByID($id)
	{
		return parent::deleteByID(__class__, $id);
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

	public function getChoiceSet()
	{
		$f = new ARSelectFilter();
		$f->setOrder(new ARFieldHandle('ProductOptionChoice', 'position'));

		return $this->getRelatedRecordSet('ProductOptionChoice', $f);
	}

}

?>