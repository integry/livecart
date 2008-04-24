<?php
ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.model.category.*");
ClassLoader::import("library.*");

/**
 * Category specification field ("extra field") controller
 *
 * @package application.controller.backend
 * @author Integry Systems
 * @role category
 */
class SpecFieldController extends StoreManagementController
{
	/**
	 * Configuration data
	 *
	 * @see self::getSpecFieldConfig
	 * @var array
	 */
	protected $specFieldConfig = array();

	/**
	 * Specification field index page
	 *
	 * @return ActionResponse
	 */
	public function index()
	{
		$response = new ActionResponse();

		$categoryID = (int)$this->request->get('id');
		$category = Category::getInstanceByID($categoryID);

		$defaultSpecFieldValues = array
		(
			'ID' => $categoryID.'_new',
			'name' => array(),
			'description' => array(),
			'handle' => '',
			'values' => Array(),
			'rootId' => 'specField_item_new_'.$categoryID.'_form',
			'type' => SpecField::TYPE_TEXT_SIMPLE,
			'dataType' => SpecField::DATATYPE_TEXT,
			'categoryID' => $categoryID,
			'isDisplayed' => true,
		);

		$response->set('categoryID', $categoryID);
		$response->set('configuration', $this->getSpecFieldConfig());
		$response->set('specFieldsList', $defaultSpecFieldValues);
		$response->set('defaultLangCode', $this->application->getDefaultLanguageCode());
		$response->set('specFieldsWithGroups', $category->getSpecFieldsWithGroupsArray());

		return $response;
	}

	/**
	 * Displays form for creating a new or editing existing one product group specification field
	 *
	 * @return ActionResponse
	 */
	public function item()
	{
		$response = new ActionResponse();
		$specFieldList = SpecField::getInstanceByID($this->request->get('id'), true, true)->toArray(false, false);

		foreach(SpecFieldValue::getRecordSetArray($specFieldList['ID']) as $value)
		{
		   $specFieldList['values'][$value['ID']] = $value;
		}

		$specFieldList['categoryID'] = $specFieldList['Category']['ID'];
		unset($specFieldList['Category']);
		unset($specFieldList['SpecFieldGroup']['Category']);

		return new JSONResponse($specFieldList);
	}

	/**
	 * @role update
	 */
	public function update()
	{
		if(SpecField::exists((int)$this->request->get('ID')))
		{
			$specField = SpecField::getInstanceByID((int)$this->request->get('ID'));
		}
		else
		{
			return new JSONResponse(array(
					'errors' => array('ID' => $this->translate('_error_record_id_is_not_valid')),
					'ID' => (int)$this->request->get('ID')
				)/*,
				'failure',
				$this->translate('_could_not_save_attribute') */
			);
		}

		return $this->save($specField);
	}

	/**
	 * @role update
	 */
	public function create()
	{
		$specField = SpecField::getNewInstance(Category::getInstanceByID($this->request->get('categoryID', false)));

		return $this->save($specField);
	}

	/**
	 * Creates a new or modifies an exisitng specification field (according to a passed parameters)
	 *
	 * @return JSONResponse Returns success status or failure status with array of erros
	 */
	private function save(SpecField $specField)
	{
		$this->getSpecFieldConfig();
		$errors = $this->validate($this->request->getValueArray(array('handle', 'values', 'name_' . $this->specFieldConfig['languageCodes'][0], 'type', 'dataType', 'categoryID', 'ID')), $this->specFieldConfig['languageCodes']);

		if(!$errors)
		{
			$type = $this->request->get('advancedText') ? SpecField::TYPE_TEXT_ADVANCED : (int)$this->request->get('type');
			$dataType = SpecField::getDataTypeFromType($type);
			$categoryID = (int)$this->request->get('categoryID');

			$handle = $this->request->get('handle');
			$values = $this->request->get('values');

			$isMultiValue = $this->request->get('multipleSelector') == 1 ? 1 : 0;
			$isRequired = $this->request->get('isRequired') == 1 ? 1 : 0;
			$isDisplayed = $this->request->get('isDisplayed') == 1 ? 1 : 0;
			$isDisplayedInList = $this->request->get('isDisplayedInList') == 1 ? 1 : 0;

			$specField->setFieldValue('dataType',		  $dataType);
			$specField->setFieldValue('type',			  $type);
			$specField->setFieldValue('handle',			$handle);

			$specField->setFieldValue('isMultiValue',	  $isMultiValue);
			$specField->setFieldValue('isRequired',		$isRequired);
			$specField->setFieldValue('isDisplayed',	   $isDisplayed);
			$specField->setFieldValue('isDisplayedInList', $isDisplayedInList);

			foreach($this->application->getLanguageArray(true) as $langCode)
			{
				$specField->setValueByLang('name', $langCode, $this->request->get('name_' . $langCode));
				$specField->setValueByLang('valueSuffix', $langCode, $this->request->get('valueSuffix_' . $langCode));
				$specField->setValueByLang('valuePrefix', $langCode, $this->request->get('valuePrefix_' . $langCode));
				$specField->setValueByLang('description', $langCode, $this->request->get('description_' . $langCode));
			}

			$specField->save();

			// save specification field values in database
			$newIDs = array();
			if($specField->isSelector() && is_array($values))
			{
				$position = 1;
				$countValues = count($values);
				$i = 0;
				foreach ($values as $key => $value)
				{
					$i++;

					// If last new is empty miss it
					if($countValues == $i && preg_match('/new/', $key) && empty($value[$this->specFieldConfig['languageCodes'][0]]))
					{
						continue;
					}

					if(preg_match('/^new/', $key))
					{
						$specFieldValues = SpecFieldValue::getNewInstance($specField);
					}
					else
					{
					   $specFieldValues = SpecFieldValue::getInstanceByID((int)$key);
					}

					if(SpecField::TYPE_NUMBERS_SELECTOR == $type)
					{
						$specFieldValues->setFieldValue('value', $value);
					}
					else
					{
						$specFieldValues->setLanguageField('value', $value, $this->specFieldConfig['languageCodes']);
					}

					$specFieldValues->setFieldValue('position', $position++);
					$specFieldValues->save();

	   				if(preg_match('/^new/', $key))
					{
						$newIDs[$specFieldValues->getID()] = $key;
					}
				}
			}



			return new JSONResponse(array('id' => $specField->getID(), 'newIDs' => $newIDs), 'success');
		}
		else
		{
			return new JSONResponse(array('errors' => $this->translateArray($errors))/*, 'failure', $this->translate('_could_not_save_attribute')*/);
		}
	}

