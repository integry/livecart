<?php
ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.model.category.*");
ClassLoader::import("application.model.filter.*");
ClassLoader::import("library.*");

/**
 * Filter group controller
 *
 * @package application.controller.backend
 * @author Sergej Andrejev <sandrejev@gmail.com>
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
     * @return ActionResponse
     */
    public function index()
    {        
        $response = new ActionResponse();

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
        
        $response->set('filters', Filter::createFiltersInGroupsCountArray($category->getFilterGroupSet()));
        $response->set('blankFilter', $blankFilter);
        $response->set('categoryID', $categoryID);
        $response->set('configuration', $this->getConfig());
        $response->set('defaultLangCode', $this->store->getDefaultLanguageCode());
        
        return $response;
    }

    /**
     * @role create
     */
    public function create()
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
    public function update()
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
        
        $errors = FilterGroup::validate($this->request->getValueArray(array('name', 'filters', 'specFieldID', 'ID')), $this->filtersConfig['languageCodes']);
        
        if(!$errors)
        {
            $name = $this->request->get('name');
            $filters = $this->request->get('filters', false);
            
            $filterGroup->setLanguageField('name',  $name, $this->filtersConfig['languageCodes']);
            $filterGroup->specField->set(SpecField::getInstanceByID((int)$this->request->get('specFieldID')));
            $filterGroup->save();
            
            $specField = $filterGroup->specField->get();
            $specField->load();
            $specFieldType = $specField->type->get();

            $newIDs = array();
            if(!empty($filters) && !$specField->isSelector()) 
            {
				$newIDs = $filterGroup->saveFilters($filters, $specFieldType, $this->filtersConfig['languageCodes']);
			}

            return new JSONResponse(array('id' => $filterGroup->getID(), 'newIDs' => $newIDs), 'success', $this->translate('_filter_group_was_successfully_saved'));
        }
        else
        {
            return new JSONResponse(array('errors' => $this->translateArray($errors)), 'failure', $this->translate('_could_not_save_filter_group'));
        }
    }

    /**
     * Get filter group data from database
     * 
     * @role update
     * 
     * @return JSONResponse
     */
    public function item()
    {
        $groupID = $this->request->get('id');
        $categoryID = $this->request->get('categoryID');
        
    	$response = new ActionResponse();
        $filterGroup = FilterGroup::getInstanceByID($groupID, true, array('SpecField', 'Category'));
        
        $filterGroupArray = $filterGroup->toArray();
                
        foreach($filterGroup->getFiltersList() as $filter)
        {
            $filterGroupArray['filters'][$filter->getID()] = $filter->toArray(false);
        }
        
        if($filterGroup->specField->get()->isSelector())
        {
            $filterGroupArray['filtersCount'] = $filterGroup->specField->get()->getValuesSet()->getTotalRecordCount();
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
    public function delete()
    {
        if($id = $this->request->get("id", false))
        {
            FilterGroup::deletebyID((int)$id);
            return new JSONResponse(false, 'success', $this->translate('_filter_group_was_successfully_removed'));
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
    public function sort()
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

        return new JSONResponse(false, 'success', $this->translate('_filter_groups_were_successfully_reordered'));
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
        
        $languages[$this->store->getDefaultLanguageCode()] =  $this->locale->info()->getLanguageName($this->store->getDefaultLanguageCode());
        foreach ($this->store->getLanguageList()->toArray() as $lang)
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
}
?>