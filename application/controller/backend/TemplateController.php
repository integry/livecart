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

	}	
	
	public function editPopup()
	{
		$template = new Template($this->request->getValue('file'));  	
		
		$response = new ActionResponse();	  	
	  	$response->setValue('fileName', $template->getFileName());
	  	$response->setValue('form', $this->getTemplateForm($template));	  	
		return $response;		
	}
	
	public function save()
	{
		$template = new Template($this->request->getValue('file')); 
		$template->setCode($this->request->getValue('code'));
		$res = $template->save();
		
		return new JSONResponse($res);
	}
	
	private function getTemplateForm(Template $template)
	{
		ClassLoader::import("framework.request.validator.Form");
		$form = new Form(new RequestValidator('template', $this->request));
		$form->setData($template->toArray());
		return $form;
	}
}

?>