<?php


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

		public $ID;
		public $fileName;
		public $extension;
		public $filePath;
		return $schema;
	}

	public static function getNewInstance($className, $sourceFilePath, $fileName, $pathOrUrl = null)
	{
		$fileInstance = parent::getNewInstance($className);

		$fileInstance->storeFile($sourceFilePath, $fileName, $pathOrUrl);

		return $fileInstance;
	}

	public function setBaseName($baseName)
	{
		// write to database
		$fileInfo = pathinfo($baseName);
		if (!empty($fileInfo['filename']))
		{
			$this->fileName = $fileInfo['filename']);
		}

		if (!empty($fileInfo['extension']))
		{
			$this->extension = $fileInfo['extension']);
		}
	}

	public function delete()
	{
		$this->deleteFile();
		parent::delete();
	}

	public function deleteFile()
	{
		$path = $this->getPath();
		if(is_file($path) && !$this->filePath->get())
		{
			return unlink($path);
		}
		else
		{
			return false;
		}
	}

	public function storeFile($sourceFilePath, $fileName, $pathOrUrl = null)
	{
		if (!$pathOrUrl)
		{
			$this->newFileUploaded = true;
		}

		$this->setBaseName($fileName ? $fileName : $pathOrUrl);
		$this->sourceFilePath = $sourceFilePath;
		$this->filePath = $pathOrUrl);
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

		if ($this->filePath->get())
		{
			return $this->filePath->get();
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
		return $this->isLocalFile() && $this->fileExists() ? filesize($this->getPath()) : null;
	}

	public function getContents()
	{
		return ($this->isLocalFile() && $this->fileExists()) ? file_get_contents($this->getPath()) : '';
	}

	public function isLocalFile()
	{
		$path = strtolower($this->filePath->get());
		return !((substr($path, 0, 7) == 'http://') || (substr($path, 0, 8) == 'https://'));
	}

	public function fileExists()
	{
		return file_exists($this->getPath());
	}

	private function moveFile()
	{
		$productFileCategoryPath = ClassLoader::getRealPath('storage.' . strtolower(get_class($this)));
		if(!is_dir($productFileCategoryPath)) mkdir($productFileCategoryPath, 0777, true);

		copy($this->sourceFilePath, $this->getPath());
	}
}

?>