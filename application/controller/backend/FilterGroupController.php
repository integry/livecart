<?php

/**
 * Filter group controller
 *
 * @package application/controller/backend
 * @author	Integry Systems
 *
 * @role filter
 */
class FilterGroupController extends StoreManagementController
{
	/**
	 * Configuration array
	 *
	 * @see self::getConfig()
	 */
	private $filtersConfig = array();

	/**
	 * Filters group index page
	 *
	 */
	public function indexAction()
	{


		$categoryID = (int)$this->request->get('id');
		$category = Category::getInstanceByID($categoryID);
		$specFieldsList = $category->getSpecificationFieldSet();

		$blankFilter = array
		(
			'ID' => $categoryID . '_new',
			'name' => array(),
			'rootId' => 'filter_item_new_'.$categoryID.'_form',
			'categoryID' => $categoryID,
			'specFields' => $this->getSpecFieldOptions($category->getSpecificationFieldArray())
		);

		$this->set('filters', $this->createFiltersInGroupsCountArray($category->getFilterGroupSet()));
		$this->set('blankFilter', $blankFilter);
		$this->set('categoryID', $categoryID);
		$this->set('configuration', $this->getConfig());
		$this->set('defaultLangCode', $this->application->getDefaultLanguageCode());

	}

	/**
	 * @role create
	 */
	public function createAction()
	{
		$filterGroup = FilterGroup::getNewInstance(SpecField::getInstanceByID($this->request->get('specFieldID', false)));

		if($specFieldID = $this->request->get('specFieldID', false))
		{
			$filterGroup->setFieldValue('specFieldID', SpecField::getInstanceByID((int)$specFieldID));
		}

		return $this->save($filterGroup);
	}

	/**
	 * @role update
	 */
	public function updateAction()
	{
		$filterGroup = FilterGroup::getInstanceByID((int)$this->request->get('ID'));

		return $this->save($filterGroup);
	}

	/**
	 * Creates a new or modifies an exisitng specification field (according to a passed parameters)
	 *
	 * @return JSONResponse Status and errors list if status was equal to failure
	 */
	private function save(FilterGroup $filterGroup)
	{
		$this->getConfig();

		$errors = $this->validate($this->request->getValueArray(array('name', 'filters', 'specFieldID', 'ID')), $this->filtersConfig['languageCodes']);

		if(!$errors)
		{
			$filters = $this->request->get('filters', false);

			$filterGroup->loadRequestData($this->request);
			$filterGroup->specField->set(SpecField::getInstanceByID((int)$this->request->get('specFieldID')));
			$filterGroup->save();

			$specField = $filterGroup->specField;
			$specField->load();
			$specFieldType = $specField->type;

			$newIDs = array();
			if(!empty($filters) && !$specField->isSelector())
			{
				$newIDs = $filterGroup->saveFilters($filters, $specFieldType, $this->filtersConfig['languageCodes']);
			}

			return new JSONResponse(array('id' => $filterGroup->getID(), 'newIDs' => $newIDs), 'success');
		}
		else
		{
			return new JSONResponse(array('errors' => $this->translateArray($errors)));
		}
	}

	/**
	 * Get filter group data from database
	 *
	 * @role update
	 *
	 * @return JSONResponse
	 */
	public function itemAction()
	{
		$groupID = $this->request->get('id');
		$categoryID = $this->request->get('categoryID');


		$filterGroup = FilterGroup::getInstanceByID($groupID, true, array('SpecField', 'Category'));

		$filterGroupArray = $filterGroup->toArray();

		foreach($filterGroup->getFiltersList() as $filter)
		{
			$filterGroupArray['filters'][$filter->getID()] = $filter->toArray(false);
		}

		if($filterGroup->specField->isSelector())
		{
			$filterGroupArray['filtersCount'] = $filterGroup->specField->getValuesSet()->getTotalRecordCount();
		}
		else
		{
			$filterGroupArray['filtersCount'] = isset($filterGroupArray['filters']) ? count($filterGroupArray['filters']) : 0;
		}

		$filterGroupArray['rootId'] = "filter_items_list_" . $categoryID . "_".$filterGroupArray['ID'];
		$filterGroupArray['categoryID'] = $categoryID;

		$filterGroupArray['specFields'] = $this->getSpecFieldOptions(Category::getInstanceByID($categoryID, ActiveRecord::LOAD_DATA)->getSpecificationFieldArray());

		return new JSONResponse($filterGroupArray);
	}

	/**
	 * Delete filter group
	 *
	 * @role remove
	 *
	 * @return JSONResponse Status
	 */
	public function deleteAction()
	{
		if($id = $this->request->get("id", null, false))
		{
			FilterGroup::deletebyID((int)$id);
			return new JSONResponse(false, 'success');
		}
		else
		{
			return new JSONResponse(false, 'failure', $this->translate('_could_not_remove_filter_group'));
		}
	}

	/**
	 * Sort filter groups
	 *
	 * @role sort
	 *
	 * @return JSONResponse Status
	 */
	public function sortAction()
	{
		foreach($this->request->get($this->request->get('target'), array()) as $position => $key)
		{
			if(!empty($key))
			{
				$group = FilterGroup::getInstanceByID((int)$key);
				$group->setFieldValue('position', (int)$position);
				$group->save();
			}
		}

		return new JSONResponse(false, 'success');
	}

	private function getSpecFieldOptions($specFieldsList)
	{
		$specFieldOptions = array();

		foreach ($specFieldsList as $field)
		{
			if(!isset($field['type'])) throw new Exception();
			if(!in_array($field['type'], array(SpecField::TYPE_TEXT_SIMPLE, SpecField::TYPE_TEXT_ADVANCED)))
			{
				$specFieldOptions[] = array(
					'ID' => $field['ID'],
					'type' => $field['type'],
					'dataType' => $field['dataType'],
					'name_lang' => $field['name_lang'],
					'values' => SpecField::getInstanceByID($field['ID'])->getValuesList()
				);
			}
		}

		return $specFieldOptions;
	}

