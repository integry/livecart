<?php

ClassLoader::import("application.model.system.Language");
//ClassLoader::import("application.model.product.ProductLangData");

/**
 * Store product (item)
 *
 * @package application.model.product
 */
class Product extends MultilingualObject
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("Product");

		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("catalogID", "Catalog", "ID", "Catalog", ARInteger::instance()));
		//todo shopinstance
		$schema->registerField(new ARField("sku", ARVarchar::instance(20)));

		$schema->registerField(new ARField("dateCreated", ARDateTime::instance()));
		$schema->registerField(new ARField("dateUpdated", ARDateTime::instance()));

		$schema->registerField(new ARField("status", ARInteger::instance(4)));
		$schema->registerField(new ARField("URL", ARVarchar::instance(256)));
		$schema->registerField(new ARField("isBestSeller", ARBool::instance()));
		$schema->registerField(new ARField("type", ARInteger::instance(4)));

		$schema->registerField(new ArField("minimumQuantity", ARInteger::instance()));
		$schema->registerField(new ArField("shippingSurgageAmount", ARFloat::instance(8)));
		$schema->registerField(new ArField("isSeparateShipment", ARBool::instance()));
		$schema->registerField(new ArField("isFreeShipping", ARBool::instance()));

		$schema->registerField(new ArField("shippingWidth", ARFloat::instance(8)));
		$schema->registerField(new ArField("shippingHeight", ARFloat::instance(8)));
		$schema->registerField(new ArField("shippingLength", ARFloat::instance(8)));
		$schema->registerField(new ArField("shippingWeight", ARFloat::instance(8)));
		$schema->registerField(new ArField("unitsType", ARInteger::instance()));
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
