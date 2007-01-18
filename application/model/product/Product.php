<?php

ClassLoader::import("application.model.system.Language");
ClassLoader::import("application.model.system.MultilingualObject");

/**
 * Store product (item)
 *
 * @package application.model.product
 */
class Product extends MultilingualObject
{
	private static $multilingualFields = array("name", "shortDescription", "longDescription");

	protected $specFieldData = array();

	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("Product");

		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("categoryID", "Category", "ID", null, ARInteger::instance()));

		$schema->registerField(new ARField("name", ARArray::instance()));
		$schema->registerField(new ARField("shortDescription", ARArray::instance()));
		$schema->registerField(new ARField("longDescription", ARArray::instance()));

		$schema->registerField(new ARField("sku", ARVarchar::instance(20)));

		$schema->registerField(new ARField("dateCreated", ARDateTime::instance()));
		$schema->registerField(new ARField("dateUpdated", ARDateTime::instance()));

		$schema->registerField(new ARField("isEnabled", ARBool::instance()));
		$schema->registerField(new ARField("URL", ARVarchar::instance(256)));
		$schema->registerField(new ARField("isBestSeller", ARBool::instance()));
		$schema->registerField(new ARField("type", ARInteger::instance(4)));

		$schema->registerField(new ArField("minimumQuantity", ARInteger::instance()));
		$schema->registerField(new ArField("shippingSurchargeAmount", ARFloat::instance(8)));
		$schema->registerField(new ArField("isSeparateShipment", ARBool::instance()));
		$schema->registerField(new ArField("isFreeShipping", ARBool::instance()));

		$schema->registerField(new ArField("shippingWidth", ARFloat::instance(8)));
		$schema->registerField(new ArField("shippingHeight", ARFloat::instance(8)));
		$schema->registerField(new ArField("shippingLength", ARFloat::instance(8)));
		$schema->registerField(new ArField("shippingWeight", ARFloat::instance(8)));
		$schema->registerField(new ArField("unitsType", ARInteger::instance()));
	}

	/**
	 * Inserts new product record to a database
	 *
	 */
	protected function insert()
	{
		ActiveRecordModel::beginTransaction();
		try
		{
			parent::insert();

			$category = $this->category->get();
			$categoryPathNodes = $category->getPathNodeSet(Category::INCLUDE_ROOT_NODE);
			// Adding current category to a record set of path
			$categoryPathNodes->add($category);

			foreach ($categoryPathNodes as $categoryNode)
			{
				$categoryNode->totalProductCount->set('totalProductCount + 1');
				if ($this->isEnabled->get() == true)
				{
					$categoryNode->activeProductCount->set('activeProductCount + 1');
				}
				$categoryNode->save();
			}
			ActiveRecordModel::commit();
		}
		catch (Exception $e)
		{
			ActiveRecordModel::rollback();
			throw $e;
		}
	}

	/**
	 * Updates product record
	 *
	 */
	protected function update()
	{
		ActiveRecordModel::beginTransaction();
		try
		{
			parent::update();
			if ($this->isEnabled->isModified())
			{
				if ($this->isEnabled->get() == true)
				{
					$productCountStr = 'activeProductCount + 1';
				}
				else
				{
					$productCountStr = 'activeProductCount - 1';
				}

				$category = $this->category->get();
				$categoryPathNodes = $category->getPathNodeSet(Category::INCLUDE_ROOT_NODE);
				$categoryPathNodes->add($category);

				foreach ($categoryPathNodes as $categoryNode)
				{
					$categoryNode->activeProductCount->set($productCountStr);
					$categoryNode->save();
				}
			}
			ActiveRecordModel::commit();
		}
		catch (Exception $e)
		{
			ActiveRecord::rollback();
			throw $e;
		}
	}

	protected function miscRecordDataHandler($miscRecordDataArray)
	{
		foreach ($miscRecordDataArray as $key => $value)
		{
			if (substr($key, 0, 10) == 'specField_')
			{
			  	$key = substr($key, 10);
			  	$this->specFieldData[$key] = $value;
			}
		}
	}

	public function toArray()
	{
	  	$array = parent::toArray();
	  	$array['specFieldData'] = $this->specFieldData;
	}

	/**
	 * Removes a product from a database
	 *
	 * @param int $recordID
	 * @return bool
	 * @throws Exception
	 */
	public static function deleteByID($recordID)
	{
		ActiveRecordModel::beginTransaction();
		try
		{
			$product = Product::getInstanceByID($recordID, Product::LOAD_DATA);
			$category = $product->category->get();

			parent::deleteByID(__CLASS__, $recordID);
			ActiveRecordModel::commit();
			return true;
		}
		catch (Exception $e)
		{
			ActiveRecordModel::rollback();
			throw $e;
		}
	}

	public static function getInstanceByID($recordID, $loadRecordData = false, $loadReferencedRecords = false)
	{
		return parent::getInstanceByID(__CLASS__, $recordID, $loadRecordData, $loadReferencedRecords);
	}

	/**
	 * Creates a new product instance
	 *
	 * @param Category $category
	 */
	public static function getNewInstance(Category $category)
	{
		$product = parent::getNewInstance(__CLASS__);
		$product->category->set($category);

		return $product;
	}

	/**
	 * @todo implement
	 *
	 */
	public function getImageArray()
	{
	}

	/**
	 * @todo implement
	 *
	 */
	public function getImageSet()
	{
	}

	/**
	 * Gets a product specification instance
	 *
	 * @return ProductSpecification
	 */
	public function getSpecification()
	{
		$specification = new ProductSpecification($this);
		return $specification;
	}

}

?>