	/**
	 * Delete specification field from database
	 *
	 * @role update
	 * @return JSONResponse
	 */
	public function delete()
	{
		if($id = $this->request->get("id", false))
		{
			SpecField::deleteById($id);
			return new JSONResponse(false, 'success');
		}
		else
		{
			return new JSONResponse(false, 'failure', $this->translate('_could_not_remove_attribute'));
		}
	}

	/**
	 * Sort specification fields
	 *
	 * @role update
	 * @return JSONResponse
	 */
	public function sort()
	{
		$target = $this->request->get('target');
		preg_match('/_(\d+)$/', $target, $match); // Get group.

		foreach($this->request->get($target, array()) as $position => $key)
		{
			if(!empty($key))
			{
				$specField = SpecField::getInstanceByID((int)$key);
				$specField->setFieldValue('position', (int)$position);

				if(isset($match[1])) $specField->setFieldValue('specFieldGroupID', SpecFieldGroup::getInstanceByID((int)$match[1])); // Change group
				else $specField->specFieldGroup->setNull();

				$specField->save();
			}
		}

		return new JSONResponse(false, 'success');
	}


	/**
	 * Create and return configurational data. If configurational data is already created just return the array
	 *
	 * @see self::$specFieldConfig
	 * @return array
	 */
	private function getSpecFieldConfig()
	{
		if(!empty($this->specFieldConfig)) return $this->specFieldConfig;

		$languages[$this->application->getDefaultLanguageCode()] =  $this->locale->info()->getOriginalLanguageName($this->application->getDefaultLanguageCode());
		foreach ($this->application->getLanguageList()->toArray() as $lang)
		{
			if($lang['isDefault'] != 1)
			{
				$languages[$lang['ID']] = $this->locale->info()->getOriginalLanguageName($lang['ID']);
			}
		}

		$this->specFieldConfig = array(
			'languages' => $languages,
			'languageCodes' => array_keys($languages),
			'messages' => array
			(
				'deleteField' => $this->translate('_delete_field'),
				'removeFieldQuestion' => $this->translate('_remove_field_question')
			),

			'selectorValueTypes' => SpecField::getSelectorValueTypes(),
			'doNotTranslateTheseValueTypes' => array(2),
			'countNewValues' => 0
		);

		return $this->specFieldConfig;
	}

	/**
	 * Validates specification field form
	 *
	 * @param array $values List of values to validate.
	 * @param array $config
	 * @return array List of all errors
	 */
	private function validate($values = array(), $languageCodes)
	{
		$errors = array();

		if(!isset($values['name_' . $languageCodes[0]]) || $values['name_' . $languageCodes[0]] == '')
		{
			$errors["name_" + $languageCodes[0]] = '_error_name_empty';
		}

		if(!isset($values['handle']) || $values['handle'] == '' || preg_match('/[^\w\d_.]/', $values['handle']))
		{
			$errors['handle'] = '_error_handle_invalid';
		}
		else
		{
			$values['ID'] = !isset($values['ID']) ? -1 : $values['ID'];
			$filter = new ARSelectFilter();
				$handleCond = new EqualsCond(new ARFieldHandle('SpecField', 'handle'), $values['handle']);
				$handleCond->addAND(new EqualsCond(new ARFieldHandle('SpecField', 'categoryID'), (int)$values['categoryID']));
				$handleCond->addAND(new NotEqualsCond(new ARFieldHandle('SpecField', 'ID'), (int)$values['ID']));
			$filter->setCondition($handleCond);
			if(count(SpecField::getRecordSetArray($filter)) > 0)
			{
				$errors['handle'] =  '_error_handle_exists';
			}
		}

		if(!isset($values['handle']) || $values['handle'] == '')
		{
			$errors['handle'] = '_error_handle_empty';
		}

		if(in_array($values['type'], SpecField::getSelectorValueTypes()) && isset($values['values']) && is_array($values['values']))
		{
			$countValues = count($values['values']);
			$i = 0;
			foreach ($values['values'] as $key => $v)
			{
				$i++;
				if($countValues == $i && preg_match('/new/', $key) && empty($v[$languageCodes[0]]))
				{
					continue;
				}
				else if(empty($v[$languageCodes[0]]))
				{
					$errors["values[$key][{$languageCodes[0]}]"] = '_error_value_empty';
				}
				else if(SpecField::getDataTypeFromType($values['type']) == 2 && !is_numeric($v[$languageCodes[0]]))
				{
					$errors["values[$key][{$languageCodes[0]}]"] = '_error_value_is_not_a_number';
				}
			}
		}

		return $errors;
	}
}

?>