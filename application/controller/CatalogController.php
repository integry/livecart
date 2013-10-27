<?php

ClassLoader::import('application/controller/FrontendController');
ClassLoader::import('application/model/Currency');
ClassLoader::import('application/model/category/Category');
ClassLoader::import('application/model/product/ProductPrice');
ClassLoader::importNow('application/helper/CreateHandleString');
include_once($this->config->getPath('application/helper/smarty') . '/function.categoryUrl.php');

/**
 * Base class for all front-end related controllers
 *
 * @author Integry Systems
 * @package application/controller
 */
abstract class CatalogController extends FrontendController
{
	protected function getCategory()
	{
		return $this->category;
	}

	protected function getContext()
	{
		$contextFilters = array();
		foreach ($this->filters as $filter)
		{
			$contextFilters[] = filterHandle($filter);
		}
		$context = array('filters' => implode(',', $contextFilters), 'originalAction' => $this->router->getActionName());

		foreach (array('category', 'quickShopSequence', 'includeSub') as $key)
		{
			if ($this->request->get($key))
			{
				$context[$key] = $this->request->get($key);
			}
		}

		return $context;
	}

	public function getSelectFilterAction()
	{
		$selectFilter = new ARSelectFilter();
		$this->application->processInstancePlugins('productFilter', $selectFilter);

		$order = $this->request->get('sort');
		$defOrder = strtolower($this->config->get('SORT_ORDER'));
		if (!$order)
		{
			$order = $defOrder;
		}

		$this->applySortorderBy($selectFilter, $order);

		// setup ProductFilter
		$productFilter = new ProductFilter($this->getCategory(), $selectFilter);

		if ($this->config->get('INCLUDE_SUBCATEGORY_PRODUCTS'))
		{
			$productFilter->includeSubcategories();
		}

		$this->productFilter = $productFilter;
		foreach ($this->filters as $filter)
		{
			$productFilter->applyFilter($filter);

			if ($filter instanceof SearchFilter)
			{
				$productFilter->includeSubcategories();
				$searchQuery = $filter->getKeywords();
			}
		}

		if (($this->getCategory()->isRoot() && $this->filters) || $this->filters || $this->request->get('includeSub'))
		{
			$productFilter->includeSubcategories();
		}

		return $productFilter;
	}

	public function getAppliedFiltersAction(FrontendController $controller = null)
	{
		if (!$controller)
		{
			$controller = $this;
		}

		if ($this->filters)
		{
			return $this->filters;
		}

		$request = $controller->getRequest();
		$app = $controller->getApplication();

		if($controller->config->get('FILTER_STYLE') == 'FILTER_STYLE_CHECKBOXES')
		{
			$delimiter = ',';
			$filters = explode($delimiter, $request->get('filters'));
			foreach($request->getRawRequest() as $key=>$value)
			{
				if(strtolower($value) == 'on') // could be a filter
				{
					$filters[] = $key;
				}
			}
			$request = 'filters', implode($delimiter, array_filter($filters)));
		}

