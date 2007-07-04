<?php

ClassLoader::import("application.controller.FrontendController");

class ErrController extends FrontendController
{
	public function index()
	{
	    $response = new ActionResponse();
	    $response->setValue('id', $this->request->getValue('id'));
	    $response->setValue('ajax', $this->request->getValue('ajax'));
	    $response->setValue('description', HTTPStatusException::getCodeMeaning($this->request->getValue('id')));
	    
	    return $response;
	}
	
	public function redirect()
	{
		$id = $this->request->getValue('id');
		$params = array();
	
		if($this->request->isAjax())
		{
		    $params['query'] = array('ajax' => 1);
		}
		
		switch($id)
		{
			case 401:
			    return new ActionRedirectResponse('user', 'login', $params);
			case 403:
			case 404:
			    $params['id'] = $id;
			    return new ActionRedirectResponse('err', 'index', $params);  
			default:
				print_r(User::getCurrentUser()->toArray());
		       	echo 'error ' . $this->request->getValue('id');
		}
	}
}

?>