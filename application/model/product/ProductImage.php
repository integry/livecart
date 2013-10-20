<?php


/**
 * Product image (icon). One product can have multiple images.
 *
 * @package application/model/product
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
	  	$image = new self();
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

		return self::getImagePath($this->getID(), $this->product->getID(), $size);
	}

	public static function getImageSizes()
	{
		if (!self::$imageSizes)
		{
			$config = self::getApplication()->getConfig();

			$sizes = array();
			$k = 0;
			while ($config->has('IMG_P_W_' . ++$k))
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

	public function resizeImage(ImageManipulator $resizer)
	{
		$res = parent::resizeImage($resizer);

		$config = self::getApplication()->getConfig();
		if ($config->get('ENABLE_WATERMARKS') && $res[3])
		{
			$isLeft = in_array($config->get('WATERMARK_POSITION'), array('BOTTOM_LEFT', 'TOP_LEFT'));
			$isTop = in_array($config->get('WATERMARK_POSITION'), array('TOP_RIGHT', 'TOP_LEFT'));
			$x = $config->get('WATERMARK_X');
			$y = $config->get('WATERMARK_Y');

			if ('IMG_CENTER' == $config->get('WATERMARK_POSITION'))
			{
				$x = $y = null;
			}

			$res[3]->watermark($config->get('WATERMARK_IMAGE'), $isLeft, $isTop, $x, $y);
			$res[4]->watermark($config->get('WATERMARK_IMAGE'), $isLeft, $isTop, $x, $y);
		}

		return $res;
	}

	/*####################  Saving ####################*/

	public static function deleteByID($id)
	{
		parent::deleteByID(__CLASS__, $id, 'productID');
	}

	public function beforeCreate()
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
		return $this->product;
	}
}

?>
