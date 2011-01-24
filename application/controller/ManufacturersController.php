<?php

ClassLoader::import('application.controller.FrontendController');
ClassLoader::import('application.model.product.Manufacturer');
ClassLoader::import('application.model.product.ProductFilter');
ClassLoader::import('application.model.filter.ManufacturerFilter');
ClassLoader::import('application.model.category.Category');

/**
 * Manufacturer list
 *
 * @author Integry Systems
 * @package application.controller
 */
class ManufacturersController extends FrontendController
{
	public function index()
	{
/*
MANUFACTURER_PAGE_LIST_STYLE
	MANPAGE_STYLE_ALL_IN_ONE_PAGE
	MANPAGE_STYLE_GROUP_BY_FIRST_LETTER
MANUFACTURER_PAGE_NUMBER_OF_COLUMNS
MANUFACTURER_PAGE_PER_PAGE
*/
		$config = $this->getApplication()->getConfig();
		$request = $this->getRequest();
		$listStyle = $config->get('MANUFACTURER_PAGE_LIST_STYLE');
		$currentLetter = $listStyle == 'MANPAGE_STYLE_GROUP_BY_FIRST_LETTER' ? $request->get('letter') : null;

		// pagination
		$page = $request->get('page', 1);

		extract(Manufacturer::getActiveProductManufacturers(array(
			'currentPage' => $page,
			'startingWith' => $currentLetter))
		); // creates $manufacturers, $count, $counts

		foreach ($manufacturers as &$manufacturer)
		{
			$manufacturer['url'] = $this->getManufacturerFilterUrl($manufacturer);
		}
		$this->addBreadCrumb($this->translate('_manufacturers'), '');
		$response = new ActionResponse();
		$response->setReference('manufacturers', $manufacturers);
		$response->set('counts', $counts); // product count
		$response->set('count', $count); // manufacturers count
		$response->set('rootCat', Category::getRootNode()->toArray());
		$response->set('currentPage', $page);
		$response->set('perPage', $config->get('MANUFACTURER_PAGE_PER_PAGE'));

		$paginateUrlParams = array('controller' => 'manufacturers', 'action' => 'index', 'query'=>array('page' => '_000_'));
		if ($listStyle == 'MANPAGE_STYLE_GROUP_BY_FIRST_LETTER')
		{
			$paginateUrlParams['query']['letter'] = $currentLetter;
		}
		$response->set('url', $this->router->createURL($paginateUrlParams, true));
		if ($listStyle == 'MANPAGE_STYLE_GROUP_BY_FIRST_LETTER')
		{
			$letters = Manufacturer::getActiveProductManufacturerFirstLetters();
			$response->set('currentLetter',$currentLetter);
			$response->set('letters',$letters);
		}
		return $response;
	}

	public function view()
	{
		$manufacturer = Manufacturer::getInstanceByID($this->request->get('id'), true);
		$manufacturer->load();
		return new RedirectResponse($this->getManufacturerFilterUrl($manufacturer->toArray()));
	}

	private function getManufacturerFilterUrl($manufacturerArray)
	{
		static $templateUrl;

		if (!$templateUrl)
		{
			$templateUrl = $this->getFilterUrlTemplate();
		}

		return strtr($templateUrl, array('#' => $manufacturerArray['ID'], '|' => createHandleString($manufacturerArray['name'])));
	}

	private function getFilterUrlTemplate()
	{
		include_once(ClassLoader::getRealPath('application.helper.smarty') . '/function.categoryUrl.php');
		$params = array('filters' => array(new ManufacturerFilter(999, '___')), 'data' => Category::getRootNode()->toArray());
		$templateUrl = createCategoryUrl($params, $this->application);
		return strtr($templateUrl, array(999 => '#', '___' => '|'));
	}
}