<?php

/**
 * Product image (icon). One product can have multiple images.
 *
 * @package application/model/product
 * @author Integry Systems <http://integry.com>
 */
class GalleryImage extends \ObjectImage
{
	public $productID;
	public static $imageSizes;
	
	public function getOwnerField()
	{
		return 'galleryID';
	}

	public function getOwnerClass()
	{
		return 'Gallery';
	}

	public static function getNewInstance(Gallery $product)
	{
	  	$image = new self();
	  	$image->set_Gallery($product);
	  	return $image;
	}

	public function getImageSizes()
	{
		if (!self::$imageSizes)
		{
			self::$imageSizes = array(array(200, 200), array(1000, 1000));
		}

		return self::$imageSizes;
	}

	protected function getImagePath($imageID, $productID, $size)
	{
		return $this->getImageRoot(__CLASS__) . $productID. '-' . $imageID . '-' . $size . '.jpg';
	}

	/*####################  Get related objects ####################*/

	public function setOwner(Gallery $product)
	{
		$this->set_Gallery($product);
	}

	public function getOwner()
	{
		return $this->get_Gallery();
	}
}

?>
