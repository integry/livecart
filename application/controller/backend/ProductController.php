<?php

ClassLoader::import('application.controller.backend.abstract.ActiveGridController');
ClassLoader::import('application.model.category.Category');
ClassLoader::import('application.model.filter.FilterGroup');
ClassLoader::import('application.model.product.Product');
ClassLoader::import('application.model.product.ProductPrice');
ClassLoader::import('application.model.category.ProductCategory');
ClassLoader::import('application.model.product.ProductSpecification');
ClassLoader::import('application.helper.ActiveGrid');
ClassLoader::import('application.helper.massAction.MassActionInterface');
ClassLoader::import('application.model.order.OrderedItem');
ClassLoader::import('application.model.delivery.ShippingClass');
ClassLoader::import('application.model.tax.TaxClass');

/**
 * Controller for handling product based actions performed by store administrators
 *
 * @package application.controller.backend
 * @author Integry Systems
 * @role product
 */
class ProductController extends ActiveGridController implements MassActionInterface
{
	private $isQuickEdit = false;
	private $quickEditValidation = false;

    public function index()
	{
		ClassLoader::import('application.LiveCartRenderer');

		$category = Category::getInstanceByID($this->request->get("id"), Category::LOAD_DATA);

		$response = new ActionResponse();
		$response->set('categoryID', $category->getID());
		$response->set('currency', $this->application->getDefaultCurrency()->getID());
		$response->set('themes', array_merge(array(''), LiveCartRenderer::getThemeList()));
		$response->set('shippingClasses', $this->getSelectOptionsFromSet(ShippingClass::getAllClasses()));
		$response->set('taxClasses', $this->getSelectOptionsFromSet(TaxClass::getAllClasses()));
		$response->set('attributes', $category->getSpecificationFieldArray());

		$this->setGridResponse($response);

		$path = $this->getCategoryPathArray($category);
		$response->set('path', $path);

		return $response;
	}

	protected function getPreparedRecord($row, $displayedColumns)
	{
		$records = parent::getPreparedRecord($row, $displayedColumns);

		$currencies = $this->application->getCurrencyArray();

		if (!empty($row['children']))
		{
			foreach ($row['children'] as $child)
			{
				$priceSetting = $child['childSettings']['price'];
				foreach ($currencies as $currency)
				{
					$priceField = 'price_' . $currency;
					if (Product::CHILD_ADD == $priceSetting)
					{
						$child[$priceField] = '+' . ($child[$priceField] - $row[$priceField]);
					}
					else if (Product::CHILD_SUBSTRACT == $priceSetting)
					{
						$child[$priceField] = $child[$priceField] - $row[$priceField];
					}

					if (empty($child[$priceField]))
					{
						$child[$priceField] = '';
					}
				}

				$weightSetting = $child['childSettings']['weight'];
				if (Product::CHILD_ADD == $weightSetting)
				{
					$child['shippingWeight'] = '+' . ($child['shippingWeight'] + $row['shippingWeight']);
				}
				else if (Product::CHILD_SUBSTRACT == $weightSetting)
				{
					$child['shippingWeight'] = $row['shippingWeight'] - $child['shippingWeight'];
				}

				$records = array_merge($records, $this->getPreparedRecord($child, $displayedColumns));
			}
		}

		return $records;
	}

	protected function getUserGroups()
	{
		if (!$this->userGroups)
		{
			$this->userGroups = array();
			$groups = ActiveRecordModel::getRecordSetArray('UserGroup', new ARSelectFilter());
			foreach ($groups as $group)
			{
				$this->userGroups[$group['ID']] = $group['name'];
			}
		}

		return $this->userGroups;
	}

	public function changeColumns()
	{
		parent::changeColumns();

		return new ActionRedirectResponse('backend.product', 'index', array('id' => $this->request->get('id')));
	}

	protected function getClassName()
	{
		return 'Product';
	}

	protected function getCSVFileName()
	{
		return 'products.csv';
	}

	protected function getRequestColumns()
	{
		return $this->getDisplayedColumns(Category::getInstanceByID($this->getRequestCategory(), Category::LOAD_DATA));
	}

	protected function getAvailableRequestColumns()
	{
		return $this->getAvailableColumns(Category::getInstanceByID($this->getRequestCategory(), Category::LOAD_DATA));
	}

	protected function getReferencedData()
	{
		return array('Category', 'Manufacturer', 'DefaultImage' => 'ProductImage', 'TaxClass', 'ShippingClass');
	}

	protected function getColumnValue($product, $class, $field)
	{
		if ($class == 'hiddenType')
		{
			return $product['type'];
		}

		$value = '';

		if ('Product' == $class)
		{
			$value = isset($product[$field . '_lang']) ?
						$product[$field . '_lang'] : (isset($product[$field]) ? $product[$field] : '');
		}
		else if ('ProductPrice' == $class)
		{
			$currency = $this->application->getDefaultCurrency()->getID();
			$value = isset($product['price_' . $currency]) ? $product['price_' . $currency] : 0;
		}
		else if ('specField' == $class)
		{
			if (isset($product['attributes'][$field]))
			{
				$attr = $product['attributes'][$field];
				if ($attr['SpecField']['isMultiValue'])
				{
					$vals = array();
					foreach ($attr['values'] as $val)
					{
						$vals[] = $val['value_lang'];
					}

					$value = implode(' / ', $vals);
				}
				else
				{
					if (isset($attr['value_lang']))
					{
						$value = $attr['value_lang'];
					}
					else if (isset($attr['value']))
					{
						$value = $attr['value'];
					}
					else
					{
						$value = '';
					}
				}
			}
		}
		else if ('ProductImage' == $class)
		{
			if (!empty($product['DefaultImage']['urls']))
			{
				$value = $product['DefaultImage']['urls'][1];
			}
		}
		else if ('Category' == $class)
		{
			if (isset($this->categories))
			{
				$value = implode(' / ', $this->getCategoryPath($product['categoryID']));
			}
		}
		else
		{
			$value = parent::getColumnValue($product, $class, $field);
		}

		return $value;
	}

