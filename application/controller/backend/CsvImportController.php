<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.model.parser.CsvFile");

/**
 * Handles product importing through a CSV file
 *
 * @package application.controller.backend
 * @author Integry Systems
 *
 */
class CsvImportController extends StoreManagementController
{
	const PREVIEW_ROWS = 10;

	private $delimiters = array(
									'_del_comma' => ',',
									'_del_semicolon' => ';',
									'_del_tab' => '	'
								);

	public function index()
	{
		$response = new ActionResponse();
		$response->set('form', $this->getForm());
		return $response;
	}

	public function setFile()
	{
		$filePath = '';

		if (!empty($_FILES['upload']))
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

		return new ActionRedirectResponse('backend.csvImport', 'delimiters', array('query' => 'file=' . $filePath));
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

			if (isset($count))
			{
				break;
			}
			else
			{
				$delimiter = ',';
			}
		}

		$form = $this->getDelimiterForm();
		$form->set('delimiter', $delimiter);

		$response = new ActionResponse();
		$response->set('form', $form);
		$response->set('file', $file);
		$response->set('delimiters', $this->delimiters);
		return $response;
	}

	public function import()
	{
		//ignore_user_abort(true);
		set_time_limit(0);

		$validator = $this->getValidator();
		if (!$validator->isValid())
		{
			return new JSONResponse(array('errors' => $validator->getErrorList()));
		}

		$dsn = $this->request->get('dbType') . '://' .
				   $this->request->get('dbUser') .
				   		($this->request->get('dbPass') ? ':' . $this->request->get('dbPass') : '') .
				   			'@' . $this->request->get('dbServer') .
				   				'/' . $this->request->get('dbName');

		try
		{
			$cart = $this->request->get('cart');
			ClassLoader::import('library.import.driver.' . $cart);
			$driver = new $cart($dsn, $this->request->get('filePath'));
		}
		catch (SQLException $e)
		{
			$validator->triggerError('dbServer', $e->getNativeError());
			$validator->saveState();
			return new JSONResponse(array('errors' => $validator->getErrorList()));
		}

		if (!$driver->isDatabaseValid())
		{
			$validator->triggerError('dbName', $this->maketext('_invalid_database', $driver->getName()));
			$validator->saveState();
			return new JSONResponse(array('errors' => $validator->getErrorList()));
		}

		if (!$driver->isPathValid())
		{
			$validator->triggerError('filePath', $this->maketext('_invalid_path', $driver->getName()));
			$validator->saveState();
			return new JSONResponse(array('errors' => $validator->getErrorList()));
		}

		$importer = new LiveCartImporter($driver);

		header('Content-type: text/javascript');

		// get importable data types
		$this->flushResponse(array('types' => $importer->getItemTypes()));
//ActiveRecord::beginTransaction();
		// process import
		while (true)
		{
			$result = $importer->process();

			$this->flushResponse($result);

			if (is_null($result))
			{
				break;
			}
		}
//ActiveRecord::rollback();

		$importer->reset();
		exit;
	}

	private function flushResponse($data)
	{
		//print_r($data);
		echo '|' . base64_encode(json_encode($data));
		flush();
	}

	private function getPreview(CsvFile $csv)
	{
		$ret = array();

		for ($k = 0; $k < self::PREVIEW_ROWS; $k++)
		{
			$ret[] = $csv->getRecord();
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

		return new RequestValidator('databaseImport', $this->request);
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

		return new RequestValidator('databaseImport', $this->request);
	}

}

?>
