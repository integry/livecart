<?php

/**
 * Index controller for frontend
 *
 * @author Integry Systems
 * @package application/controller
 */
class IndexController extends ControllerBase
{
    public function indexAction()
    {
    	$url = str_replace('/', '', $_REQUEST['_url']);
		if ($url)
		{
			return $this->notFound();
		}
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
		$f = query::query()->where('NewsPost.isEnabled = :NewsPost.isEnabled:', array('NewsPost.isEnabled' => true));
		$f->orderBy('NewsPost.position', 'DESC');
		$f->limit($this->config->get('NUM_NEWS_INDEX') + 1);
		$news = ActiveRecordModel::getRecordSetArray('NewsPost', $f);
		$this->set('news', $news);
		$this->set('isNewsArchive', count($news) > $this->config->get('NUM_NEWS_INDEX'));

	}

}
*/
