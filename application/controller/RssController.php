<?php

ClassLoader::import('application/model/category/Category');
ClassLoader::import('application/model/product/ProductFilter');
ClassLoader::import('application/model/product/Product');
ClassLoader::import('application/model/feed/ProductFeed');
ClassLoader::import('application/model/sitenews/NewsPost');

/**
 * 
 *
 * @package application/rss
 * @author Integry Systems <http://integry.com>
 */

class RssController extends FrontendController
{
	private $enabledFeeds = null;

	public function productsAction()
	{
		$this->setLayout('empty');
		set_time_limit(0);
		$response = new XMLResponse();
		$filter = new ARSelectFilter();
		$filter->orderBy(f('Product.dateCreated'), ARSelectFilter::ORDER_DESC);
		$categoryId = $this->getRequest()->get('id');
		if(preg_match('/^\d+$/', $categoryId))
		{
			$this->shouldBeEnabledFeed('CATEGORY_PRODUCTS');
			$category = Category::getInstanceById($categoryId, Category::LOAD_DATA);
			$filter = new ProductFilter($category, $filter);
		}
		else
		{
			$this->shouldBeEnabledFeed('ALL_PRODUCTS');
			$category = Category::getRootNode(true);
			$filter = new ProductFilter($category, $filter);
			$filter->includeSubCategories();
		}
		$feed = new ProductFeed($filter);
		$feed->setFlush();
		$feed->limit($this->config->get('NUMBER_OF_PRODUCTS_TO_INCLUDE'));
		$this->set('feed', $feed);
		$this->set('category', $category->toArray());
	}

	public function newsAction()
	{
		$this->shouldBeEnabledFeed('NEWS_POSTS');
		$this->setLayout('empty');
		$response = new XMLResponse();
		$f = select(eq(f('NewsPost.isEnabled'), true));
		$f->limit($this->config->get('NUMBER_OF_NEWS_POSTS_TO_INCLUDE'));
		$f->orderBy(f('NewsPost.position'), ARSelectFilter::ORDER_DESC);
		$this->set('feed', ActiveRecordModel::getRecordSetArray('NewsPost', $f));
		$this->application->getLocale()->translationManager()->loadFile('News');
	}

	private function shouldBeEnabledFeed($feedName)
	{
		if(!is_array($this->enabledFeeds))
		{
			$this->enabledFeeds = $this->config->get('ENABLED_FEEDS');	
		}
		if(!array_key_exists($feedName, $this->enabledFeeds))
		{
			throw new NotFoundException($this);
		}
		return true;
	}
}

?>