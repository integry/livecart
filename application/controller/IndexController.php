<?php

ClassLoader::import("application.controller.FrontendController");
ClassLoader::import('application.model.sitenews.NewsPost');

/**
 * Index controller for frontend
 *
 * @package application.controller
 */
class IndexController extends FrontendController 
{
	public function index() 
	{
        ClassLoader::import('application.controller.CategoryController');
		
		$this->request->set('id', Category::ROOT_ID);
		$this->request->set('cathandle', '.');
		
        $controller = new CategoryController($this->application);		
		$response = $controller->index();
		
		// load site news
        $f = new ARSelectFilter(new EqualsCond(new ARFieldHandle('NewsPost', 'isEnabled'), true));
		$f->setOrder(new ARFieldHandle('NewsPost', 'position'), 'DESC');
		$f->setLimit($this->config->get('NUM_NEWS_INDEX') + 1);
		$news = ActiveRecordModel::getRecordSetArray('NewsPost', $f);
		$response->set('news', $news);
		$response->set('isNewsArchive', count($news) > $this->config->get('NUM_NEWS_INDEX'));
		
		return $response;
	}

}

?>