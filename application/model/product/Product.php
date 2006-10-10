<?php

ClassLoader::import("application.model.locale.Language");
ClassLoader::import("application.model.product.ProductLangData");

/**
 * Store product (item)
 *
 * @package application.model.product
 */
class Product extends MultilingualDataObject
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("Product");

		$schema->registerField(new ARPrimaryKeyField("ID", Integer::instance()));
		$schema->registerField(new ARForeignKeyField("catalogID", "Catalog", "ID", "Catalog", Integer::instance()));
		//todo shopinstance
		$schema->registerField(new ARField("sku", Varchar::instance(20)));

		$schema->registerField(new ARField("dateCreated", DateTime::instance()));
		$schema->registerField(new ARField("dateUpdated", DateTime::instance()));

		$schema->registerField(new ARField("status", Integer::instance(4)));
		$schema->registerField(new ARField("URL", Varchar::instance(256)));
		$schema->registerField(new ARField("isBestSeller", Bool::instance()));
		$schema->registerField(new ARField("type", Integer::instance(4)));

		$schema->registerField(new ArField("minimumQuantity", Integer::instance()));
		$schema->registerField(new ArField("shippingSurgageAmount", Float::instance(8)));
		$schema->registerField(new ArField("isSeparateShipment", Bool::instance()));
		$schema->registerField(new ArField("isFreeShipping", Bool::instance()));

		$schema->registerField(new ArField("shippingWidth", Float::instance(8)));
		$schema->registerField(new ArField("shippingHeight", Float::instance(8)));
		$schema->registerField(new ArField("shippingLength", Float::instance(8)));
		$schema->registerField(new ArField("shippingWeight", Float::instance(8)));
		$schema->registerField(new ArField("unitsType", Integer::instance()));
	}

	public static function getArrayFromArSet($arSet, $lang)
	{
		$array = $arSet->toArray();

		foreach($array as $key => $value)
		{
			foreach($value['lang'][$lang] as $key2 => $value2)
			{
				$array[$key][$key2] = $value2;
			}
			unset($array[$key]['lang']);
		}

		return $array;
	}

	/**
	 * Gets prices array of product (key => currency, value => price)
	 * @return array
	 */
	public function getPriceArray()
	{
		$priceArray = array();
		$filter = new ArSelectFilter();
		$filter->setCondition(new EqualsCond(new ARFieldHandle("ProductPrice", "productID"), $this->getId()));
		$priceSet = ActiveRecord::getRecordSet("ProductPrice", $filter);

		foreach($priceSet as $price)
		{
			$priceArray[$price->currency->get()->getId()] = $price->price->get();
		}
		return $priceArray;
	}

	/**
	 * @return ArSet
	 */
	public function getDiscountsSet()
	{
		$filter = new ArSelectFilter();
		$filter->setCondition(new EqualsCond(new ArFieldHandle("Discount", "productID"), $this->getId()));
		$filter->setOrder(new ARFieldHandle("Discount", "amount"));
		$discountSet = ActiveRecord::getRecordSet("Discount", $filter, false);
		return $discountSet;
	}

	/**
	 * Gets product imagesSet
	 * @return ArSet
	 */
	public function getImagesSet()
	{
		$filter = new ArSelectFilter();
		$filter->setCondition(new EqualsCond(new ArFieldHandle("ProductImage", "productID"), $this->getId()));
		$filter->setOrder(new ARFieldHandle("ProductImage", "position"));
		$arSet = ProductImage::getRecordSet("ProductImage", $filter, false);

		return $arSet;
	}

	/**
	 * @return int
	 */
	public static function getProductsCount()
	{
		$schema = ActiveRecord::getSchemaInstance("Product");

		$db = ActiveRecord::GetDbConnection();
		$res = $db->executeQuery("SELECT count(id) AS products_count FROM ".$schema->getName());
		$res->next();
		return (int)$res->getInt("products_count");
	}

	/**
	 * Counts products images.
	 * @return int
	 */
	public function getImagesCount()
	{
		$schema = ActiveRecord::getSchemaInstance("ProductImage");
		$tableName = $schema->getName();

		$db = ActiveRecord::getDbConnection();

		$res = $db->executeQuery("SELECT count(ID) AS sum FROM ".$tableName." WHERE productID = ".$this->getId());
		$res->next();

		return (int)$res->getInt("sum");
	}

	/**
	 * @return int
	 */
	public function getMaxImagePosition()
	{
		$schema = ActiveRecord::getSchemaInstance("ProductImage");
		$tableName = $schema->getName();

		$db = ActiveRecord::getDbConnection();

		$res = $db->executeQuery("SELECT max(position) AS max FROM ".$tableName." WHERE productID = ".$this->getId());
		$res->next();

		return (int)$res->getInt("max");
	}
}

?>
