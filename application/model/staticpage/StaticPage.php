<?php

/**
 * Static site pages (shipping information, contact information, terms of service, etc.)
 *
 * @package application.model.delivery
 */
class StaticPage extends ActiveRecordModel
{
	private $title = array();
	
	private $text = array();
	
	private $isFileLoaded = false;
	
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName($className);
		
		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARField("handle", ARVarchar::instance(255)));
		$schema->registerField(new ARField("isInformationBox", ARBool::instance()));
		$schema->registerField(new ARField("position", ARInteger::instance()));
	}

	/**
	 * Gets an existing record instance (persisted on a database).
	 * @param mixed $recordID
	 * @param bool $loadRecordData
	 * @param bool $loadReferencedRecords
	 * @param array $data	Record data array (may include referenced record data)
	 *
	 * @return StaticPage
	 */
	public static function getInstanceByID($recordID, $loadRecordData = false, $loadReferencedRecords = false, $data = array())
	{		    
		return parent::getInstanceByID(__CLASS__, $recordID, $loadRecordData, $loadReferencedRecords, $data);
	}
	
	public static function getNewInstance()
	{
		return parent::getNewInstance(__CLASS__);
	}
	
	public static function getInstanceByHandle($handle)
	{
		$f = new ARSelectFilter(new EqualsCond(new ARFieldHandle(__CLASS__, 'handle'), $handle));
		$s = self::getRecordSet(__CLASS__, $f);
		
		if (!$s->size())
		{
			throw new ARNotFoundException(__CLASS__, 'handle: ' . $handle);
		}
		
		return $s->get(0);
	}
	
	public function setTitle($title, $lang = null)
	{
		if (!$lang)
		{
			$lang = Store::getInstance()->getDefaultLanguageCode();
		}		
				
		$this->title[$lang] = $title;
	}
	
	public function setText($text, $lang = null)
	{
		if (!$lang)
		{
			$lang = Store::getInstance()->getDefaultLanguageCode();
		}		
		
		$this->text[$lang] = $text;
	}
	
	public function getTitle($lang = null)
	{
		$this->loadFile();
		
		if (!$lang || !isset($this->title[$lang]))
		{
			$lang = Store::getInstance()->getDefaultLanguageCode();
		}		
		
		if (isset($this->title[$lang]))
		{
			return $this->title[$lang];
		}
	}
	
	public function getText($lang = null)
	{
		$this->loadFile();
		
		if (!$lang || !isset($this->text[$lang]))
		{
			$lang = Store::getInstance()->getDefaultLanguageCode();
		}		
		
		if (isset($this->text[$lang]))
		{
			return $this->text[$lang];
		}
	}

	public function getFileName()
	{
		return ClassLoader::getRealPath('storage.staticpage') . '/' . $this->getID() . '.php';
	}
	
	public function save()
	{
		$this->loadFile();
		
		if (!$this->handle->get())
		{
			$this->handle->set(Store::createHandleString($this->getTitle()));		
		}
	
		parent::save();
		
		$fileData = array('handle' => $this->handle->get(),
						  'title' => $this->title,
						  'text' => $this->text,	
						 );
						 
		$dir = dirname($this->getFileName());
		
		if (!file_exists($dir))
		{
			mkdir($dir, 0777, true);
			chmod($dir, 0777);
		}
		
		file_put_contents($this->getFileName(), '<?php $pageData = ' . var_export($fileData, true) . '; ?>');
	}
	
	public function delete()
	{
		@unlink($this->getFileName());
		return parent::delete();
	}
	
	public function toArray()
	{
		$array = parent::toArray();
		
		$this->loadFile();
		$array['title'] = $this->title;
		$array['text'] = $this->text;
		
		$lang = Store::getInstance()->getLocaleCode();
		
		$array['title_lang'] = $this->getTitle($lang);
		$array['text_lang'] = $this->getText($lang);
		
		return $array;
	}
	
	protected function insert()
	{
	  	// get max position
	  	$f = new ARSelectFilter();
	  	$f->setOrder(new ARFieldHandle('StaticPage', 'position'), 'DESC');
	  	$f->setLimit(1);
	  	$rec = ActiveRecord::getRecordSetArray('StaticPage', $f);
		$position = (is_array($rec) && count($rec) > 0) ? $rec[0]['position'] + 1 : 1;

		$this->position->set($position);		
		
		return parent::insert();
	}
	
	private function loadFile()
	{
		if (!$this->isFileLoaded && file_exists($this->getFileName()))
		{
			include $this->getFileName();
			$this->title = $pageData['title'];
			$this->text = $pageData['text'];
		}	
	}	
}

?>