<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.model.parser.CsvFile");
ClassLoader::import("application.model.category.Category");
ClassLoader::import("application.model.product.Product");
ClassLoader::import("application.model.category.SpecField");

/**
 * Handles product importing through a CSV file
 *
 * @package application.controller.backend
 * @author Integry Systems
 * @role csvimport
 */
class CsvImportController extends StoreManagementController
{
	const PREVIEW_ROWS = 10;

	const PROGRESS_FLUSH_INTERVAL = 5;

	private $categories = array();

	private $delimiters = array(
									'_del_comma' => ',',
									'_del_semicolon' => ';',
									'_del_pipe' => '|',
									'_del_tab' => "\t"
								);

	public function index()
	{
		$form = $this->getForm();
		$root = Category::getInstanceByID($this->request->isValueSet('category') ? $this->request->get('category') : Category::ROOT_ID, Category::LOAD_DATA);
		$form->set('category', $root->getID());
		$form->set('atServer', $this->request->get('file'));

		$response = new ActionResponse();
		$response->set('form', $form);
		$response->set('catPath', $root->getPathNodeArray(true));
		return $response;
	}

	public function setFile()
	{
		$filePath = '';

		if (!empty($_FILES['upload']['tmp_name']))
		{
			$filePath = ClassLoader::getRealPath('cache') . '/upload.csv';
			move_uploaded_file($_FILES['upload']['tmp_name'], $filePath);
		}
		else
		{
			$filePath = $this->request->get('atServer');
			if (!file_exists($filePath))
			{
				$filePath = '';
			}
		}

		if (empty($filePath))
		{
			$validator = $this->buildValidator();
			$validator->triggerError('atServer', $this->translate('_err_no_file'));
			$validator->saveState();
			return new ActionRedirectResponse('backend.csvImport', 'index');
		}

		return new ActionRedirectResponse('backend.csvImport', 'delimiters', array('query' => 'file=' . $filePath . '&category=' . $this->request->get('category')));
	}

	public function delimiters()
	{
		$file = $this->request->get('file');
		if (!file_exists($file))
		{
			return new ActionRedirectResponse('backend.csvImport', 'index');
		}

		// try to guess the delimiter
		foreach ($this->delimiters as $delimiter)
		{
			$csv = new CsvFile($file, $delimiter);
			unset($count);
			foreach ($this->getPreview($csv) as $row)
			{
				if (!isset($count))
				{
					$count = count($row);
				}

				if ($count != count($row))
				{
					unset($count);
					break;
				}
			}

			if (isset($count) && ($count > 1))
			{
				break;
			}
			else
			{
				$delimiter = ',';
			}
		}

		if (!$delimiter)
		{
			$delimiter = ',';
		}

		$form = $this->getDelimiterForm();
		$form->set('delimiter', $delimiter);
		$form->set('file', $file);
		$form->set('category', $this->request->get('category'));

		$response = new ActionResponse();
		$response->set('form', $form);
		$response->set('file', $file);

		$delimiters = array_flip($this->delimiters);
		foreach ($delimiters as &$title)
		{
			$title = $this->translate($title);
		}

		$response->set('delimiters', $delimiters);

		$csv = new CsvFile($file, $delimiter);
		$preview = $this->getPreview($csv);
		$response->set('preview', $preview);
		$response->set('previewCount', count($preview));
		$response->set('total', $csv->getRecordCount());
		$response->set('currencies', $this->application->getCurrencyArray());
		$response->set('languages', $this->application->getLanguageSetArray(true));
		$response->set('groups', ActiveRecordModel::getRecordSetArray('UserGroup', new ARSelectFilter()));
		$response->set('catPath', Category::getInstanceByID($this->request->get('category'), Category::LOAD_DATA)->getPathNodeArray(true));

		return $response;
	}

	public function preview()
	{
		return new ActionResponse('preview', $this->getPreview(new CsvFile($this->request->get('file'), $this->request->get('delimiter'))));
	}

