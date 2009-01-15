<?php

ClassLoader::import('application.controller.backend.abstract.ActiveGridController');
ClassLoader::import('application.model.category.Category');
ClassLoader::import('application.model.filter.FilterGroup');
ClassLoader::import('application.model.product.Product');
ClassLoader::import('application.model.product.ProductSpecification');
ClassLoader::import('application.helper.ActiveGrid');
ClassLoader::import('application.helper.massAction.MassActionInterface');
ClassLoader::import('application.model.order.OrderedItem');

/**
 * Controller for handling product based actions performed by store administrators
 *
 * @package application.controller.backend
 * @author Integry Systems
 * @role product
 */
class ProductController extends ActiveGridController implements MassActionInterface
{
	public function index()
	{
		ClassLoader::import('application.LiveCartRenderer');

		$category = Category::getInstanceByID($this->request->get("id"), Category::LOAD_DATA);

		$response = new ActionResponse();
		$response->set('categoryID', $category->getID());
		$response->set('currency', $this->application->getDefaultCurrency()->getID());
		$response->set('themes', array_merge(array(''), LiveCartRenderer::getThemeList()));

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
		return $this->getDisplayedColumns(Category::getInstanceByID(substr($this->request->get("id"), 9), Category::LOAD_DATA));
	}

	protected function getAvailableRequestColumns()
	{
		return $this->getAvailableColumns(Category::getInstanceByID(substr($this->request->get("id"), 9), Category::LOAD_DATA));
	}

	protected function getReferencedData()
	{
		return array('Category', 'Manufacturer', 'DefaultImage' => 'ProductImage');
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
			$value = isset($product['attributes'][$field]['value_lang']) ? $product['attributes'][$field]['value_lang'] : '';
		}
		else if ('ProductImage' == $class)
		{
			if (!empty($product['DefaultImage']['urls']))
			{
				$value = $product['DefaultImage']['urls'][1];
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
		$id = $this->request->get("id");
		$id = is_numeric($id) ? $id : substr($this->request->get("id"), 9);
		$category = Category::getInstanceByID($id, Category::LOAD_DATA);

		$filter = new ARSelectFilter($category->getProductCondition(true));
		$filter->joinTable('ProductPrice', 'Product', 'productID AND (ProductPrice.currencyID = "' . $this->application->getDefaultCurrencyCode() . '")', 'ID');

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
				ProductSpecification::loadSpecificationForRecordSetArray($productArray);
				break;
			}
		}

		// load price data
		ProductPrice::loadPricesForRecordSetArray($productArray);

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
			ClassLoader::import('application.model.presentation.ProductPresentation');
			ActiveRecord::deleteRecordSet('ProductPresentation', new ARDeleteFilter($filter->getCondition()), null, array('Product', 'Category'));

