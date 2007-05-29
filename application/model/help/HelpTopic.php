<?php

ClassLoader::import('application.model.help.HelpTopicDataHandler');
ClassLoader::import('application.model.help.HelpComment');

class HelpTopic
{
	protected $dataHandler = null;
	
	protected $id;
	
	protected $name;
	
	public function __construct($topicId, $name, HelpTopicDataHandler $handler)
	{
	  	$this->id = $topicId;
	  	$this->name = $name;
	  	$this->dataHandler = $handler;
	}
	
	public static function getRootTopic($language = false)
	{
		if (!$language)
		{
			$language = Store::getInstance()->getLocaleCode();
		}
		$handler = new HelpTopicDataHandler($language);
		return $handler->getTopic('index');
  	}	

 	public function getParent()
 	{
		return $this->dataHandler->getParentTopic($this);	   
	}
	 
	public function getNext()
	{
		return $this->dataHandler->getNextTopic($this);
	}

	public function getPrevious()
	{
		return $this->dataHandler->getPreviousTopic($this);
	}

	public function getSubTopics()
	{
		return $this->dataHandler->getSubTopics($this);	  	
	}

	public function getSubTopicArray()
	{
		$topics = $this->dataHandler->getSubTopics($this);	  	
		$ret = array();
		foreach ($topics as $topic)
		{
		  	$ret[$topic->getID()] = $topic->toArray();
		}
		
		return $ret;
	}

	public function getTopic($topicId)
	{
		return $this->dataHandler->getTopic($topicId);
	}

	public function getPath($includeRootNode = false)
	{
		$path = explode('.', $this->id);
		$currentPath = '';
		$result = array();
		
		if ($includeRootNode)
		{
			$result['index'] = $this->getTopic('index');		  
		}
		
		foreach ($path as $value)
		{
			$currentPath .= ('' != $currentPath ? '.' : '') . $value;
			$result[$currentPath] = $this->getTopic($currentPath);
		}
		return $result;
	}	

  	public function getTemplateFile()
  	{
		$path = explode('.', $this->id);
	  	return 'help/' . $this->dataHandler->getLanguage() . '/' . implode('/', $path) . '.tpl';
	}

	public function getID()
	{
	  	return $this->id;
	}

	public function getName()
	{
	  	return $this->name;
	}
	
	public function toArray()
	{
	  	$ret = array();
	  	$ret['name'] = $this->name;
	  	$ret['ID'] = $this->id;
	  	$ret['language'] = $this->dataHandler->getLanguage();
	  	
	  	return $ret;
	}
	
	public function getCommentArray()
	{
		$filter = new ARSelectFilter();
		$filter->setCondition(new EqualsCond(new ARFieldHandle('HelpComment', 'topicID'), $this->id));
		$filter->setOrder(new ARFieldHandle('HelpComment', 'timeAdded'));
		return ActiveRecordModel::getRecordSetArray('HelpComment', $filter);
	}
}

?>