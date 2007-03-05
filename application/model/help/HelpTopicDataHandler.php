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
		
		if (!isset($this->topics[$topicId]))
		{
			return false;
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

?>