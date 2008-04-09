<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.model.parser.CsvFile");
ClassLoader::import("application.model.product.Product");

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
			$validator = $this->getValidator();
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

		$fields = array('' => '');

		foreach (ProductController::getAvailableColumns(Category::getInstanceByID($this->request->get('category')), true) as $key => $data)
		{
			$fields[$key] = $this->translate($data['name']);
		}

		unset($fields['hiddenType']);

		$fields['Category.ID'] = $this->translate('Category.ID');

		for ($k = 1; $k <= 10; $k++)
		{
			$fields['Category.' . $k] = $this->maketext('_category_x', $k);
		}

		$csv = new CsvFile($this->request->get('file'), $this->request->get('delimiter'));

		$response = new ActionResponse('columns', $csv->getRecord());
		$response->set('fields', $fields);
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
		$root = Category::getInstanceById($this->request->get('category'), Category::LOAD_DATA);

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
			$csv->getRecord();
		}

		$progress = 0;
		$failed = 0;
		$categories = array();
		$impReq = new Request();

		ActiveRecord::beginTransaction();

		foreach ($csv as $record)
		{
			if (!is_array($record))
			{
				continue;
			}

			foreach ($record as &$cell)
			{
				$cell = trim($cell);
			}

			// detect product category
			if (isset($fields['Category']['ID']))
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
			else if (isset($fields['Category']))
			{
				$hash = '';
				$hashRoot = $root;
				foreach ($fields['Category'] as $level => $csvIndex)
				{
					if (!$record[$csvIndex])
					{
						continue;
					}

					$hash .= "\n" . $record[$csvIndex];

					if (!isset($categories[$hash]))
					{
						$f = $hashRoot->getSubcategoryFilter();
						$f->mergeCondition(
								new EqualsCond(
									MultiLingualObject::getLangSearchHandle(
										new ARFieldHandle('Category', 'name'),
										$this->application->getDefaultLanguageCode()
									),
									$record[$csvIndex]
								)
							);
						$set = ActiveRecordModel::getRecordSet('Category', $f);
						if ($set->size())
						{
							$cat = $set->get(0);
						}
						else
						{
							$cat = Category::getNewInstance($hashRoot);
							$cat->isEnabled->set(true);
							$cat->setValueByLang('name', $this->application->getDefaultLanguageCode(), $record[$csvIndex]);
							$cat->save();
						}

						$categories[$hash] = $cat;
					}

					$hashRoot = $categories[$hash];
				}
			}
			else
			{
				$cat = $root;
			}

			if (isset($fields['Product']))
			{
				$product = Product::getNewInstance($cat);
				$product->isEnabled->set(true);

				// product information
				$impReq->clearData();
				foreach ($fields['Product'] as $field => $csvIndex)
				{
					$impReq->set($field, $record[$csvIndex]);
				}

				// manufacturer
				if (isset($fields['Manufacturer']['name']))
				{
					$impReq->set('manufacturer', $record[$fields['Manufacturer']['name']]);
				}

				// price
				if (isset($fields['ProductPrice']['price']))
				{
					$record[$fields['ProductPrice']['price']] = str_replace(',', '.', $record[$fields['ProductPrice']['price']]);
					$record[$fields['ProductPrice']['price']] = preg_replace('/[^\.0-9]/', '', $record[$fields['ProductPrice']['price']]);
					$impReq->set('price_' . $this->application->getDefaultCurrencyCode(), (float)$record[$fields['ProductPrice']['price']]);
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
							$f = new ARSelectFilter(
									new EqualsCond(
										SpecFieldValue::getLangSearchHandle(
											new ARFieldHandle('SpecFieldValue', 'value'),
											$this->application->getDefaultLanguageCode()
										),
										$record[$csvIndex]
									)
								);
							$f->setLimit(1);

							if (!$value = $attr->getRelatedRecordSet('SpecFieldValue', $f)->shift())
							{
								$value = SpecFieldValue::getNewInstance($attr);

								if ($attr->type->get() == SpecField::TYPE_NUMBERS_SELECTOR)
								{
									$value->value->set($record[$csvIndex]);
								}
								else
								{
									$value->setValueByLang('value', $this->application->getDefaultLanguageCode(), $record[$csvIndex]);
								}

								$value->save();
							}

							$impReq->set($attr->getFormFieldName(), $value->getID());
						}

						else
						{
							$impReq->set($attr->getFormFieldName(), $record[$csvIndex]);
						}
					}
				}

				$product->loadRequestData($impReq);

				$product->save();

				$product->__destruct();
				unset($product);
			}

			$progress++;

			if ($progress % self::PROGRESS_FLUSH_INTERVAL == 0 || ($total == $progress))
			{
				$response->flush($this->getResponse(array('progress' => $progress, 'total' => $total)));
			}

			if (connection_aborted())
			{
				$this->cancel();
			}
		}

		//ActiveRecord::rollback();
		ActiveRecord::commit();

		$response->flush($this->getResponse(array('progress' => 0, 'total' => $total)));

		//echo '|' . round(memory_get_usage() / (1024*1024), 1);

		exit;
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
		ClassLoader::import('framework.request.validator.Form');
		return new Form($this->getValidator());
	}

	private function getValidator()
	{
		ClassLoader::import('framework.request.validator.RequestValidator');
		ClassLoader::import('application.helper.filter.HandleFilter');

		return new RequestValidator('csvFile', $this->request);
	}

	private function getDelimiterForm()
	{
		ClassLoader::import('framework.request.validator.Form');
		return new Form($this->getDelimiterValidator());
	}

	private function getDelimiterValidator()
	{
		ClassLoader::import('framework.request.validator.RequestValidator');
		ClassLoader::import('application.helper.filter.HandleFilter');

		return new RequestValidator('csvDelimiters', $this->request);
	}

	private function getFieldsForm()
	{
		ClassLoader::import('framework.request.validator.Form');
		return new Form($this->getFieldsValidator());
	}

	private function getFieldsValidator()
	{
		ClassLoader::import('framework.request.validator.RequestValidator');
		ClassLoader::import('application.helper.filter.HandleFilter');

		return new RequestValidator('csvFields', $this->request);
	}
}

?>