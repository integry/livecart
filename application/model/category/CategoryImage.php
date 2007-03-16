<?php

ClassLoader::import("application.model.system.MultilingualObject");

/**
 *
 * @package application.model.category
 */
class CategoryImage extends MultilingualObject
{
	public static $imageSizes = array(0 => array(50, 80),
								1 => array(80, 150),
								2 => array(300, 400),
								);

	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName("CategoryImage");

		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("categoryID", "Category", "ID", "Category", ARInteger::instance()));
		$schema->registerField(new ARField("title", ARArray::instance()));
		$schema->registerField(new ARField("position", ARInteger::instance()));
	}
	
	public static function getNewInstance(Category $category)
	{
	  	$catImage = ActiveRecord::getNewInstance('CategoryImage');
	  	$catImage->category->set($category);
	  	return $catImage;
	}
	
	public function getPath($size = 0)
	{
		if (!$this->isLoaded)
		{
            $this->load();    
        }   
        
	  	return self::getImagePath($this->getID(), $this->category->get()->getID(), $size);
	}
	
	public function deleteImageFiles()
	{
		foreach ($this->imageSizes as $key => $value)
	  	{
			unlink($this->getPath($key));					
		}			
	}
	
	protected static function getImagePath($imageID, $categoryID, $size)
	{
        return 'upload/categoryimage/' . $categoryID. '-' . $imageID . '-' . $size . '.jpg';
    }
	
	public static function transformArray($array)
	{
		$array = parent::transformArray($array, __CLASS__);
        
        $array['paths'] = array();
		foreach (self::$imageSizes as $key => $value)
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

	public static function deleteByID($id)
	{
		$inst = ActiveRecord::getInstanceById('CategoryImage', $id, true);
		$inst->deleteImageFiles();
		return ActiveRecord::deleteByID('CategoryImage', $id);
	}	
	
	public function resizeImage(ImageManipulator $resizer)
	{
	  	$publicRoot = ClassLoader::getRealPath('public') . '/';
		  
		foreach ($this->imageSizes as $key => $size)
	  	{
			$filePath = $publicRoot . $this->getPath($key);
			$res = $resizer->resize($size[0], $size[1], $filePath);
			if (!$res)
			{
			  	break;
			}
		}
		
		return $res;	  
	}	
	
	protected function insert()
	{
	  	// get current max image position
	  	$filter = new ARSelectFilter();
	  	$filter->setCondition(new EqualsCond(new ARFieldHandle('CategoryImage', 'categoryID'), $this->category->get()->getID()));
	  	$filter->setOrder(new ARFieldHandle('CategoryImage', 'position'), 'DESC');
	  	$filter->setLimit(1);
	  	$maxPosSet = ActiveRecord::getRecordSet('CategoryImage', $filter);
		if ($maxPosSet->size() > 0)
		{
			$maxPos = $maxPosSet->get(0)->position->get() + 1;  	
		}
		else
		{
		  	$maxPos = 0;
		}			  

		$this->position->set($maxPos);
		
		parent::insert();
	}

	public static function countItems(Category $category)
	{
        return $category->getCategoryImagesSet()->getTotalRecordCount();
	}
}

?>