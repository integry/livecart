<?php

ClassLoader::import('application.model.sitenews.NewsPost');

/**
 * Site news controller
 *
 * @author Integry Systems
 * @package application.controller
 */	
class NewsController extends FrontendController
{
	public function view()
	{       		
        $f = new ARSelectFilter(new EqualsCond(new ARFieldHandle('NewsPost', 'ID'), $this->request->get('id')));
		$f->mergeCondition(new EqualsCond(new ARFieldHandle('NewsPost', 'isEnabled'), true));
		
		$s = ActiveRecordModel::getRecordSet('NewsPost', $f);
		if (!$s->size())
		{
            throw new ARNotFoundException('NewsPost', $this->request->get('id'));
        }

		$newsPost = $s->get(0)->toArray();
		
        $this->addIndexBreadCrumb();
		$this->addBreadCrumb($newsPost['title_lang'], '');

		return new ActionResponse('news', $newsPost);
	}	
	
	public function index()
	{
        $this->addIndexBreadCrumb();
        
        $f = new ARSelectFilter(new EqualsCond(new ARFieldHandle('NewsPost', 'isEnabled'), true));
		$f->setOrder(new ARFieldHandle('NewsPost', 'position'), 'DESC');
		
        return new ActionResponse('news', ActiveRecordModel::getRecordSetArray('NewsPost', $f));
    }
	
	private function addIndexBreadCrumb()
	{
		$this->addBreadCrumb($this->translate('_news'), $this->router->createUrl(array('controller' => 'news')));
    }
}

?>