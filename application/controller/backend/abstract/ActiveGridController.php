<?php

use \Phalcon\Mvc\Model\Query\Builder;

abstract class ActiveGridController extends ControllerBackend
{
	const EXPORT_BUFFER_ROW_COUNT = 200;

	abstract protected function getClassName();

	abstract protected function getDefaultColumns();

	public function exportAction()
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

	public function listsAction()
	{
		$filter = $this->getListFilter();
		$this->setDefaultSortorderBy($filter);
		$recordCount = true;

		$exportBufferIndex = 0;
		
		/*
		if ($exportBufferIndex)
		{
			$exportFrom = ($exportBufferIndex - 1) * self::EXPORT_BUFFER_ROW_COUNT;
			$filter->limit(self::EXPORT_BUFFER_ROW_COUNT, $exportFrom);
		}
		*/

		// todo: $this->getReferencedData()
		// todo: $recordCount
		
		$productArray = $filter->getQuery()->execute();
		
		$displayedColumns = $this->getRequestColumns();
		
		$data = $this->recordSetArrayToListData($productArray, $displayedColumns, $exportBufferIndex);

		// get total count
		$filter->limit(10000000000000);
		$filter->columns('COUNT(*) AS cnt');
		
		$countRow = $filter->getQuery()->execute()->getFirst();

		$return = array();
		$return['columns'] = array_keys($displayedColumns);
		$return['totalCount'] = $countRow->cnt;
		$return['data'] = $data;
		$return['options'] = $this->getGridOptions();

		echo json_encode($return);
	}

	protected function recordSetArrayToListData($productArray, $displayedColumns, $exportBufferIndex=true)
	{
		$data = array();

		foreach ($this->processDataArray($productArray, $displayedColumns) as $row)
		{
			$data = array_merge($data, $this->getPreparedRecord($row, $displayedColumns));

			// avoid possible memory leaks due to circular references (http://bugs.php.net/bug.php?id=33595)
			// only do this for CSV export
			// @todo: if necessary, move to ActiveRecordModel::unsetArray($row);
			/*
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
			*/
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
			
			$record[$this->getJsColumn($column)] = $this->formatValue($value, $type);
		}

		return array($record);
	}

