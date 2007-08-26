<?php

ClassLoader::import("application.controller.backend.abstract.BackendController");

class ErrController extends BackendController 
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
			    return new ActionRedirectResponse('backend.session', 'index', $params);
			case 403:
			case 404:
			    $params['id'] = $id;
			    return new ActionRedirectResponse('backend.err', 'index', $params);  
			default:
				print_r($this->user->toArray());
		       	echo 'error ' . $this->request->get('id');
		}
	}
}

?>