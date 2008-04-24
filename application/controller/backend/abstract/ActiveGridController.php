<?php

ClassLoader::import('application.controller.backend.abstract.StoreManagementController');

abstract class ActiveGridController extends StoreManagementController
{
	abstract protected function getClassName();

	abstract protected function getDefaultColumns();

	public function export()
	{
		@set_time_limit(0);

		// init file download
		header('Content-Disposition: attachment; filename="' . $this->getCSVFileName() . '"');
		$out = fopen('php://output', 'w');

		// header row
		$columns = $this->getRequestColumns();
		unset($columns['hiddenType']);
		foreach ($columns as $column => $type)
		{
			$header[] = $this->translate($column);
		}
		fputcsv($out, $header);

		// columns
		foreach ($this->lists(true, $columns) as $row)
		{
			fputcsv($out, $row);
		}

		exit;
	}

	public function lists($dataOnly = false, $displayedColumns = null)
	{
		$filter = $this->getSelectFilter();
		new ActiveGrid($this->application, $filter, $this->getClassName());

		$recordCount = true;
		$productArray = ActiveRecordModel::getRecordSetArray($this->getClassName(), $filter, $this->getReferencedData(), $recordCount);

		if (!$displayedColumns)
		{
			$displayedColumns = $this->getRequestColumns();
		}

		$productArray = $this->processDataArray($productArray, $displayedColumns);

		$data = array();

		foreach ($productArray as $row)
		{
			$record = array();
			foreach ($displayedColumns as $column => $type)
			{
				$fieldData = explode('.', $column, 2);
				$class = array_shift($fieldData);
				$field = array_shift($fieldData);

				$value = $this->getColumnValue($row, $class, $field);

				$record[] = $this->formatValue($value, $type);
			}

			$data[] = $record;
		}

		if ($dataOnly)
		{
			return $data;
		}

		$return = array();
		$return['columns'] = array_keys($displayedColumns);
		$return['totalCount'] = $recordCount;
		$return['data'] = $data;

		return new JSONResponse($return);
	}

	protected function processDataArray($productArray, $displayedColumns)
	{
		return $productArray;
	}

	public function processMass($params = array())
	{
		$processorClass = $this->getMassActionProcessor();
		$grid = new ActiveGrid($this->application, $this->getSelectFilter(), $this->getClassName());
		$mass = new $processorClass($grid, $params);
		$mass->setCompletionMessage($this->getMassCompletionMessage());

		return $mass->process($this->getReferencedData());
	}

	protected function getMassCompletionMessage()
	{
		return $this->translate('_mass_action_succeeded');
	}

	protected function getDisplayedColumns($params = null, $customColumns = array())
	{
		// get displayed columns
		$displayedColumns = $this->getSessionData('columns');

		if (!$displayedColumns)
		{
			$displayedColumns = $this->getDefaultColumns();
		}

		$availableColumns = $this->getAvailableColumns($params);
		$displayedColumns = array_intersect_key(array_flip($displayedColumns), $availableColumns);

		if ($customColumns)
		{
			$displayedColumns = array_merge($customColumns, $displayedColumns);
		}

		$displayedColumns = array_merge(array($this->getClassName() . '.ID' => 'numeric'), $displayedColumns);

		// set field type as value
		foreach ($displayedColumns as $column => $foo)
		{
			if (is_numeric($displayedColumns[$column]))
			{
				$displayedColumns[$column] = $availableColumns[$column]['type'];
			}
		}

		return $displayedColumns;
	}

	public function getAvailableColumns()
	{
		$productSchema = ActiveRecordModel::getSchemaInstance($this->getClassName());

		$availableColumns = array();
		foreach ($productSchema->getFieldList() as $field)
		{
			$type = ActiveGrid::getFieldType($field);

			if (!$type)
			{
				continue;
			}

			$availableColumns[$this->getClassName() . '.' . $field->getName()] = $type;
		}

		$availableColumns = array_merge($availableColumns, $this->getCustomColumns());

		foreach ($availableColumns as $column => $type)
		{
			$availableColumns[$column] = array('name' => $this->translate($column), 'type' => $type);
		}

		return $availableColumns;
	}

	protected function getAvailableRequestColumns()
	{
		return $this->getAvailableColumns();
	}

	protected function setGridResponse(ActionResponse $response)
	{
		$displayedColumns = $this->getRequestColumns();
		$availableColumns = $this->getAvailableRequestColumns();

		// sort available columns by display state (displayed columns first)
		$displayedAvailable = array_intersect_key($availableColumns, $displayedColumns);
		$notDisplayedAvailable = array_diff_key($availableColumns, $displayedColumns);
		$availableColumns = array_merge($displayedAvailable, $notDisplayedAvailable);

		$response->set('displayedColumns', $displayedColumns);
		$response->set('availableColumns', $availableColumns);
		$response->set('massForm', $this->getMassForm());
		$response->set('offset', $this->request->get('offset'));
		$response->set('totalCount', '0');
		$response->set('filters', $this->request->get('filters'));

		return $response;
	}

	protected function postProcessDataArray($array)
	{
		return $array;
	}

	protected function formatValue($value, $type)
	{
		if ('bool' == $type)
		{
			$value = $value ? $this->translate('_yes') : $this->translate('_no');
		}

		return $value;
	}

	protected function getReferencedData()
	{
		return array();
	}

	protected function getColumnValue($record, $class, $field)
	{
		if (!isset($record[$class][$field]) && isset($record[$field]))
		{
			return $record[$field];
		}
		else if (isset($record[$class][$field]))
		{
			return $record[$class][$field];
		}
		else
		{
			return '';
		}
	}

	protected function getSelectFilter()
	{
		return new ARSelectFilter();
	}

	protected function getRequestColumns()
	{
		return $this->getDisplayedColumns();
	}

	protected function getCustomColumns()
	{
		return array();
	}

	protected function getCSVFileName()
	{
		return 'exported.csv';
	}

	protected function getMassValidator()
	{
		ClassLoader::import("framework.request.validator.RequestValidator");

		return new RequestValidator(get_class($this) . "MassFormValidator", $this->request);
	}

	protected function getMassForm()
	{
		ClassLoader::import("framework.request.validator.Form");
		return new Form($this->getMassValidator());
	}

	protected function getMassActionProcessor()
	{
		ClassLoader::import('application.helper.massAction.MassActionProcessor');
		return 'MassActionProcessor';
	}

	public function isMassCancelled()
	{
		$isCancelled = call_user_func_array(array($this->getMassActionProcessor(), 'isCancelled'), array($this->request->get('pid')));
		return new JSONResponse(array('isCancelled' => $isCancelled));
	}

	public function changeColumns()
	{
		$columns = array_keys($this->request->get('col', array()));
		$this->setSessionData('columns', $columns);
	}

	public static function isCallable()
	{
		return true;
	}
}

?>