	protected function getListFilter()
	{
		$filter = $this->getSelectFilter();
		new ActiveGrid($this->getDI(), $filter, $this->getClassName(), $this->getHavingClauseColumnTypes() );
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

	public function massAction()
	{
		$filter = $this->getListFilter();
		if ($ids = $this->request->getJson('ids'))
		{
			$func = $this->request->getJson('allSelected') ? 'notInWhere' : 'inWhere';
			$filter->$func('ID', $ids);
		}
		
		$records = $filter->getQuery()->execute();
		
		$action = $this->request->getJson('action');
		foreach ($records as $record)
		{
			switch ($action)
			{
				case 'delete':
					$record->delete();
				break;
			}
		}
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
//		$displayedColumns = $this->getSessionData('columns');
		$displayedColumns = null;

		if (!$displayedColumns)
		{
			$displayedColumns = $this->user->getPreference('columns_' . get_class($this));
		}

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

	protected function getSchemaColumns($schemaName, $customColumns = array())
	{
		$model = new $schemaName();
		$metaData = new Phalcon\Mvc\Model\MetaData\Memory();
		$attributes = $metaData->getDataTypes($model);

		$availableColumns = array();
		foreach ($attributes as $field => $dataType)
		{
			switch ($dataType)
			{
				case 0: 
					$type = 'numeric';
				break;
				
				case 2: 
				case 5: 
				case 6: 
				default:
					$type = 'text';
				break;
			}

			$availableColumns[$schemaName . '.' . $field] = $type;
		}

		$availableColumns = array_merge($availableColumns, $customColumns);

		foreach ($availableColumns as $column => $type)
		{
			$availableColumns[$column] = array('name' => $this->application->translate($column), 'type' => $type);
		}

		/*
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
		*/
		
		return $availableColumns;
	}

	public function getHavingClauseColumnTypes()
	{
		return array();
	}

	public function getAvailableColumns($schemaName = null)
	{
		$schemaName = $schemaName ? $schemaName : $this->getClassName();
		
		$availableColumns = $this->getSchemaColumns($schemaName, $this->getCustomColumns());

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
	
	protected function getJsColumn($fieldName)
	{
		$jsColumn = str_replace('.', '_', $fieldName);
		if ($p = strpos($jsColumn, '\\'))
		{	
			$jsColumn = substr($jsColumn, $p + 1);
		}
		
		return $jsColumn;
	}

	protected function getGridOptions()
	{
		$options = array();

		$displayedColumns = $this->getRequestColumns();
		foreach ($this->getAvailableRequestColumns() as $field => $column)
		{
			if (empty($displayedColumns[$field]))
			{
				//continue;
			}

			if (substr($field, -3) == '.ID')
			{
				$options['primaryKey'] = $this->getJsColumn($field);
			}

			$options['columnDefs'][] = array(
				'field' => $this->getJsColumn($field),
				'displayName' => $column['name'],
				'visible' => !empty($displayedColumns[$field]) && (substr($field, -3) != '.ID')
				);
		}

		return $options;

		var_dump($displayedColumns, $availableColumns);

		return;

		// sort available columns by display state (displayed columns first)
		$displayedAvailable = array_intersect_key($availableColumns, $displayedColumns);
		$notDisplayedAvailable = array_diff_key($availableColumns, $displayedColumns);
		$availableColumns = array_merge($displayedAvailable, $notDisplayedAvailable);

		$this->set('displayedColumns', $displayedColumns);
		$this->set('availableColumns', $availableColumns);
		$this->set('columnWidths', $this->user->getPreference('columnWidth_' . get_class($this)));

		$this->set('massForm', $this->getMassForm());
		$this->set('totalCount', '0');
		$this->set('filters', $this->request->get('filters'));
		$this->set('data', $this->lists(false, $displayedColumns)->getData());

	}

	protected function setGridResponse(ActionResponse $response = null)
	{
		if (empty($response))
		{
			$jsonResponse = true;

		}

		$displayedColumns = $this->getRequestColumns();
		$availableColumns = $this->getAvailableRequestColumns();

		// sort available columns by display state (displayed columns first)
		$displayedAvailable = array_intersect_key($availableColumns, $displayedColumns);
		$notDisplayedAvailable = array_diff_key($availableColumns, $displayedColumns);
		$availableColumns = array_merge($displayedAvailable, $notDisplayedAvailable);

		$this->set('displayedColumns', $displayedColumns);
		$this->set('availableColumns', $availableColumns);
		$this->set('advancedSearchColumns', $this->getAdvancedSearchFields());
		$this->set('columnWidths', $this->user->getPreference('columnWidth_' . get_class($this)));

		$this->set('massForm', $this->getMassForm());
		$this->set('offset', $this->request->get('offset'));
		$this->set('totalCount', '0');
		$this->set('filters', $this->request->get('filters'));
		$this->set('data', $this->lists(false, $displayedColumns)->getData());

		if (isset($jsonResponse))
		{
			return $response->getData();
		}

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
		if ($record instanceof Phalcon\Mvc\Model\Row)
		{
			$record = $record[$class];
		}

		if (isset($record->$field))
		{
			return $record->$field;
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
		$f = $this->modelsManager->createBuilder()->from($this->getClassName());

		/*
		// specField columns
		if ($this->isEav())
		{
			$needsJoin = true;
			$fields = EavFieldManager::getClassFieldSet($this->getClassName());

			foreach ($fields as $field)
			{
				if (!$field->isMultiValue)
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
		*/

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

	protected function setDefaultSortorderBy(Builder $filter)
	{
		$filter->orderBy($this->getClassName() . '.ID DESC');
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
		return 'MassActionProcessor';
	}

	public function isMassCancelledAction()
	{
		$isCancelled = call_user_func_array(array($this->getMassActionProcessor(), 'isCancelled'), array($this->request->get('pid')));
		return new JSONResponse(array('isCancelled' => $isCancelled));
	}

	public function changeColumnsAction()
	{
		$columns = array_keys($this->request->get('col', array()));
		$this->setSessionData('columns', $columns);
		$this->user->setPreference('columns_' . get_class($this), $columns);
		$this->user->save();
	}

	public function sortColumnsAction()
	{
		$columns = json_decode($this->request->get('columns'));
		$this->setSessionData('columns', $columns);
		$this->user->setPreference('columns_' . get_class($this), $columns);
		$this->user->save();
	}

	public function saveColumnWidthAction()
	{
		$columns = json_decode($this->request->get('width'));
		$this->setSessionData('columnWidth', $columns);
		$this->user->setPreference('columnWidth_' . get_class($this), $columns);
		$this->user->save();
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
}
?>
