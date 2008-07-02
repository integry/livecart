<?php

/**
 * Product specification wrapper class. Loads/modifies product specification data.
 *
 * This class usually should not be used directly as most of the attribute manipulations
 * can be done with Product class itself.
 *
 * @package application.model.eav
 * @author Integry Systems <http://integry.com>
 */
abstract class EavSpecificationManagerCommon
{
	/**
	 * Owner object instance
	 *
	 * @var ActiveRecordModel
	 */
	protected $owner = null;

	protected $attributes = array();

	protected $removedAttributes = array();

	public abstract function getFieldClass();

	public abstract function getSpecificationFieldSet($loadReferencedRecords = false);

	public function __construct(ActiveRecordModel $owner, $specificationDataArray = null)
	{
		$this->owner = $owner;

		if (is_null($specificationDataArray) && $owner->getID())
		{
			$specificationDataArray = self::fetchRawSpecificationData(get_class($this), array($owner->getID()), true);

			$groupClass = $this->getFieldClass() . 'Group';
			$groupIDColumn = strtolower(substr($groupClass, 0, 1)) . substr($groupClass, 1) . 'ID';

			// preload attribute groups
			$groups = array();
			foreach ($specificationDataArray as $spec)
			{
				if ($spec[$groupIDColumn])
				{
					$groups[$spec[$groupIDColumn]] = true;
				}
			}
			$groups = array_keys($groups);

			ActiveRecordModel::getInstanceArray($groupClass, $groups);
		}

		$this->loadSpecificationData($specificationDataArray);
	}

	public function getGroupClass()
	{
		return $this->getFieldClass() . 'Group';
	}

	/**
	 * Sets specification attribute value by mapping product, specification field, and
	 * assigned value to one record (atomic item)
	 *
	 * @param iEavSpecification $specification Specification item value
	 */
	public function setAttribute(iEavSpecification $newSpecification)
	{
		$specField = $newSpecification->getFieldInstance();
		$itemClass = $specField->getSelectValueClass();

		if(
			$this->owner->isExistingRecord()
			&& isset($this->attributes[$newSpecification->getFieldInstance()->getID()])
			&& ($itemClass == $specField->getSpecificationFieldClass()
			&& $newSpecification->getValue()->isModified())
		)
		{
			// Delete old value
			ActiveRecord::deleteByID($itemClass, $this->attributes[$specField->getID()]->getID());

			// And create new
			$this->attributes[$specField->getID()] = call_user_func_array(array($itemClass, 'getNewInstance'), array($this->owner, $specField, $newSpecification->getValue()->get()));
		}
		else
		{
			$this->attributes[$specField->getID()] = $newSpecification;
		}

		unset($this->removedAttributes[$specField->getID()]);
	}

	/**
	 * Removes persisted product specification property
	 *
	 *	@param SpecField $field SpecField instance
	 */
	public function removeAttribute(EavFieldCommon $field)
	{
		if (isset($this->attributes[$field->getID()]))
		{
			$this->removedAttributes[$field->getID()] = $this->attributes[$field->getID()];
		}

		unset($this->attributes[$field->getID()]);
	}

	public function removeAttributeValue(EavFieldCommon $field, EavValueCommon $value)
	{
		if (!$field->isSelector())
	  	{
			throw new Exception('Cannot remove a value from non selector type specification field');
		}

		if (!isset($this->attributes[$field->getID()]))
		{
		  	return false;
		}

		if ($field->isMultiValue->get())
		{
			$this->attributes[$field->getID()]->removeValue($value);
		}
		else
		{
			// no changes should be made until the save() function is called
			$this->attributes[$field->getID()]->delete();
		}
	}

	public function isAttributeSet(EavFieldCommon $field)
	{
		return isset($this->attributes[$field->getID()]);
	}

