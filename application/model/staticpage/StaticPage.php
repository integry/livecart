<?php

namespace staticpage;

/**
 * Static site pages (shipping information, contact information, terms of service, etc.)
 *
 * @package application/model/staticpage
 * @author Integry Systems <http://integry.com>
 */
class StaticPage extends \system\MultilingualObject implements \eav\EavAble
{
	private $isFileLoaded = false;

	public $ID;
	public $parentID;
	public $eavObjectID;
	public $handle;
	public $title;
	public $text;
	public $metaDescription;
	public $menu;
	public $position;

	public function initialize()
	{
        $this->hasMany('ID', '\staticpage\StaticPage', 'parentID');

		$this->belongsTo('parentID', '\staticpage\StaticPage', 'ID');
		$this->hasOne('eavObjectID', '\eav\EavObject', 'ID', array('foreignKey' => true));
	}

	/*####################  Static method implementations ####################*/

	public static function getNewInstance()
	{
		return new self();
	}

	public static function getInstanceByHandle($handle)
	{
		$f = new ARSelectFilter(new EqualsCond(new ARFieldHandle(__CLASS__, 'handle'), $handle));
		$s = self::getRecordSet(__CLASS__, $f);

		if (!$s->count())
		{
			throw new ARNotFoundException(__CLASS__, 'handle: ' . $handle);
		}

		return $s->shift();
	}

	public static function getIsInformationMenuCondition()
	{
		return new LikeCond('StaticPage.menu', '%"INFORMATION";b:1%');
	}

	public static function getIsRootCategoriesMenuCondition()
	{
		return new LikeCond('StaticPage.menu', '%"ROOT_CATEGORIES";b:1%');
	}


	public function getFileName()
	{
		return $this->config->getPath('cache/staticpage') . '/' . $this->getID() . '.php';
	}

	/*
	public function save($forceOperation = null)
	{
		$this->loadFile();

		if (!$this->handle)
		{
			$this->handle = createHandleString($this->getValueByLang('title')));
		}

		parent::save($forceOperation);

		$this->saveFile();
	}
	*/

	public function delete()
	{
		//@unlink($this->getFileName());
		return parent::delete();
	}

	/*
	public function toArray()
	{
		$array = parent::toArray();
		if (!$this->isLoaded())
		{
			$this->loadFile();
			$lang = self::getApplication()->getLocaleCode();
			$array['title_lang'] = $this->getValueByLang('title', $lang);
			$array['text_lang'] = $this->getValueByLang('text', $lang);
		}

		// when the instance is not loaded
		if (!$array['handle'])
		{
			$array['handle'] = $this->handle;
		}

		$array['menuInformation'] = false;
		$array['menuRootCategories'] = false;

		if (array_key_exists('menuData', $array))
		{
			if (array_key_exists('INFORMATION', $array['menuData']))
			{
				$array['menuInformation'] = (bool)$array['menuData']['INFORMATION'];
			}
			if (array_key_exists('ROOT_CATEGORIES', $array['menuData']))
			{
				$array['menuRootCategories'] = (bool)$array['menuData']['ROOT_CATEGORIES'];
			}
		}
		return $array;
	}
	*/

	public function getSubPageArray()
	{
		$f = select();
		$f->orderBy(f('StaticPage.position'));
		return $this->getRelatedRecordSetArray('StaticPage', $f);
	}

	public static function createTree(array $pages)
	{
		$index = array();
		foreach ($pages as &$page)
		{
			$index[$page['ID']] =& $page;
		}

		foreach ($pages as $key => &$page)
		{
			if ($page['parentID'] && isset($index[$page['parentID']]))
			{
				$index[$page['parentID']]['children'][] =& $page;
				unset($pages[$key]);
			}
		}

		return $pages;
	}

	public function beforeCreate()
	{
		parent::beforeCreate();
		$this->setLastPosition();
	}

	private function saveFile()
	{
		$fileData = array('handle' => $this->handle,
						  'title' => $this->title,
						 );

		$dir = dirname($this->getFileName());

		if (!file_exists($dir))
		{
			mkdir($dir, 0777, true);
		}

		file_put_contents($this->getFileName(), '<?php $pageData = ' . var_export($fileData, true) . '; ?>');
	}

	private function loadFile()
	{
		if (!$this->isLoaded() && !$this->isFileLoaded && file_exists($this->getFileName()))
		{
			if (!file_exists($this->getFileName()))
			{

				$this->saveFile();
			}

			include $this->getFileName();
			$this->title = $pageData['title'];

			if (!$this->handle)
			{
				$this->handle = $pageData['handle'];
			}

			$this->isFileLoaded = true;
		}
	}
}

?>