	protected function getSelectFilter()
	{
		$id = $this->getRequestCategory();
		$category = Category::getInstanceByID($id, Category::LOAD_DATA);

		$filter = new ARSelectFilter($category->getProductCondition(true));
		$filter->joinTable('ProductPrice', 'Product', 'productID AND (ProductPrice.currencyID = "' . $this->application->getDefaultCurrencyCode() . '")', 'ID');

		$filter->mergeCondition(
			new EqualsCond(new ARFieldHandle('ProductPrice', 'type'), ProductPrice::TYPE_GENERAL_PRICE));

		foreach ($this->getDisplayedColumns($category) as $column => $type)
		{
			$parts = explode('.', $column);
			if (array_shift($parts) == 'specField')
			{
				$field = SpecField::getInstanceByID(array_shift($parts));

				if (!$field->isMultiValue->get())
				{
					$field->defineJoin($filter);
				}
				else
				{
					$values = is_array($this->request->get('filters')) ? $this->request->get('filters') : json_decode($this->request->get('filters'), true);
					$values = isset($values[$column]) ? $values[$column] : null; 
					
					if ($values)
					{
						foreach (ActiveRecordModel::getRecordSet('SpecFieldValue', select(in(f('SpecFieldValue.ID'), explode(',', urldecode($values))))) as $field)
						{
							$f = new SelectorFilter($field);
							$f->defineJoin($filter);
							$filter->mergeCondition($f->getCondition());
						}
					}
				}
			}
		}

		return $filter;
	}

	protected function processDataArray($productArray, $displayedColumns)
	{
		// load specification data
		foreach ($displayedColumns as $column => $type)
		{
			if($column == 'hiddenType') continue;

			list($class, $field) = explode('.', $column, 2);
			if ('specField' == $class)
			{
				ProductSpecification::loadSpecificationForRecordSetArray($productArray, true);
				break;
			}
		}

		// load price data
		ProductPrice::loadPricesForRecordSetArray($productArray, false);

		// load child products
		if (isset($displayedColumns['Product.parentID']))
		{
			ProductSet::loadVariationTypesForProductArray($productArray);
			ProductSet::loadChildrenForProductArray($productArray);
		}

		$defCurrency = $this->application->getDefaultCurrencyCode();
		foreach ($productArray as &$product)
		{
			foreach ($this->getUserGroups() as $groupID => $groupName)
			{
				if (isset($product['priceRules'][$defCurrency][1][$groupID]))
				{
					$product['GroupPrice'][$groupID] = $product['priceRules'][$defCurrency][1][$groupID];
				}
			}
		}

		return $productArray;
	}

	/**
	 * @role mass
	 */
	public function processMass()
	{
		$filter = $this->getSelectFilter();

		$act = $this->request->get('act');
		$field = array_pop(explode('_', $act, 2));

		if ('move' == $act)
		{
			new ActiveGrid($this->application, $filter, $this->getClassName());

			$cat = Category::getInstanceById($this->request->get('categoryID'), Category::LOAD_DATA);
			$update = new ARUpdateFilter();

			$update->setCondition($filter->getCondition());
			$update->addModifier('Product.categoryID', $cat->getID());
			$update->joinTable('ProductPrice', 'Product', 'productID AND (ProductPrice.currencyID = "' . $this->application->getDefaultCurrencyCode() . '")', 'ID');

			ActiveRecord::beginTransaction();
			ActiveRecord::updateRecordSet('Product', $update, Product::LOAD_REFERENCES);
			Category::recalculateProductsCount();
			ActiveRecord::commit();

			return new JSONResponse(array('act' => $this->request->get('act')), 'success', $this->translate('_move_succeeded'));
		}

		// remove design themes
		if (('theme' == $act) && !$this->request->get('theme'))
		{
			ClassLoader::import('application.model.presentation.CategoryPresentation');
			ActiveRecord::deleteRecordSet('CategoryPresentation', new ARDeleteFilter($filter->getCondition()), null, array('Product', 'Category'));

			return new JSONResponse(array('act' => $this->request->get('act')), 'success', $this->translate('_themes_removed'));
		}

		$params = array();
		if ('manufacturer' == $act)
		{
			$params['manufacturer'] = Manufacturer::getInstanceByName($this->request->get('manufacturer'));
		}
		else if (in_array($act, array('price', 'inc_price', 'multi_price', 'div_price')))
		{
			$params['baseCurrency'] = $this->application->getDefaultCurrencyCode();
			$params['price'] = $this->request->get($act);
			$params['currencies'] = $this->application->getCurrencySet();
			$params['inc_price_value'] = $this->request->get($act . '_value');
			$params['inc_quant_price'] = $this->request->get($act . '_price');
		}
		else if ('addRelated' == $act)
		{
			$params['relatedProduct'] = Product::getInstanceBySKU($this->request->get('related'));
			if (!$params['relatedProduct'])
			{
				return new JSONResponse(0);
			}
		}
		else if ((substr($act, 0, 13) == 'set_specField') || (substr($act, 0, 16) == 'remove_specField'))
		{
			$id = array_pop(explode('_', $act));
			$field = SpecField::getInstanceByID($id, true);
			$a = substr($act, 0, 3);
			
			$request = clone $this->request;
			foreach ($request->toArray() as $key => $value)
			{
				if (substr($key, 0, 18) == 'checkbox_specItem_')
				{
					$item = substr($key, 9);
					
					if (!$request->isValueSet($item))
					{
						$request->remove($key);
						$request->remove($item);
					}
					else if ('rem' == $a)
					{
						$request->remove($item);
					}
				}
			}
			
			$params['field'] = $field;
			$params['request'] = $request;
		}
		else if ($this->request->get('categoryID'))
		{
			$params['category'] = Category::getInstanceById($this->request->get('categoryID'), Category::LOAD_DATA);
		}
		else if ('theme' == $act)
		{
			ClassLoader::import('application.model.presentation.CategoryPresentation');
			$params['theme'] = $this->request->get('theme');
		}
		else if ('shippingClass' == $act)
		{
			$params['shippingClass'] = $this->request->get('shippingClass');
		}
		else if ('taxClass' == $act)
		{
			$params['taxClass'] = $this->request->get('taxClass');
		}

		$response = parent::processMass($params);

		if ($this->request->get('categoryID'))
		{
			Category::recalculateProductsCount();
		}

		return $response;
	}

