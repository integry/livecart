<?php

ClassLoader::import('application.controller.backend.ProductController');
ClassLoader::import('application.model.datasync.DataImport');
ClassLoader::import('application.model.category.Category');
ClassLoader::import('application.model.product.Product');

/**
 *  Handles product import logic
 *
 *  @package application.model.datasync.import
 *  @author Integry Systems
 */
class ProductImport extends DataImport
{
	public function getFields()
	{
		$this->loadLanguageFile('backend/Product');

		$fields['Product.ID'] = $this->translate('Product.ID');

		$productController = new ProductController($this->application);
		foreach ($productController->getAvailableColumns(Category::getInstanceByID($this->application->getRequest()->get('category'), true), true) as $key => $data)
		{
			$fields[$key] = $this->translate($data['name']);
		}

		unset($fields['Product.reviewCount']);
		unset($fields['ShippingClass.name']);
		unset($fields['TaxClass.name']);
		unset($fields['hiddenType']);
		unset($fields['ProductImage.url']);

		$groupedFields = array();
		foreach ($fields as $field => $fieldName)
		{
			list($class, $field) = explode('.', $field, 2);
			$groupedFields[$class][$class . '.' . $field] = $fieldName;
		}

		$groupedFields['Product']['Product.shippingClass'] = $this->translate('Product.shippingClass');
		$groupedFields['Product']['Product.taxClass'] = $this->translate('Product.taxClass');

		// do not show manufacturer field in a separate group
		$groupedFields['Product'] = array_merge($groupedFields['Product'], $groupedFields['Manufacturer']);
		unset($groupedFields['Manufacturer']);

		// variations
		$groupedFields['ProductVariation']['Product.parentID'] = $this->translate('Product.parentID');
		$groupedFields['ProductVariation']['Parent.parentSKU'] = $this->translate('Product.parentSKU');
		for ($k = 1; $k <= 5; $k++)
		{
			$groupedFields['ProductVariation']['ProductVariation.' . $k] = $this->maketext('_variation_name', $k);
		}

		// image fields
		$groupedFields['ProductImage']['ProductImage.mainurl'] = $this->translate('_main_image_location');
		for ($k = 1; $k <= 3; $k++)
		{
			$groupedFields['ProductImage']['ProductAdditionalImage.' . $k] = $this->maketext('_additional_image_location', $k);
		}
		$groupedFields['ProductImage']['ProductImage.Images'] = $this->translate('_images');

		// category fields
		$groupedFields['Category']['Category.ID'] = $this->translate('Category.ID');
		for ($k = 1; $k <= 10; $k++)
		{
			$groupedFields['Category']['Category.' . $k] = $this->maketext('_category_x', $k);
		}

		$groupedFields['Category']['Categories.Categories'] = $this->translate('_categories');
		$groupedFields['Category']['Categories.ExtraCategories'] = $this->translate('_extra_categories');

		// price fields
		$groupedFields['ProductPrice']['ProductPrice.listPrice'] = $this->translate('_list_price');
		for ($k = 1; $k <= 5; $k++)
		{
			$groupedFields['ProductPrice']['ProductPrice.' . $k] = $this->maketext('_quantity_level_x', $k);
		}

		return $groupedFields;
	}

	public function isRootCategory()
	{
		return true;
	}

	public function getSortedFields(CsvImportProfile $profile)
	{
		$fields = $profile->getSortedFields();

		if (isset($fields['Category']))
		{
			ksort($fields['Category']);
		}

		return $fields;
	}

	public function beforeImport(CsvImportProfile $profile)
	{
		$fields = $this->getSortedFields($profile);

		// pre-load attributes
		if (isset($fields['specField']))
		{
			ActiveRecordModel::getRecordSet('SpecField',
											new ARSelectFilter(
												new INCond(
													new ARFieldHandle('SpecField', 'ID'),
													array_keys($fields['specField'])
												)
											)
											);
		}
	}

	protected function getInstance($record, CsvImportProfile $profile)
	{
		return false;
	}

