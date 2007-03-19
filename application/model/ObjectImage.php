<?php

ClassLoader::import("application.model.system.MultilingualObject");

abstract class ObjectImage extends MultilingualObject
{
	abstract public static function getImageSizes();
	abstract public function getOwner();
		
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName($className);

		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARField("title", ARArray::instance()));
		$schema->registerField(new ARField("position", ARInteger::instance()));
		
		return $schema;
	}    
	
	public function deleteImageFiles()
	{
		foreach ($this->getImageSizes() as $key => $value)
	  	{
			unlink($this->getPath($key));					
		}			
	}

	public static function deleteByID($className, $id)
	{
		$inst = ActiveRecord::getInstanceById($className, $id, true);
		$inst->deleteImageFiles();
		return ActiveRecord::deleteByID($className, $id);
	}	
	
	public function resizeImage(ImageManipulator $resizer)
	{
	  	$publicRoot = ClassLoader::getRealPath('public') . '/';
		  
		foreach ($this->getImageSizes() as $key => $size)
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
	
	protected function insert($foreignKeyName)
	{
	  	$className = get_class($this);
          
        // get current max image position
	  	$filter = new ARSelectFilter();
	  	$filter->setCondition(new EqualsCond(new ARFieldHandle($className, $foreignKeyName), $this->getOwner()->getID()));
	  	$filter->setOrder(new ARFieldHandle($className, 'position'), 'DESC');
	  	$filter->setLimit(1);
	  	$maxPosSet = ActiveRecord::getRecordSet($className, $filter);
		if ($maxPosSet->size() > 0)
		{
			$maxPos = $maxPosSet->get(0)->position->get() + 1;  	
		}
		else
		{
		  	$maxPos = 0;
		}			  

		$this->position->set($maxPos);
		
		return parent::insert();
	}	    	
}

?>