	protected function getMassActionProcessor()
	{
		 ClassLoader::import('application.helper.massAction.ProductMassActionProcessor');
		 return 'ProductMassActionProcessor';
	}

	protected function getMassCompletionMessage()
	{
		return $this->translate('_mass_action_succeed');
	}

	protected function getMassValidator()
	{
		$validator = parent::getMassValidator();

		$validator->addFilter('set_price', new NumericFilter(''));
		$validator->addFilter('set_stock', new NumericFilter(''));
		$validator->addFilter('inc_price', new NumericFilter(''));
		$validator->addFilter('inc_stock', new NumericFilter(''));
		$validator->addFilter('set_minimumQuantity', new NumericFilter(''));
		$validator->addFilter('set_shippingSurchargeAmount', new NumericFilter(''));

		return $validator;
	}

	public function getAvailableColumns(Category $category, $specField = true)
	{
		$availableColumns = parent::getAvailableColumns();

		// specField columns
		if ($specField)
		{
			$fields = $category->getSpecificationFieldSet(Category::INCLUDE_PARENT);
			foreach ($fields as $field)
			{
				$fieldArray = $field->toArray();
				$availableColumns['specField.' . $field->getID()] = array
					(
						'name' => $fieldArray['name_lang'],
						'type' => $field->isSimpleNumbers() ? 'numeric' : 'text'
					);
			}
		}

		$availableColumns['ProductImage.url'] = array
			(
				'name' => $this->translate('ProductImage.url'),
				'type' => 'text'
			);

		$availableColumns['ShippingClass.name'] = array
			(
				'name' => $this->translate('Product.shippingClass'),
				'type' => 'text'
			);

		$availableColumns['TaxClass.name'] = array
			(
				'name' => $this->translate('Product.taxClass'),
				'type' => 'text'
			);

		unset($availableColumns['Product.categoryIntervalCache']);
		unset($availableColumns['Product.childSettings']);
		unset($availableColumns['Product.ratingSum']);
		unset($availableColumns['Product.salesRank']);

		return $availableColumns;
	}

	protected function getCustomColumns()
	{
		$availableColumns['Manufacturer.name'] = 'text';
		$availableColumns['ProductPrice.price'] = 'numeric';
		$availableColumns['hiddenType'] = 'numeric';

		return $availableColumns;
	}

	public function export()
	{
		$category = Category::getInstanceByID($this->getRequestCategory());
		$category->load();

		$filter = select(gte(f('Category.lft'), $category->lft->get()), lte(f('Category.rgt'), $category->rgt->get()));
		$categories = ActiveRecordModel::getRecordsetArray('Category', $filter);

		$this->categories = array();
		foreach (array_merge($category->getPathNodeArray(), $categories) as $cat)
		{
			$this->categories[$cat['ID']] = $cat;
		}

		return parent::export();
	}

	protected function getExportColumns()
	{
		$category = Category::getInstanceByID($this->request->get('id'), Category::LOAD_DATA);
		$columns = array();
		$available = $this->getAvailableColumns($category);

		foreach ($this->getAvailableColumns($category) as $column => $data)
		{
			$columns[$column] = $available[$column]['name'];
		}

		$columns['Category.path'] = $this->translate('Category');

		// prices
		foreach ($this->application->getCurrencyArray(false) as $currency)
		{
			$columns['definedPrices.' . $currency] = $this->translate('ProductPrice.price') . ' (' . $currency . ')';
		}

		// list prices
		foreach ($this->application->getCurrencyArray(true) as $currency)
		{
			$columns['definedListPrices.' . $currency] = $this->translate('ProductPrice.listPrice') . ' (' . $currency . ')';
		}

		// child products
		$columns['Product.parentID'] = $this->translate('Product.parentID');
		$columns['Parent.sku'] = $this->translate('Parent.sku');
		for ($k = 0; $k <= 4; $k++)
		{
			$columns['variationTypes.' . $k . '.name'] = $this->translate('ProductVariationType.name') . ' (' . ($k + 1) . ')';
		}

		// group prices
		foreach ($this->getUserGroups() as $groupID => $groupName)
		{
			$columns['GroupPrice.' . $groupID] = $this->translate('ProductPrice.GroupPrice') . ' (' . $groupName . ') [' . $groupID . ']';
		}

		return $columns;
	}

	protected function getDisplayedColumns(Category $category)
	{
		// product ID is always passed as the first column
		return parent::getDisplayedColumns($category, array('hiddenType' => 'numeric'));
	}

	protected function getDefaultColumns()
	{
		return array('Product.ID', 'hiddenType','Product.sku', 'Product.name', 'Manufacturer.name', 'ProductPrice.price', 'Product.stockCount', 'Product.isEnabled');
	}

