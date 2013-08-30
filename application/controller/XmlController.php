<?php

ini_set('memory_limit', '128M');
set_time_limit(0);


/**
 * Export product data to XML feeds
 *
 * @author Integry Systems
 * @package application/controller
 */
class XmlController extends FrontendController
{
	const CHUNK_SIZE = 100;

	/*
	public function getCacheControlAction($action)
	{
		$control = new CacheControl();

		if ('export' == $action)
		{
			$control->setLifeTime(30 * 60);
			$control->setVaryBy(array('id'));
		}

		return $control;
	}
	*/

	public function exportAction()
	{
		$module = $this->request->get('module');
		$enabledFeeds = $this->config->get('ENABLED_FEEDS');

		if (!isset($enabledFeeds[$module]) || ($this->request->get('key') != $this->config->get('FEED_KEY')))
		{
			return;
		}

		$this->setLayout('empty');
		set_time_limit(0);

		$cat = Category::getRootNode(true);
		$filter = new ProductFilter($cat, new ARSelectFilter());
		$filter->includeSubCategories();

		$feed = new ProductFeed($filter);
		$feed->setFlush();

		$response = new XMLResponse();
		$this->set('feed', $feed);
		$this->set('tpl', 'xml/feed/' . $module . '.tpl');

	}
}

?>