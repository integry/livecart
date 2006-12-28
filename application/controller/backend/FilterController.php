<?php
ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.model.category.*");
ClassLoader::import("library.*");

/**
 * ...
 *
 * @package application.controller.backend
 * @author Saulius Rupainis <saulius@integry.net>
 *
 * @role admin.store.catalog
 */
class FilterController extends StoreManagementController
{
    private $filtersConfig = array();

    function __construct($request)
    {
        parent::__construct($request);
        $this->createConfig();
    }

    public function index()
    {        
        $response = new ActionResponse();

        $categoryID = (int)$this->request->getValue('id');
        $category = Category::getInstanceByID($categoryID);
        $specFieldsList = $category->getSpecificationFieldSet();

        $filters = array();
        foreach($specFieldsList as $specField)
        {
            $specFieldObj = SpecField::getInstanceByID($specField['ID']);
            $filters = array_merge($filters, $specFieldObj->getFiltersGroupsList());
        }

        
        $blankFilter = array
        (
            'ID' => 'new',
            'name' => array(),
            'rootId' => 'filter_item_new_'.$categoryID.'_form',
            'categoryID' => $categoryID,
            'specFields' => $this->getSpecFieldOptions($specFieldsList)
        );
        
        $response->setValue('filters', $filters);
        $response->setValue('blankFilter', $blankFilter);
        $response->setValue('categoryID', $categoryID);
        $response->setValue('configuration', $this->filtersConfig);
        $response->setValue('defaultLangCode', $this->store->getDefaultLanguageCode());

        return $response;
    }

    private function getSpecFieldOptions($specFieldsList)
    {
        $specFieldOptions = array();
        foreach ($specFieldsList as $field)
        {
            if(!in_array($field['type'], array(SpecField::TYPE_TEXT_SIMPLE, SpecField::TYPE_TEXT_ADVANCED)))
            {
                $specFieldOptions[] = array(
                    'ID' => $field['ID'],
                    'type' => $field['type'],
                    'dataType' => $field['dataType'],
                    'name' => isset($field['name'][$_lng = $this->store->getDefaultLanguageCode()]) ? $field['name'][$_lng] : '',
                    'values' => SpecField::getInstanceByID($field['ID'])->getValuesList()
                );
            }
        }
        
        return $specFieldOptions;
    }
    
    private function createConfig()
    {
        $languages[$this->store->getDefaultLanguageCode()] =  $this->locale->info()->getLanguageName($this->store->getDefaultLanguageCode());
        foreach ($this->store->getLanguageList()->toArray() as $lang)
        {
            if($lang['isEnabled']==1 && $lang['isDefault'] != 1)
            {
                $languages[$lang['ID']] = $this->locale->info()->getLanguageName($lang['ID']);
            }
        }

        $this->filtersConfig = array (
            'languages'=> $languages,

            'messages' => array (
                'deleteField' => 'delete field'
                ),

            'selectorValueTypes' => SpecField::getSelectorValueTypes(),
            'countNewFilters' => 0,
            'typesWithNoFiltering' => array(),
            'dateFormat' => $this->locale->info()->getDateFormat()
            );
    }