	/**
	 * Create and return configuration array
	 *
	 * @see self::$filtersConfig
	 */
	private function getConfig()
	{
		if(!empty($this->filtersConfig)) return $this->filtersConfig;

		$languages[$this->application->getDefaultLanguageCode()] =  $this->locale->info()->getLanguageName($this->application->getDefaultLanguageCode());
		foreach ($this->application->getLanguageList()->toArray() as $lang)
		{
			if($lang['isDefault'] != 1)
			{
				$languages[$lang['ID']] = $this->locale->info()->getLanguageName($lang['ID']);
			}
		}

		$this->filtersConfig = array (
			'languages'=> $languages,
			'languageCodes'=> array_keys($languages),

			'messages' => array (
				'deleteField' => $this->translate('_delete_field'),
				'removeFilter' => $this->translate('_remove_filter_question'),
				),

			'selectorValueTypes' => SpecField::getSelectorValueTypes(),
			'countNewFilters' => 0,
			'typesWithNoFiltering' => array(),
			'dateFormat' => $this->locale->info()->getDateFormat()
			);

		return $this->filtersConfig;
	}

	private function createFiltersInGroupsCountArray(ARSet $filtersGroupsSet)
	{
		$filterGroupIds = array();
		$filtersGroupsArray = array();
		foreach($filtersGroupsSet as $filterGroup)
		{
			$filterGroupIds[] = $filterGroup->getID();
		}

		if(!empty($filterGroupIds))
		{
			$db = ActiveRecord::getDBConnection();

			$filterGroupIdsString = implode(',',  $filterGroupIds);

			$filtersResultArray = ActiveRecord::getDataBySQL("SELECT filterGroupID, COUNT(*) AS filtersCount FROM Filter WHERE filterGroupID IN ($filterGroupIdsString) GROUP BY filterGroupID");
			$filtersResultCount = count($filtersResultArray);

			$specFieldValuesResultArray = ActiveRecord::getDataBySQL("SELECT specFieldID, COUNT(specFieldID) AS filtersCount FROM SpecFieldValue WHERE specFieldID IN (SELECT specFieldID FROM FilterGroup WHERE ID in ($filterGroupIdsString)) GROUP BY specFieldID");
			$specFieldValuesResultCount = count($specFieldValuesResultArray);

			foreach($filtersGroupsSet as $filterGroup)
			{
				$filterGroupArray = $filterGroup->toArray();
				$filterGroupArray['filtersCount'] = 0;

				$field = $filterGroup->specField;
				if($field->isDate() || $field->isSimpleNumbers())
				{
					for($i = 0; $i < $filtersResultCount; $i++)
					{
						if($filtersResultArray[$i]['filterGroupID'] == $filterGroupArray['ID'])
						{
							$filterGroupArray['filtersCount'] = $filtersResultArray[$i]['filtersCount'];
						}
					}
				}
				else
				{

	   				for($i = 0; $i < $specFieldValuesResultCount; $i++)
					{
						if($specFieldValuesResultArray[$i]['specFieldID'] == $filterGroupArray['SpecField']['ID'])
						{
							$filterGroupArray['filtersCount'] = $specFieldValuesResultArray[$i]['filtersCount'];
						}
					}

				}

				$filtersGroupsArray[] = $filterGroupArray;
			}
		}

		return $filtersGroupsArray;
	}

	/**
	 * Validates filter group form
	 *
	 * @param array $values List of values to validate.
	 * @return array List of all errors
	 */
	private function validate($values = array(), $languageCodes)
	{
		$errors = array();

		if(!isset($values['name']) || $values['name'][$languageCodes[0]] == '')
		{
			$errors['name['.$languageCodes[0].']'] = '_error_name_empty';
		}

		$specField = SpecField::getInstanceByID((int)$values['specFieldID']);
		if(!$specField->isLoaded()) $specField->load();

		if(isset($values['filters']) && !$specField->isSelector())
		{
			$filtersCount = count($values['filters']);
			$i = 0;
			foreach ($values['filters'] as $key => $v)
			{
				$i++;
				// If emty last new filter, ignore it
				if($filtersCount == $i && $v['name'][$languageCodes[0]] == '' && preg_match("/new/", $key)) continue;

				switch($specField->readAttribute('type'))
				{
					case SpecField::TYPE_NUMBERS_SIMPLE:
						if(!isset($v['rangeStart']) || !is_numeric($v['rangeStart']) | !isset($v['rangeEnd']) || !is_numeric($v['rangeEnd']))
						{
							$errors['filters['.$key.'][rangeStart]'] = '_error_filter_value_is_not_a_number';
						}
					break;
					case SpecField::TYPE_TEXT_DATE:
						if(
								!isset($v['rangeDateStart'])
							 || !isset($v['rangeDateEnd'])
							 || count($sdp = explode('-', $v['rangeDateStart'])) != 3
							 || count($edp = explode('-', $v['rangeDateEnd'])) != 3
							 || !checkdate($edp[1], $edp[2], $edp[0])
							 || !checkdate($sdp[1], $sdp[2], $sdp[0])
						){
							$errors['filters['.$key.'][rangeDateStart_show]'] = '_error_illegal_date';
						}
					break;
				}

				if($v['name'][$languageCodes[0]] == '')
				{
					$errors['filters['.$key.'][name]['.$languageCodes[0].']'] = '_error_filter_name_empty';
				}
			}
		}

		return $errors;
	}
}

?>