	public function fields()
	{
		ClassLoader::import('application.controller.backend.ProductController');

		$this->loadLanguageFile('backend/Product');

		$fields['Product.ID'] = $this->translate('Product.ID');

		$productController = new ProductController($this->application);
		foreach ($productController->getAvailableColumns(Category::getInstanceByID($this->request->get('category'), true), true) as $key => $data)
		{
			$fields[$key] = $this->translate($data['name']);
		}

		unset($fields['Product.reviewCount']);
		unset($fields['hiddenType']);
		unset($fields['ProductImage.url']);

		$groupedFields = array();
		foreach ($fields as $field => $fieldName)
		{
			list($class, $field) = explode('.', $field, 2);
			$groupedFields[$class][$class . '.' . $field] = $fieldName;
		}

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

		$csv = new CsvFile($this->request->get('file'), $this->request->get('delimiter'));

		$response = new ActionResponse('columns', $csv->getRecord());
		$response->set('fields', $groupedFields);
		$response->set('form', $this->getFieldsForm());
		return $response;
	}

	public function import()
	{
		$response = new JSONResponse(null);

		if (file_exists($this->getCancelFile()))
		{
			unlink($this->getCancelFile());
		}

		if (!$this->request->get('continue'))
		{
			$this->clearCacheProgress();
		}

		set_time_limit(0);
		ignore_user_abort(true);

		$fields = array();
		// map CSV fields to LiveCart fields
		foreach ($this->request->get('column') as $key => $value)
		{
			if ($value)
			{
				list($type, $column) = explode('.', $value, 2);
				$fields[$type][$column] = $key;
			}
		}

		if (isset($fields['Category']))
		{
			ksort($fields['Category']);
		}

		// get import root category
		$this->root = Category::getInstanceById($this->request->get('category'), Category::LOAD_DATA);

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

		$csv = new CsvFile($this->request->get('file'), $this->request->get('delimiter'));
		$total = $csv->getRecordCount();
		if ($this->request->get('firstHeader'))
		{
			$total -= 1;
		}

		$progress = 0;
		$failed = 0;
		$categories = array();
		$request = $this->request->toArray();
		$impReq = new Request();
		$defLang = $this->application->getDefaultLanguageCode();

		$references = array('DefaultImage' => 'ProductImage', 'Manufacturer');

		$processed = 0;
		if ($this->request->get('continue'))
		{
			$startFrom = $this->getCacheProgress() + 1;
		}

		if (!$this->request->get('continue'))
		{
			ActiveRecord::beginTransaction();
		}

		$isFirst = true;
		foreach ($csv as $record)
		{
			if (!is_array($record))
			{
				continue;
			}

			if ($isFirst && $this->request->get('firstHeader'))
			{
				$isFirst = false;
				continue;
			}

			// continue timed-out import
			if ($this->request->get('continue'))
			{
				if (++$processed < $startFrom)
				{
					$progress++;
					continue;
				}

				$this->setCacheProgress($processed);
			}

			foreach ($record as &$cell)
			{
				$cell = trim($cell);
			}

			$extraCategories = null;

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
				$cat = $this->getCategoryByPath(array_shift($categories));

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

				$cat = $this->getCategoryByPath($path);
			}
			else
			{
				$cat = $this->root;
			}

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
				else if (!empty($record[$fields['Product']['sku']]))
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

				foreach ($this->request->get('column') as $csvIndex => $column)
				{
					if (!isset($record[$csvIndex]) || empty($column))
					{
						continue;
					}

					$value = $record[$csvIndex];

					if (!$this->isValidUTF8($value) && function_exists('utf8_encode'))
					{
						$value = utf8_encode($value);
					}

					list($className, $field) = explode('.', $column, 2);
					if (isset($request['language'][$csvIndex]))
					{
						$lang = $request['language'][$csvIndex];
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

					if ('Product' == $className)
					{
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
						$currency = $request['currency'][$csvIndex];
						$quantityLevel = $request['quantityLevel'][$csvIndex];
						$group = $request['group'][$csvIndex];

						$price = $product->getPricingHandler()->getPriceByCurrencyCode($currency);
						$product->getPricingHandler()->setPrice($price);

						if (($group || $quantityLevel) && $value)
						{
							$quantity = $quantityLevel ? $record[$fields['ProductPrice'][$quantityLevel]] : 1;
							$group = $group ? UserGroup::getInstanceByID($group) : null;
							$price->setPriceRule($quantity, $group, $value);
						}
						else
						{
							$price->price->set($value);
						}
					}
					else if ('ProductPrice.listPrice' == $column)
					{
						$value = (float)preg_replace('/[^\.0-9]/', '', str_replace(',', '.', $value));
						$currency = $request['currency'][$csvIndex];
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

				// attributes
				if (isset($fields['specField']))
				{
					foreach ($fields['specField'] as $specFieldID => $csvIndex)
					{
						if (empty($record[$csvIndex]))
						{
							continue;
						}

						$attr = SpecField::getInstanceByID($specFieldID, SpecField::LOAD_DATA);
						if ($attr->isSimpleNumbers())
						{
							$impReq->set($attr->getFormFieldName(), (float)$record[$csvIndex]);
						}
						else if ($attr->isSelector())
						{
							if ($attr->isMultiValue->get())
							{
								$values = explode(',', $record[$csvIndex]);
							}
							else
							{
								$values = array($record[$csvIndex]);
							}

							foreach ($values as $fieldValue)
							{
								$fieldValue = trim($fieldValue);

								$f = new ARSelectFilter(
										new EqualsCond(
											SpecFieldValue::getLangSearchHandle(
												new ARFieldHandle('SpecFieldValue', 'value'),
												$this->application->getDefaultLanguageCode()
											),
											$fieldValue
										)
									);
								$f->setLimit(1);

								if (!$value = $attr->getRelatedRecordSet('SpecFieldValue', $f)->shift())
								{
									$value = SpecFieldValue::getNewInstance($attr);

									if ($attr->type->get() == SpecField::TYPE_NUMBERS_SELECTOR)
									{
										$value->value->set($fieldValue);
									}
									else
									{
										$value->setValueByLang('value', $this->application->getDefaultLanguageCode(), $fieldValue);
									}

									$value->save();
								}

								if (!$attr->isMultiValue->get())
								{
									$impReq->set($attr->getFormFieldName(), $value->getID());
								}
								else
								{
									$impReq->set($value->getFormFieldName(), true);
								}
							}
						}

						else
						{
							$impReq->set($attr->getFormFieldName(), $record[$csvIndex]);
						}
					}
				}

				$product->loadRequestData($impReq);
				$product->save();

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
					$this->importAdditionalCategories($product, $extraCategories);
				}

				$lastName = $product->getValueByLang('name', 'en');
				$product->__destruct();
				$product->destruct(true);
			}

			ActiveRecord::clearPool();

			$progress++;

			if ($progress % self::PROGRESS_FLUSH_INTERVAL == 0 || ($total == $progress))
			{
				$response->flush($this->getResponse(array('progress' => $progress, 'total' => $total, 'lastName' => $lastName)));
				//echo '|' . round(memory_get_usage() / (1024*1024), 1) . '|' . count($categories) . "\n";
			}

			// test non-transactional mode
			//if (!$this->request->get('continue')) exit;

			if (connection_aborted())
			{
				if ($this->request->get('continue'))
				{
					exit;
				}
				else
				{
					$this->cancel();
				}
			}
		}

		Category::recalculateProductsCount();

		if (!$this->request->get('continue'))
		{
			//ActiveRecord::rollback();
			ActiveRecord::commit();
		}

		$response->flush($this->getResponse(array('progress' => 0, 'total' => $total)));

		//echo '|' . round(memory_get_usage() / (1024*1024), 1);

		exit;
	}

