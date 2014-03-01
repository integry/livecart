<?php

include_once __ROOT__ . '/library/image/ImageManipulator.php';

/**
 * Generic associated image handler. Images can be associated to products, categories and possibly
 * other entities in the future
 *
 * @package application/model
 * @author Integry Systems <http://integry.com>
 */
abstract class ObjectImage extends \system\MultilingualObject
{
	public $ID;
	public $title;
	public $position;

	abstract public function getImageSizes();
	abstract public function getOwner();
	abstract public function getOwnerField();
	abstract public function getOwnerClass();
	
	public function initialize()
	{
		$this->belongsTo($this->getOwnerField(), $this->getOwnerClass(), 'ID', array('alias' => $this->getOwnerAlias()));
	}
	
	protected function _postSaveRelatedRecords()
	{
		return true;
	}
		
	protected function _preSaveRelatedRecords()
	{
		return true;
	}
		
	public function getOwnerAlias()
	{
		return get_real_class($this->getOwnerClass());
	}

	public function deleteImageFiles()
	{
		foreach ($this->getImageSizes() as $key => $value)
	  	{
	  		if(is_file($this->getPath($key)))
	  		{
			   unlink($this->getPath($key));
	  		}
		}
	}

	/*
	public static function deleteByID($className, $id, $foreignKeyName)
	{
		$inst = ActiveRecordModel::getInstanceById($className, $id, ActiveRecordModel::LOAD_DATA);
		$inst->getOwner()->load();
		$inst->deleteImageFiles();

		// check if main image is being deleted
		$owner = $inst->getOwner();
		$owner->load(array(get_class($inst)));
		if ($owner->defaultImage->getID() == $id)
		{
			// set next image (by position) as the main image
			$f = new ARSelectFilter();
			$cond = new EqualsCond(new ARFieldHandle(get_class($inst), $foreignKeyName), $owner->getID());
			$cond->andWhere(new NotEqualsCond(new ARFieldHandle(get_class($inst), 'ID'), $inst->getID()));
			$f->setCondition($cond);
			$f->orderBy(new ARFieldHandle(get_class($inst), 'position'));
			$f->limit(1);
			$newDefaultImage = ActiveRecordModel::getRecordSet(get_class($inst), $f);
			if ($newDefaultImage->size() > 0)
			{
			  	$owner->defaultImage = $newDefaultImage->get(0));
			  	$owner->save();
			}
		}

		return parent::deleteByID($className, $id);
	}
	*/

	public function setFile($file)
	{
		if (substr(strtolower($file), 0, 7) == 'http://')
		{
			$fetch = new NetworkFetch($file);
			$fetch->fetch();
			$this->cacheFile = $fetch->getTmpFile();

			if (!file_exists($this->cacheFile))
			{
				return null;
			}

			$file = $this->cacheFile;
		}

		// keep original image as well for future resizing, etc
		$path = $this->getPath('original');
		copy($file, $path);

		return $this->resizeImage(new ImageManipulator($file));
	}

	public function resizeImage(ImageManipulator $resizer)
	{
		$ret = array();
		foreach ($this->getImageSizes() as $key => $size)
		{
			$filePath = $this->getPath($key);

			if (!file_exists(dirname($filePath)))
			{
				mkdir(dirname($filePath), 0777, true);
			}

			$ret[$key] = $resizer->resize($size[0], $size[1], $filePath);
			if (!$ret[$key])
			{
				break;
			}
		}

		return $ret;
	}

	protected function beforeInsert()
	{
		parent::beforeInsert();
		$this->setLastPosition();
	}

	protected function getImageRoot($className)
	{
		return $this->getDI()->get('config')->getPath('public/upload/' . strtolower(get_real_class($className)) . '/');
	}

	public function getPath($size = 0)
	{
		$ownerID = $this->getOwnerField();
		return $this->getRelativePath($this->getImagePath($this->getID(), $this->$ownerID, $size));
	}
	
	protected function getRelativePath($path, &$urlPrefix = null)
	{
		$origPath = $path;

		// path located within the /public directory - as default
		$config = $this->getDI()->get('config');
		$path = str_replace($config->getPath('public/'), '', $path);
		if ($path != $origPath)
		{
			$urlPrefix = '/public/';
			return self::fixSlashes($path);
		}

		// path within application web root directory
		$path = str_replace($config->getPath('.'), '', $path);
		$path = self::fixSlashes($path);
		if ($path != $origPath)
		{
			$path = self::getApplication()->getRouter()->getBaseDirFromUrl() . self::fixSlashes($path);
			return $path;
		}

		// relative to document root
		if (!empty($_SERVER['DOCUMENT_ROOT']))
		{
			$path = str_replace($_SERVER['DOCUMENT_ROOT'], '', '/' . self::fixSlashes($path));
		}

		$path = self::fixSlashes($path);
		if ($path == $origPath)
		{
			$path = substr($path, strpos($path, '/public/') + 8);
		}

		return $path;
	}

	private function fixSlashes($path)
	{
		$path = str_replace('//', '/', $path);
		$path = str_replace('http:/', 'http://', $path);
		return str_replace('\\', '/', $path);
	}

	public function afterSave()
	{
		parent::afterSave();

		// set as main image if it's the first image being uploaded
		if ($this->position == 0)
		{
			$owner = $this->getOwner();
			$owner->defaultImageID = $this->getID();
			$owner->save();
		}
	}

	/*####################  Data array transformation ####################*/
	public function toArray()
	{
		$array = parent::toArray();

		if (!$array['ID'])
		{
			return $array;
		}

		$array['paths'] = $array['urls'] = array();

		foreach ($this->getImageSizes() as $key => $value)
	  	{
			$ownerField = $this->getOwnerField();
			$ownerID = $this->$ownerField;

			if (!$ownerID)
			{
				break;
			}

			$array['paths'][$key] = $this->getRelativePath($this->getImagePath($array['ID'], $ownerID, $key));

/*
			$url = $app->getFullUploadUrl($urlPrefix . $array['paths'][$key]);
			$url = str_replace('/public//public/', '/public/', $url);
			$url = str_replace('/public/public/', '/public/', $url);
			$array['urls'][$key] = $url;
*/
		}

		$array['paths']['original'] = $this->getRelativePath($this->getImagePath($array['ID'], $ownerID, 'original'));

		return $array;
	}

	public function _clone(ActiveRecordModel $owner)
	{
		$cloned = clone $this;
		$cloned->setOwner($owner);
		$cloned->save();

		foreach ($this->getImageSizes() as $key => $value)
	  	{
			if (!@copy($this->getPath($key), $cloned->getPath($key)))
			{
				return false;
			}
		}

		return $cloned;
	}

	public function __destruct()
	{
		if (!empty($this->cacheFile))
		{
			@unlink($this->cacheFile);
		}
	}
}

?>