		if ($request->get('filters'))
		{
			$filterGroups = $this->getCategory()->getFilterGroupSet();

			$valueFilterIds = array();
			$selectorFilterIds = array();
			$manufacturerFilterIds = array();
			$priceFilterIds = array();
			$searchFilters = array();

			$filters = explode(',', $request->get('filters'));

			foreach ($filters as $filter)
			{
			  	$pair = explode('-', $filter);

			  	if (count($pair) < 2)
			  	{
					continue;
				}

				$id = array_pop($pair);

				if (substr($id, 0, 1) == 'v')
				{
					$selectorFilterIds[] = substr($id, 1);
				}
				else if (substr($id, 0, 1) == 'm')
				{
					$manufacturerFilterIds[] = substr($id, 1);
				}
				else if (substr($id, 0, 1) == 'p')
				{
					$priceFilterIds[] = substr($id, 1);
				}
				else if ('s' == $id)
				{
					$searchFilters[] = implode('-', $pair);
				}
				else
				{
					$valueFilterIds[] = $id;
				}
			}

			// get value filters
			if ($valueFilterIds)
			{
				$f = new ARSelectFilter();
				$c = new INCond('Filter.ID', $valueFilterIds);
				$f->setCondition($c);
				$filters = ActiveRecordModel::getRecordSet('Filter', $f, Filter::LOAD_REFERENCES);
				foreach ($filters as $filter)
				{
					$this->filters[] = $filter;
				}
			}

			if ($selectorFilterIds)
			{
				$f = new ARSelectFilter();
				$c = new INCond('SpecFieldValue.ID', $selectorFilterIds);
				$f->setCondition($c);
				$filterValues = ActiveRecordModel::getRecordSet('SpecFieldValue', $f, array('SpecField', 'Category'));
				foreach ($filterValues as $value)
				{
					$this->filters[] = new SelectorFilter($value, $filterGroups->filter('specField', $value->specField)->get(0));
				}
			}

			if ($manufacturerFilterIds)
			{
				$f = new ARSelectFilter();
				$c = new INCond('Manufacturer.ID', $manufacturerFilterIds);
				$f->setCondition($c);
				$manufacturers = ActiveRecordModel::getRecordSetArray('Manufacturer', $f);
				foreach ($manufacturers as $manufacturer)
				{
					$this->filters[] = new ManufacturerFilter($manufacturer['ID'], $manufacturer['name']);
				}
			}

			if ($priceFilterIds)
			{
				foreach ($priceFilterIds as $filterId)
				{
					$this->filters[] = new PriceFilter($filterId, $app);
				}
			}

			if ($searchFilters)
			{
				foreach ($searchFilters as $query)
				{
					$this->filters[] = new SearchFilter($query);
				}
			}
		}
	}

	/**
	 *  Apply selected product sort order to ARSelectFilter instance
	 */
	protected function applySortorderBy(ARSelectFilter $selectFilter, $order)
	{
		$dir = array_pop(explode('_', $order)) == 'asc' ? 'ASC' : 'DESC';

		if (substr($order, 0, 12) == 'product_name')
		{
			$selectFilter->orderBy(Product::getLangOrderHandle('Product.name'), $dir);
		}
		else if (substr($order, 0, 5) == 'price')
		{
			$selectFilter->orderBy('ProductPrice.price', $dir);
			$selectFilter->joinTable('ProductPrice', 'Product', 'productID AND (ProductPrice.currencyID = "' . $this->application->getDefaultCurrencyCode() . '")', 'ID');
		}
		else if (substr($order, 0, 3) == 'sku')
		{
			$selectFilter->orderBy('ProductPrice.price', $dir);
			$selectFilter->joinTable('ProductPrice', 'Product', 'productID AND (ProductPrice.currencyID = "' . $this->application->getDefaultCurrencyCode() . '")', 'ID');
		}
		else if ('newest_arrivals' == $order)
		{
			$selectFilter->orderBy('Product.dateCreated', 'DESC');
		}
		else if (in_array($order, array('rating', 'sku')))
		{
			$selectFilter->orderBy(new ARFieldHandle('Product', $order), $dir);
		}
		else if ('sales_rank' == $order)
		{
			Product::updateSalesRank();
			$selectFilter->orderBy('Product.salesRank', 'DESC');
		}
		else if (is_numeric($fieldID = array_shift(explode('-', $order))) && !SpecField::getInstanceByID($fieldID, true)->isMultiValue)
		{
			$field = SpecField::getInstanceByID($fieldID);
			$field->defineJoin($selectFilter);
			$f = $field->getJoinAlias() . ($field->isSelector() ? '_value' : '') . '.value';
			$selectFilter->orderBy(new ARExpressionHandle($f . ' IS NOT NULL'), 'DESC');
			$selectFilter->orderBy(new ARExpressionHandle($f . ' != ""'), 'DESC');

			$f = new ARExpressionHandle($f);
			if ($field->isSelector())
			{
				$f = MultiLingualObject::getLangOrderHandle($f);
			}

			$selectFilter->orderBy($f, array_pop(explode('_', $order)) == 'desc' ? 'DESC' : 'ASC');
		}
		else
		{
			$selectFilter->orderBy('Product.isFeatured', 'DESC');
			$selectFilter->orderBy('Product.salesRank', 'DESC');
			$selectFilter->orderBy('Product.position', 'DESC');
		}
	}

	protected function addCategoriesToBreadCrumb($path, $addLast = false)
	{
		include_once($this->config->getPath('application/helper/smarty') . '/function.categoryUrl.php');

		$i = 0;
		$max = $this->config->get('BREADCRUMB_DEPTH') ? $this->config->get('BREADCRUMB_DEPTH') : count($path);
		foreach ($path as $nodeArray)
		{
			$url = createCategoryUrl(array('data' => $nodeArray), $this->application);

			if (++$i <= $max || ($addLast && ($i == count($path))))
			{
				$this->addBreadCrumb($nodeArray['name_lang'], $url);
			}
		}

		// set return path
		if (isset($url))
		{
			$this->router->setReturnPath($this->router->getRouteFromUrl($url));
		}

		if (isset($nodeArray))
		{
			return $nodeArray;
		}
	}

	protected function addFiltersToBreadCrumb($category, $page = 1)
	{
		include_once($this->config->getPath('application/helper/smarty') . '/function.categoryUrl.php');

		// add filters to breadcrumb
		if (!isset($category))
		{
			$category = $this->getCategory()->toArray();
		}

		$params = array('data' => $category, 'filters' => array());
		foreach ($this->filters as $filter)
		{
			$filter = $filter->toArray();
			$params['filters'][] = $filter;

			// add current page number to the last item URL
			if (count($params['filters']) == count($this->filters))
			{
				$params['page'] = $page;
			}

			$url = createCategoryUrl($params, $this->application);

			if ($this->config->get('BREADCRUMB_FILTERS'))
			{
				$this->addBreadCrumb($filter['name_lang'], $url);
			}
		}

		// set return path
		if (isset($url))
		{
			$this->router->setReturnPath($this->router->getRouteFromUrl($url));
		}

		return $params;
	}
}

?>