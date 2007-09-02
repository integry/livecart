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
		
	/*####################  Static method implementations ####################*/		
		
	public static function getNewInstance(Category $category)
	{
	  	$catImage = ActiveRecord::getNewInstance(__CLASS__);
	  	$catImage->category->set($category);
	  	return $catImage;
	}
	
	/*####################  Value retrieval and manipulation ####################*/	
	
    public function getPath($size = 0)
	{
		if (!$this->isLoaded)
		{
            $this->load();    
        }   
        
	  	return self::getImagePath($this->getID(), $this->category->get()->getID(), $size);
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
    	
	protected static function getImagePath($imageID, $categoryID, $size)
	{
        return 'upload/categoryimage/' . $categoryID. '-' . $imageID . '-' . $size . '.jpg';
    }
    
	/*####################  Saving ####################*/	
	
	public static function deleteByID($id)
	{
        parent::deleteByID(__CLASS__, $id, 'categoryID');
    }
	
    protected function insert()
    {
        return parent::insert('categoryID');
    }
	
	/*####################  Data array transformation ####################*/	
	
	public static function transformArray($array, ARSchema $schema)
	{
		$array = parent::transformArray($array, $schema);
        
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

	/*####################  Get related objects ####################*/    
    
    public function getOwner()
    {
		return $this->category->get();
	}			
}

?>