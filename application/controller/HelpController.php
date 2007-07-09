<?php

ClassLoader::import("application.model.help.*");

/**
 * Help system related actions
 *
 * @package application.controller.backend
 */
class HelpController extends BaseController
{
	public function view()
	{
	  	$id = $this->request->get('id');
	  	$lang = $this->application->getLocaleCode();

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
	  	$response->set('helpTemplate', $currentTopic->getTemplateFile());
	  	$response->set('topicTree', array(0 => $rootTopic));
	  	$response->set('topic', $currentTopic->toArray());
	  	$response->set('next', $currentTopic->getNext()->toArray());
	  	$response->set('prev', $currentTopic->getPrevious()->toArray());
	  	$response->set('PAGE_TITLE', $currentTopic->getName());
	  	$response->set('path', $path);
	  	$response->set('currentId', $id);
	  	$response->set('comments', $commentArray);
	  	$response->set('commentCount', count($commentArray));
	  	$response->set('commentForm', $this->getCommentForm());
	  	$response->set('rootTopic', $root);
		  		  	  	
	  	return $response;
	}
	
	/**
	 * @role create
	 */
	public function addComment()
	{
		$comment = HelpComment::getNewInstance($this->request->get('topicId'));
		$comment->username->set($this->request->get('username'));
		$comment->text->set($this->request->get('text'));
		$comment->save();
		
		ActiveRecordModel::removeFromPool($comment);
		
		$comment = ActiveRecordModel::getInstanceById('HelpComment', $comment->getID(), HelpComment::LOAD_DATA);
		
		$response = new ActionResponse();
		$response->set('comment', $comment->toArray());
		return $response;
	}
	
	/**
	 * @role remove
	 */
	public function deleteComment()
	{
	    HelpComment::getInstanceByID((int)$this->request->get('id'))->delete();
		return new JSONResponse(1);
	}
	
	/**
	 * @role update
	 */
	public function saveComment()
	{
		$comment = ActiveRecordModel::getInstanceByID('HelpComment', $this->request->get('id'), HelpComment::LOAD_DATA);
		$comment->username->set($this->request->get('username'));
		$comment->text->set($this->request->get('text'));
		$comment->save();
		
		$response = new ActionResponse();
		$response->set('comment', $comment->toArray());
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