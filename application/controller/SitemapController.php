<?php


ClassLoader::importNow('application.helper.smarty.function#categoryUrl');
ClassLoader::importNow('application.helper.smarty.function#productUrl');
ClassLoader::importNow('application.helper.smarty.function#newsUrl');


/**
 * Generates XML sitemaps
 *
 * @author Integry Systems
 * @package application.controller
 */
class SitemapController extends FrontendController
{
	const MAX_URLS = 5000;

	public function init()
	{
		$this->setLayout('empty');

		if (!$this->config->get('ENABLE_SITEMAPS'))
		{
			throw new ActionNotFoundException($this);
		}
	}

	public function index()
	{
		$languages = $this->application->getLanguageArray(true);
		$defaultLanguage = $this->application->getDefaultLanguageCode();

		$maps = array();
		foreach ($this->getSupportedTypes() as $type)
		{
			for ($k = 0; $k < $this->getPageCount($type, $this->getSelectFilter($type)); $k++)
			{
				foreach ($languages as $lang)
				{
					$params = array('controller' => 'sitemap', 'action' => 'sitemap', 'id' => $k, 'type' => $type);
					if ($lang != $defaultLanguage)
					{
						$params['requestLanguage'] = $lang;
					}
					$maps[] = array('loc' => $this->router->createFullUrl($this->router->createUrl($params)));
				}
			}
		}

		$this->router->removeAutoAppendVariable('requestLanguage');

		return new ActionResponse('maps', $maps);
	}

	public function sitemap()
	{
		$class = $this->request->gget('type');
		$page = $this->request->gget('id', 0);

		if (!in_array($class, $this->getSupportedTypes()))
		{
			throw new ActionNotFoundException($this);
		}

		$cache = new OutputCache('sitemap', $this->request->gget('route'));
		if ($cache->isCached() && ($cache->getAge() < 86400))
		{
			return new RawResponse($cache->getData());
		}

		$this->setCache($cache);

		$f = $this->getSelectFilter($class);

		$entries = array();
		foreach ($this->getPage($class, $page, $f, $this->getClassFields($class)) as $row)
		{
			$entries[] = $this->getEntryData($class, $row);
		}

		return new ActionResponse('entries', $entries);
	}

	public function full()
	{
		$languages = $this->application->getLanguageArray(true);
		$defaultLanguage = $this->application->getDefaultLanguageCode();
		$class = $this->request->gget('type');
		$page = $this->request->gget('id', 0);

		$entries = array();
		foreach ($this->getSupportedTypes() as $type)
		{
			for ($k = 0; $k < $this->getPageCount($type, $this->getSelectFilter($type)); $k++)
			{
				$f = $this->getSelectFilter($type);
				foreach ($this->getPage($type, $page, $f, $this->getClassFields($type)) as $row)
				{
					foreach ($languages as $lang)
					{
						if ($lang != $defaultLanguage)
						{
							$this->router->setAutoAppendVariables(array('requestLanguage' => $lang));
						}
						else
						{
							$this->router->removeAutoAppendVariable('requestLanguage');
						}

						$entries[] = $this->getEntryData($type, $row);
					}
				}
			}
		}

		return new ActionResponse('entries', $entries);
	}

	public function ping()
	{
		if (!$this->user->hasBackendAccess())
		{
			return new RawResponse('unauthorized');
		}

		$url = $this->router->createFullUrl($this->router->createUrl(array('controller' => 'sitemap')));
		$ping = array(
			'Google' => 'http://www.google.com/webmasters/tools/ping?sitemap=' . $url,
			'MSN Live' => 'http://www.bing.com/webmaster/ping.aspx?siteMap=' . $url,
			'Ask' => 'http://submissions.ask.com/ping?sitemap=' . $url,
			);
			
		$result = array();
		foreach ($ping as $site => $pingUrl)
		{
			$result[$site] = strpos($this->fetchUrl($pingUrl, true), '200 OK') > 0;
		}

		return new ActionResponse('result', $result);
	}

	private function getSupportedTypes()
	{
		return array('Category', 'Product', 'NewsPost', 'StaticPage');
	}

