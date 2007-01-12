<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");

/**
 * Help system related actions
 *
 * @package application.controller.backend
 */
class HelpController extends StoreManagementController
{
	function view()
	{
	  	$id = $this->request->getValue('id');
	  	$lang = $this->request->getValue('language');

		$lang = 'en';

		$help = new HelpTopic($lang);

	  	// get help template file
	  	$helpTemplate = $help->getTemplateFile($id);

	  	// get breadcrumb path
		$breadCrumb = $help->getPath($id);

		// get page title
		$title = end($breadCrumb);

		// get next and previous topics
		$nextId = $help->getNextTopic($id);
		$prevId = $help->getPreviousTopic($id);

		if ($nextId !== FALSE)
		{
		  	$nextTitle = $help->getName($nextId);
		}

		if ($prevId !== FALSE)
		{
		  	$prevTitle = $help->getName($prevId);
		}

	  	$response = new ActionResponse();
	  	$response->setValue('helpTemplate', $helpTemplate);
	  	$response->setValue('breadCrumb', $breadCrumb);
	  	$response->setValue('nextId', $nextId);
	  	$response->setValue('prevId', $prevId);
	  	$response->setValue('nextTitle', $nextTitle);
	  	$response->setValue('prevTitle', $prevTitle);
	  	$response->setValue('PAGE_TITLE', $title);
	  	return $response;
	}
}

class HelpTopic
{
	private $helpDir = '';

	private $topics = array();

	private $language;

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
			while (chr(9) == $line[$tabCount])
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

			$currentPath = $currentTopic[$tabCount - 1];
			$fullPath = $currentPath . ('' != $currentPath ? '.' : '') . $id;

		  	$currentTopic[$tabCount] = $fullPath;
		  	if ('' == $fullPath)
		  	{
			    $fullPath = 'index';
			}
			$this->topics[$fullPath] = $name;
		}
	}

  	public function getPath($topicId)
	{
		$path = explode('.', $topicId);
		$currentPath = '';
		$result = array();
		$result['index'] = $this->topics['index'];
		foreach ($path as $value)
		{
			$currentPath .= ('' != $currentPath ? '.' : '') . $value;
			$result[$currentPath] = $this->topics[$currentPath];
		}
		return $result;
	}

  	public function getTemplateFile($topicId)
  	{
		$path = explode('.', $topicId);
	  	$helpTemplate = 'backend/help/' . $this->language . '/' . implode('/', $path) . '.tpl';
		return $helpTemplate;
	}

  	public function getName($topicId)
  	{
		if (isset($this->topics[$topicId]))
		{
		  	return $this->topics[$topicId];
		}
	}

	public function getNextTopic($topicId)
	{
		$this->setTopicArrayPointer($topicId);
		next($this->topics);
		$ret = key($this->topics);
		reset($this->topics);
		return $ret;
	}

	public function getPreviousTopic($topicId)
	{
		reset($this->topics);
		$this->setTopicArrayPointer($topicId);
		prev($this->topics);
		$ret = key($this->topics);
		reset($this->topics);
		return $ret;
	}

	private function setTopicArrayPointer($topicId)
	{
		while ((key($this->topics) != $topicId) && (current($this->topics) !== FALSE))
		{
		  	next($this->topics);
		}
	}
}

?>