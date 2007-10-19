<?php

/**
 * Page template file logic - saving and retrieving template code.
 *
 * There are two sets of template files active at the same time:
 *	
 *		1) application.view - default view template files
 *		2) storage.customize - edited template files.
 *
 * This system allows to modify template files without overwriting the existing ones, among other benefits.
 *
 * @package application.model.template
 * @author Integry Systems <http://integry.com>
 */
class Template
{
	protected $code;
	
	protected $file;
	
	public function __construct($fileName)
	{
		$path = self::getRealFilePath($fileName);
		if (file_exists($path))
		{
			$this->code = file_get_contents($path);
		}
		
		$this->file = $fileName;
	}

    public static function getTree($dir = null)
    {
	  	if (!$dir)
	  	{
			$dir = ClassLoader::getRealPath('application.view.');
		}
		
		$rootLn = strlen(ClassLoader::getRealPath('application.view.'));
		
		$res = array();
		$d = new DirectoryIterator($dir);
		
		foreach ($d as $file)
		{
			if (!$file->isDot())
			{
                $id = substr($file->getPathName(), $rootLn);
                
                if ($file->isDir())
    			{
                    $dir = self::getTree($file->getPathName());
                    if ($dir)
                    {
                        $res[$file->getFileName()]['id'] = $id;
                        $res[$file->getFileName()]['subs'] = $dir;
                    }
                }
                elseif (substr($file->getFileName(), -4) == '.tpl')
                {
                    $res[$file->getFileName()]['id'] = $id;
                }                
            }
		}
		
		return $res;        
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
	
	private function checkForChanges()
	{
        $l = str_replace("\r\n", "\n", file_get_contents(self::getCustomizedFilePath($this->file)));
        $r = str_replace("\r\n", "\n", file_get_contents(self::getOriginalFilePath($this->file)));
        
        if ($l == $r)
        {
            $this->restoreOriginal();
        }
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
		
		$this->checkForChanges();
		
		return $res !== false;		
	}

	public function restoreOriginal()
	{
		$path = self::getCustomizedFilePath($this->file);
		if (!file_exists($path))
		{
			return true;
		}
		
		$cacheDir = ClassLoader::getRealPath('cache.templates_c.customize');
		if (is_dir($cacheDir))
		{
            foreach (new DirectoryIterator($cacheDir) as $file)
            {
                if (!$file->isDot())
                {
                    unlink($file->getPathname());
                }
            }
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