	/**
	 *	Get attribute instance for the particular SpecField.
	 *
	 *	If it is a single value selector a SpecFieldValue instance needs to be passed as well
	 *
	 *	@param SpecField $field SpecField instance
	 *	@param SpecFieldValue $defaultValue SpecFieldValue instance (or nothing if SpecField is not selector)
	 *
	 * @return Specification
	 */
	public function getAttribute(EavFieldCommon $field, $defaultValue = null)
	{
		if (!$this->isAttributeSet($field))
		{
		  	$params = array($this->owner, $field, $defaultValue);
			$this->attributes[$field->getID()] = call_user_func_array(array($field->getSpecificationFieldClass(), 'getNewInstance'), $params);
		}

		return $this->attributes[$field->getID()];
	}

	/**
	 * Sets specification attribute value
	 *
	 * @param SpecField $field Specification field instance
	 * @param mixed $value Attribute value
	 */
	public function setAttributeValue(EavFieldCommon $field, $value)
	{
		if (!is_null($value))
		{
			$specification = $this->getAttribute($field, $value);
			$specification->set($value);

			$this->setAttribute($specification);
		}
		else
		{
			$this->removeAttribute($field);
		}
	}

	/**
	 * Sets specification String attribute value by language
	 *
	 * @param SpecField $field Specification field instance
	 * @param unknown $value Attribute value
	 */
	public function setAttributeValueByLang(EavFieldCommon $field, $langCode, $value)
	{
		$specification = $this->getAttribute($field);
		$specification->setValueByLang($langCode, $value);
		$this->setAttribute($specification);
	}

	public function save()
	{
		foreach ($this->removedAttributes as $attribute)
		{
		  	$attribute->delete();
		}
		$this->removedAttributes = array();

		foreach ($this->attributes as $attribute)
		{
			$attribute->save();
		}
	}

	public function toArray()
	{
		$arr = array();
		foreach ($this->attributes as $id => $attribute)
		{
			$arr[$id] = $attribute->toArray();
		}

		uasort($arr, array($this, 'sortAttributeArray'));

		return $arr;
	}

	private function sortAttributeArray($a, $b)
	{
		$field = $this->getFieldClass();
		$fieldGroup = $field . 'Group';

		if (!isset($a[$field][$fieldGroup]['position']))
		{
			$a[$field][$fieldGroup]['position'] = -1;
		}

		if (!isset($b[$field][$fieldGroup]['position']))
		{
			$b[$field][$fieldGroup]['position'] = -1;
		}

		if (($a[$field][$fieldGroup]['position'] == $b[$field][$fieldGroup]['position']))
		{
			return ($a[$field]['position'] < $b[$field]['position']) ? -1 : 1;
		}

		return ($a[$field][$fieldGroup]['position'] < $b[$field][$fieldGroup]['position']) ? -1 : 1;
	}

	public function loadRequestData(Request $request)
	{
		$fields = $this->getSpecificationFieldSet();
		$application = ActiveRecordModel::getApplication();

		// create new select values
		if ($request->isValueSet('other'))
		{
			foreach ($request->get('other') as $fieldID => $values)
			{
				$field = call_user_func_array(array($this->getFieldClass(), 'getInstanceByID'), array($fieldID, ActiveRecordModel::LOAD_DATA));

				if (is_array($values))
				{
					// multiple select
					foreach ($values as $value)
					{
						if ($value)
						{
							$fieldValue = $field->getNewValueInstance();
							$fieldValue->setValueByLang('value', $application->getDefaultLanguageCode(), $value);
							$fieldValue->save();

							$request->set('specItem_' . $fieldValue->getID(), 'on');
						}
					}
				}
				else
				{
					// single select
					if ('other' == $request->get('specField_' . $fieldID))
					{
						$fieldValue = $field->getNewValueInstance();
						$fieldValue->setValueByLang('value', $application->getDefaultLanguageCode(), $values);
						$fieldValue->save();

						$request->set('specField_' . $fieldID, $fieldValue->getID());
					}
				}
			}
		}

		$languages = ActiveRecordModel::getApplication()->getLanguageArray(LiveCart::INCLUDE_DEFAULT);

		foreach ($fields as $field)
		{
			$fieldName = $field->getFormFieldName();

			if ($field->isSelector())
			{
				if (!$field->isMultiValue->get())
				{
					if ($request->isValueSet($fieldName) && !in_array($request->get($fieldName), array('other', '')))
				  	{
				  		$this->setAttributeValue($field, $field->getValueInstanceByID($request->get($fieldName), ActiveRecordModel::LOAD_DATA));
				  	}
				}
				else
				{
					$values = $field->getValuesSet();

					foreach ($values as $value)
					{
					  	if ($request->isValueSet($value->getFormFieldName()) || $request->isValueSet('checkbox_' . $value->getFormFieldName()))
					  	{
						  	if ($request->get($value->getFormFieldName()))
						  	{
								$this->setAttributeValue($field, $value);
							}
							else
							{
								$this->removeAttributeValue($field, $value);
							}
						}
					}
				}
			}
			else
			{
				if ($request->isValueSet($fieldName))
			  	{
			  		if ($field->isTextField())
					{
						foreach ($languages as $language)
						{
						  	if ($request->isValueSet($field->getFormFieldName($language)))
						  	{
								$this->setAttributeValueByLang($field, $language, $request->get($field->getFormFieldName($language)));
							}
						}
					}
					else
					{
						if (strlen($request->get($fieldName)))
						{
							$this->setAttributeValue($field, $request->get($fieldName));
						}
						else
						{
							$this->removeAttribute($field);
						}
					}
				}
			}
		}
	}