	public function autoComplete()
	{
	  	$f = new ARSelectFilter();
		$f->setLimit(20);

		$resp = array();

		$field = $this->request->get('field');

		if (in_array($field, array('sku', 'URL', 'keywords')))
		{
		  	$c = new LikeCond(new ARFieldHandle('Product', $field), $this->request->get($field) . '%');
		  	$f->setCondition($c);

			$f->setOrder(new ARFieldHandle('Product', $field), 'ASC');

		  	$query = new ARSelectQueryBuilder();
		  	$query->setFilter($f);
		  	$query->includeTable('Product');
		  	$query->addField('DISTINCT(Product.' . $field . ')');

		  	$results = ActiveRecordModel::getDataBySQL($query->createString());

		  	foreach ($results as $value)
		  	{
				$resp[] = $value[$field];
			}
		}

		else if ('name' == $field)
		{
		  	$c = new LikeCond(new ARFieldHandle('Product', $field), '%:"' . $this->request->get($field) . '%');
		  	$f->setCondition($c);

			$locale = $this->locale->getLocaleCode();
			$langCond = new LikeCond(Product::getLangSearchHandle(new ARFieldHandle('Product', 'name'), $locale), $this->request->get($field) . '%');
			$c->addAND($langCond);

		  	$f->setOrder(Product::getLangSearchHandle(new ARFieldHandle('Product', 'name'), $locale), 'ASC');

		  	$results = ActiveRecordModel::getRecordSet('Product', $f);

		  	foreach ($results as $value)
		  	{
				$resp[$value->getValueByLang('name', $locale, Product::NO_DEFAULT_VALUE)] = true;
			}

			$resp = array_keys($resp);
		}

		else if ('specField_' == substr($field, 0, 10))
		{
			list($foo, $id) = explode('_', $field);

			$handle = new ARFieldHandle('SpecificationStringValue', 'value');
			$locale = $this->locale->getLocaleCode();
			$searchHandle = MultiLingualObject::getLangSearchHandle($handle, $locale);

		  	$f->setCondition(new EqualsCond(new ARFieldHandle('SpecificationStringValue', 'specFieldID'), $id));
			$f->mergeCondition(new LikeCond($handle, '%:"' . $this->request->get($field) . '%'));
			$f->mergeCondition(new LikeCond($searchHandle, $this->request->get($field) . '%'));

		  	$f->setOrder($searchHandle, 'ASC');

		  	$results = ActiveRecordModel::getRecordSet('SpecificationStringValue', $f);

		  	foreach ($results as $value)
		  	{
				$resp[$value->getValueByLang('value', $locale, Product::NO_DEFAULT_VALUE)] = true;
			}

			$resp = array_keys($resp);
		}

		return new AutoCompleteResponse($resp);
	}

	/**
	 * Displays main product information form
	 *
	 * @role create
	 *
	 * @return ActionResponse
	 */
	public function add()
	{
		$this->loadLanguageFile('backend/ProductPrice');

		$category = Category::getInstanceByID($this->request->get("id"), ActiveRecordModel::LOAD_DATA);

		$response = $this->productForm(Product::getNewInstance($category, ''));
		if ($this->config->get('AUTO_GENERATE_SKU'))
		{
			$response->get('productForm')->set('autosku', true);
		}

		$response->get('productForm')->set('isEnabled', $this->config->get('DEFAULT_PRODUCT_ENABLED'));

		return $response;
	}

	/**
	 * @role create
	 */
	public function create()
	{
		$product = Product::getNewInstance(Category::getInstanceByID($this->request->get('categoryID')), $this->translate('_new_product'));

		$response = $this->save($product);

		if ($response instanceOf ActionResponse)
		{
			$response->get('productForm')->clearData();
			$response->set('id', $product->getID());
			return $response;
		}
		else
		{
			return $response;
		}
	}

	/**
	 * @role update
	 */
	public function update()
	{
	  	$product = Product::getInstanceByID($this->request->get('id'), ActiveRecordModel::LOAD_DATA);
	  	$product->loadPricing();
	  	$product->loadSpecification();

	  	return $this->save($product);
	}

	public function basicData()
	{
		ClassLoader::import('application.LiveCartRenderer');
		ClassLoader::import('application.model.presentation.CategoryPresentation');

		$product = Product::getInstanceById($this->request->get('id'), ActiveRecord::LOAD_DATA, array('DefaultImage' => 'ProductImage', 'Manufacturer', 'Category'));
		$product->loadSpecification();

		$response = $this->productForm($product);
		$response->set('counters', $this->countTabsItems()->getData());
		$response->set('themes', array_merge(array(''), LiveCartRenderer::getThemeList()));

		$set = $product->getRelatedRecordSet('CategoryPresentation', new ARSelectFilter());
		if ($set->size())
		{
			$response->get('productForm')->setData($set->get(0)->toArray());
		}

		// pricing

		$f = new ARSelectFilter(new NotEqualsCond(new ARFieldHandle('Currency', 'isDefault'), true));
		$f->setOrder(new ARFieldHandle('Currency', 'position'));
		$otherCurrencies = array();
		foreach (ActiveRecordModel::getRecordSetArray('Currency', $f) as $row)
		{
			$otherCurrencies[] = $row['ID'];
		}

		$response->set("product", $product->toFlatArray());
		$response->set("otherCurrencies", $otherCurrencies);
		$response->set("baseCurrency", $this->application->getDefaultCurrency()->getID());
		$productForm = $response->get('productForm');
		// $response->set("pricingForm", $pricingForm);

		// get user groups
		$f = new ARSelectFilter();
		$f->setOrder(new ARFieldHandle('UserGroup', 'name'));
		$groups[0] = $this->translate('_all_customers');
		foreach (ActiveRecordModel::getRecordSetArray('UserGroup', $f) as $group)
		{
			$groups[$group['ID']] = $group['name'];
		}
		$groups[''] = '';
		$response->set('userGroups', $groups);

		// all product prices in a separate array
		$prices = array();
		foreach ($product->getRelatedRecordSetArray('ProductPrice', new ARSelectFilter()) as $price)
		{
			$prices[$price['currencyID']] = $price;
			$productForm->/*$pricingForm->*/set('price_' . $price['currencyID'], $price['price']);
			$productForm->/*$pricingForm->*/set('listPrice_' . $price['currencyID'], $price['listPrice']);
		}
		$response->set('prices', $prices);

		if ($this->isQuickEdit == false) // viewing in quick edit form does not add to last viewed.
		{
			BackendToolbarItem::registerLastViewedProduct($product);
		}

		return $response;
	}