	public function importInstance($record, CsvImportProfile $profile)
	{
		$impReq = new Request();
		$defLang = $this->application->getDefaultLanguageCode();
		$references = array('DefaultImage' => 'ProductImage', 'Manufacturer', 'ShippingClass', 'TaxClass');

		$cat = $this->getCategory($profile, $record);

		$extraCategories = null;

		$fields = $profile->getSortedFields();

		if (isset($fields['Categories']['ExtraCategories']))
		{
			$extraCategories = explode('; ', $record[$fields['Categories']['ExtraCategories']]);
		}

		if (isset($fields['Product']) && $cat)
		{
			$product = null;

			if (isset($fields['Product']['ID']))
			{
				$id = $record[$fields['Product']['ID']];
				if (ActiveRecord::objectExists('Product', $id))
				{
					$product = Product::getInstanceByID($id, Product::LOAD_DATA, $references);
				}
			}
			else if (isset($fields['Product']['sku']) && !empty($record[$fields['Product']['sku']]))
			{
				$product = Product::getInstanceBySku($record[$fields['Product']['sku']], $references);
			}

			if ($product)
			{
				$product->loadSpecification();
				$product->loadPricing();
			}
			else
			{
				if ($cat instanceof Category)
				{
					$product = Product::getNewInstance($cat);
				}
				else
				{
					$product = $cat->createChildProduct();
				}

				$product->isEnabled->set(true);
			}

			// product information
			$impReq->clearData();

			foreach ($profile->getFields() as $csvIndex => $field)
			{
				$column = $field['name'];
				$params = $field['params'];

				if (!isset($record[$csvIndex]) || empty($column))
				{
					continue;
				}

				$value = $record[$csvIndex];

				list($className, $field) = explode('.', $column, 2);

				if (isset($params['language']))
				{
					$lang = $params['language'];
					if ($lang != $defLang)
					{
						$field .= '_' . $lang;
					}
				}

				if ($value)
				{
					if ('Product.parentID' == $column)
					{
						$product->parent->set();
						continue;
					}

					if ('Product.parentSKU' == $column)
					{
						$product->parent->set(Product::getInstanceBySKU($value));
						continue;
					}
				}

				if ('Product.taxClass' == $column)
				{
					$product->taxClass->set(TaxClass::findByName($value));
				}

				if ('Product.shippingClass' == $column)
				{
					$product->shippingClass->set(ShippingClass::findByName($value));
				}

				if ('Product' == $className)
				{
					if ('shippingWeight' == $field)
					{
						if ($this->application->getConfig()->get('UNIT_SYSTEM') == 'ENGLISH')
						{
							$value = $value / 0.45359237;
						}
					}

					if (('shippingWeight' == $field) && ($product->parent->get()))
					{
						$value = $this->setChildSetting($product, 'weight', $value);
					}

					$impReq->set($field, $value);
				}
				else if ('Manufacturer' == $className)
				{
					$impReq->set('manufacturer', $value);
				}
				else if ('ProductPrice.price' == $column)
				{
					if ($product->parent->get())
					{
						$value = $this->setChildSetting($product, 'price', $value);
					}

					$value = preg_replace('/,([0-9]{3})/', '\\1', $value);
					$value = (float)preg_replace('/[^\.0-9]/', '', str_replace(',', '.', $value));

					$currency = isset($params['currency']) ? $params['currency'] : $this->application->getDefaultCurrencyCode();
					$quantityLevel = isset($params['quantityLevel']) ? $params['quantityLevel'] : '';
					$group = isset($params['group']) ? $params['group'] : '';

					$price = $product->getPricingHandler()->getPriceByCurrencyCode($currency);
					$product->getPricingHandler()->setPrice($price);

					if ($group || $quantityLevel)
					{
						if ($value > 0)
						{
							$quantity = $quantityLevel ? $record[$fields['ProductPrice'][$quantityLevel]] : 1;
							$group = $group ? UserGroup::getInstanceByID($group) : null;
							$price->setPriceRule($quantity, $group, $value);
						}
					}
					else
					{
						$price->price->set($value);
					}
				}
				else if ('ProductPrice.listPrice' == $column)
				{
					$value = (float)preg_replace('/[^\.0-9]/', '', str_replace(',', '.', $value));
					$currency = $params['currency'];
					$price = $product->getPricingHandler()->getPriceByCurrencyCode($currency);
					$price->listPrice->set($value);
					$product->getPricingHandler()->setPrice($price);
				}
				else if ('ProductVariation' == $className)
				{
					if ($parent = $product->parent->get())
					{
						$this->importProductVariationValue($product, $field, $value);
					}
					else
					{
						$this->importVariationType($product, $field, $value);
					}
				}
			}

			$product->loadRequestData($impReq);
			$product->save();

			$this->importAttributes($product, $record, $fields, 'specField');

			$this->setLastImportedRecordName($product->getValueByLang('name'));

			if (isset($fields['ProductImage']['mainurl']))
			{
				if (!$image = $product->defaultImage->get())
				{
					$image = ProductImage::getNewInstance($product);
				}

				$this->importImage($image, $record[$fields['ProductImage']['mainurl']]);

				unset($image);
			}

			if (isset($fields['ProductAdditionalImage']))
			{
				foreach ($fields['ProductAdditionalImage'] as $index)
				{
					$this->importImage(ProductImage::getNewInstance($product), $record[$index]);
				}
			}

			if (isset($fields['ProductImage']['Images']))
			{
				$images = explode('; ', $record[$fields['ProductImage']['Images']]);

				if ($images)
				{
					$product->deleteRelatedRecordSet('ProductImage');
					foreach ($images as $path)
					{
						$image = ProductImage::getNewInstance($product);
						$this->importImage($image, $path);
						unset($image);
					}
				}
			}

			// create variation by name
			if ((isset($fields['Product']['parentID']) || isset($fields['Parent']['parentSKU'])) && !isset($fields['ProductVariation']) && $product->parent->get())
			{
				$this->importProductVariationValue($product, 1, $product->getValueByLang('name', 'en'));
			}

			// additional categories
			if (is_array($extraCategories))
			{
				$this->importAdditionalCategories($profile, $product, $extraCategories);
			}

			$product->__destruct();
			$product->destruct(true);

			ActiveRecord::clearPool();

			return true;
		}
	}

