<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");

ClassLoader::import("application.model.help.*");

/**
 * Help system related actions
 *
 * @package application.controller.backend
 * 
 * @role help
 */
class HelpController extends StoreManagementController
{
	public function view()
	{
	  	$id = $this->request->getValue('id');
	  	$lang = $this->store->getLocaleCode();

		// get topic structure
		$root = HelpTopic::getRootTopic($lang);
		
		$tree = array();
		foreach ($root->getSubTopics() as $topic)
		{
		  	$tree[$topic->getID()] = $topic->toArray();
		}
		
		$rootTopic = $root->toArray();
		$rootTopic['sub'] = $tree;
		
		// get requested topic
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

		// get user comments
		$commentArray = $currentTopic->getCommentArray();		
		
		$current['sub'] = $currentTopic->getSubTopicArray();
	  	
	  	$response = new ActionResponse();
	  	$response->setValue('helpTemplate', $currentTopic->getTemplateFile());
	  	$response->setValue('topicTree', array(0 => $rootTopic));
	  	$response->setValue('topic', $currentTopic->toArray());
	  	$response->setValue('next', $currentTopic->getNext()->toArray());
	  	$response->setValue('prev', $currentTopic->getPrevious()->toArray());
	  	$response->setValue('PAGE_TITLE', $currentTopic->getName());
	  	$response->setValue('path', $path);
	  	$response->setValue('currentId', $id);
	  	$response->setValue('comments', $commentArray);
	  	$response->setValue('commentCount', count($commentArray));
	  	$response->setValue('commentForm', $this->getCommentForm());
	  	$response->set('rootTopic', $root);
		  		  	  	
	  	return $response;
	}
	
	/**
	 * @role create
	 */
	public function addComment()
	{
		$comment = HelpComment::getNewInstance($this->request->getValue('topicId'));
		$comment->username->set($this->request->getValue('username'));
		$comment->text->set($this->request->getValue('text'));
		$comment->save();
		
		ActiveRecordModel::removeFromPool($comment);
		
		$comment = ActiveRecordModel::getInstanceById('HelpComment', $comment->getID(), HelpComment::LOAD_DATA);
		
		$response = new ActionResponse();
		$response->setValue('comment', $comment->toArray());
		return $response;
	}
	
	/**
	 * @role remove
	 */
	public function deleteComment()
	{
	    HelpComment::getInstanceByID((int)$this->request->getValue('id'))->delete();
		return new JSONResponse(1);
	}
	
	/**
	 * @role update
	 */
	public function saveComment()
	{
		$comment = ActiveRecordModel::getInstanceByID('HelpComment', $this->request->getValue('id'), HelpComment::LOAD_DATA);
		$comment->username->set($this->request->getValue('username'));
		$comment->text->set($this->request->getValue('text'));
		$comment->save();
		
		$response = new ActionResponse();
		$response->setValue('comment', $comment->toArray());
		return $response;
	}

	private function getCommentForm()
	{
		ClassLoader::import('framework.request.validator.Form');
		return new Form($this->getCommentValidator());		
	}

	private function getCommentValidator()
	{
		ClassLoader::import('framework.request.validator.RequestValidator');
		$validator = new RequestValidator('commentForm', $this->request);
		$validator->addCheck('username', new IsNotEmptyCheck($this->translate('_err_enter_name')));
		$validator->addCheck('text', new IsNotEmptyCheck($this->translate('_err_enter_comment')));
		return $validator;
	}
}

?>