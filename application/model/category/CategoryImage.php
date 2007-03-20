<?php


/**
 *
 * @package application.model.category
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
	   return array(0 => array(50, 80),
								1 => array(80, 150),
								2 => array(300, 400),
								);   
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