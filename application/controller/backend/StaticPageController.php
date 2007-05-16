<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.model.staticPage.StaticPage");

/*
ClassLoader::import('framework.request.validator.check.*');
ClassLoader::import('framework.request.validator.filter.*');
*/
		
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
		$s = ActiveRecordModel::getRecordSet('StaticPage', $f);
		
		$pages = array();
		foreach ($s as $page)
		{
			$pages[$page->getID()] = $page->getTitle($this->locale->getLocaleCode());
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
		$instance = StaticPage::getInstanceById($this->request->getValue('id'), StaticPage::LOAD_DATA);
		
		$inst = $instance->toArray();
		
		$form = $this->getForm();
		
		$form->setData($inst);
		
		foreach ($inst['title'] as $key => $value)
		{
			$form->setValue('title_' . $key, $value);
		}

		foreach ($inst['text'] as $key => $value)
		{
			$form->setValue('text_' . $key, $value);
		}
		
		$form->setValue('title', $instance->getTitle());
		$form->setValue('text', $instance->getText());
		$form->setValue('handle', $instance->handle->get());
				
		$response = new ActionResponse();				
		$response->setValue('form', $form);
		$response->setValue('page', $inst);
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
	
		// default language
		$inst->setTitle($this->request->getValue('title'));
		$inst->setText($this->request->getValue('text'));
		$inst->handle->set($this->request->getValue('handle'));
				
		// other languages
		foreach ($this->store->getLanguageArray() as $lang)
		{
			$inst->setTitle($this->request->getValue('title_' . $lang), $lang);
			$inst->setText($this->request->getValue('text_' . $lang), $lang);			
		}	
		
		$inst->isInformationBox->set($this->request->getValue('isInformationBox'));
		
		$inst->save();
		
		return new JSONResponse(array('id' => $inst->getID(), 'title' => $inst->getTitle()));
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