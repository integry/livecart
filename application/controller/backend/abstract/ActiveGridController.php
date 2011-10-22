<?php

ClassLoader::import('application.controller.backend.abstract.StoreManagementController');
ClassLoader::import('application.model.eav.EavFieldManager');

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
		header('Content-Type: text/csv');
		$out = fopen('php://output', 'w');

		// header row
		$columns = $this->getExportColumns();
		unset($columns['hiddenType']);
		fputcsv($out, $columns);

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
		$productArray = ActiveRecordModel::getRecordSetArray($this->getClassName(), $filter, $this->getReferencedData(), $recordCount);

		if (!$displayedColumns)
		{
			$displayedColumns = $this->getRequestColumns();
		}
		$data = $this->recordSetArrayToListData($productArray, $displayedColumns, $exportBufferIndex);
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

	protected function recordSetArrayToListData($productArray, $displayedColumns, $exportBufferIndex=true)
	{
		$data = array();
		$productArray = $this->processDataArray($productArray, $displayedColumns);
		foreach ($productArray as &$row)
		{
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
		return $data;
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
		new ActiveGrid($this->application, $filter, $this->getClassName(), $this->getHavingClauseColumnTypes() );
		return $filter;
	}

	protected function processDataArray($dataArray, $displayedColumns)
	{
		// load specification data
		if ($this->isEav())
		{
			foreach ($displayedColumns as $column => $type)
			{
				if (!strpos($column, '.'))
				{
					continue;
				}

				list($class, $field) = explode('.', $column, 2);
				if ('eavField' == $class)
				{
					ActiveRecordModel::addArrayToEavQueue($this->getClassName(), $dataArray);
					ActiveRecordModel::loadEav();
					break;
				}
			}
		}

		return $dataArray;
	}

	public function processMass($params = array())
	{
		$processorClass = $this->getMassActionProcessor();
		$grid = new ActiveGrid($this->application, $this->getSelectFilter(), $this->getClassName(), $this->getHavingClauseColumnTypes());
		$mass = new $processorClass($grid, $params);
		$mass->setCompletionMessage($this->getMassCompletionMessage());

		return $mass->process($this->getReferencedData());
	}

	protected function getMassCompletionMessage()
	{
		return $this->translate('_mass_action_succeeded');
	}

	protected function getAdvancedSearchFields()
	{
		return array();
	}

	protected function translateFieldArray($fields)
	{
		foreach($fields as $key=>&$value)
		{
			if(empty($value['name']))
			{
				$value['name'] = $this->translate($key);
			}
		}
		return $fields;

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

	public static function getSchemaColumns($schemaName, LiveCart $application, $customColumns = array())
	{
		$productSchema = ActiveRecordModel::getSchemaInstance($schemaName);

		$availableColumns = array();
		foreach ($productSchema->getFieldList() as $field)
		{
			$type = ActiveGrid::getFieldType($field);

			if (!$type && ('ID' != $field->getName()))
			{
				continue;
			}

			$availableColumns[$schemaName . '.' . $field->getName()] = $type;
		}

		$availableColumns = array_merge($availableColumns, $customColumns);

		foreach ($availableColumns as $column => $type)
		{
			$availableColumns[$column] = array('name' => $application->translate($column), 'type' => $type);
		}

		// specField columns
		if (self::isEav($schemaName))
		{
			$fields = EavFieldManager::getClassFieldSet($schemaName);
			foreach ($fields as $field)
			{
				$fieldArray = $field->toArray();

				if ($field->isDate())
				{
					$type = 'date';
				}
				else
				{
					$type = $field->isSimpleNumbers() ? 'numeric' : 'text';
				}

				$availableColumns['eavField.' . $field->getID()] = array
					(
						'name' => $fieldArray['name_lang'],
						'type' => $type
					);
			}
		}
		return $availableColumns;
	}

	public function getHavingClauseColumnTypes()
	{
		return array();
	}

	public function getAvailableColumns($schemaName = null)
	{
		$schemaName = $schemaName ? $schemaName : $this->getClassName();
		$availableColumns = self::getSchemaColumns($schemaName, $this->application, $this->getCustomColumns());

		// sort available columns by placing the default columns first
		$default = array();
		foreach ($this->getDefaultColumns() as $column)
		{
			if (isset($availableColumns[$column]))
			{
				$default[$column] = $availableColumns[$column];
				unset($availableColumns[$column]);
			}
		}
		$availableColumns = array_merge($default, $availableColumns);
		return $availableColumns;
	}

	protected function getAvailableRequestColumns()
	{
		// $this->getAdvancedSearchFields()
		return $this->getAvailableColumns();
	}

	protected function getExportColumns()
	{
		$available = $this->getAvailableRequestColumns();
		$columns = array();
		foreach ($this->getDisplayedColumns() as $column => $type)
		{
			if (isset($available[$column]))
			{
				$columns[$column] = $available[$column]['name'];
			}
			else
			{
				$columns[$column] = $this->translate($column);
			}
		}

		return $columns;
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
		$response->set('advancedSearchColumns', $this->getAdvancedSearchFields());

		$response->set('massForm', $this->getMassForm());
		$response->set('offset', $this->request->get('offset'));
		$response->set('totalCount', '0');
		$response->set('filters', $this->request->get('filters'));
		$response->set('data', $this->lists(false, $displayedColumns)->getData());
		$isQuickEdit = $this->isQuickEdit();
		$response->set('isQuickEdit', $isQuickEdit);
		if ($isQuickEdit)
		{
			$router = $this->getApplication()->getRouter();
			$className = get_class($this);
			$className = str_replace('Controller', '', $className);
			$className[0] = strtolower($className[0]); //lcfirst() when php5.3

			$idToken = '000';
			$response->set('quickEditUrlIdentificatorToken',$idToken);
			$response->set('saveQuickEditUrl', $router->createURL(
				array('controller'=>'backend.'.$className, 'action'=>'saveQuickEdit', 'id'=>$idToken)));
			$response->set('quickEditUrl', $router->createURL(
				array('controller'=>'backend.'.$className, 'action'=>'quickEdit', 'id'=>$idToken)));
		}
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
		else if (isset($record[$class][$field]) && $field)
		{
			return $record[$class][$field];
		}
		else if (isset($record[$class]) && !is_array($record[$class]))
		{
			return $record[$class];
		}
		else if (strpos($field, '.'))
		{
			list ($field, $sub) = explode('.', $field, 2);
			if (isset($record[$class][$field][$sub]))
			{
				return $record[$class][$field][$sub];
			}
		}
		else if ('eavField' == $class)
		{
			if (isset($record['attributes'][$field]))
			{
				$attr = $record['attributes'][$field];

				if (isset($attr['values']))
				{
					$values = array();
					foreach ($attr['values'] as $val)
					{
						$values[] = $val['value_lang'];
					}

					return implode(' / ', $values);
				}

				foreach (array('value_lang', 'value') as $valType)
				{
					if (isset($attr[$valType]))
					{
						return $attr[$valType];
					}
				}
			}
		}

		return '';
	}

	protected function getSelectFilter()
	{
		$f = new ARSelectFilter();

		// specField columns
		if ($this->isEav())
		{
			$needsJoin = true;
			$fields = EavFieldManager::getClassFieldSet($this->getClassName());

			foreach ($fields as $field)
			{
				if (!$field->isMultiValue->get())
				{
					if ($needsJoin)
					{
						$f->joinTable('EavObject', $this->getClassName(), 'ID', 'eavObjectID');
						$needsJoin = false;
					}

					$field->defineJoin($f);
				}
			}
		}

		return $f;
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
		return $this->getValidator(get_class($this) . "MassFormValidator", $this->request);
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

	protected function isEav($className = null)
	{
		$className = $className ? $className : $this->getClassName();
		return ActiveRecordModel::isEav($className);
	}

	public function isQuickEdit()
	{
		return false;
	}

	public function quickEdit()
	{
		return false;
	}

	public function saveQuickEdit()
	{
		return false;
	}

	protected function loadQuickEditLanguageFile()
	{
		$this->loadLanguageFile('backend/abstract/ActiveGridQuickEdit');
	}

	protected function quickEditSaveResponse($object)
	{
		$displayedColumns = $this->getRequestColumns();
		$r = array(
			'data'=> $this->recordSetArrayToListData(array($object->toArray()), $displayedColumns),
			'columns'=>array_keys($displayedColumns)
		);
		return new JSONResponse($r, 'success');
	}
}
?>