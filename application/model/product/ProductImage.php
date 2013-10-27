<?php

namespace product;

/**
 * Product image (icon). One product can have multiple images.
 *
 * @package application/model/product
 * @author Integry Systems <http://integry.com>
 */
class ProductImage extends \ObjectImage
{
	public $productID;
	public static $imageSizes;
	
	public function getOwnerField()
	{
		return 'productID';
	}

	public function getOwnerClass()
	{
		return 'product\Product';
	}

	public static function getNewInstance(Product $product)
	{
	  	$image = new self();
	  	$image->set_Product($product);
	  	return $image;
	}

	public function getImageSizes()
	{
		if (!self::$imageSizes)
		{
			$config = $this->getDI()->get('config');

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

	protected function getImagePath($imageID, $productID, $size)
	{
		return $this->getImageRoot(__CLASS__) . $productID. '-' . $imageID . '-' . $size . '.jpg';
	}

	public function resizeImage(\ImageManipulator $resizer)
	{
		$res = parent::resizeImage($resizer);

		$config = $this->getDI()->get('config');
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

	/*####################  Get related objects ####################*/

	public function setOwner(Product $product)
	{
		$this->set_Product($product);
	}

	public function getOwner()
	{
		return $this->get_Product();
	}
}

?>
