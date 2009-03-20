<?php

ClassLoader::importNow("application.helper.getDateFromString");
ClassLoader::importNow("application.model.eav.EavField");

/**
 * @package application.helper
 * @author Integry Systems
 */
class ActiveGrid
{
	const SORT_HANDLE =   0;
	const FILTER_HANDLE = 1;

	private $filter;
	private $application;
	private $modelClass;

	public static function getFieldType(ARField $field)
	{
		$fieldType = $field->getDataType();

		if ($field instanceof ARForeignKeyField || $field instanceof ARPrimaryKeyField)
		{
		  	return null;
		}

		if ($fieldType instanceof ARBool)
		{
		  	$type = 'bool';
		}
		elseif ($fieldType instanceof ARNumeric)
		{
			$type = 'numeric';
		}
		elseif ($fieldType instanceof ARPeriod)
		{
			$type = 'date';
		}
		else
		{
		  	$type = 'text';
		}

		return $type;
	}

	public function __construct(LiveCart $application, ARSelectFilter $filter, $modelClass = false)
	{
		$this->application = $application;
		$this->modelClass = $modelClass;
		$this->filter = $filter;
		$request = $this->application->getRequest();

		// set recordset boundaries (limits)
		$filter->setLimit($request->get('page_size', 50), $request->get('offset', 0));

		// set order
		if ($request->isValueSet('sort_col'))
		{
			$handle = $this->getFieldHandle($request->get('sort_col'), self::SORT_HANDLE);

			if ($handle)
			{
				$filter->setOrder($handle, $request->get('sort_dir'));
			}
		}

		// apply filters
		$filters = $request->get('filters');
		if (!is_array($filters))
		{
			$filters = (array)json_decode($request->get('filters'));
		}

		$conds = array();
		if ($filter->getCondition())
		{
			$conds[] = $filter->getCondition();
		}

		foreach ($filters as $field => $value)
		{
			if (!strlen($value))
			{
				continue;
			}

			$value = urldecode($value);

			$handle = $this->getFieldHandle($field, self::FILTER_HANDLE);

			if (!is_array($handle) && !is_null($handle))
			{
				$fieldInst = $this->getFieldInstance($field);

				if ($fieldInst && ($fieldInst->getDataType() instanceof ARNumeric))
				{
					$value = preg_replace('/[ ]{2,}/', ' ', $value);

					$constraints = ($fieldInst->getDataType() instanceof ARNumeric) ? explode(' ', $value) : array($value);

					foreach ($constraints as $c)
					{
						if (in_array(substr($c, 0, 2), array('<>', '<=', '>=')))
						{
							$operator = substr($c, 0, 2);
							$value = substr($c, 2);
						}
						else if (in_array(substr($c, 0, 1), array('>', '<', '=')))
						{
							$operator = substr($c, 0, 1);
							$value = substr($c, 1);
						}
						else
						{
							$operator = '=';
							$value = $c;
						}

						if (!is_numeric($value) && ($fieldInst->getDataType() instanceof ARNumeric))
						{
							continue;
						}

						$conds[] = new OperatorCond($handle, $value, $operator);
					}
				}
				else if ($fieldInst->getDataType() instanceof ARPeriod)
				{
					list($from, $to) = explode(' | ', $value);

					$cond = new EqualsOrMoreCond($handle, getDateFromString($from));

					if ('now' != $to)
					{
						$cond->addAnd(new EqualsOrLessCond($handle, getDateFromString($to)));
					}

					$conds[] = $cond;
				}
				else
				{
					$conds[] = new LikeCond($handle, '%' . $value . '%');
				}
			}

			// language field filter
			else if (is_array($handle))
			{
				$cond = null;
				foreach ($handle as $h)
				{
					$c = new LikeCond($h, '%' . $value . '%');
					if (!$cond)
					{
						$cond = $c;
					}
					else
					{
						$cond->addOR($c);
					}
				}

				$conds[] = $cond;
			}
		}

		// apply IDs to filter
		if ($request->get('selectedIDs') || $request->get('isInverse'))
		{
			$selectedIDs = json_decode($request->get('selectedIDs'));
			if ($selectedIDs)
			{
				if ((bool)$request->get('isInverse'))
				{
					$idcond = new NotINCond(new ARFieldHandle($modelClass, 'ID'), $selectedIDs);
				}
				else
				{
					$idcond = new INCond(new ARFieldHandle($modelClass, 'ID'), $selectedIDs);
				}

				$conds[] = $idcond;
			}
			else
			{
				if (!(bool)$request->get('isInverse'))
				{
					$idcond = new EqualsCond(new ARExpressionHandle(1), 2);
					$conds[] = $idcond;
				}
			}
		}

		if ($conds)
		{
			$filter->setCondition(new AndChainCondition($conds));
		}
	}

