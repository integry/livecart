<?php

ClassLoader::import('application.helper.CreateHandleString');

/**
 * Static site pages (shipping information, contact information, terms of service, etc.)
 *
 * @package application.model.staticpage
 * @author Integry Systems <http://integry.com>  
 */
class StaticPage extends MultilingualObject
{
	private $isFileLoaded = false;
	
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName($className);
		
		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARField("handle", ARVarchar::instance(255)));
		$schema->registerField(new ARField("title", ARArray::instance()));
		$schema->registerField(new ARField("text", ARArray::instance()));
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
	
	public function getFileName()
	{
		return ClassLoader::getRealPath('cache.staticpage') . '/' . $this->getID() . '.php';
	}
	
	public function save()
	{
		$this->loadFile();
		
		if (!$this->handle->get())
		{
            $this->handle->set(createHandleString($this->getValueByLang('title', $lang)));		
		}
	
		parent::save();
		
		$this->saveFile();
	}
	
	public function delete()
	{
		@unlink($this->getFileName());
		return parent::delete();
	}
	
	public function toArray()
	{
		$array = parent::toArray();
		
		if (!$this->isLoaded())
		{
            $this->loadFile();
    		$lang = $this->getStore()->getLocaleCode();
    		
    		$array['title_lang'] = $this->getValueByLang('title', $lang);
    		$array['text_lang'] = $this->getValueByLang('text', $lang);            
        }

        // when the instance is not loaded
        if (!$array['handle'])
        {
            $array['handle'] = $this->handle->get();
        }

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
	
	private function saveFile()
	{
		$fileData = array('handle' => $this->handle->get(),
						  'title' => $this->title->get(),
						 );
						 
		$dir = dirname($this->getFileName());
		
		if (!file_exists($dir))
		{
			mkdir($dir, 0777, true);
			chmod($dir, 0777);
		}
		
		file_put_contents($this->getFileName(), '<?php $pageData = ' . var_export($fileData, true) . '; ?>');       
    }
	
	private function loadFile()
	{
		if (!$this->isLoaded() && !$this->isFileLoaded && file_exists($this->getFileName()))
		{
			if (!file_exists($this->getFileName()))
			{
                $this->load();
                $this->saveFile();       
            }
            
            include $this->getFileName();
			$this->title->set($pageData['title']);

			if (!$this->handle->get())
			{
                $this->handle->set($pageData['handle']);
            }

            $this->isFileLoaded = true;
		}		
	}	
}

?>