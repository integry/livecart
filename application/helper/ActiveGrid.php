<?php

ClassLoader::importNow("application.helper.getDateFromString");
ClassLoader::importNow("application.model.eav.EavField");
ClassLoader::importNow("application.model.category.SpecField");

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
	private $columnTypes;

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

	public function __construct(LiveCart $application, ARSelectFilter $filter, $modelClass = false, $columnTypes=array())
	{
		$this->application = $application;
		$this->modelClass = $modelClass;
		$this->filter = $filter;
		$this->columnTypes = $columnTypes;

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

			if ($handle instanceof EavFieldCommon)
			{
				continue;
			}
			else if (!is_array($handle) && !is_null($handle) && !($handle instanceof ARExpressionHandle))
			{
				$fieldInst = $this->getFieldInstance($field);

				if ($fieldInst && (($fieldInst->getDataType() instanceof ARNumeric) || $handle->getField()->getDataType() instanceof ARNumeric))
				{
					$value = preg_replace('/[ ]{2,}/', ' ', $value);

					$constraints = ($fieldInst->getDataType() instanceof ARNumeric) ? explode(' ', $value) : array($value);

					foreach ($constraints as $c)
					{
						list($operator, $value) = $this->parseOperatorAndValue($c);
						if (!is_numeric($value) && ($fieldInst->getDataType() instanceof ARNumeric))
						{
							continue;
						}

						$conds[] = new OperatorCond($handle, $value, $operator);
					}
				}
				else if ($fieldInst && ($fieldInst->getDataType() instanceof ARPeriod))
				{
					if(substr($value, 0, 10) == 'daterange ')
					{
						$value = str_replace('daterange ', '', $value);
						list($from, $to) = explode('|', $value);
						$from = trim($from);
						$to = trim($to);
						// convert
						// 2010-9-1 to 2010-09-01 ( +first or last minute of day)
						// unset dates to 'inf' (meaning ingnore condition)
						if ($from == '')
						{
							$from = 'inf';
						}
						else
						{
							list($y, $m, $d) = explode('-', $from);
							$from = $y.'-'.str_pad($m, 2 ,'0', STR_PAD_LEFT).'-'.str_pad($d, 2 ,'0', STR_PAD_LEFT).' 00:00:00';
						}
						if ($to == '')
						{
							$to  = 'inf';
						}
						else
						{
							list($y, $m, $d) = explode('-', $to);
							$to = $y.'-'.str_pad($m, 2 ,'0',STR_PAD_LEFT).'-'.str_pad($d, 2 ,'0',STR_PAD_LEFT). ' 23:59:59';
						}
					}
					else
					{
						list($from, $to) = explode(' | ', $value);
					}
					$cond = null;
					// from condition
					if ('inf' != $from)
					{
						$cond = new EqualsOrMoreCond($handle, getDateFromString($from));
					}

					// to condition
					if ('now' != $to && 'inf' != $to)
					{
						$condTo = new EqualsOrLessCond($handle, getDateFromString($to));
						if ($cond)
						{
							$cond->addAnd($condTo);
						}
						else
						{
							$cond = $condTo;
						}
					}

					if ($cond)
					{
						$conds[] = $cond;
					}
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
			else
			{
				if (array_key_exists($field, $this->columnTypes))
				{
					$type = $this->columnTypes[$field]['type'];
				}
				else
				{
					$type = null;
				}

				$value = preg_replace('/[ ]{2,}/', ' ', $value);
				switch($type)
				{
					case 'numeric':
						$constraints = explode(' ', $value);
						foreach ($constraints as $c)
						{
							list($operator, $value) = $this->parseOperatorAndValue($c);
							if (!is_numeric($value))
							{
								continue;
							}
							$having[] = new OperatorCond($handle, $value, $operator);
						}
						break;
					default:
						$having[] = eq(new ARExpressionHandle($field), $value);
				}
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

		if (!empty($having))
		{
			$filter->setHavingCondition(new AndChainCondition($having));
		}
	}

	private function parseOperatorAndValue($c)
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
		return array($operator, $value);
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

		if ($this->getEavTableAlias() == $schemaName)
		{
			$eavField = call_user_func_array(array($this->getEavFieldClass(), 'getInstanceByID'), array($fieldName, true));

			if (!$eavField->isMultiValue->get())
			{
				$schemaName = $eavField->getValueTableName();
			}
			else
			{
				// intentionally return wrong schema for multi-select values as they cannot be sorted or searched
				$schemaName = $this->getEavFieldClass();
			}
		}

		$possibleSchemas = ActiveRecordModel::getSchemaInstance($this->modelClass)->getDirectlyReferencedSchemas();
		if (isset($possibleSchemas[$schemaName]))
		{
			$schema = $possibleSchemas[$schemaName];
		}
		else
		{
			foreach ((array)$possibleSchemas as $name => $schemaArray)
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
		elseif ('SpecificationItem' == $schema->getName())
		{
			$schema = ActiveRecordModel::getSchemaInstance('SpecFieldValue');
		}
		return $schema;
	}

	private function getFieldInstance($field)
	{
		// check if expression
		if (!strpos($field, '.'))
		{
			return null;
		}

		list($schemaName, $fieldName) = explode('.', $field);

		if ($this->getEavTableAlias() == $schemaName)
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
		// aliases
		if (!strpos($field, '.'))
		{
			return f($field);
		}

		list($schemaName, $fieldName) = explode('.', $field);

		if ($this->getEavTableAlias() == $schemaName)
		{
			$fieldID = $fieldName;
			$fieldName = 'value';

			$specField = call_user_func(array($this->getEavFieldClass(), 'getInstanceByID'), $fieldID);
			if ($specField->isSelector())
			{
				if ($specField->isMultiValue->get())
				{
					return $specField;
				}
				else
				{
					if (self::FILTER_HANDLE == $handleType)
					{
						$fieldName = 'ID';
					}
				}
			}
		}

		$handle = null;
		if ($schemaName)
		{
			$schema = $this->getSchemaInstance($field);

			if ($field = $schema->getField($fieldName))
			{
				$handle = $schema->getHandle($fieldName);

				if ($this->getEavTableAlias() == $schemaName)
				{
					$handle->setTable('specField_' . $fieldID . ($schema->getName() == $this->getEavValueClass() ? '_value' : ''));
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

	private function getEavFieldClass()
	{
		return $this->modelClass == 'Product' ? 'SpecField' : 'EavField';
	}

	private function getEavValueClass()
	{
		return $this->modelClass == 'Product' ? 'SpecFieldValue' : 'EavValue';
	}

	private function getEavTableAlias()
	{
		return $this->modelClass == 'Product' ? 'specField' : 'eavField';
	}
}

?>