    /**
     * Creates a new or modifies an exisitng specification field (according to a passed parameters)
     *
     * @return ActionRedirectResponse Redirects back to a form if validation fails or to a field list
     */
    public function save()
    {
        if($this->request->getValue('ID') == 'new')
        {
            $filterGroup = FilterGroup::getNewInstance();
            $filterGroup->setFieldValue('position', 100000); // Now new group will appear last in active list.

            if($specFieldID = $this->request->getValue('specFieldID', false))
            {
                $filterGroup->setFieldValue('specFieldID', SpecField::getInstanceByID((int)$specFieldID));

            }
        }
        else
        {

            if(FilterGroup::exists((int)$this->request->getValue('ID')))
            {
                $filterGroup = FilterGroup::getInstanceByID((int)$this->request->getValue('ID'));
            }
            else
            {
                return new JSONResponse(array('errors' => array('ID' => 'Record with such id does not exist'), 'status' => 'failure'));
            }
        }

        if(count($errors = $this->validate($this->request->getValueArray(array('name', 'filters', 'specFieldID', 'ID')))) == 0)
        {
            $htmlspecialcharsUtf_8 = create_function('$val', 'return htmlspecialchars($val, null, "UTF-8");');
            $name = $this->request->getValue('name');
            $filters = $this->request->getValue('filters', false);
            $specFieldID = SpecField::getInstanceByID((int)$this->request->getValue('specFieldID'));
            
            $filterGroup->setLanguageField('name',        @array_map($htmlspecialcharsUtf_8, $name),        array_keys($this->filtersConfig['languages']));
            $filterGroup->setFieldValue('specFieldID', $specFieldID);

            $filterGroup->save();
            
            $filterGroupID = $filterGroup->getID();
            
            $specField = $filterGroup->getFieldValue('specFieldID');
            $specField->load();
            $specFieldType = $specField->getFieldValue('type');

            if(!empty($filters))
            {
                $position = 1;
                foreach ($filters as $key => $value)
                {
                    if(preg_match('/^new/', $key))
                    {
                        $filter = Filter::getNewInstance();
                        $filter->setFieldValue('position', 100000); // Now new filter will appear last in active list.
                    }
                    else
                    {
                        $filter = Filter::getInstanceByID((int)$key);
                    }

                    $filter->setLanguageField('name', @array_map($htmlspecialcharsUtf_8, $value['name']),  array_keys($this->filtersConfig['languages']));
                    
                    
                    
                    if($specFieldType == SpecField::TYPE_TEXT_DATE)
                    {
                        $filter->setFieldValue('rangeDateStart', $value['rangeDateStart']);
                        $filter->setFieldValue('rangeDateEnd', $value['rangeDateEnd']);
                        $filter->rangeStart->setNull();
                        $filter->rangeEnd->setNull();
                        $filter->specFieldValue->setNull();
                    }
                    else if(!in_array($specFieldType, $this->filtersConfig['selectorValueTypes']))
                    {
                        $filter->setFieldValue('rangeStart', $value['rangeStart']);
                        $filter->setFieldValue('rangeEnd', $value['rangeEnd']);
                        $filter->rangeDateStart->setNull();
                        $filter->rangeDateEnd->setNull();
                        $filter->specFieldValue->setNull();
                    }
                    else
                    {
                        $filter->setFieldValue('specFieldValueID', SpecFieldValue::getInstanceByID((int)$value['specFieldValueID']));
                        $filter->rangeDateStart->setNull();
                        $filter->rangeDateEnd->setNull();
                        $filter->rangeStart->setNull();
                        $filter->rangeEnd->setNull();
                    }
                    
                    
                    $filter->setFieldValue('filterGroupID', $filterGroup);
                    $filter->setFieldValue('position', $position);

                    $filter->save();

                    $position++;
                }
            }

            return new JSONResponse(array('status' => 'success', 'id' => $filterGroupID));
        }
        else
        {
            return new JSONResponse(array('errors' => $errors, 'status' => 'failure'));
        }
    }

    public function create()
    {
        $filter = ActiveRecordModel::getNewInstance("Filter");
        srand();
        $filter->setValueByLang("name", "en", "This is my test filter " . rand());
        $filter->rangeStart->set(rand());
        $filter->rangeEnd->set(rand());
        $filter->save();
    }


    /**
     * Validates spec field form
     *
     * @param array $values List of values to validate.
     * @return array List of all errors
     */
    private function validate($values = array())
    {
        $errors = array();

        $languageCodes = array_keys($this->filtersConfig['languages']);
        
        if(!isset($values['name']) || $values['name'][$languageCodes[0]] == '')
        {
            $errors['name'] = $this->translate('_error_name_empty');
        }

        return $errors;
    }


    /**
     * Displays form for creating a new or editing existing one product group specification field
     *
     * @return ActionResponse
     */
    public function item()
    {
        $response = new ActionResponse();
        $filterGroup = FilterGroup::getInstanceByID($this->request->getValue('id'), true, true);
        $filterGroupArray = $filterGroup->toArray(false);


        foreach($filterGroup->getFiltersList() as $filter)
        {
            $filterGroupArray['filters'][$filter['ID']] = $filter;
        }
        
        $filterGroupArray['rootId'] = "filter_items_list_".$filterGroupArray['SpecField']['categoryID']."_".$filterGroupArray['ID'];
        $filterGroupArray['categoryID'] = $filterGroupArray['SpecField']['categoryID'];

        $filterGroupArray['specFields'] = $this->getSpecFieldOptions(Category::getInstanceByID($filterGroupArray['categoryID'])->getSpecificationFieldSet());           

        return new JSONResponse($filterGroupArray);
    }


    public function deleteFilter()
    {
        if($id = $this->request->getValue("id", false))
        {
            Filter::delete($id);
            return new JSONResponse(array('status' => 'success'));
        }
        else
        {
            return new JSONResponse(array('status' => 'failure'));
        }
    }


    public function delete()
    {
        if($id = $this->request->getValue("id", false))
        {
            FilterGroup::delete($id);
            return new JSONResponse(array('status' => 'success'));
        }
        else
        {
            return new JSONResponse(array('status' => 'failure'));
        }
    }

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


    public function sortFilter()
    {
        foreach($this->request->getValue($this->request->getValue('target'), array()) as $position => $key)
        {
            // Except new fields, because they are not yet in database
            if(!empty($key) && !preg_match('/^new/', $key))
            {
                $filter = Filter::getInstanceByID((int)$key);
                $filter->setFieldValue('position', (int)$position);
                $filter->save();
            }
        }

        return new JSONResponse(array('status' => 'success'));
    }
    
    public function generateFilters()
    {        
        $specFieldValues = SpecField::getInstanceByID((int)$this->request->getValue('specFieldID'), true)->getValuesList();        
        
        $return = array();
                
        foreach($specFieldValues as $value) $return[$value['ID']] = array('name' => $value['value'], 'specFieldValueID' => $value['ID']);
        
        return new JSONResponse(array("status" => "success", "filters" => $return));
    }
}

?>