<?php

ClassLoader::import('application.controller.backend.abstract.StoreManagementController');

abstract class ActiveGridController extends StoreManagementController
{
	const EXPORT_BUFFER_ROW_COUNT = 200;

	abstract protected function getClassName();

	abstract protected function getDefaultColumns();

	public function export()
	{
		@set_time_limit(0);

		$count = ActiveRecordModel::getRecordCount($this->getClassName(), $this->getListFilter(), $this->getReferencedData());
		$bufferCnt = ceil($count / self::EXPORT_BUFFER_ROW_COUNT);

		// init file download
		header('Content-Disposition: attachment; filename="' . $this->getCSVFileName() . '"');
		//header('Content-Type: text/javascript');
		$out = fopen('php://output', 'w');

		// header row
		$columns = $this->getExportColumns();
		unset($columns['hiddenType']);
		foreach ($columns as $column => $type)
		{
			$header[] = $type['name'];
		}
		fputcsv($out, $header);

		// data
		for ($bufferIndex = 1; $bufferIndex <= $bufferCnt; $bufferIndex++)
		{
			foreach ($this->lists(true, $columns, $bufferIndex) as $row)
			{
				fputcsv($out, $row);
			}
		}

		exit;
	}

	public function lists($dataOnly = false, $displayedColumns = null, $exportBufferIndex = 0)
	{
		$filter = $this->getListFilter();
		$this->setDefaultSortOrder($filter);

		$recordCount = true;

		if ($exportBufferIndex)
		{
			$exportFrom = ($exportBufferIndex - 1) * self::EXPORT_BUFFER_ROW_COUNT;
			$filter->setLimit(self::EXPORT_BUFFER_ROW_COUNT, $exportFrom);
		}

		if (!$displayedColumns)
		{
			$displayedColumns = $this->getRequestColumns();
		}

		$productArray = ActiveRecordModel::getRecordSetArray($this->getClassName(), $filter, $this->getReferencedData(), $recordCount);

		$productArray = $this->processDataArray($productArray, $displayedColumns);

		$data = array();

		foreach ($productArray as &$row)
		{
			//print_r($row); exit;
			$data = array_merge($data, $this->getPreparedRecord($row, $displayedColumns));

			// avoid possible memory leaks due to circular references (http://bugs.php.net/bug.php?id=33595)
			// only do this for CSV export
			// @todo: if necessary, move to ActiveRecordModel::unsetArray($row);
			if ($exportBufferIndex)
			{
				foreach ($row as $key => $subArray)
				{
					if (is_array($subArray))
					{
						foreach ($subArray as $subKey => $subValue)
						{
							unset($row[$key][$subKey]);
						}
					}
				}
			}
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

	protected function getPreparedRecord($row, $displayedColumns)
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

		return array($record);
	}

	protected function getListFilter()
	{
		$filter = $this->getSelectFilter();
		new ActiveGrid($this->application, $filter, $this->getClassName());
		return $filter;
	}

	protected function processDataArray($dataArray, $displayedColumns)
	{
		return $dataArray;
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

	protected function getExportColumns()
	{
		return $this->getDisplayedColumns();
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
		$response->set('data', $this->lists(false, $displayedColumns)->getData());

		return $response;
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
		if (!isset($record[$class][$field]) && isset($record[$field]) && ($this->getClassName() == $class))
		{
			return $record[$field];
		}
		else if (isset($record[$class][$field]))
		{
			return $record[$class][$field];
		}
		else if (strpos($field, '.'))
		{
			list ($field, $sub) = explode('.', $field, 2);
			if (isset($record[$class][$field][$sub]))
			{
				return $record[$class][$field][$sub];
			}
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

	protected function setDefaultSortOrder(ARSelectFilter $filter)
	{
		$filter->setOrder(new ARFieldHandle($this->getClassName(), 'ID'), 'DESC');
	}

	protected function getMassValidator()
	{
		return new RequestValidator(get_class($this) . "MassFormValidator", $this->request);
	}

	protected function getMassForm()
	{
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