	public function getFormData()
	{
		$selectorTypes = EavFieldCommon::getSelectorValueTypes();
		$multiLingualTypes = SpecField::getMultilanguageTypes();
		$languageArray = ActiveRecordModel::getApplication()->getLanguageArray();
		$fieldClass = $this->getFieldClass();

		$formData = array();

		foreach($this->toArray() as $attr)
		{
			if(in_array($attr[$fieldClass]['type'], $selectorTypes))
			{
				if(1 == $attr[$fieldClass]['isMultiValue'])
				{
					foreach($attr['valueIDs'] as $valueID)
					{
						$formData['specItem_' . $valueID] = "on";
					}
				}
				else
				{
					$formData[$attr[$fieldClass]['fieldName']] = $attr['ID'];
				}
			}
			else if(in_array($attr[$fieldClass]['type'], $multiLingualTypes))
			{
				$formData[$attr[$fieldClass]['fieldName']] = $attr['value'];
				foreach($languageArray as $lang)
				{
					if (isset($attr['value_' . $lang]))
					{
						$formData[$attr[$fieldClass]['fieldName'] . '_' . $lang] = $attr['value_' . $lang];
					}
				}
			}
			else
			{
				$formData[$attr[$fieldClass]['fieldName']] = $attr['value'];
			}
		}

		return $formData;
	}

	public function setFormResponse(ActionResponse $response, Form $form)
	{
		$specFields = $this->owner->getSpecification()->getSpecificationFieldSet(ActiveRecordModel::LOAD_REFERENCES);
		$specFieldArray = $specFields->toArray();

		// set select values
		$selectors = EavFieldCommon::getSelectorValueTypes();
		foreach ($specFields as $key => $field)
		{
		  	if (in_array($field->type->get(), $selectors))
		  	{
				$values = $field->getValuesSet()->toArray();
				$specFieldArray[$key]['values'] = array('' => '');
				foreach ($values as $value)
				{
					$specFieldArray[$key]['values'][$value['ID']] = $value['value_lang'];
				}

				if (!$field->isMultiValue->get())
				{
					$specFieldArray[$key]['values']['other'] = ActiveRecordModel::getApplication()->translate('_enter_other');
				}
			}
		}

		// arrange SpecFields's into groups
		$specFieldsByGroup = array();
		$prevGroupID = -1;

		$groupClass = $this->getFieldClass() . 'Group';
		foreach ($specFieldArray as $field)
		{
			$groupID = isset($field[$groupClass]['ID']) ? $field[$groupClass]['ID'] : '';
			if((int)$groupID && $prevGroupID != $groupID)
			{
				$prevGroupID = $groupID;
			}

			$specFieldsByGroup[$groupID][] = $field;
		}

		// get multi language spec fields
		$multiLingualSpecFields = array();
		foreach ($specFields as $key => $field)
		{
		  	if ($field->isTextField())
		  	{
		  		$multiLingualSpecFields[] = $field->toArray();
			}
		}

		$response->set("groupClass", $groupClass);
		$response->set("specFieldList", $specFieldsByGroup);
		$response->set("multiLingualSpecFieldss", $multiLingualSpecFields);

		$form->setData($this->getFormData());

		//$this->setFormValidator($form->getValidator());
	}

