<?php


/**
 * Import data from other shopping cart programs
 *
 * @package application/controller/backend
 * @author Integry Systems
 * @role dbmigration
 */
class DatabaseImportController extends StoreManagementController
{
	public function indexAction()
	{
		$response = new ActionResponse();
		$response->set('form', $this->getForm());
		$response->set('carts', $this->getDrivers());
		$response->set('dbTypes', array('mysql' => 'MySQL'));
		$response->set('recordTypes', LiveCartImporter::getRecordTypes());
		return $response;
	}

	public function importAction()
	{
		//ignore_user_abort(true);
		set_time_limit(0);

		$validator = $this->buildValidator();
		if (!$validator->isValid())
		{
			return new JSONResponse(array('errors' => $validator->getErrorList()));
		}

		$dsn = $this->request->gget('dbType') . '://' .
				   $this->request->gget('dbUser') .
				   		($this->request->gget('dbPass') ? ':' . $this->request->gget('dbPass') : '') .
				   			'@' . $this->request->gget('dbServer') .
				   				'/' . $this->request->gget('dbName');

		try
		{
			$cart = $this->request->gget('cart');
						$driver = new $cart($dsn, $this->request->gget('filePath'));
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

		$response = new JSONResponse(null);

		// get importable data types
		$response->flush($this->getResponse(array('types' => $importer->getItemTypes())));

		ActiveRecord::beginTransaction();

		// process import
		try
		{
			while (true)
			{
				$result = $importer->process();

				$response->flush($this->getResponse($result));

	//echo '|' . round(memory_get_usage() / (1024*1024), 1) . " ($result[type] : " . array_shift(array_shift(ActiveRecord::getDataBySQL("SELECT COUNT(*) FROM " . $result['type']))) . ")<br> \n";

				if (is_null($result))
				{
					break;
				}
			}
		}
		catch (Exception $e)
		{
			print_r($e->getMessage());
			ActiveRecord::rollback();
		}

		if (!$this->application->isDevMode() || 1)
		{
			ActiveRecord::commit();
		}
		else
		{
			ActiveRecord::rollback();
		}

		$importer->reset();

		return $response;
	}

	private function getResponse($data)
	{
		return '|' . base64_encode(json_encode($data));
	}

	private function getDrivers()
	{
		$drivers = array();

		foreach (new DirectoryIterator($this->config->getPath('library/import.driver')) as $file)
		{
			if (!$file->isDot())
			{
				include_once $file->getPathname();
				$className = basename($file->getFileName(), '.php');
				$drivers[$className] = call_user_func(array($className, 'getName'));
			}
		}

		natcasesort($drivers);

		return $drivers;
	}

	private function getForm()
	{
		return new Form($this->buildValidator());
	}

	private function buildValidator()
	{

		$val = $this->getValidator('databaseImport', $this->request);
		$val->addCheck('cart', new IsNotEmptyCheck($this->translate('_err_no_cart_selected')));
		$val->addCheck('dbServer', new IsNotEmptyCheck($this->translate('_err_no_database_server')));
		$val->addCheck('dbType', new IsNotEmptyCheck($this->translate('_err_no_db_type')));
		$val->addCheck('dbName', new IsNotEmptyCheck($this->translate('_err_no_database_name')));
		$val->addCheck('dbUser', new IsNotEmptyCheck($this->translate('_err_no_database_username')));

		return $val;
	}
}

?>
