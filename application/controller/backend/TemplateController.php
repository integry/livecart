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
		$files = Template::getTree();
		
		if (!$this->config->getValue('SHOW_BACKEND_TEMPLATE_FILES'))
		{
            unset($files['backend']);
            unset($files['block']['subs']['backend']);
            unset($files['block']['subs']['activeGrid']);
            unset($files['layout']['subs']['backend']);
            unset($files['layout']['subs']['dev']);
            unset($files['layout']['subs']['empty.tpl']);
        }

        $response = new ActionResponse();
		$response->setValue('categories', json_encode($files));        
        return $response;
	}	
	
	public function edit()
	{
		$template = new Template($this->request->getValue('file'));  	
		
		$response = new ActionResponse();	  	
	  	$response->setValue('fileName', $template->getFileName());
	  	$response->setValue('form', $this->getTemplateForm($template));
	  	$response->setValue('code', base64_encode($template->getCode()));
		return $response;		       
    }
	
	public function editPopup()
	{
	   return $this->edit();
    }
	
	public function save()
	{
		$code = $this->request->getValue('code');
        //$code = preg_replace('/&\#([\d]{1,3});/e', "chr('\\1')", $code);		
        
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
		$form->setValue('code', '');
		return $form;
	}
}

?>