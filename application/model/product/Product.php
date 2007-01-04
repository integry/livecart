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

		$schema->registerField(new ARField("status", ARInteger::instance(4)));
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
	 * Saves product data and increases productCount in related categories
	 *
	 */
	public function save()
	{
		ActiveRecordModel::beginTransaction();
		try
		{
			$category = $this->category->get();
			$categoryPathNodes = $category->getPathNodeSet(Category::INCLUDE_ROOT_NODE);

			foreach ($categoryPathNodes as $categoryNode)
			{
				$categoryNode->productCount->set($categoryNode->productCount->get() + 1);
				$categoryNode->save();
			}
			parent::save();
			ActiveRecordModel::commit();
		}
		catch (Exception $e)
		{
			ActiveRecord::rollback();
			throw $e;
		}
	}

	public static function getInstanceByID($recordID, $loadRecordData = false, $loadReferencedRecords = false)
	{
		return parent::getInstanceByID(__CLASS__, $recordID, $loadRecordData, $loadReferencedRecords);
	}
}

?>