	public function getModelClass()
	{
		return $this->modelClass;
	}

	public function getApplication()
	{
		return $this->application;
	}

	public function getFilter()
	{
		return $this->filter;
	}

	private function getSchemaInstance($fieldName)
	{
		list($schemaName, $fieldName) = explode('.', $fieldName);

		if ('eavField' == $schemaName)
		{
			$eavField = EavField::getInstanceByID($fieldName, true);

			if (!$eavField->isMultiValue->get())
			{
				$schemaName = $eavField->getValueTableName();
			}
			else
			{
				// intentionally return wrong schema for multi-select values as they cannot be sorted or searched
				$schemaName = 'EavField';
			}
		}

		$possibleSchemas = ActiveRecordModel::getSchemaInstance($this->modelClass)->getDirectlyReferencedSchemas();
		if (isset($possibleSchemas[$schemaName]))
		{
			$schema = $possibleSchemas[$schemaName];
		}
		else
		{
			foreach ($possibleSchemas as $name => $schemaArray)
			{
				$parts = explode('_', $name, 2);
				if (isset($parts[1]) && ($parts[1] == $schemaName))
				{
					$schema = $schemaArray[0];
					break;
				}
			}
		}

		if (!isset($schema) || !is_object($schema))
		{
			$schema = ActiveRecordModel::getSchemaInstance($schemaName);
		}

		if ('EavItem' == $schema->getName())
		{
			$schema = ActiveRecordModel::getSchemaInstance('EavValue');
		}

		return $schema;
	}

	private function getFieldInstance($field)
	{
		list($schemaName, $fieldName) = explode('.', $field);

		if ('eavField' == $schemaName)
		{
			$fieldName = 'value';
		}

		if ($schemaName)
		{
			return $this->getSchemaInstance($field)->getField($fieldName);
		}
	}

	private function getFieldHandle($field, $handleType)
	{
		list($schemaName, $fieldName) = explode('.', $field);

		if ('eavField' == $schemaName)
		{
			$fieldID = $fieldName;
			$fieldName = 'value';
		}

		$handle = null;
		if ($schemaName)
		{
			$schema = $this->getSchemaInstance($field);

			if ($field = $schema->getField($fieldName))
			{
				$handle = $schema->getHandle($fieldName);

				if ('eavField' == $schemaName)
				{
					$handle->setTable('specField_' . $fieldID . ($schema->getName() == 'EavValue' ? '_value' : ''));
				}

				// language fields
				if ($field->getDataType() instanceof ARArray)
				{
				  	if (self::SORT_HANDLE == $handleType)
				  	{
						$handle = MultiLingualObject::getLangOrderHandle($handle);
					}

					// filtering by language fields needs two conditions (filter by both current and default language)
					else
					{
						$handleres = array();
						$defLang = $this->application->getDefaultLanguageCode();
						$locale = $this->application->getLocaleCode();
						$handleres[] = MultiLingualObject::getLangSearchHandle($handle, $locale);
						if ($locale != $defLang)
						{
							$handleres[] = MultiLingualObject::getLangSearchHandle($handle, $defLang);
						}

						$handle = $handleres;
					}
				}
			}
		}
		else
		{
			$handle = new ARExpressionHandle($fieldName);
		}

		return $handle;
	}
}

?>