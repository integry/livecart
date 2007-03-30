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
 * @role admin.store.catalog
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

        $categoryID = (int)$this->request->getValue('id');
        $category = Category::getInstanceByID($categoryID);
        $specFieldsList = $category->getSpecificationFieldSet();

        $filters = Filter::createFiltersInGroupsCountArray($category->getFilterGroupSet());
        
        $blankFilter = array
        (
            'ID' => $categoryID . '_new',
            'name' => array(),
            'rootId' => 'filter_item_new_'.$categoryID.'_form',
            'categoryID' => $categoryID,
            'specFields' => $this->getSpecFieldOptions($category->getSpecificationFieldArray())
        );
        
        $response->setValue('filters', $filters);
        $response->setValue('blankFilter', $blankFilter);
        $response->setValue('categoryID', $categoryID);
        $response->setValue('configuration', $this->getConfig());
        $response->setValue('defaultLangCode', $this->store->getDefaultLanguageCode());
        
        return $response;
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

    /**
     * Creates a new or modifies an exisitng specification field (according to a passed parameters)
     *
     * @return JSONResponse Status and errors list if status was equal to failure
     */
    public function save()
    {
        if(preg_match('/new$/', $this->request->getValue('ID')))
        {
            $filterGroup = FilterGroup::getNewInstance(SpecField::getInstanceByID($this->request->getValue('specFieldID', false)));

            if($specFieldID = $this->request->getValue('specFieldID', false))
            {
                $filterGroup->setFieldValue('specFieldID', SpecField::getInstanceByID((int)$specFieldID));
            }
        }
        else
        {
            $filterGroup = FilterGroup::getInstanceByID((int)$this->request->getValue('ID'));
        }

        $this->getConfig();
        
        $errors = FilterGroup::validate($this->request->getValueArray(array('name', 'filters', 'specFieldID', 'ID')), $this->filtersConfig['languageCodes']);
        
        if(!$errors)
        {
            $name = $this->request->getValue('name');
            $filters = $this->request->getValue('filters', false);
            
            $filterGroup->setLanguageField('name',  $name, $this->filtersConfig['languageCodes']);
            $filterGroup->specField->set(SpecField::getInstanceByID((int)$this->request->getValue('specFieldID')));
            $filterGroup->save();
            
            $specField = $filterGroup->specField->get();
            $specField->load();
            $specFieldType = $specField->type->get();

            $newIDs = array();
            if(!empty($filters) && !$specField->isSelector()) 
            {
				$newIDs = $filterGroup->saveFilters($filters, $specFieldType, $this->filtersConfig['languageCodes']);
			}

            return new JSONResponse(array('status' => 'success', 'id' => $filterGroup->getID(), 'newIDs' => $newIDs));
        }
        else
        {
            return new JSONResponse(array('errors' => $this->translateArray($errors), 'status' => 'failure'));
        }
    }

    /**
     * Get filter group data from database
     * 
     * @return JSONResponse
     */
    public function item()
    {
        $groupID = $this->request->getValue('id');
        $categoryID = $this->request->getValue('categoryID');
        
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
     * @return JSONResponse Status
     */
    public function delete()
    {
        if($id = $this->request->getValue("id", false))
        {
            FilterGroup::deletebyID((int)$id);
            return new JSONResponse(array('status' => 'success'));
        }
        else
        {
            return new JSONResponse(array('status' => 'failure'));
        }
    }

    /**
     * Sort filter groups
     * 
     * @return JSONResponse Status
     */
    public function sort()
    {
        foreach($this->request->getValue($this->request->getValue('target'), array()) as $position => $key)
        {
            if(!empty($key))
            {
                $group = FilterGroup::getInstanceByID((int)$key);
                $group->setFieldValue('position', (int)$position);
                $group->save();
            }
        }

        return new JSONResponse(array('status' => 'success'));
    }
}
?>