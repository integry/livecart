<?php

ClassLoader::import('application.model.product.ProductOption');
ClassLoader::import('application.model.product.ProductPrice');

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

		public $ID;
		public $optionID", "ProductOption", "ID", "ProductOption;

		public $priceDiff;
		public $hasImage;
		public $position;
		public $name;
		public $config;

		$schema->registerCircularReference('Option', 'ProductOption');
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
		$choice->option = $option);

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

	public function getPriceDiff($currencyCode, $basePrice = false)
	{
		$basePrice = false === $basePrice ? $this->priceDiff->get() : $basePrice;
		return ProductPrice::convertPrice(Currency::getInstanceByID($currencyCode), $basePrice);
	}

	public function setColor($color)
	{
		$config = unserialize($this->config->get());
		$config['color'] = $color;
		$this->config = serialize($config));
	}

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

	public static function transformArray($array, ARSchema $schema)
	{
		$array = parent::transformArray($array, $schema);

		$array['formattedPrice'] = array();
		foreach (self::getApplication()->getCurrencySet() as $id => $currency)
		{
			$array['formattedPrice'][$id] = $currency->getFormattedPrice(self::getPriceDiff($id, $array['priceDiff']));
		}

		$array['config'] = unserialize($array['config']);

		return $array;
	}

}

?>