	private function pricingInformation(Product $product)
	{
		// $this->locale->translationManager()->loadFile('backend/Product');
		// $product = Product::getInstanceByID($this->request->get('id'), ActiveRecord::LOAD_DATA, ActiveRecord::LOAD_REFERENCES);

		// $pricingForm = $this->buildPricingForm($product);

		$f = new ARSelectFilter(new NotEqualsCond(new ARFieldHandle('Currency', 'isDefault'), true));
		$f->setOrder(new ARFieldHandle('Currency', 'position'));
		$otherCurrencies = array();
		foreach (ActiveRecordModel::getRecordSetArray('Currency', $f) as $row)
		{
			$otherCurrencies[] = $row['ID'];
		}

		$response = new ActionResponse();
		$response->set("product", $product->toFlatArray());
		$response->set("otherCurrencies", $otherCurrencies);
		$response->set("baseCurrency", $this->application->getDefaultCurrency()->getID());
		$response->set("pricingForm", $pricingForm);

		// get user groups
		$f = new ARSelectFilter();
		$f->setOrder(new ARFieldHandle('UserGroup', 'name'));
		$groups[0] = $this->translate('_all_customers');
		foreach (ActiveRecordModel::getRecordSetArray('UserGroup', $f) as $group)
		{
			$groups[$group['ID']] = $group['name'];
		}
		$groups[''] = '';
		$response->set('userGroups', $groups);

		// all product prices in a separate array
		$prices = array();
		foreach ($product->getRelatedRecordSetArray('ProductPrice', new ARSelectFilter()) as $price)
		{
			$prices[$price['currencyID']] = $price;
			$pricingForm->set('price_' . $price['currencyID'], $price['price']);
			$pricingForm->set('listPrice_' . $price['currencyID'], $price['listPrice']);
		}

		$response->set('prices', $prices);

		return $response;
	}



	public function countTabsItems()
	{
	  	ClassLoader::import('application.model.product.*');
	  	$product = Product::getInstanceByID((int)$this->request->get('id'), ActiveRecord::LOAD_DATA);

	  	return new JSONResponse(array(
			'tabProductBundle' => count(ProductBundle::getBundledProductArray($product)),
			'tabProductRelationship' => $product->getRelationships(ProductRelationship::TYPE_CROSS)->getTotalRecordCount(),
			'tabProductUpsell' => $product->getRelationships(ProductRelationship::TYPE_UP)->getTotalRecordCount(),
			'tabProductFiles' => $product->getFiles(false)->getTotalRecordCount(),
			'tabProductImages' => count($product->getImageArray()),
			'tabProductOptions' => $product->getOptions()->getTotalRecordCount(),
			'tabProductReviews' => $product->getRelatedRecordCount('ProductReview'),
			'tabProductCategories' => $product->getRelatedRecordCount('ProductCategory') + 1,
			'tabProductVariations' => $product->getRelatedRecordCount('Product', new ARSelectFilter(new EqualsCond(new ARFieldHandle('Product', 'isEnabled'), true)))
		));
	}

	public function info()
	{
		ClassLoader::importNow("application.helper.getDateFromString");

		$product = Product::getInstanceById($this->request->get('id'), ActiveRecord::LOAD_DATA, array('DefaultImage' => 'ProductImage', 'Manufacturer', 'Category'));

		$thisMonth = date('m');
		$lastMonth = date('Y-m', strtotime(date('m') . '/15 -1 month'));

		$periods = array(

			'_last_1_h' => "-1 hours | now",
			'_last_3_h' => "-3 hours | now",
			'_last_6_h' => "-6 hours | now",
			'_last_12_h' => "-12 hours | now",
			'_last_24_h' => "-24 hours | now",
			'_last_3_d' => "-3 days | now",
			'_this_week' => "w:Monday | now",
			'_last_week' => "w:Monday ~ -1 week | w:Monday",
			'_this_month' => $thisMonth . "/1 | now",
			'_last_month' => $lastMonth . "-1 | " . $lastMonth . "/1",
			'_this_year' => "January 1 | now",
			'_last_year' => "January 1 last year | January 1",
			'_overall' => "now | now"

		);

		$purchaseStats = array();
		$prevCount = 0;
		foreach ($periods as $key => $period)
		{
			list($from, $to) = explode(' | ', $period);

			$cond = new EqualsCond(new ARFieldHandle('OrderedItem', 'productID'), $product->getID());

			if ('now' != $from)
			{
				$cond->addAND(new EqualsOrMoreCond(new ARFieldHandle('CustomerOrder', 'dateCompleted'), getDateFromString($from)));
			}

			if ('now' != $to)
			{
				$cond->addAnd(new EqualsOrLessCond(new ARFieldHandle('CustomerOrder', 'dateCompleted'), getDateFromString($to)));
			}

			$f = new ARSelectFilter($cond);
			$f->mergeCondition(new EqualsCond(new ARFieldHandle('CustomerOrder', 'isFinalized'), true));
			$f->removeFieldList();
			$f->addField('SUM(OrderedItem.count)');

			$query = new ARSelectQueryBuilder();
			$query->setFilter($f);
			$query->includeTable('OrderedItem');
			$query->joinTable('CustomerOrder', 'OrderedItem', 'ID', 'customerOrderID');

			if (($count = array_shift(array_shift(ActiveRecordModel::getDataBySql($query->getPreparedStatement(ActiveRecord::getDBConnection()))))) && ($count > $prevCount || '_overall' == $key))
			{
				$purchaseStats[$key] = $count;
			}

			if ($count > $prevCount)
			{
				$prevCount = $count;
			}
		}

		$response = new ActionResponse();
		$response->set('together', $product->getProductsPurchasedTogether(10));
		$response->set('product', $product->toArray());
		$response->set('purchaseStats', $purchaseStats);
		return $response;
	}