	public function setValidation(RequestValidator $validator)
	{
		$specFields = $this->getSpecificationFieldSet(ActiveRecordModel::LOAD_REFERENCES);

		$application = ActiveRecordModel::getApplication();

		foreach ($specFields as $key => $field)
		{
			$fieldname = $field->getFormFieldName();

		  	// validate numeric values
			if (EavFieldCommon::TYPE_NUMBERS_SIMPLE == $field->type->get())
		  	{
				$validator->addCheck($fieldname, new IsNumericCheck($application->translate('_err_numeric')));
				$validator->addFilter($fieldname, new NumericFilter());
			}

		  	// validate required fields
			if ($field->isRequired->get())
		  	{
				if (!($field->isSelector() && $field->isMultiValue->get()))
				{
					$validator->addCheck($fieldname, new IsNotEmptyCheck($application->translate('_err_specfield_required')));
				}
				else
				{
					ClassLoader::import('application.helper.check.SpecFieldIsValueSelectedCheck');
					$validator->addCheck($fieldname, new SpecFieldIsValueSelectedCheck($application->translate('_err_specfield_multivaluerequired'), $field, $application->getRequest()));
				}
			}
		}
	}

	public static function loadSpecificationForRecordArray($class, &$productArray)
	{
		$array = array(&$productArray);
		self::loadSpecificationForRecordSetArray($class, $array, true);

		$fieldClass = call_user_func(array($class, 'getFieldClass'));
		$groupClass = $fieldClass . 'Group';
		$groupIDColumn = strtolower(substr($groupClass, 0, 1)) . substr($groupClass, 1) . 'ID';

		$groupIds = array();
		foreach ($productArray['attributes'] as $attr)
		{
			$groupIds[isset($attr[$fieldClass][$groupIDColumn]) ? $attr[$fieldClass][$groupIDColumn] : 'NULL'] = true;
		}

		$f = new ARSelectFilter(new INCond(new ARFieldHandle($groupClass, 'ID'), array_keys($groupIds)));
		$indexedGroups = array();
		$res = ActiveRecordModel::getRecordSetArray($groupClass, $f);
		foreach ($res as $group)
		{
			$indexedGroups[$group['ID']] = $group;
		}

		foreach ($productArray['attributes'] as &$attr)
		{
			if (isset($attr[$fieldClass][$groupIDColumn]))
			{
				$attr[$fieldClass][$groupClass] = $indexedGroups[$attr[$fieldClass][$groupIDColumn]];
			}
		}
	}

	/**
	 * Load product specification data for a whole array of products at once
	 */
	public static function loadSpecificationForRecordSetArray($class, &$productArray, $fullSpecification = false)
	{
		$ids = array();
		foreach ($productArray as $key => $product)
	  	{
			$ids[$product['ID']] = $key;
		}

		$fieldClass = call_user_func(array($class, 'getFieldClass'));
		$stringClass = call_user_func(array($fieldClass, 'getStringValueClass'));
		$fieldColumn = call_user_func(array($fieldClass, 'getFieldIDColumnName'));
		$objectColumn = call_user_func(array($fieldClass, 'getObjectIDColumnName'));
		$valueItemClass = call_user_func(array($fieldClass, 'getSelectValueClass'));
		$valueColumn = call_user_func(array($valueItemClass, 'getValueIDColumnName'));

		$specificationArray = self::fetchSpecificationData($class, array_flip($ids), $fullSpecification);

		$specFieldSchema = ActiveRecordModel::getSchemaInstance($fieldClass);
		$specStringSchema = ActiveRecordModel::getSchemaInstance($stringClass);
		$specFieldColumns = array_keys($specFieldSchema->getFieldList());

		foreach ($specificationArray as &$spec)
		{
			if ($spec['isMultiValue'])
			{
				$value['value'] = $spec['value'];
				$value = MultiLingualObject::transformArray($value, $specStringSchema);

				if (isset($productArray[$ids[$spec[$objectColumn]]]['attributes'][$spec[$fieldColumn]]))
				{
					$sp =& $productArray[$ids[$spec[$objectColumn]]]['attributes'][$spec[$fieldColumn]];
					//$sp['valueIDs'][] = $spec[$valueColumn];
					$sp['valueIDs'][] = $spec['valueID'];
					$sp['values'][] = $value;
					continue;
				}
			}

			foreach ($specFieldColumns as $key)
			{
				$spec[$fieldClass][$key] = $spec[$key];
				unset($spec[$key]);
			}

			// transform for presentation
			$spec[$fieldClass] = MultiLingualObject::transformArray($spec[$fieldClass], $specFieldSchema);

			if ($spec[$fieldClass]['isMultiValue'])
			{
				//var_dump($spec);
				//$spec['valueIDs'] = array($spec[$valueColumn]);
				$spec['valueIDs'] = array($spec['valueID']);
				$spec['values'] = array($value);
			}
			else
			{
				$spec = MultiLingualObject::transformArray($spec, $specStringSchema);
			}

			if ((!empty($spec['value']) || !empty($spec['values']) || !empty($spec['value_lang'])))
			{
				// append to product array
				$productArray[$ids[$spec[$objectColumn]]]['attributes'][$spec[$fieldColumn]] = $spec;
				Product::sortAttributesByHandle($productArray[$ids[$spec[$objectColumn]]]);
			}
		}
	}

