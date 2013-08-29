<?php

/**
 * Index controller for frontend
 *
 * @author Integry Systems
 * @package application/controller
 */
class IndexController extends ControllerBase
{
    public function indexActionAction()
    {
		$test = new StaticpageController();
		echo 'HOME PAGE';
    }
}

/*
class IndexController extends ControllerBase
{
	public function indexAction()
	{
		ClassLoader::import('application/controller/CategoryController');

		$this->request = 'id', Category::ROOT_ID);
		$this->request = 'cathandle', '-');

		$response = parent::index();

		// load site news
		$f = new ARSelectFilter(new EqualsCond(new ARFieldHandle('NewsPost', 'isEnabled'), true));
		$f->setOrder(new ARFieldHandle('NewsPost', 'position'), 'DESC');
		$f->setLimit($this->config->get('NUM_NEWS_INDEX') + 1);
		$news = ActiveRecordModel::getRecordSetArray('NewsPost', $f);
		$response = 'news', $news);
		$response = 'isNewsArchive', count($news) > $this->config->get('NUM_NEWS_INDEX'));

		return $response;
	}

}
*/