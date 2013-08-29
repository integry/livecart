<?php


/**
 * Handles product importing through a CSV file
 *
 * @package application/controller/backend
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

	public function indexAction()
	{
		$classes = array_diff($this->application->getPluginClasses('application/model/datasync/import'), array('ProductImport'));
		$classes = array_merge(array('ProductImport'), $classes);
		$types = array();

		foreach ($classes as $class)
		{
			$types[$class] = $this->translate($class);
		}

		$form = $this->getForm();
		$root = Category::getInstanceByID($this->request->isValueSet('category') ? $this->request->gget('category') : Category::ROOT_ID, Category::LOAD_DATA);
		$form->set('category', $root->getID());
		$form->set('atServer', $this->request->gget('file'));

		$response = new ActionResponse();
		$response->set('form', $form);
		$response->set('catPath', $root->getPathNodeArray(true));
		$response->set('types', $types);
		return $response;
	}

	public function setFileAction()
	{
		$filePath = '';

		if (!empty($_FILES['upload']['tmp_name']))
		{
			$filePath = $this->config->getPath('cache') . '/upload.csv';
			move_uploaded_file($_FILES['upload']['tmp_name'], $filePath);
		}
		else
		{
			$filePath = $this->request->gget('atServer');
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

		return new ActionRedirectResponse('backend.csvImport', 'delimiters', array('query' => array('file' => $filePath, 'category' => $this->request->gget('category'), 'type' => $this->request->gget('type'), 'options' => base64_encode(serialize($this->request->gget('options'))))));
	}

	public function delimitersAction()
	{
		$file = $this->request->gget('file');
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
		$form->set('options', $this->request->gget('options'));
		$form->set('delimiter', $delimiter);
		$form->set('file', $file);
		$form->set('type', $this->request->gget('type'));
		$form->set('category', $this->request->gget('category'));

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
		$response->set('type', $this->request->gget('type'));
		$response->set('preview', $preview);
		$response->set('previewCount', count($preview));
		$response->set('total', $csv->getRecordCount());
		$response->set('currencies', $this->application->getCurrencyArray());
		$response->set('languages', $this->application->getLanguageSetArray(true));
		$response->set('groups', ActiveRecordModel::getRecordSetArray('UserGroup', new ARSelectFilter()));
		$response->set('catPath', Category::getInstanceByID($this->request->gget('category'), Category::LOAD_DATA)->getPathNodeArray(true));

		$profiles = array('' => '');
		foreach ((array)glob($this->getProfileDirectory($this->getImportInstance()) . '*.ini') as $path)
		{
			$profile = basename($path, '.ini');
			$profiles[$profile] = $profile;
		}
		$response->set('profiles', $profiles);

		return $response;
	}

	public function previewAction()
	{
		return new ActionResponse('preview', $this->getPreview(new CsvFile($this->request->gget('file'), $this->request->gget('delimiter'))));
	}

	public function fieldsAction()
	{
		$import = $this->getImportInstance();

		$csv = new CsvFile($this->request->gget('file'), $this->request->gget('delimiter'));

		$response = new ActionResponse('columns', $csv->getRecord());
		$response->set('fields', $import->getFields());
		$response->set('form', $this->getFieldsForm());
		$response->set('type', $this->request->gget('type'));
		$response->set('options', $this->request->gget('options'));
		return $response;
	}

	public function loadProfileAction()
	{
		$import = $this->getImportInstance();
		$file = $this->getProfileDirectory($import) . $this->request->gget('profile') . '.ini';
		$profile = CsvImportProfile::load($file);
		return new JSONResponse($profile->toArray());
	}

	public function deleteProfileAction()
	{
		$import = $this->getImportInstance();
		$file = $this->getProfileDirectory($import) . $this->request->gget('profile') . '.ini';
		unlink($file);

		return new JSONResponse(array('profile' => $this->request->gget('profile')), 'success', $this->translate('_profile_deleted'));
	}

	public function importAction()
	{
		$options = unserialize(base64_decode($this->request->gget('options')));

		$response = new JSONResponse(null);

		if (file_exists($this->getCancelFile()))
		{
			unlink($this->getCancelFile());
		}

		if (!$this->request->gget('continue'))
		{
			$this->clearCacheProgress();
		}

		$import = $this->getImportInstance();

		set_time_limit(0);
		ignore_user_abort(true);

		$profile = new CsvImportProfile($import->getClassName());

		// map CSV fields to LiveCart fields
		$params = $this->request->gget('params');
		foreach ($this->request->gget('column') as $key => $value)
		{
			if ($value)
			{
				$fieldParams = !empty($params[$key]) ? $params[$key] : array();
				$profile->setField($key, $value, array_filter($fieldParams));
			}
		}

		$profile->setParam('isHead', $this->request->gget('firstHeader'));

		if ($this->request->gget('saveProfile'))
		{
			$path = $this->getProfileDirectory($import) . $this->request->gget('profileName') . '.ini';
			$profile->setFileName($path);
			$profile->save();
		}

		// get import root category
		if ($import->isRootCategory())
		{
			$profile->setParam('category', $this->request->gget('category'));
		}

		$import->beforeImport($profile);

		$csv = new CsvFile($this->request->gget('file'), $this->request->gget('delimiter'));
		$total = $csv->getRecordCount();
		if ($this->request->gget('firstHeader'))
		{
			$total -= 1;
		}

		if ($this->request->gget('firstHeader'))
		{
			$import->skipHeader($csv);
			$import->skipHeader($csv);
		}

		$progress = 0;
		$processed = 0;

		if ($this->request->gget('continue'))
		{
			$import->setImportPosition($csv, $this->getCacheProgress() + 1);
			$progress = $this->getCacheProgress();
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

		if ($uid = $this->request->gget('uid'))
		{
			$import->setUID($uid);
		}

		do
		{
			$progress += $import->importFileChunk($csv, $profile, 1);

			// continue timed-out import
			if ($this->request->gget('continue'))
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
			//if (!$this->request->gget('continue')) exit;

			if (connection_aborted())
			{
				if ($this->request->gget('continue'))
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

		if (!$this->request->gget('continue'))
		{
			//ActiveRecord::rollback();
			ActiveRecord::commit();
		}

		$response->flush($this->getResponse(array('progress' => 0, 'total' => $total)));

		//echo '|' . round(memory_get_usage() / (1024*1024), 1);

		exit;
	}

	public function isCancelledAction()
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
			$class = $this->request->gget('type');
			$this->application->loadPluginClass('application/model/datasync/import', $class);
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
		return $this->config->getPath('cache') . '/.csvImportCancel';
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

		return $this->getValidator('csvFile', $this->request);
	}

	private function getDelimiterForm()
	{
		return new Form($this->getDelimiterValidator());
	}

	private function getDelimiterValidator()
	{

		return new RequestValidator('csvDelimiters', $this->request);
	}

	private function getFieldsForm()
	{
		return new Form($this->getFieldsValidator());
	}

	private function getFieldsValidator()
	{

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
		return $this->config->getPath('cache/') . 'csvProgress';
	}

	private function getProfileDirectory(DataImport $import)
	{
		return $this->config->getPath('storage/importProfiles/' . get_class($import) . '.');
	}
}

?>