	private static function fetchRawSpecificationData($class, $objectIDs, $fullSpecification = false)
	{
		if (!$objectIDs)
		{
			return array();
		}

		$fieldClass = call_user_func(array($class, 'getFieldClass'));
		$groupClass = $fieldClass . 'Group';
		$fieldColumn = call_user_func(array($fieldClass, 'getFieldIDColumnName'));
		$objectColumn = call_user_func(array($fieldClass, 'getObjectIDColumnName'));
		$stringClass = call_user_func(array($fieldClass, 'getStringValueClass'));
		$numericClass = call_user_func(array($fieldClass, 'getNumericValueClass'));
		$dateClass = call_user_func(array($fieldClass, 'getDateValueClass'));
		$valueItemClass = call_user_func(array($fieldClass, 'getSelectValueClass'));
		$valueClass = call_user_func(array($valueItemClass, 'getValueClass'));
		$valueColumn = call_user_func(array($valueItemClass, 'getValueIDColumnName'));
		$groupColumn = strtolower(substr($groupClass, 0, 1)) . substr($groupClass, 1) . 'ID';

		$cond = '
		LEFT JOIN
			' . $fieldClass . ' ON ' . $fieldColumn . ' = ' . $fieldClass . '.ID
		LEFT JOIN
			' . $groupClass . ' ON ' . $fieldClass . '.' . $groupColumn . ' = ' . $groupClass . '.ID
		WHERE
			' . $objectColumn . ' IN (' . implode(', ', $objectIDs) . ')' . ($fullSpecification ? '' : ' AND ' . $fieldClass . '.isDisplayedInList = 1');

		$query = '
		SELECT ' . $dateClass . '.*, NULL AS valueID, NULL AS specFieldValuePosition, ' . $groupClass . '.position AS SpecFieldGroupPosition, ' . $fieldClass . '.* as valueID FROM ' . $dateClass . ' ' . $cond . '
		UNION
		SELECT ' . $stringClass . '.*, NULL, NULL AS specFieldValuePosition, ' . $groupClass . '.position, ' . $fieldClass . '.* as valueID FROM ' . $stringClass . ' ' . $cond . '
		UNION
		SELECT ' . $numericClass . '.*, NULL, NULL AS specFieldValuePosition, ' . $groupClass . '.position, ' . $fieldClass . '.* as valueID FROM ' . $numericClass . ' ' . $cond . '
		UNION
		SELECT ' . $valueItemClass . '.' . $objectColumn . ', ' . $valueItemClass . '.' . $fieldColumn . ', ' . $valueClass . '.value, ' . $valueClass . '.ID, ' . $valueClass . '.position, ' . $groupClass . '.position, ' . $fieldClass . '.*
				 FROM ' . $valueItemClass . '
				 	LEFT JOIN ' . $valueClass . ' ON ' . $valueItemClass . '.' . $valueColumn . ' = ' . $valueClass . '.ID
				 ' . str_replace('ON ' . $fieldColumn, 'ON ' . $valueItemClass . '.' . $fieldColumn, $cond) .
				 ' ORDER BY ' . $objectColumn . ', SpecFieldGroupPosition, position, specFieldValuePosition';

		return ActiveRecordModel::getDataBySQL($query);
	}

