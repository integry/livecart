<?php

ClassLoader::import("application.model.system.MultilingualObject");

class ObjectFileException extends ApplicationException { }

/**
 * Generic associated file handler. Files can be associated to products, articles and possibly
 * other entities in the future
 *
 * @package application.model
 * @author Integry Systems <http://integry.com>   
 */
class ObjectFile extends MultilingualObject
{			
	private $sourceFilePath = false;
	private $newFileUploaded = false;
	
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName($className);

		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARField("fileName", ARText::instance()));
		$schema->registerField(new ARField("extension", ARText::instance()));
		
		return $schema;
	}	

	public static function getNewInstance($className, $sourceFilePath, $fileName)
	{
		$fileInstance = parent::getNewInstance($className);
		$fileInstance->storeFile($sourceFilePath, $fileName);
		
		return $fileInstance;
	}

	public function setBaseName($baseName) 
	{				
		$this->newFileUploaded = true;

		// write to database
		$fileInfo = pathinfo($baseName);
		$this->fileName->set($fileInfo['filename']);
		$this->extension->set($fileInfo['extension']);
	}
	
	public function delete()
	{
		$this->deleteFile();
		parent::delete();
	}
	
	public function deleteFile()
	{
		$path = $this->getPath();
		if(is_file($path)) 
		{
			return unlink($path);
		}
		else
		{
			return false;
		}
	}
	
	public function storeFile($sourceFilePath, $fileName) 
	{
		$this->setBaseName($fileName);
		$this->sourceFilePath = $sourceFilePath;
	}
	
	public function save($forceOperation = false)
	{
		parent::save($forceOperation);
		
		if($this->newFileUploaded) $this->moveFile();
	}

	public function getPath()
	{
		if(!$this->isExistingRecord()) 
		{
			if ($this->sourceFilePath)
			{
				return $this->sourceFilePath;
			}
			else
			{
				throw new ObjectFileException('Instance has no existing database record');
			}
		}
		
		return ClassLoader::getRealPath('storage.' . strtolower(get_class($this))) . DIRECTORY_SEPARATOR . $this->getID();
	}
	
	public function getMimeType()
	{
		$baseMimeTypesFile = ClassLoader::getRealPath('application.configuration.fileType.base') . '.ini';
		$extendedMimeTypesFile = ClassLoader::getRealPath('application.configuration.fileType.extended') . '.ini';
		
		$baseTypes = is_file($baseMimeTypesFile) ? parse_ini_file($baseMimeTypesFile) : array();
		$extendedTypes = is_file($extendedMimeTypesFile) ? parse_ini_file($extendedMimeTypesFile) : array();
		
		$baseTypes = array_merge($baseTypes, $extendedTypes);
		
		return isset($baseTypes[$this->extension->get()]) ? $baseTypes[$this->extension->get()] : 'application/octet-stream';
	}

	public function getBaseName() 
	{
		return $this->fileName->get().'.'.$this->extension->get();
	}
	
	public function getSize()
	{
		return filesize($this->getPath());
	}
	
	public function getContents()
	{
		return file_get_contents($this->getPath());
	}

	private function moveFile()
	{   
		$productFileCategoryPath = ClassLoader::getRealPath('storage.' . strtolower(get_class($this)));
		if(!is_dir($productFileCategoryPath)) mkdir($productFileCategoryPath, 0777, true);
		
		copy($this->sourceFilePath, $this->getPath());
	}
}

?>