	private function getCategoryByPath($names)
	{
		if (!is_array($names))
		{
			$names = explode(' / ', $names);
		}

		$hash = '';
		$hashRoot = $this->root->getID();

		foreach ($names as $name)
		{
			$hash .= "\n" . $name;

			if (!isset($categories[$hash]))
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

	private function importAdditionalCategories(Product $product, array $extraCategories)
	{
		$product->deleteRelatedRecordSet('ProductCategory');
		foreach ($extraCategories as $names)
		{
			ProductCategory::getNewInstance($product, $this->getCategoryByPath($names))->save();
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
				foreach (array('/tmp/import/', 'import/') as $loc)
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

	public function isCancelled()
	{
		$k = 0;
		$ret = false;

		// wait the cancel file for 5 seconds
		while (++$k < 6 && !$ret)
		{
			$ret = file_exists($this->getCancelFile());
			if ($ret)
			{
				unlink($this->getCancelFile());
			}
			else
			{
				sleep(1);
			}
		}

		return new JSONResponse(array('cancelled' => $ret));
	}

	private function cancel()
	{
		file_put_contents($this->getCancelFile(), '');
		ActiveRecord::rollback();
		exit;
	}

	private function getCancelFile()
	{
		return ClassLoader::getRealPath('cache') . '/.csvImportCancel';
	}

	private function getResponse($data)
	{
		return '|' . base64_encode(json_encode($data));
	}

	private function getPreview(CsvFile $csv)
	{
		$ret = array();

		for ($k = 0; $k < self::PREVIEW_ROWS; $k++)
		{
			$row = $csv->getRecord();
			if (!is_array($row))
			{
				break;
			}

			foreach ($row as &$cell)
			{
				if (strlen($cell) > 102)
				{
					$cell = substr($cell, 0, 100) . '...';
				}
			}

			$ret[] = $row;
		}

		return $ret;
	}

	private function getForm()
	{
		return new Form($this->buildValidator());
	}

	private function buildValidator()
	{
		ClassLoader::import('application.helper.filter.HandleFilter');

		return new RequestValidator('csvFile', $this->request);
	}

	private function getDelimiterForm()
	{
		return new Form($this->getDelimiterValidator());
	}

	private function getDelimiterValidator()
	{
		ClassLoader::import('application.helper.filter.HandleFilter');

		return new RequestValidator('csvDelimiters', $this->request);
	}

	private function getFieldsForm()
	{
		return new Form($this->getFieldsValidator());
	}

	private function getFieldsValidator()
	{
		ClassLoader::import('application.helper.filter.HandleFilter');

		return new RequestValidator('csvFields', $this->request);
	}

	private function setCacheProgress($index)
	{
		file_put_contents($this->getProgressFile(), $index);
	}

	private function getCacheProgress()
	{
		return file_exists($this->getProgressFile()) ? file_get_contents($this->getProgressFile()) : null;
	}

	private function clearCacheProgress()
	{
		if (file_exists($this->getProgressFile()))
		{
			unlink($this->getProgressFile());
		}
	}

	private function getProgressFile()
	{
		return ClassLoader::getRealPath('cache.') . 'csvProgress';
	}

	private function isValidUTF8($str)
	{
		// values of -1 represent disalloweded values for the first bytes in current UTF-8
		static $trailing_bytes = array (
			0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0, 0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,
			0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0, 0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,
			0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0, 0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,
			0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0, 0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,
			-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1, -1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,
			-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1, -1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,
			-1,-1,1,1,1,1,1,1,1,1,1,1,1,1,1,1, 1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1,
			2,2,2,2,2,2,2,2,2,2,2,2,2,2,2,2, 3,3,3,3,3,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1
		);

		$ups = unpack('C*', $str);
		if (!($aCnt = count($ups))) return true; // Empty string *is* valid UTF-8
		for ($i = 1; $i <= $aCnt;)
		{
			if (!($tbytes = $trailing_bytes[($b1 = $ups[$i++])])) continue;
			if ($tbytes == -1) return false;

			$first = true;
			while ($tbytes > 0 && $i <= $aCnt)
			{
				$cbyte = $ups[$i++];
				if (($cbyte & 0xC0) != 0x80) return false;

				if ($first)
				{
					switch ($b1)
					{
						case 0xE0:
							if ($cbyte < 0xA0) return false;
							break;
						case 0xED:
							if ($cbyte > 0x9F) return false;
							break;
						case 0xF0:
							if ($cbyte < 0x90) return false;
							break;
						case 0xF4:
							if ($cbyte > 0x8F) return false;
							break;
						default:
							break;
					}
					$first = false;
				}
				$tbytes--;
			}
			if ($tbytes) return false; // incomplete sequence at EOS
		}
		return true;
	}
}

?>