	protected static function fetchSpecificationData($class, $objectIDs, $fullSpecification = false)
	{
		if (!$objectIDs)
		{
			return array();
		}

		$specificationArray = self::fetchRawSpecificationData($class, $objectIDs, $fullSpecification);

		$multiLingualFields = array('name', 'description', 'valuePrefix', 'valueSuffix');

		foreach ($specificationArray as &$spec)
		{
			// unserialize language field values
			foreach ($multiLingualFields as $value)
			{
				$spec[$value] = unserialize($spec[$value]);
			}

			if ((EavFieldCommon::DATATYPE_TEXT == $spec['dataType'] && EavFieldCommon::TYPE_TEXT_DATE != $spec['type'])
				|| (EavFieldCommon::TYPE_NUMBERS_SELECTOR == $spec['type']))
			{
				$spec['value'] = unserialize($spec['value']);
			}
		}

		return $specificationArray;
	}

	protected function loadSpecificationData($specificationDataArray)
	{
		if (!is_array($specificationDataArray))
		{
			$specificationDataArray = array();
		}

		// get value class and field names
		$fieldClass = $this->getFieldClass();
		$fieldColumn = call_user_func(array($fieldClass, 'getFieldIDColumnName'));
		$valueItemClass = call_user_func(array($fieldClass, 'getSelectValueClass'));
		$valueClass = call_user_func(array($valueItemClass, 'getValueClass'));
		$multiValueItemClass = call_user_func(array($fieldClass, 'getMultiSelectValueClass'));

		// preload all specFields from database
		$specFieldIds = array();

		$selectors = array();
		$simpleValues = array();
		foreach ($specificationDataArray as $value)
		{
		  	$specFieldIds[$value[$fieldColumn]] = $value[$fieldColumn];
		  	if ($value['valueID'])
		  	{
		  		$selectors[$value[$fieldColumn]][$value['valueID']] = $value;
			}
			else
			{
				$simpleValues[$value[$fieldColumn]] = $value;
			}
		}

		$specFields = ActiveRecordModel::getInstanceArray($fieldClass, $specFieldIds);

		// simple values
		foreach ($simpleValues as $value)
		{
		  	$specField = $specFields[$value[$fieldColumn]];

		  	$class = $specField->getValueTableName();

			$specification = call_user_func_array(array($class, 'restoreInstance'), array($this->owner, $specField, $value['value']));
		  	$this->attributes[$specField->getID()] = $specification;
		}

		// selectors
		foreach ($selectors as $specFieldId => $value)
		{
			$specField = $specFields[$specFieldId];
		  	if ($specField->isMultiValue->get())
		  	{
				$values = array();
				foreach ($value as $val)
				{
					$values[$val['valueID']] = $val['value'];
				}

				$specification = call_user_func_array(array($multiValueItemClass, 'restoreInstance'), array($this->owner, $specField, $values));
			}
			else
			{
			  	$value = array_pop($value);
				$specFieldValue = call_user_func_array(array($valueClass, 'restoreInstance'), array($specField, $value['valueID'], $value['value']));
				$specification = call_user_func_array(array($valueItemClass, 'restoreInstance'), array($this->owner, $specField, $specFieldValue));
			}

		  	$this->attributes[$specField->getID()] = $specification;
		}
	}

	public function __destruct()
	{
		foreach ($this->attributes as $k => $attr)
		{
			$this->attributes[$k]->__destruct();
			unset($this->attributes[$k]);
		}

		foreach ($this->removedAttributes as $k => $attr)
		{
			$this->removedAttributes[$k]->__destruct();
			unset($this->removedAttributes[$k]);
		}

		unset($this->owner);
	}
}

?>