	private function save(Product $product)
	{
		ClassLoader::import('application.model.presentation.CategoryPresentation');
		$validator = $this->buildValidator($product);
		if ($validator->isValid())
		{
			$product->loadRequestData($this->request);

			foreach (array('ShippingClass' => 'shippingClassID', 'TaxClass' => 'taxClassID') as $class => $field)
			{
				$value = $this->request->get($field, null);
				$instance = $value ? ActiveRecordModel::getInstanceByID($class, $value) : null;
				$product->setFieldValue($field, $instance);
			}

			$product->save();

			// presentation
			$instance = CategoryPresentation::getInstance($product);
			$instance->loadRequestData($this->request);
			$instance->save();

			// save pricing
			$product->loadSpecification();
			$product->loadPricing();
			if ($quantities = $this->request->get('quantityPricing'))
			{
				foreach ($product->getRelatedRecordSet('ProductPrice', new ARSelectFilter()) as $price)
				{
					$id = $price->currency->get()->getID();
					$prices = array();
					if (!empty($quantities[$id]))
					{
						$values = json_decode($quantities[$id], true);
						$prices = array();

						// no group selected - set all customers
						if ('' == $values['group'][0])
						{
							$values['group'][0] = 0;
						}

						$quantCount = count($values['quant']);
						foreach ($values['group'] as $groupIndex => $group)
						{
							foreach ($values['quant'] as $quantIndex => $quant)
							{
								$pr = $values['price'][($groupIndex * $quantCount) + $quantIndex];
								if (strlen($pr) != 0)
								{
									$prices[$quant][$group] = (float)$pr;
								}
							}
						}
					}

					ksort($prices);
					$price->serializedRules->set(serialize($prices));
					$price->save();
				}
			}

			// save product images
			$inputImages = $this->request->get('productImage');
			$tmpImages = array();
			if (is_array($inputImages))
			{
				$dir = ClassLoader::getRealPath('public.upload.tmpimage.');
				foreach($inputImages as $tmpImage)
				{
					if (strlen(trim($tmpImage)) == 0 || strpos($tmpImage, '/'))
					{
						continue;
					}
					if(file_exists($dir.$tmpImage))
					{
						$tmpImages[] = $dir.$tmpImage;
						$productImage = ProductImage::getNewInstance($product);
						$productImage->save();
						$productImage->setFile($dir.$tmpImage);
					}
				}
			}

			// $product->loadRequestData($this->request);
			// $product->save();

			if ($this->isQuickEdit == false)
			{
				BackendToolbarItem::registerLastViewedProduct($product);
			}
			$response = $this->productForm($product);

			$response->setHeader('Cache-Control', 'no-cache, must-revalidate');
			$response->setHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT');
			$response->setHeader('Content-type', 'text/javascript');

			return $response;
		}
		else
		{
			// reset validator data (as we won't need to restore the form)
			$validator->restore();

			return new JSONResponse(array('errors' => $validator->getErrorList(), 'failure', $this->translate('_could_not_save_product_information')));
		}
	}

	private function productForm(Product $product)
	{
		$productFormData = $product->toArray();

		if($product->isLoaded())
		{
			$product->loadSpecification();
			$productFormData = array_merge($productFormData, $product->getSpecification()->getFormData());

			if (isset($productFormData['Manufacturer']['name']))
			{
				$productFormData['manufacturer'] = $productFormData['Manufacturer']['name'];
			}
		}
		else
		{
			$product->load(ActiveRecord::LOAD_REFERENCES);
		}

		$product->loadPricing();

		$form = $this->buildForm($product);
		$pricing = $product->getPricingHandler();

		$pricesData = $product->toArray();
		$listPrices = $pricing->toArray(ProductPricing::DEFINED, ProductPricing::LIST_PRICE);
		$pricesData['shippingHiUnit'] = (int)$pricesData['shippingWeight'];
		$pricesData['shippingLoUnit'] = ($pricesData['shippingWeight'] - $pricesData['shippingHiUnit']) * 1000;

		if(array_key_exists('defined', $pricesData))
		{
			foreach ($pricesData['calculated'] as $currency => $price)
			{
				$pricesData['price_' . $currency] = isset($pricesData['defined'][$currency]) ? $pricesData['defined'][$currency] : '';
				$productFormData['price_' . $currency] = $pricesData['price_' . $currency];
			}
		}

		foreach ($listPrices as $currency => $price)
		{
			$pricesData['listPrice_' . $currency] = $price;
		}

		$form->setData($productFormData);

		// status values
		$status = array(0 => $this->translate('_disabled'),
						1 => $this->translate('_enabled'),
					  );

		// product types
		$types = array(
			Product::TYPE_TANGIBLE => $this->translate('_tangible'),
			Product::TYPE_DOWNLOADABLE => $this->translate('_intangible'),
			Product::TYPE_BUNDLE => $this->translate('_bundle'),
			Product::TYPE_RECURRING => $this->translate('_recurring')
		);

		// default product type
		if (!$product->isLoaded())
		{
			$product->type->set(substr($this->config->get('DEFAULT_PRODUCT_TYPE'), -1));
			$form->set('type', $product->type->get());
		}

		$response = new ActionResponse();
		$product->getSpecification()->setFormResponse($response, $form);
		$response->set("cat", $product->getCategory()->getID());
		$response->set("hideFeedbackMessage", $this->request->get("afterAdding") == 'on');
		$response->set("productForm", $form);
		$response->set("path", $product->getCategory()->getPathNodeArray(true));
		$response->set("productTypes", $types);
		$response->set("productStatuses", $status);
		$response->set("baseCurrency", $this->application->getDefaultCurrency()->getID());
		$response->set("otherCurrencies", $this->application->getCurrencyArray(LiveCart::EXCLUDE_DEFAULT_CURRENCY));
		$response->set("shippingClasses", $this->getSelectOptionsFromSet(ShippingClass::getAllClasses()));
		$response->set("taxClasses", $this->getSelectOptionsFromSet(TaxClass::getAllClasses()));

		$productData = $product->toArray();
		if (empty($productData['ID']))
		{
			$productData['ID'] = 0;
		}
		$response->set("product", $productData);

		return $response;
	}

