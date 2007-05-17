<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.model.staticpage.StaticPage");
		
/**
 * Static page management
 *
 * @package application.controller.backend
 *
 * @role page
 */
class StaticPageController extends StoreManagementController
{
	/**
	 *	Main settings page
	 */
	public function index()
	{
		$f = new ARSelectFilter();
		$f->setOrder(new ARFieldHandle('StaticPage', 'position'));
		$s = ActiveRecordModel::getRecordSetArray('StaticPage', $f);
		
		$pages = array();
		foreach ($s as $page)
		{
			$pages[$page['ID']] = $page['title_lang'];
		}
		
		$response = new ActionResponse();
		$response->setValue('pages', json_encode($pages));
		return $response;
	}
	
	public function add()
	{
		$response = new ActionResponse();		
		$response->setValue('form', $this->getForm());				
		return $response;
	}	  
	
	public function edit()
	{
		$page = StaticPage::getInstanceById($this->request->getValue('id'), StaticPage::LOAD_DATA)->toArray();
		
		$form = $this->getForm();
				
		$form->setData($page);		
				
		$response = new ActionResponse();				
		$response->setValue('form', $form);
		$response->setValue('page', $page);
		$response->setValue('languages', $this->store->getLanguageSetArray());
		return $response;		
	}
	
	/**
	 * Reorder pages
	 */
	public function reorder()
	{
	    $inst = StaticPage::getInstanceById($this->request->getValue('id'), StaticPage::LOAD_DATA);
	    
	    $f = new ARSelectFilter();
	    $handle = new ARFieldHandle('StaticPage', 'position');
	    if ('down' == $this->request->getValue('order'))
	    {
			$f->setCondition(new MoreThanCond($handle, $inst->position->get()));
			$f->setOrder($handle, 'ASC');
		}
		else
		{
			$f->setCondition(new LessThanCond($handle, $inst->position->get()));
			$f->setOrder($handle, 'DESC');		
		}
	    $f->setLimit(1);
		
		$s = ActiveRecordModel::getRecordSet('StaticPage', $f);
		
		if ($s->size())
		{
			$pos = $inst->position->get();
			$replace = $s->get(0);
			$inst->position->set($replace->position->get());
			$replace->position->set($pos);
			$inst->save();
			$replace->save();
			
			return new JSONResponse(array('id' => $inst->getID(), 'order' => $this->request->getValue('order')));	
		}
		else
		{
			return new JSONResponse(0);	
		}	
	}	
	
	public function save()
	{
		if ($this->request->getValue('id'))
		{
			$inst = StaticPage::getInstanceById($this->request->getValue('id'), StaticPage::LOAD_DATA);
		}
		else
		{
			$inst = StaticPage::getNewInstance();	
		}
	
		$inst->loadRequestData($this->request);
		$inst->save();
		
		$arr = $inst->toArray();
		
		return new JSONResponse(array('id' => $inst->getID(), 'title' => $arr['title_lang']));
	}

	public function delete()
	{
		try
		{
			$inst = StaticPage::getInstanceById($this->request->getValue('id'), StaticPage::LOAD_DATA);	
			
			$inst->delete();
				
			return new JSONResponse($inst->getID());
		}
		catch (Exception $e)
		{
			return new JSONResponse(0);	
		}		
	}
	
	public function emptyPage()
	{
		return new ActionResponse();
	}
	
	private function getForm()
	{
		ClassLoader::import('framework.request.validator.Form');
		return new Form($this->getValidator());
	}

	private function getValidator()
	{	
		ClassLoader::import('framework.request.validator.RequestValidator');
		
		$val = new RequestValidator('staticPage', $this->request);
		$val->addCheck('title', new IsNotEmptyCheck($this->translate('_err_title_empty')));
		$val->addCheck('text', new IsNotEmptyCheck($this->translate('_err_text_empty')));
		
		return $val;
	}
}

?>