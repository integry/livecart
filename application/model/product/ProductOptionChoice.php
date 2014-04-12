<?php

namespace product;

/**
 * One of the main entities of the system - defines and handles product related logic.
 * This class allows to assign or change product attribute values, product files, images, related products, etc.
 *
 * @package application/model/product
 * @author Integry Systems <http://integry.com>
 */
class ProductOptionChoice extends \system\MultilingualObject
{
	public $ID;
	public $priceDiff;
	public $hasImage;
	public $position;
	public $name;
	public $config;

	public function initialize()
	{
		$this->belongsTo('optionID', 'product\ProductOption', 'ID', array('alias' => 'ProductOption'));
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
		$choice = new self();
		$choice->productOption = $option;

		return $choice;
	}

	/*####################  Value retrieval and manipulation ####################*/

	public function getPriceDiff(\Currency $currency, $basePrice = false)
	{
		$basePrice = false === $basePrice ? $this->priceDiff : $basePrice;
		return ProductPrice::convertPrice($currency, $basePrice);
	}

	public function setColor($color)
	{
		$config = unserialize($this->config);
		$config['color'] = $color;
		$this->config = serialize($config);
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