	private function getEntryData($class, $row, $params = array())
	{
		switch ($class)
		{
			case 'StaticPage':
				return $this->getStaticPageEntry($row, $params);

			case 'NewsPost':
				return $this->getNewsPostEntry($row, $params);

			case 'Product':
				return $this->getProductEntry($row, $params);

			case 'Category':
				return $this->getCategoryEntry($row, $params);
		}
	}

	private function getStaticPageEntry($row, $params)
	{
		$urlParams = array('controller' => 'staticPage',
						   'action' => 'view',
						   'handle' => $row['handle'],
						   );

		$urlParams = array_merge($urlParams, $params);

		$router = $this->application->getRouter();

		return array('loc' => $router->createFullUrl($router->createUrl($urlParams, true)));
	}

	private function getCategoryEntry($row, $params)
	{
		$row['name'] = unserialize($row['name']);
		$row = MultilingualObject::transformArray($row, ActiveRecord::getSchemaInstance('Category'));

		return array('loc' => $this->router->createFullUrl(createCategoryUrl(array('data' => $row, 'params' => $params), $this->application)));
	}

	private function getProductEntry($row, $params)
	{
		$row['name'] = unserialize($row['name']);
		$row = MultilingualObject::transformArray($row, ActiveRecord::getSchemaInstance('Product'));

		return array('loc' => $this->router->createFullUrl(createProductUrl(array('product' => $row), $this->application)));
	}

	private function getNewsPostEntry($row, $params)
	{
		$row['title'] = unserialize($row['title']);
		$row = MultilingualObject::transformArray($row, ActiveRecord::getSchemaInstance('NewsPost'));

		return array('loc' => $this->router->createFullUrl(createNewsPostUrl(array('news' => $row), $this->application)));
	}

	private function getPage($class, $page, ARSelectFilter $f, $fields)
	{
		$f->setLimit(self::MAX_URLS, $page * self::MAX_URLS);

		$query = new ARSelectQueryBuilder();
		$query->setFilter($f);
		$query->includeTable($class);
		foreach ($fields as $field)
		{
			$query->addField($field);
		}

		return ActiveRecord::fetchDataFromDB($query);
	}

	private function getSelectFilter($class)
	{
		if ('Product' == $class)
		{
			$cat = Category::getRootNode();
			$f = $cat->getProductFilter(new ARSelectFilter());
			$f->mergeCondition($cat->getProductCondition(true));
			$f->joinTable('Category', 'Product', 'ID', 'categoryID');

			return $f;
		}

		$f = new ARSelectFilter();
		$f->setOrder(new ARFieldHandle($class, 'ID'));

		if ('StaticPage' != $class)
		{
			$f->setCondition(new EqualsCond(new ARFieldHandle($class, 'isEnabled'), true));
		}

		if ('Category' == $class)
		{
			$f->mergeCondition(new NotEqualsCond(new ARFieldHandle($class, 'ID'), Category::ROOT_ID));
		}

		return $f;
	}

	private function getClassFields($class)
	{
		switch ($class)
		{
			case 'StaticPage':
				return array('ID', 'handle');

			case 'Category':
				return array('ID', 'name');

			case 'Product':
				return array('Product.ID AS ID', 'Product.name AS name');

			case 'NewsPost':
				return array('ID', 'title');
		}
	}

	private function getPageCount($class, ARSelectFilter $f)
	{
		return ceil(ActiveRecord::getRecordCount($class, $f) / self::MAX_URLS);
	}

	private function fetchURL($url, $headersOnly = false)
	{
		$url_parsed = parse_url($url);
		$host = $url_parsed["host"];
		$port = isset($url_parsed["port"]) ? $url_parsed["port"] : 80;

		$path = $url_parsed["path"];
		if ($url_parsed["query"] != "")
			$path .= "?".$url_parsed["query"];

		$out = "GET $path HTTP/1.0\r\nHost: $host\r\n\r\n";

		$fp = fsockopen($host, $port, $errno, $errstr, 30);

		fwrite($fp, $out);
		$body = false;
		$in = '';
		while (!feof($fp)) {
			$s = fgets($fp, 1024);
			if ( ($body && !$headersOnly) || (!$body && $headersOnly))
			{
				$in .= $s;
			}

			if ( $s == "\r\n" )
				$body = true;
		}

		fclose($fp);

		return $in;
	}
}

?>