	private function getSelectOptionsFromSet(ARSet $set)
	{
		if (!$set->size())
		{
			return array();
		}

		$options = array('' => '');

		foreach ($set as $record)
		{
			$arr = $record->toArray();
			$options[$record->getID()] = $arr['name_lang'];
		}

		return $options;
	}

	/**
	 *
	 * @return RequestValidator
	 */
	public function buildValidator(Product $product)
	{
		$validator = $this->getValidator("productFormValidator", $this->request);

		$validator->addCheck('name', new IsNotEmptyCheck($this->translate('_err_name_empty')));

		// check if SKU is entered if not autogenerating
		if ($this->request->get('save') && !$product->isExistingRecord() && !$this->request->get('autosku'))
		{
			$validator->addCheck('sku', new IsNotEmptyCheck($this->translate('_err_sku_empty')));
		}

		// check if entered SKU is unique
		if ($this->request->get('sku') && $this->request->get('save') && (!$product->isExistingRecord() || ($this->request->isValueSet('sku') && $product->getFieldValue('sku') != $this->request->get('sku'))))
		{
			ClassLoader::import('application.helper.check.IsUniqueSkuCheck');
			$validator->addCheck('sku', new IsUniqueSkuCheck($this->translate('_err_sku_not_unique'), $product));
		}

		// validate price input in all currencies
		if(!$product->isExistingRecord())
		{
			self::addPricesValidator($validator);
			self::addShippingValidator($validator);
			self::addInventoryValidator($validator);
		}

		if($this->isQuickEdit)
		{
			// nothing now
		} else {
			// quick edit forms does not have specification fields
			$product->getSpecification()->setValidation($validator);
		}
		self::addPricesValidator($validator);
		self::addShippingValidator($validator);
		self::addInventoryValidator($validator);

		return $validator;
	}

	public function quickEdit()
	{
		$this->isQuickEdit = true;

		$this->loadQuickEditLanguageFile();
		$request = $this->getRequest();
		$response = $this->basicData();
		return $response;
	}

	public function isQuickEdit()
	{
		return true;
	}

	public function saveQuickEdit()
	{
		$this->isQuickEdit = true;
		$this->quickEditValidation = true;

		$response = $this->update(true);
		if($response instanceof JSONResponse)
		{
			return $response;
		}
		$product = $response->get('product');
		$displayedColumns = $this->getRequestColumns();
		$r = array(
			'data'=> $this->recordSetArrayToListData(array($product), $displayedColumns),
			'columns'=>array_keys($displayedColumns)
		);
		return new JSONResponse($r, 'success');
	}
	
	public function massActionField()
	{
		$field = SpecField::getInstanceByID($this->request->get('id'), true);
		$array = $field->toArray();
		
		if ($field->isSelector())
		{
			$array['values'] = array();
			
			foreach ($field->getValuesList() as $val)
			{
				$array['values'][$val['ID']] = $val['value_lang'];
			}
		}
		
		$response = new ActionResponse('field', $array);
		$response->set('form', new Form($this->getValidator('massActionField')));
		return $response;
	}

	protected function setGridResponse(ActionResponse $response)
	{
		parent::setGridResponse($response);

		$displayedColumns = $response->get('displayedColumns');
		$availableColumns = $response->get('availableColumns');

		foreach ($displayedColumns as $column => $type)
		{
			$parts = explode('.', $column);
			if ('specField' == array_shift($parts))
			{
				$field = SpecField::getInstanceByID(array_shift($parts));
				if ($field->isSelector())
				{
					$displayedColumns[$column] = $field->isMultiValue->get() ? 'multi-select' : 'select';

					$values = array();
					foreach ($field->getValuesList() as $value)
					{
						$values[$value['ID']] = $value['value_lang'];
					}

					$availableColumns[$column]['values'] = $values;
				}
			}
		}

		$response->set('displayedColumns', $displayedColumns);
		$response->set('availableColumns', $availableColumns);
	}

	private function buildForm(Product $product)
	{
		return new Form($this->buildValidator($product));
	}

