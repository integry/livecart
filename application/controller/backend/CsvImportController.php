<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.model.parser.CsvFile");
ClassLoader::import("application.model.category.Category");
ClassLoader::import("application.model.product.Product");
ClassLoader::import("application.model.category.SpecField");
ClassLoader::import("application.model.datasync.CsvImportProfile");

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
		$classes = array_diff($this->application->getPluginClasses('application.model.datasync.import'), array('ProductImport'));
		$classes = array_merge(array('ProductImport'), $classes);
		$types = array();

		foreach ($classes as $class)
		{
			$types[$class] = $this->translate($class);
		}

		$form = $this->getForm();
		$root = Category::getInstanceByID($this->request->isValueSet('category') ? $this->request->get('category') : Category::ROOT_ID, Category::LOAD_DATA);
		$form->set('category', $root->getID());
		$form->set('atServer', $this->request->get('file'));

		$response = new ActionResponse();
		$response->set('form', $form);
		$response->set('catPath', $root->getPathNodeArray(true));
		$response->set('types', $types);
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

		return new ActionRedirectResponse('backend.csvImport', 'delimiters', array('query' => array('file' => $filePath, 'category' => $this->request->get('category'), 'type' => $this->request->get('type'), 'options' => base64_encode(serialize($this->request->get('options'))))));
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
		$form->set('options', $this->request->get('options'));
		$form->set('delimiter', $delimiter);
		$form->set('file', $file);
		$form->set('type', $this->request->get('type'));
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
		$response->set('type', $this->request->get('type'));
		$response->set('preview', $preview);
		$response->set('previewCount', count($preview));
		$response->set('total', $csv->getRecordCount());
		$response->set('currencies', $this->application->getCurrencyArray());
		$response->set('languages', $this->application->getLanguageSetArray(true));
		$response->set('groups', ActiveRecordModel::getRecordSetArray('UserGroup', new ARSelectFilter()));
		$response->set('catPath', Category::getInstanceByID($this->request->get('category'), Category::LOAD_DATA)->getPathNodeArray(true));

		$profiles = array('' => '');
		foreach ((array)glob($this->getProfileDirectory($this->getImportInstance()) . '*.ini') as $path)
		{
			$profile = basename($path, '.ini');
			$profiles[$profile] = $profile;
		}
		$response->set('profiles', $profiles);

		return $response;
	}

	public function preview()
	{
		return new ActionResponse('preview', $this->getPreview(new CsvFile($this->request->get('file'), $this->request->get('delimiter'))));
	}

	public function fields()
	{
		$import = $this->getImportInstance();

		$csv = new CsvFile($this->request->get('file'), $this->request->get('delimiter'));

		$response = new ActionResponse('columns', $csv->getRecord());
		$response->set('fields', $import->getFields());
		$response->set('form', $this->getFieldsForm());
		$response->set('type', $this->request->get('type'));
		$response->set('options', $this->request->get('options'));
		return $response;
	}

	public function loadProfile()
	{
		$import = $this->getImportInstance();
		$file = $this->getProfileDirectory($import) . $this->request->get('profile') . '.ini';
		$profile = CsvImportProfile::load($file);
		return new JSONResponse($profile->toArray());
	}

	public function deleteProfile()
	{
		$import = $this->getImportInstance();
		$file = $this->getProfileDirectory($import) . $this->request->get('profile') . '.ini';
		unlink($file);

		return new JSONResponse(array('profile' => $this->request->get('profile')), 'success', $this->translate('_profile_deleted'));
	}

	public function import()
	{
		$options = unserialize(base64_decode($this->request->get('options')));

		$response = new JSONResponse(null);

		if (file_exists($this->getCancelFile()))
		{
			unlink($this->getCancelFile());
		}

		if (!$this->request->get('continue'))
		{
			$this->clearCacheProgress();
		}

		$import = $this->getImportInstance();

		set_time_limit(0);
		ignore_user_abort(true);

		$profile = new CsvImportProfile($import->getClassName());

		// map CSV fields to LiveCart fields
		$params = $this->request->get('params');
		foreach ($this->request->get('column') as $key => $value)
		{
			if ($value)
			{
				$fieldParams = !empty($params[$key]) ? $params[$key] : array();
				$profile->setField($key, $value, array_filter($fieldParams));
			}
		}

		$profile->setParam('isHead', $this->request->get('firstHeader'));

		if ($this->request->get('saveProfile'))
		{
			$path = $this->getProfileDirectory($import) . $this->request->get('profileName') . '.ini';
			$profile->setFileName($path);
			$profile->save();
		}

		// get import root category
		if ($import->isRootCategory())
		{
			$profile->setParam('category', $this->request->get('category'));
		}

		$import->beforeImport($profile);

		$csv = new CsvFile($this->request->get('file'), $this->request->get('delimiter'));
		$total = $csv->getRecordCount();
		if ($this->request->get('firstHeader'))
		{
			$total -= 1;
		}

		if ($this->request->get('firstHeader'))
		{
			$import->skipHeader($csv);
			$import->skipHeader($csv);
		}

		$progress = 0;
		$processed = 0;

		if ($this->request->get('continue'))
		{
			$import->setImportPosition($csv, $this->getCacheProgress() + 1);
		}
		else
		{
			if (!empty($options['transaction']))
			{
				ActiveRecord::beginTransaction();
			}
		}

		if (empty($options['transaction']))
		{
			$this->request->set('continue', true);
		}

		$import->setOptions($options);

		if ($uid = $this->request->get('uid'))
		{
			$import->setUID($uid);
		}

		do
		{
			$progress += $import->importFileChunk($csv, $profile, 1);

			// continue timed-out import
			if ($this->request->get('continue'))
			{
				$this->setCacheProgress($progress);
			}

			ActiveRecord::clearPool();

			if ($progress % self::PROGRESS_FLUSH_INTERVAL == 0 || ($total == $progress))
			{
				$response->flush($this->getResponse(array('progress' => $progress, 'total' => $total, 'uid' => $import->getUID(), 'lastName' => $import->getLastImportedRecordName())));
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
		while (!$import->isCompleted($csv));

		if (!empty($options['missing']) && ('keep' != $options['missing']))
		{
			$filter = $import->getMissingRecordFilter($profile);

			if ('disable' == $options['missing'])
			{
				$import->disableRecords($filter);
			}
			else if ('delete' == $options['missing'])
			{
				$import->deleteRecords($filter);
			}
		}

		$import->afterImport();

		if (!$this->request->get('continue'))
		{
			//ActiveRecord::rollback();
			ActiveRecord::commit();
		}

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

	private function getImportInstance()
	{
		if (!$this->importInstance)
		{
			$class = $this->request->get('type');
			$this->application->loadPluginClass('application.model.datasync.import', $class);
			$this->importInstance = new $class($this->application);
		}

		return $this->importInstance;
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

		return $this->getValidator('csvFile', $this->request);
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

	private function getProfileDirectory(DataImport $import)
	{
		return ClassLoader::getRealPath('storage.importProfiles.' . get_class($import) . '.');
	}
}

?>
