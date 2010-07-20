<?php

ClassLoader::import('application.model.ObjectImage');

/**
 * Product image (icon). One product can have multiple images.
 *
 * @package application.model.product
 * @author Integry Systems <http://integry.com>
 */
class ProductImage extends ObjectImage
{
	public static $imageSizes;

	public static function defineSchema($className = __CLASS__)
	{
		$schema = parent::defineSchema($className);
		$schema->registerField(new ARForeignKeyField("productID", "Product", "ID", "Product", ARInteger::instance()));
	}

	/*####################  Static method implementations ####################*/

	public static function getNewInstance(Product $product)
	{
	  	$image = ActiveRecord::getNewInstance(__CLASS__);
	  	$image->product->set($product);
	  	return $image;
	}

	/*####################  Value retrieval and manipulation ####################*/

	public function getPath($size = 0)
	{
		if (!$this->isLoaded)
		{
			$this->load(array('Product'));
		}

		return self::getImagePath($this->getID(), $this->product->get()->getID(), $size);
	}

	public static function getImageSizes()
	{
		if (!self::$imageSizes)
		{
			$config = self::getApplication()->getConfig();

			$sizes = array();
			$k = 0;
			while ($config->isValueSet('IMG_P_W_' . ++$k))
			{
				$sizes[$k] = array($config->get('IMG_P_W_' . $k), $config->get('IMG_P_H_' . $k));
			}

			self::$imageSizes = $sizes;
		}

		return self::$imageSizes;
	}

	protected static function getImagePath($imageID, $productID, $size)
	{
		return self::getImageRoot(__CLASS__) . $productID. '-' . $imageID . '-' . $size . '.jpg';
	}

	/*####################  Saving ####################*/

	public static function deleteByID($id)
	{
		parent::deleteByID(__CLASS__, $id, 'productID');
	}

	protected function insert()
	{
		return parent::insert('productID');
	}

	/*####################  Data array transformation ####################*/
	public static function transformArray($array, ARSchema $schema)
	{
		return parent::transformArray($array, $schema, 'Product', 'productID');
	}

	/*####################  Get related objects ####################*/

	public function setOwner(Product $product)
	{
		$this->product->set($product);
	}

	public function getOwner()
	{
		return $this->product->get();
	}
}

?>