	/**
	 * Gets path to a current node (including current node)
	 *
	 * Overloads parent method
	 * @return array
	 */
	private function getCategoryPathArray(Category $category)
	{
		$path = array();
		$pathNodes = $category->getPathNodeSet(Category::INCLUDE_ROOT_NODE);
		$defaultLang = $this->application->getDefaultLanguageCode();

		foreach ($pathNodes as $node)
		{
			$path[] = $node->getValueByLang('name', $defaultLang);
		}
		return $path;
	}

	private function getCategoryPath($id)
	{
		$ret = array();
		while (!empty($this->categories[$id]))
		{
			$ret[] = $this->categories[$id]['name_lang'];
			$id = $this->categories[$id]['parentNodeID'];
		}

		return array_reverse($ret);
	}

	public function addShippingValidator(RequestValidator $validator)
	{
		// shipping related numeric field validations
		$validator->addCheck('shippingSurchargeAmount', new IsNumericCheck($this->translate('_err_surcharge_not_numeric')));
		$validator->addFilter('shippingSurchargeAmount', new NumericFilter());

		$validator->addCheck('minimumQuantity', new IsNumericCheck($this->translate('_err_quantity_not_numeric')));
		$validator->addCheck('minimumQuantity', new MinValueCheck($this->translate('_err_quantity_negative'), 0));
		$validator->addFilter('minimumQuantity', new NumericFilter());

		$validator->addFilter('shippingHiUnit', new NumericFilter());
		$validator->addCheck('shippingHiUnit', new IsNumericCheck($this->translate('_err_weight_not_numeric')));
		$validator->addCheck('shippingHiUnit', new MinValueCheck($this->translate('_err_weight_negative'), 0));

		$validator->addFilter('shippingLoUnit', new NumericFilter());
		$validator->addCheck('shippingLoUnit', new IsNumericCheck($this->translate('_err_weight_not_numeric')));
		$validator->addCheck('shippingLoUnit', new MinValueCheck($this->translate('_err_weight_negative'), 0));

		return $validator;
	}

	public function addPricesValidator(RequestValidator $validator, $prefix = '')
	{
		// price in base currency
		$baseCurrency = $this->getApplication()->getDefaultCurrency()->getID();
		$validator->addCheck($prefix.'price_' . $baseCurrency, new IsNotEmptyCheck($this->translate('_err_price_empty')));

		$currencies = $this->getApplication()->getCurrencyArray();
		foreach ($currencies as $currency)
		{
			$validator->addCheck($prefix.'price_' . $currency, new IsNumericCheck($this->translate('_err_price_invalid')));
			$validator->addCheck($prefix.'price_' . $currency, new MinValueCheck($this->translate('_err_price_negative'), 0));
			$validator->addCheck($prefix.'listPrice_' . $currency, new MinValueCheck($this->translate('_err_price_negative'), 0));
			$validator->addFilter($prefix.'price_' . $currency, new NumericFilter());
			$validator->addFilter($prefix.'listPrice_' . $currency, new NumericFilter());
		}

		return $validator;
	}

	public function addInventoryValidator(RequestValidator $validator)
	{
		if ($this->config->get('INVENTORY_TRACKING') != 'DISABLE')
		{
			$validator->addCheck('stockCount',
				new OrCheck(
					array('stockCount', 'isUnlimitedStock'),
					array(
						new IsNotEmptyCheck($this->translate('_err_stock_required')),
						new IsNotEmptyCheck($this->translate('_err_stock_required'))
					),
					$this->request
				));

			$validator->addCheck('stockCount', new IsNumericCheck($this->translate('_err_stock_not_numeric')));
			$validator->addCheck('stockCount', new MinValueCheck($this->translate('_err_stock_negative'), 0));
		}

		$validator->addFilter('stockCount', new NumericFilter());

		return $validator;
	}

	public function uploadProductImage()
	{
		ClassLoader::import('application.model.product.ProductImage');
		ClassLoader::import('library.image.ImageManipulator');
		$field = 'upload_' . $this->request->get('field');

		$dir = ClassLoader::getRealPath('public.upload.tmpimage.');

		// delete old tmp files
		chdir($dir);
		$dh = opendir($dir);
		$threshold = strtotime("-1 day");
		while (($dirent = readdir($dh)) != false)
		{
			if (is_file($dirent))
			{
				if (filemtime($dirent) < $threshold)
				{
					unlink($dirent);
				}
			}
		}
		closedir($dh);

		// create tmp file
		$file = $_FILES[$field];
		$tmp = 'tmp_' . $field . md5($file['tmp_name']) .  '__' . $file['name'];

		if (!file_exists($dir))
		{
			mkdir($dir, 0777, true);
			chmod($dir, 0777);
		}
		$path = $dir . $tmp;
		move_uploaded_file($file['tmp_name'], $path);
		if (@getimagesize($path))
		{
			$thumb = 'tmp_thumb_' . $tmp;
			$thumbPath = $dir . $thumb;
			$thumbDir = dirname($thumbPath);
			if (!file_exists($thumbDir))
			{
				mkdir($thumbDir, 0777, true);
				chmod($thumbDir, 0777);
			}
			$conf = self::getApplication()->getConfig();
			$img = new ImageManipulator($path);
			$thumbSize = ProductImage::getImageSizes();

			$thumbSize = $thumbSize[2]; // 1 is too small, cant see a thing.
			$img->resize($thumbSize[0], $thumbSize[1], $thumbPath);
			$thumb = 'upload/tmpimage/'. $thumb;
		}
		else
		{
			return new JSONResponse(null, 'failure', $this->translate('_error_uploading_image'));
		}
		return new JSONResponse(array('name' => $file['name'], 'file' => $tmp, 'thumb' => $thumb), 'success');
	}

	protected function getRequestCategory()
	{
		$id = $this->request->get("id");
		return is_numeric($id) ? $id : substr($id, 9);
	}
}

?>
