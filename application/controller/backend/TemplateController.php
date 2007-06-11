<?php

ClassLoader::import('application.controller.backend.abstract.StoreManagementController');
ClassLoader::import('application.model.template.Template');

/**
 * Template modification
 *
 * @package application.controller.backend
 * @author Integry Systems <info@livecart.com>
 * 
 * @ro le template
 */
class TemplateController extends StoreManagementController
{
	public function index()
	{        
        $response = new ActionResponse();
		$response->setValue('categories', json_encode(Template::getTree()));        
        return $response;
	}	
	
	public function edit()
	{
		$template = new Template($this->request->getValue('file'));  	
		
		$response = new ActionResponse();	  	
	  	$response->setValue('fileName', $template->getFileName());
	  	$response->setValue('form', $this->getTemplateForm($template));	  	
		return $response;		       
    }
	
	public function editPopup()
	{
	   return $this->edit();
    }
	
	public function save()
	{
		$code = $this->request->getValue('code');
        $code = preg_replace('/&\#([\d]{1,3});/e', "chr('\\1')", $code);		
        
        $template = new Template($this->request->getValue('file')); 
		$template->setCode($code);
		$res = $template->save();
		
		return new JSONResponse($res);
	}
	
	public function emptyPage()
	{
        return new ActionResponse();
    }
	
	private function getTemplateForm(Template $template)
	{
		ClassLoader::import("framework.request.validator.Form");
		$form = new Form(new RequestValidator('template', $this->request));
        $form->setData($template->toArray());
        		
        $s = rawurlencode($template->getCode());
        $s = str_replace('%26', '&#38;', $s);
        $s = preg_replace('/%([\dABCDEF]{2})/e', "'&#'.hexdec('\\1').';'", $s);
        $form->setValue('code', $s);        

		return $form;
	}
}

?>