	private function getCategory(CsvImportProfile $profile, $record)
	{
		$fields = $this->getSortedFields($profile);

		// detect product category
		if (isset($fields['Product']['parentID']) && !empty($record[$fields['Product']['parentID']]))
		{
			$cat = Product::getInstanceByID($record[$fields['Product']['parentID']], true);
		}
		else if (isset($fields['Parent']['parentSKU']) && !empty($record[$fields['Parent']['parentSKU']]))
		{
			$cat = Product::getInstanceBySKU($record[$fields['Parent']['parentSKU']]);
		}
		else if (isset($fields['Category']['ID']))
		{
			try
			{
				$cat = Category::getInstanceById($fields['Category']['ID'], Category::LOAD_DATA);
			}
			catch (ARNotFoundException $e)
			{
				$failed++;
				continue;
			}
		}
		else if (isset($fields['Categories']['Categories']))
		{
			$index = $fields['Categories']['Categories'];

			$categories = explode('; ', $record[$index]);
			$cat = $this->getCategoryByPath($profile, array_shift($categories));

			$extraCategories = $categories;
		}
		else if (isset($fields['Category']))
		{
			$path = array();
			foreach ($fields['Category'] as $level => $csvIndex)
			{
				if ($record[$csvIndex])
				{
					$path[] = $record[$csvIndex];
				}
			}

			$cat = $this->getCategoryByPath($profile, $path);
		}
		else
		{
			return $this->getRoot($profile);
		}

		return $cat;
	}

	private function getRoot(CsvImportProfile $profile)
	{
		$id = $profile->getParam('category');
		$cat = $id ? Category::getInstanceByID($id, true) : Category::getRootNode();
		return $cat;
	}

	private function getCategoryByPath($profile, $names)
	{
		if (!is_array($names))
		{
			$names = explode(' / ', $names);
		}

		$hash = '';
		$hashRoot = $this->getRoot($profile)->getID();

		foreach ($names as $name)
		{
			$hash .= "\n" . $name;

			if (!isset($this->categories[$hash]))
			{
				$f = Category::getInstanceByID($hashRoot)->getSubcategoryFilter();
				$f->mergeCondition(
						new EqualsCond(
							MultiLingualObject::getLangSearchHandle(
								new ARFieldHandle('Category', 'name'),
								$this->application->getDefaultLanguageCode()
							),
							$name
						)
					);

				$cat = ActiveRecordModel::getRecordSet('Category', $f)->get(0);
				if (!$cat)
				{
					$cat = Category::getNewInstance(Category::getInstanceByID($hashRoot));
					$cat->isEnabled->set(true);
					$cat->setValueByLang('name', $this->application->getDefaultLanguageCode(), $name);
					$cat->save();
				}

				$this->categories[$hash] = $cat->getID();
			}

			$hashRoot = $this->categories[$hash];
			$cat = Category::getInstanceByID($hashRoot, true);
		}

		return $cat;
	}

