<?php

class HelpTopicDataHandler
{
	private $language;

	private $helpDir = '';

	private $topics = array();

	function __construct($language)
	{
	  	$this->language = $language;

		// get help file directory
		$this->helpDir = ClassLoader::getRealPath('application.view') . '/backend/help/' . $language . '/';
		if (!is_dir($this->helpDir))
		{
		  	$this->helpDir = ClassLoader::getRealPath('application.view') . '/backend/help/en/';
		}

		$currentTopic = array();

		// parse topic file
		$cont = file_get_contents($this->helpDir . 'topics.txt');
		$cont = str_replace("\r", '', $cont);
		$l = explode("\n", $cont);
		foreach ($l as $line)
		{
			// count the number of tabs at the beginning of each line
			$tabCount = 0;
			while (isset($line[$tabCount]) && chr(9) == $line[$tabCount])
			{
				$tabCount++;
			}

			$line = substr($line, $tabCount);

			// skip empty lines and comments (starting with #)
			if (empty($line) || '#' == $line[0])
			{
			  	continue;
			}

			// get topic ID, name and full path
			list ($id, $name) = explode('=', $line, 2);

			$currentPath = isset($currentTopic[$tabCount - 1]) ? $currentTopic[$tabCount - 1] : '';
			$fullPath = $currentPath . ('' != $currentPath ? '.' : '') . $id;

		  	$currentTopic[$tabCount] = $fullPath;
		  	if ('' == $fullPath)
		  	{
			    $fullPath = 'index';
			}
			$this->topics[$fullPath] = $name;
		}
	}  	
	
	public function getTopic($topicId)
	{
		return $this->createTopic($topicId);
	}
	
	public function getNextTopic(HelpTopic $topic)
	{
		$topicId = $topic->getID();
		
		$this->setTopicArrayPointer($topicId);
		next($this->topics);
		$ret = key($this->topics);
		reset($this->topics);

		return $this->createTopic($ret);
	}

	public function getPreviousTopic(HelpTopic $topic)
	{
		$topicId = $topic->getID();
		
		reset($this->topics);
		$this->setTopicArrayPointer($topicId);
		prev($this->topics);
		$ret = key($this->topics);
		reset($this->topics);

		return $this->createTopic($ret);
	}
	
	public function getSubTopics(HelpTopic $topic)
	{
		$topicId = $topic->getID();
		
		$this->setTopicArrayPointer($topicId);

		$level = count(explode('.', $topicId));
		if ('index' == $topicId)
		{
		  	$level = 0;
		}
		
		$ret = array();
		
		do
		{
			next($this->topics);
			$key = key($this->topics);	

		  	if ($key)
		  	{
				$sublevel = count(explode('.', $key));		  	
	
				if (1 + $level == $sublevel)
			  	{
				  	$ret[] = $this->createTopic($key);
				}			    
			}		  
		}
		while (((substr($key, 0, strlen($topicId) + 1) == $topicId . '.') ||
			   ('index' == $topicId))
			   &&
			   ($key != FALSE)
				);
				
		return $ret;
	}	
	
 	public function getParentTopic(HelpTopic $topic)
	{
		$path = explode('.', $topic->getID());
		array_pop($path);
		
		if (!count($path))
		{
		  	$path[0] = 'index';
		}
		
		$parentTopic = implode('.', $path);

		return $this->getTopic($parentTopic);	   
	}	
	
	public function getLanguage()
	{
	  	return $this->language;
	}
	
	protected function createTopic($topicId)
	{
		if (empty($topicId))
		{
			$topicId = 'index';  
		}
		return new HelpTopic($topicId, $this->topics[$topicId], $this);  
	}
	
	private function setTopicArrayPointer($topicId)
	{
		reset($this->topics);
		while ((key($this->topics) != $topicId) && (current($this->topics) !== FALSE))
		{
		  	next($this->topics);
		}
	}	
}

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
	
	public static function getRootTopic($language)
	{
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
	  	return 'backend/help/' . $this->dataHandler->getLanguage() . '/' . implode('/', $path) . '.tpl';
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
}

?>