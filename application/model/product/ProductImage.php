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
	public static function defineSchema($className = __CLASS__)
	{
		$schema = parent::defineSchema($className);
		$schema->registerField(new ARForeignKeyField("productID", "Product", "ID", "Product", ARInteger::instance()));
	}
		
	public static function getImageSizes()
	{
        $config = Config::getInstance();
     
        $sizes = array();
        $k = 0;
        while ($config->isValueSet('IMG_P_W_' . ++$k))
        {
            $sizes[$k] = array($config->getValue('IMG_P_W_' . $k), $config->getValue('IMG_P_H_' . $k));
        }

        return $sizes;
    }
		
    public function getOwner()
    {
		return $this->product->get();
	}	
		
	public static function getNewInstance(Product $product)
	{
	  	$image = ActiveRecord::getNewInstance(__CLASS__);
	  	$image->product->set($product);
	  	return $image;
	}
	
	public static function deleteByID($id)
	{
        parent::deleteByID(__CLASS__, $id, 'productID');
    }
	
    public function getPath($size = 0)
	{
		if (!$this->isLoaded)
		{
            $this->load(array('Product'));    
        }   
        
		return self::getImagePath($this->getID(), $this->product->get()->getID(), $size);
	}
		
	protected static function getImagePath($imageID, $productID, $size)
	{
        return 'upload/productimage/' . $productID. '-' . $imageID . '-' . $size . '.jpg';
    }
	
	public static function transformArray($array)
	{
		$array = parent::transformArray($array, __CLASS__);
        
        $array['paths'] = array();
		foreach (self::getImageSizes() as $key => $value)
	  	{
			$productID = isset($array['Product']['ID']) ? $array['Product']['ID'] : (isset($array['productID']) ? $array['productID'] : false);
			
			if (!$productID)
			{
                break;
            }
			
            $array['paths'][$key] = self::getImagePath($array['ID'], $productID, $key);
		}

		return $array;	  	
	}
	
    protected function insert()
    {
        return parent::insert('productID');
    }

/*
	public static function countItems(Product $category)
	{
        return $category->getCategoryImagesSet()->getTotalRecordCount();
	}
*/
}

?>