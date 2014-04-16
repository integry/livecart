<?php

use product\Product;
use category\Category;
use staticpage\StaticPage;

/**
 * Generates XML sitemaps
 *
 * @author Integry Systems
 * @package application/controller
 */
class SitemapController extends FrontendController
{
	const MAX_URLS = 5000;

	public function initialize()
	{
		if (!$this->config->get('ENABLE_SITEMAPS'))
		{
			return $this->response->redirect('/');
		}
	}

	public function indexAction()
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
					$params = array('id' => $k, 'type' => $type);
					if ($lang != $defaultLanguage)
					{
						$params['requestLanguage'] = $lang;
					}
					$maps[] = array('loc' => $this->url->get('sitemap/sitemap', $params));
				}
			}
		}

		$this->set('maps', $maps);
		$this->set('xml', '<' . '?xml');
	}

	public function sitemapAction()
	{
		$class = $this->request->get('type');
		$page = $this->request->get('id', null, 0);

		if (!in_array($class, $this->getSupportedTypes()))
		{
			throw new ActionNotFoundException($this);
		}

		/*
		$cache = new OutputCache('sitemap', $this->request->get('route'));
		if ($cache->isCached() && ($cache->getAge() < 86400))
		{
			return new RawResponse($cache->getData());
		}

		$this->setCache($cache);
		*/

		$f = $this->getSelectFilter($class);

		$entries = array();
		foreach ($this->getPage($class, $page, $f, $this->getClassFields($class)) as $row)
		{
			$entries[] = $this->getEntryData($class, $row);
		}

		$this->set('entries', $entries);
		$this->set('xml', '<' . '?xml');
	}

	public function fullAction()
	{
		$languages = $this->application->getLanguageArray(true);
		$defaultLanguage = $this->application->getDefaultLanguageCode();
		$class = $this->request->get('type');
		$page = $this->request->get('id', 0);

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

		$this->set('entries', $entries);
	}

	public function pingAction()
	{
		if (!$this->user->hasBackendAccess())
		{
			return new RawResponse('unauthorized');
		}

		$url = $this->router->createFullUrl($this->url->get('sitemap'));
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

		$this->set('result', $result);
	}

	private function getSupportedTypes()
	{
		return array('category\Category', 'product\Product', /* 'NewsPost', */'staticpage\StaticPage');
	}

	private function getEntryData($class, $row, $params = array())
	{
		switch ($class)
		{
			case 'staticpage\StaticPage':
				return $this->getStaticPageEntry($row, $params);

			case 'NewsPost':
				return $this->getNewsPostEntry($row, $params);

			case 'product\Product':
				return $this->getProductEntry($row, $params);

			case 'category\Category':
				return $this->getCategoryEntry($row, $params);
		}
	}

	private function getStaticPageEntry($row, $params)
	{
		return array('loc' => $this->url->get(route($row)));
		$urlParams = array('controller' => 'staticpage\StaticPage',
						   'action' => 'view',
						   'handle' => $row['handle'],
						   );

		$urlParams = array_merge($urlParams, $params);

		$router = $this->application->getRouter();

		return array('loc' => $router->createFullUrl($router->createUrl($urlParams, true)));
	}

	private function getCategoryEntry($row, $params)
	{
		return array('loc' => $this->url->get(route($row)));
	}

	private function getProductEntry($row, $params)
	{
		return array('loc' => $this->url->get(route($row)));
	}

	private function getNewsPostEntry($row, $params)
	{
		return array('loc' => $this->url->get(route($row)));
	}

	private function getPage($class, $page, $f, $fields)
	{
		$f->limit(self::MAX_URLS, $page * self::MAX_URLS);

		return $f->getQuery()->execute();
	}

	private function getSelectFilter($class)
	{
		if ('product\Product' == $class)
		{
			$cat = Category::getRootNode();
			$f = new \product\ProductFilter($cat);
			return $f;
		}
		
		$f = $this->modelsManager->createBuilder()
			->addFrom($class)
			->columns($class . '.*');

		$f->orderBy($class . '.ID');

		if ('staticpage\StaticPage' != $class)
		{
			$f->andWhere('isEnabled = 1');
		}

		if ('category\Category' == $class)
		{
			$f->andWhere('ID != :root:', array('root' => Category::ROOT_ID));
		}

		return $f;
	}

	private function getClassFields($class)
	{
		switch ($class)
		{
			case 'staticpage\StaticPage':
				return array('ID', 'handle');

			case 'category\Category':
				return array('ID', 'name');

			case 'product\Product':
				return array('Product.ID AS ID', 'Product.name AS name');

			case 'NewsPost':
				return array('ID', 'title');
		}
	}

	private function getPageCount($class, $f)
	{
		return ceil($f->getQuery()->execute()->count() / self::MAX_URLS);
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