	private function setChildSetting(Product $product, $setting, $value)
	{
		$value = trim($value);

		if (substr($value, 0, 1) == '+')
		{
			$product->setChildSetting($setting, Product::CHILD_ADD);
			$value = substr($value, 1);
		}
		else if (substr($value, 0, 1) == '-')
		{
			$product->setChildSetting($setting, Product::CHILD_SUBSTRACT);
			$value = substr($value, 1);
		}
		else if ($value)
		{
			$product->setChildSetting($setting, Product::CHILD_OVERRIDE);
		}
		else
		{
			$value = 0;
			$product->setChildSetting($setting, '');
		}

		return $value;
	}

	private function importAdditionalCategories(CsvImportProfile $profile, Product $product, array $extraCategories)
	{
		$product->deleteRelatedRecordSet('ProductCategory');
		foreach ($extraCategories as $names)
		{
			ProductCategory::getNewInstance($product, $this->getCategoryByPath($profile, $names))->save();
		}
	}

	private function importVariationType(Product $product, $index, $name)
	{
		$type = $this->getVariationTypeByIndex($product, $index);

		if (!$product->getID())
		{
			$product->save();
		}

		$type->setValueByLang('name', null, $name);
		$type->save();

		return $type;
	}

	private function getVariationTypeByIndex(Product $product, $index)
	{
		$f = new ARSelectFilter();
		$f->setOrder(new ARFieldHandle('ProductVariationType', 'position'));
		$f->setLimit(1, $index - 1);

		if ($product->getID())
		{
			$types = $product->getRelatedRecordSet('ProductVariationType', $f);
		}

		if (isset($types) && $types->size())
		{
			return $types->get(0);
		}
		else
		{
			$type = ProductVariationType::getNewInstance($product);
		}

		return $type;
	}

	private function importProductVariationValue(Product $product, $index, $name)
	{
		$parent = $product->parent->get();
		$type = $this->getVariationTypeByIndex($parent, $index);
		if (!$type->getID())
		{
			$type = $this->importVariationType($parent, $index, '');
		}

		$f = new ARSelectFilter();
		$f->mergeCondition(
			new EqualsCond(
				MultiLingualObject::getLangSearchHandle(
					new ARFieldHandle('ProductVariation', 'name'),
					$this->application->getDefaultLanguageCode()
				),
				$name
			)
		);

		$values = $type->getRelatedRecordSet('ProductVariation', $f);
		if ($values->size())
		{
			$variation = $values->get(0);
		}
		else
		{
			$variation = ProductVariation::getNewInstance($type);
			$variation->setValueByLang('name', null, $name);
			$variation->save();
		}

		if (!$product->getID())
		{
			$product->save();
		}

		$f = new ARDeleteFilter(new EqualsCond(new ARFieldHandle('ProductVariation', 'typeID'), $type->getID()));
		$product->deleteRelatedRecordSet('ProductVariationValue', $f, array('ProductVariation'));

		ProductVariationValue::getNewInstance($product, $variation)->save();
	}

	private function importImage(ProductImage $image, $path)
	{
		if (!$path)
		{
			return false;
		}

		if (@parse_url($path, PHP_URL_SCHEME))
		{
			$fetch = new NetworkFetch($path);
			$path = $fetch->fetch() ? $fetch->getTmpFile() : '';
		}
		else
		{
			if (!file_exists($path))
			{
				foreach (array('/tmp/import/', ClassLoader::getRealPath('public.import.')) as $loc)
				{
					$p = $loc . $path;
					if (file_exists($p))
					{
						$path = $p;
						break;
					}
				}
			}

			if (!file_exists($path))
			{
				$path = '';
			}
		}

		if ($path)
		{
			$man = new ImageManipulator($path);
			if ($man->isValidImage())
			{
				$image->save();
				$image->resizeImage($man);
				$image->save();
			}
		}
	}

	public function afterImport()
	{
		Category::recalculateProductsCount();
	}
}

?>