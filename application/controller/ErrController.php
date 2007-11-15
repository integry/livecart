<?php

ClassLoader::import("application.controller.FrontendController");

/**
 * @author Integry Systems
 * @package application.controller
 */
class ErrController extends FrontendController
{
	public function index()
	{
		$response = new ActionResponse();
		$response->set('id', $this->request->get('id'));
		$response->set('ajax', $this->request->get('ajax'));
		$response->set('description', HTTPStatusException::getCodeMeaning($this->request->get('id')));
		
		return $response;
	}
	
	public function redirect()
	{
		$id = $this->request->get('id');
		$params = array();
	
		if($this->request->isAjax())
		{
			$params['query'] = array('ajax' => 1);
		}
		
		switch($id)
		{
			case 401:
				return new ActionRedirectResponse('user', 'login', $params);
			default:
				return new ActionResponse('id', $id);  
		}
	}
	
	public function backendBrowser()
	{
		return new ActionResponse();
	}
}

?>