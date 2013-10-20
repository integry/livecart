<?php



/**
 *
 * @package application/model/category
 * @author Integry Systems
 */
class ThemeFile
{
	private $theme;

	public static function getNewInstance($theme)
	{
		static $instance = null;

		if($instance == null)
		{
			$instance = new ThemeFile($theme);
		}

		return $instance;
	}

	/**
	 *
	 * @param string $key in $_FILES array
	 * @param string $filename user given filename
	 * @param string $orginalFilename existing file name (only when editing file)
	 */
	public function processFileUpload($key, $filename=null, $orginalFilename=null)
	{
		if ((strtolower(substr($filename, -3)) == 'php') || (strtolower(substr($orginalFilename, -3)) == 'php'))
		{
			return;
		}

		$result = array();
		if(array_key_exists($key, $_FILES) == false)
		{
			return null;
		}
		$file = $_FILES[$key];

		if (!$file['name'] || $file['error'] != 0)
		{
			// if no file is uploaded, but changed file name field, then rename
			if($orginalFilename && $filename && $filename != $orginalFilename)
			{
				$filename = $this->versionedFileName($filename);
				rename($this->path.$orginalFilename, $this->path.$filename);
				$this->removeThumbnail($orginalFilename);
				$this->createThumbnail($filename);
			}
			// and done
			return array('filename' => $filename);
		}

		if ($filename != null)
		{
			$file['name'] = $filename;
		}

		if($orginalFilename) // editing existing file.
		{
			if($orginalFilename != $file['name']) // new file has different name, remove orginal file.
			{
				$this->removeFile($orginalFilename);
			}
		}

		$file['name'] = $this->versionedFileName($file['name']);
		move_uploaded_file($file['tmp_name'], $this->path . $file['name']);
		$this->createThumbnail($file['name']);
		return array('filename'=>$file['name']);
	}


	private function createThumbnail($fileName)
	{
		$im = new ImageManipulator($this->path.$fileName);
		if($im->isValidImage())
		{
			$size = $this->getThumbnailSize();
			$res = $im->resize($size[0], $size[1], $this->thumbnailPath.$fileName);
			return $res;
		}
		return false;
	}

	public function __construct($theme)
	{
		if(!$theme || is_string($theme) == false || strpos($theme, '..') !== false || strpos($theme,'\\') !== false || strpos($theme,'/') !== false || (strpos($theme,'.') !== false && strlen($theme) == 1))
		{
			throw new Exception('Illegal theme name');
		}
		$this->theme = $theme;
		$this->path = $this->config->getPath('public/upload/theme/'.$this->theme.'.');
		$this->thumbnailPath = $this->config->getPath('public/upload/theme/'.$this->theme.'.thumbs.');

		if(file_exists($this->path) == false)
		{
			mkdir($this->path,0777, true);
		}
		if(file_exists($this->thumbnailPath) == false)
		{
			mkdir($this->thumbnailPath, 0777, true);
		}

	}

	public function getFiles()
	{



		$files = array();
		$handle = opendir($this->path);
		if(!$handle)
		{
			// throw new Exception('cant open..');
			return $files;
		}
		while (false !== ($file = readdir($handle)))
		{
			if(is_file($this->path.$file))
			{
				$hasThumbnail = file_exists($this->thumbnailPath.$file);
				if ($hasThumbnail == false)
				{
					$tr = $this->createThumbnail($file);
					if($tr != false)
					{
						$hasThumbnail = true;
					}
				}

				$files[] = array(
					'ID' => $file,
					'fn' => $file,
					'fs' => filesize($this->path.$file),
					'theme'=>$this->theme,
					'hasThumbnail' => $hasThumbnail
				);
			}
		}
		sort($files);
		return $files;
	}

	public function removeFile($fn)
	{
		$fullName = $this->path.$fn;
		if(is_file($fullName) && is_readable($fullName))
		{
			unlink($fullName);
			$this->removeThumbnail($fn);
			return true;
		}
		return false;
	}

	private function removeThumbnail($fileName)
	{
		$fullName = $this->thumbnailPath.$fileName;
		if(is_file($fullName) && is_readable($fullName))
		{
			unlink($fullName);
			return true;
		}
		return false;
	}

	private function versionedFileName($fileName)
	{
		$withoutExtension = strpos($fileName,'.') === false;
		$fullFileName = $this->path . $fileName;

		while(file_exists($fullFileName) == true)
		{
			// file versioning examples:
			//  normal:                foo.txt, foo.1.txt, foo.2.txt;
			//  without extension:     foo,     foo.1,     foo.2
			//       also for numeric: 100,     100.1,     100.2
			//  numeric filename:      100.txt, 101.txt,   102.txt
			$chunks = explode('.', $fileName);
			if($withoutExtension)
			{
				$ext = null;
				if(count($chunks) == 1)
				{
					$version = 0; // only <filename>
				}
				else
				{
					$version = array_pop($chunks); // <filename>.<version>
				}
			}
			else
			{
				$ext = array_pop($chunks);
				$version = array_pop($chunks);
			}
			if(is_numeric($version))
			{
				$version++;
			}
			else
			{
				// file without version
				$chunks[] = $version;
				$version=1;
			}
			$chunks[] = $version;
			if($ext !== null)
			{
				$chunks[] = $ext;
			}
			$fileName = implode('.',$chunks);
			$fullFileName = $this->path . $fileName;
		}

		return $fileName;
	}

	public static function getThumbnailSize()
	{
		$config = ActiveRecordModel::getApplication()->getConfig();
		$sizes = array();
		$k = 0;
		while ($config->has('IMG_O_W_' . ++$k))
		{
			$sizes[$k] = array($config->get('IMG_O_W_' . $k), $config->get('IMG_O_H_' . $k));
			$sizes[$k]['area'] = $sizes[$k][0] * $sizes[$k][1];
		}
		// find smaller
		$smallest = array_shift($sizes);

		while($t = array_shift($sizes))
		{
			if($t['area'] < $smallest['area'])
			{
				$smallest = $t;
			}
		}
		unset($smallest['area']);
		return $smallest;
	}
}

?>