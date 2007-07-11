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
		
		if (!$this->config->get('SHOW_BACKEND_TEMPLATE_FILES'))
		{
            unset($files['backend']);
            unset($files['block']['subs']['backend']);
            unset($files['block']['subs']['activeGrid']);
            unset($files['layout']['subs']['backend']);
            unset($files['layout']['subs']['dev']);
            unset($files['layout']['subs']['empty.tpl']);
        }

        $response = new ActionResponse();
		$response->set('categories', json_encode($files));        
        return $response;
	}	
	
	public function edit()
	{
		$template = new Template($this->request->get('file'));  	
		
		$response = new ActionResponse();	  	
	  	$response->set('fileName', $template->getFileName());
	  	$response->set('form', $this->getTemplateForm($template));
	  	$response->set('code', base64_encode($template->getCode()));
		return $response;		       
    }
	
	public function editPopup()
	{
	   return $this->edit();
    }
	
	public function save()
	{
		$code = $this->request->get('code');
        //$code = preg_replace('/&\#([\d]{1,3});/e', "chr('\\1')", $code);		

        $template = new Template($this->request->get('file')); 
		$template->setCode($code);
		$res = $template->save();
		
		if($res)
		{
		    return new JSONResponse(false, 'success', $this->translate('_template_has_been_successfully_updated'));
		}
		else
		{
		    return new JSONResponse(false, 'failure', $this->translate('_could_not_update_template'));
		}
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
		$form->set('code', '');
		return $form;
	}
}

?>