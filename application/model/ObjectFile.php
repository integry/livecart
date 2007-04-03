<?php

ClassLoader::import("application.model.system.MultilingualObject");
ClassLoader::import("library.image.ImageManipulator");

class ObjectFileException extends ApplicationException { }

abstract class ObjectFile extends MultilingualObject
{    		
	private $sourceFilePath = false;
    
    public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName($className);

		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARField("fileName", ARText::instance()));
		$schema->registerField(new ARField("extension", ARText::instance()));
		
		return $schema;
	}    
	
	public function delete()
	{
	    unlink($this->getPath());
	    
	    parent::delete();
	}

	public function getPath()
	{
	    if(!$this->isExistingRecord()) throw new ObjectFileException('Instance has no existing database record');
	    
	    return ClassLoader::getRealPath('storage.' . strtolower(get_class($this))) . DIRECTORY_SEPARATOR . $this->getID() . '.' . $this->extension->get();
    }
    
    public static function getNewInstance($className, $sourceFilePath, $fileName)
    {
        $fileInstance = parent::getNewInstance($className);
        $fileInstance->sourceFilePath = $sourceFilePath;
        
        // write to database
        $fileInfo = pathinfo($fileName);
        $fileInstance->fileName->set($fileInfo['filename']);
        $fileInstance->extension->set($fileInfo['extension']);
        
        $productFileCategoryPath = ClassLoader::getRealPath('storage.' . strtolower($className));
        if(!is_dir($productFileCategoryPath)) mkdir($productFileCategoryPath);

        return $fileInstance;
    }
    
    protected function moveFile()
    {
        copy($this->sourceFilePath, $this->getPath());
    }
    
    public function save($forceOperation = false)
    {
        parent::save($forceOperation);
        
        $this->moveFile();
    }
}

?>