<?php

ClassLoader::import('application.model.ObjectImage');

/**
 * Category image (icon)
 * 
 * @package application.model.category
 * @author Integry Systems <http://integry.com>   
 */
class CategoryImage extends ObjectImage
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = parent::defineSchema($className);
		$schema->registerField(new ARForeignKeyField("categoryID", "Category", "ID", "Category", ARInteger::instance()));
	}
		
	public static function getImageSizes()
	{
        $config = self::getApplication()->getConfig();
     
        $sizes = array();
        $k = 0;
        while ($config->isValueSet('IMG_C_W_' . ++$k))
        {
            $sizes[$k] = array($config->get('IMG_C_W_' . $k), $config->get('IMG_C_H_' . $k));
        }

        return $sizes;
    }
    
    public function getOwner()
    {
		return $this->category->get();
	}
		
	public static function getNewInstance(Category $category)
	{
	  	$catImage = ActiveRecord::getNewInstance(__CLASS__);
	  	$catImage->category->set($category);
	  	return $catImage;
	}
	
	public static function deleteByID($id)
	{
        parent::deleteByID(__CLASS__, $id, 'categoryID');
    }
	
    public function getPath($size = 0)
	{
		if (!$this->isLoaded)
		{
            $this->load();    
        }   
        
	  	return self::getImagePath($this->getID(), $this->category->get()->getID(), $size);
	}
		
	protected static function getImagePath($imageID, $categoryID, $size)
	{
        return 'upload/categoryimage/' . $categoryID. '-' . $imageID . '-' . $size . '.jpg';
    }
	
	public static function transformArray($array)
	{
		$array = parent::transformArray($array, __CLASS__);
        
        $array['paths'] = array();
		foreach (self::getImageSizes() as $key => $value)
	  	{
			$categoryID = isset($array['Category']['ID']) ? $array['Category']['ID'] : (isset($array['categoryID']) ? $array['categoryID'] : false);
			
			if (!$categoryID)
			{
                break;
            }
			
            $array['paths'][$key] = self::getImagePath($array['ID'], $categoryID, $key);
		}

		return $array;	  	
	}
	
    protected function insert()
    {
        return parent::insert('categoryID');
    }

	public static function countItems(Category $category)
	{
        return $category->getCategoryImagesSet()->getTotalRecordCount();
	}
}

?>