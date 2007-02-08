<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");

ClassLoader::import("application.model.help.*");

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

		// get topic structure
		$root = HelpTopic::getRootTopic($lang);
		
		$tree = array();
		foreach ($root->getSubTopics() as $topic)
		{
		  	$tree[$topic->getID()] = $topic->toArray();
		}
		
		$rootTopic = $root->toArray();
		$rootTopic['sub'] = $tree;
		
		$currentTopic = $root->getTopic($id);

		$path = array();
		foreach ($currentTopic->getPath(true) as $topic)
		{
			$path[] = $topic->toArray();
			if (!isset($current))
			{
			  	$current =& $rootTopic;
			}	
			else
			{
				$current['sub'] = $topic->getParent()->getSubTopicArray();
			  	$current =& $current['sub'][$topic->getID()];
			}
		}
		
		$current['sub'] = $currentTopic->getSubTopicArray();
		
//print_r($rootTopic);
		/*
		$help = new HelpTopic($lang);

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
	  	*/
	  	
	  	$response = new ActionResponse();
	  	$response->setValue('helpTemplate', $currentTopic->getTemplateFile());
	  	$response->setValue('topicTree', array(0 => $rootTopic));
	  	$response->setValue('topic', $currentTopic->toArray());
	  	$response->setValue('next', $currentTopic->getNext()->toArray());
	  	$response->setValue('prev', $currentTopic->getPrevious()->toArray());
	  	$response->setValue('PAGE_TITLE', $currentTopic->getName());
	  	$response->setValue('path', $path);
	  	$response->setValue('currentId', $id);
	  	$response->set('rootTopic', $root);
		  		  	  	
/*
	  	$response->setValue('breadCrumb', $breadCrumb);

*/
	  	return $response;

	}
}

?>