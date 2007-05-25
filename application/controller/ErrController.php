<?php

ClassLoader::import("application.controller.FrontendController");

class ErrController extends FrontendController
{
	public function index()
	{
	    $response = new ActionResponse();
	    $response->setValue('id', $this->request->getValue('id'));
	    $response->setValue('description', HTTPStatusException::getCodeMeaning($this->request->getValue('id')));
	    return $response;
	}
	
	public function redirect()
	{
		$id = $this->request->getValue('id');
		switch($id)
		{
			case 401:
			    return new ActionRedirectResponse('user', 'login');
			case 403:
			case 404:
			    return new ActionRedirectResponse('err', 'index', array('id' => $id));  
			default:
				print_r(User::getCurrentUser()->toArray());
		       	echo 'error ' . $this->request->getValue('id');
		}
	}
}

?>