			return new JSONResponse(array('act' => $this->request->get('act')), 'success', $this->translate('_themes_removed'));
		}

		$params = array();
		if ('manufacturer' == $act)
		{
			$params['manufacturer'] = Manufacturer::getInstanceByName($this->request->get('manufacturer'));
		}
		else if ('price' == $act || 'inc_price' == $act)
		{
			$params['baseCurrency'] = $this->application->getDefaultCurrencyCode();
			$params['price'] = $this->request->get($act);
			$params['currencies'] = $this->application->getCurrencySet();
		}
		else if ('addRelated' == $act)
		{
			$params['relatedProduct'] = Product::getInstanceBySKU($this->request->get('related'));
			if (!$params['relatedProduct'])
			{
				return new JSONResponse(0);
			}
		}
		else if ('copy' == $act)
		{
			$params['category'] = Category::getInstanceById($this->request->get('categoryID'), Category::LOAD_DATA);
		}
		else if ('theme' == $act)
		{
			ClassLoader::import('application.model.presentation.ProductPresentation');
			$params['theme'] = $this->request->get('theme');
		}

		$response = parent::processMass($params);

		if ('delete' == $act || 'copy' == $act)
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

	public function getAvailableColumns(Category $category, $specField = false)
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

	protected function getExportColumns()
	{
		$category = Category::getInstanceByID($this->request->get('id'), Category::LOAD_DATA);
		$columns = $this->getAvailableColumns($category);

		// prices
		foreach ($this->application->getCurrencyArray(false) as $currency)
		{
			$columns['definedPrices.' . $currency] = array('name' => $this->translate('ProductPrice.price') . ' (' . $currency . ')' , 'type' => 'numeric');
		}

		// list prices
		foreach ($this->application->getCurrencyArray(true) as $currency)
		{
			$columns['definedListPrices.' . $currency] = array('name' => $this->translate('ProductPrice.listPrice') . ' (' . $currency . ')' , 'type' => 'numeric');
		}

		// child products
		$columns['Product.parentID'] = array('name' => $this->translate('Product.parentID'), 'type' => 'numeric');
		$columns['Parent.sku'] = array('name' => $this->translate('Parent.sku'), 'type' => 'string');
		for ($k = 0; $k <= 4; $k++)
		{
			$columns['variationTypes.' . $k . '.name'] = array('name' => $this->translate('ProductVariationType.name') . ' (' . ($k + 1) . ')', 'type' => 'string');
		}

		// group prices
		foreach ($this->getUserGroups() as $groupID => $groupName)
		{
			$columns['GroupPrice.' . $groupID] = array('name' => $this->translate('ProductPrice.GroupPrice') . ' (' . $groupName . ') [' . $groupID . ']', 'type' => 'numeric');
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

		$response->get('productForm')->set('isEnabled', true);

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
		ClassLoader::import('application.model.presentation.ProductPresentation');

		$product = Product::getInstanceById($this->request->get('id'), ActiveRecord::LOAD_DATA, array('DefaultImage' => 'ProductImage', 'Manufacturer', 'Category'));
		$product->loadSpecification();

		$response = $this->productForm($product);
		$response->set('counters', $this->countTabsItems()->getData());
		$response->set('themes', array_merge(array(''), LiveCartRenderer::getThemeList()));

		$set = $product->getRelatedRecordSet('ProductPresentation', new ARSelectFilter());
		if ($set->size())
		{
			$response->get('productForm')->set('theme', $set->get(0)->getTheme());
		}

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
		ClassLoader::import('application.model.presentation.ProductPresentation');
		$validator = $this->buildValidator($product);
		if ($validator->isValid())
		{
			$product->loadRequestData($this->request);
			$product->save();

			// presentation
			if ($theme = $this->request->get('theme'))
			{
				$instance = ProductPresentation::getInstance($product);
				$instance->loadRequestData($this->request);
				$instance->save();
			}
			else
			{
				ActiveRecord::deleteByID('ProductPresentation', $product->getID());
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
		$form = $this->buildForm($product);

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

		$form->setData($productFormData);

		// status values
		$status = array(0 => $this->translate('_disabled'),
						1 => $this->translate('_enabled'),
					  );

		// product types
		$types = array(Product::TYPE_TANGIBLE => $this->translate('_tangible'),
					   Product::TYPE_DOWNLOADABLE => $this->translate('_intangible'),
					   Product::TYPE_BUNDLE => $this->translate('_bundle'),
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

		$productData = $product->toArray();
		if (empty($productData['ID']))
		{
			$productData['ID'] = 0;
		}
		$response->set("product", $productData);

		return $response;
	}

	/**
	 * @return RequestValidator
	 */
	private function buildValidator(Product $product)
	{
		$validator = new RequestValidator("productFormValidator", $this->request);

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
			ClassLoader::import('application.controller.backend.ProductPriceController');
			ProductPriceController::addPricesValidator($validator);
			ProductPriceController::addShippingValidator($validator);
			ProductPriceController::addInventoryValidator($validator);
		}

		$product->getSpecification()->setValidation($validator);

		return $validator;
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
}

?>