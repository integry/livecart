<?php

class Template
{
	private $code;
	
	private $file;
	
	public function __construct($fileName)
	{
		$path = self::getRealFilePath($fileName);
		if (file_exists($path))
		{
			$this->code = file_get_contents($path);
		}
		
		$this->file = $fileName;
	}

	public static function getRealFilePath($fileName)
	{
		$paths = array();
		$paths[] = self::getCustomizedFilePath($fileName);
		$paths[] = self::getOriginalFilePath($fileName);
		
		foreach ($paths as $path)
		{
			if (file_exists($path))
			{
				return $path;
			}
		}
	}

	public static function getOriginalFilePath($fileName)
	{
		return ClassLoader::getRealPath('application.view.') . $fileName;
	}

	public static function getCustomizedFilePath($fileName)
	{
		return ClassLoader::getRealPath('storage.customize.view.') . $fileName;		
	}

	public function setCode($code)
	{
		$this->code = $code;
	}	
	
	public function getCode()
	{
	 	return $this->code;
	}
	
	public function getFileName()
	{
		return $this->file;
	}
	
	public function save()
	{
		$path = self::getCustomizedFilePath($this->file);
		
		$dir = dirname($path);
		if (!is_dir($dir))
		{
			mkdir($dir, 0777, true);
			chmod($dir, 0777);
		}
		
		$res = file_put_contents($path, $this->code);
		return $res !== false;		
	}

	public function restoreOriginal()
	{
		$path = self::getCustomizedFilePath($this->file);
		if (!file_exists($path))
		{
			return true;
		}
		
		return unlink($path);
	}

	public function toArray()
	{
		$array = array();
		$array['code'] = $this->code;
		$array['file'] = $this->file;
		$array['isCustomized'] = file_exists(self::getCustomizedFilePath($this->file));
		return $array;				
	}
}

?>