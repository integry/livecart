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

		$this->set('form', $this->getForm());
		$this->set('carts', $this->getDrivers());
		$this->set('dbTypes', array('mysql' => 'MySQL'));
		$this->set('recordTypes', LiveCartImporter::getRecordTypes());
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

		$dsn = $this->request->get('dbType') . '://' .
				   $this->request->get('dbUser') .
				   		($this->request->get('dbPass') ? ':' . $this->request->get('dbPass') : '') .
				   			'@' . $this->request->get('dbServer') .
				   				'/' . $this->request->get('dbName');

		try
		{
			$cart = $this->request->get('cart');
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
		$val->add('cart', new PresenceOf(array('message' => $this->translate('_err_no_cart_selected'))));
		$val->add('dbServer', new PresenceOf(array('message' => $this->translate('_err_no_database_server'))));
		$val->add('dbType', new PresenceOf(array('message' => $this->translate('_err_no_db_type'))));
		$val->add('dbName', new PresenceOf(array('message' => $this->translate('_err_no_database_name'))));
		$val->add('dbUser', new PresenceOf(array('message' => $this->translate('_err_no_database_username'))));

		return $val;
	}
}

?>
