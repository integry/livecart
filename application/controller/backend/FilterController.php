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
        foreach($specFieldsList as $specFieldObj)
        {
            $filters = array_merge($filters, $specFieldObj->getFiltersGroupsListArray());
        }
        
        
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
                'deleteField' => $this->translate('_delete_field'),
                'removeFilter' => $this->translate('_remove_filter_question'),
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
        if(preg_match('/new$/', $this->request->getValue('ID')))
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
                return new JSONResponse(array('errors' => array('ID' => $this->translate("_error_record_id_is_not_unique")), 'status' => 'failure'));
            }
        }

        if(count($errors = $this->validate($this->request->getValueArray(array('name', 'filters', 'specFieldID', 'ID')))) == 0)
        {
            $htmlspecialcharsUtf_8 = create_function('$val', 'return htmlspecialchars($val, null, "UTF-8");');
            $name = $this->request->getValue('name');
            $filters = $this->request->getValue('filters', false);
            $specFieldID = SpecField::getInstanceByID((int)$this->request->getValue('specFieldID'));
            
            $filterGroup->setLanguageField('name',  @array_map($htmlspecialcharsUtf_8, $name),        array_keys($this->filtersConfig['languages']));
            $filterGroup->setFieldValue('specFieldID', $specFieldID);

            $filterGroup->save();
            
            $filterGroupID = $filterGroup->getID();
            
            $specField = $filterGroup->getFieldValue('specFieldID');
            $specField->load();
            $specFieldType = $specField->getFieldValue('type');

            if(!empty($filters)) $filterGroup->saveFilters($filters, $specFieldType, $this->filtersConfig['languages']);

            return new JSONResponse(array('status' => 'success', 'id' => $filterGroupID));
        }
        else
        {
            return new JSONResponse(array('errors' => $errors, 'status' => 'failure'));
        }
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

        if(isset($values['filters']))
        {                      
            $specField = SpecField::getInstanceByID((int)$values['specFieldID']);
            if(!$specField->isLoaded()) $specField->load();
                                
            foreach ($values['filters'] as $key => $v)
            {                
                switch($specField->getFieldValue('type'))
                {
                    case SpecField::TYPE_NUMBERS_SIMPLE:
                        if(!isset($v['rangeStart']) || !is_numeric($v['rangeStart']) | !isset($v['rangeEnd']) || !is_numeric($v['rangeEnd']))
                        {
                            $errors['filters'][$key]['range'] = $this->translate('_error_filter_value_is_not_a_number');
                        }
                    break;
                    case SpecField::TYPE_NUMBERS_SELECTOR: 
                    case SpecField::TYPE_TEXT_SELECTOR: 
                        if(!isset($v['specFieldValueID']))
                        {
                            $errors['filters'][$key]['selector'] = $this->translate('_error_spec_field_is_not_selected');
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
                            $errors['filters'][$key]['date_range'] = $this->translate('_error_illegal_date');
                        }
                    break;
                }
                
                if($v['name'][$languageCodes[0]] == '')
                {
                    $errors['filters'][$key]['name'] = $this->translate('_error_filter_name_empty');
                }        
                
                if(!isset($v['handle']) || $v['handle'] == '' || preg_match('/[^\w\d_.]/', $v['handle']))
                {
                    $errors['filters'][$key]['handle'] = $this->translate('_error_filter_handle_invalid');
                }
            }
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
        $filterGroupArray = $filterGroup->toArray(false, false);
        
        foreach($filterGroup->getFiltersList()->toArray(false, false) as $filter)
        {
            $filterGroupArray['filters'][$filter['ID']] = $filter;
        }
        $filterGroupArray['filtersCount'] = isset($filterGroupArray['filters']) ? count($filterGroupArray['filters']) : 0;
        
        $filterGroupArray['rootId'] = "filter_items_list_".$filterGroupArray['SpecField']['Category']['ID']."_".$filterGroupArray['ID'];
        $filterGroupArray['categoryID'] = $filterGroupArray['SpecField']['Category']['ID'];

        $filterGroupArray['specFields'] = $this->getSpecFieldOptions(Category::getInstanceByID($filterGroupArray['categoryID'])->getSpecificationFieldArray());           

        return new JSONResponse($filterGroupArray);
    }


    public function deleteFilter()
    {
        if($id = $this->request->getValue("id", false))
        {
            Filter::deleteByID($id);
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