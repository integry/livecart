<?php
ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.model.category.*");
ClassLoader::import("library.*");

/**
 * Category specification field ("extra field") controller
 *
 * @package application.controller.backend
 * @author Saulius Rupainis <saulius@integry.net>
 * @role admin.store.category
 */
class SpecFieldController extends StoreManagementController
{
    protected $specFieldConfig = array();

    public function __construct(Request $request)
    {
        $this->htmlspecialcharsUtf_8 = create_function('$val', 'return htmlspecialchars($val, null, "UTF-8");');
        
        parent::__construct($request);
        $this->createSpecFieldConfig();
    }

    /**
     * Types:
     * 1 - numbers
     * 2 - text
     */
    private function createSpecFieldConfig()
    {
        $languages[$this->store->getDefaultLanguageCode()] =  $this->locale->info()->getOriginalLanguageName($this->store->getDefaultLanguageCode());
        foreach ($this->store->getLanguageList()->toArray() as $lang)
        {
            if($lang['isEnabled']==1 && $lang['isDefault'] != 1)
            {
                $languages[$lang['ID']] = $this->locale->info()->getOriginalLanguageName($lang['ID']);
            }
        }

        $this->specFieldConfig = array(
            'languages' => $languages,

            'types' => array
            (
                2 => array
                (
                    2 => $this->translate('_type_numbers'),
                    1 => $this->translate('_type_numbers_selector')
                ),
                1 => array
                (
                    3 => $this->translate('_type_simple_text'),
                    4 => $this->translate('_type_formatted_text'),
                    5 => $this->translate('_type_text_selector'),
                    6 => $this->translate('_type_date')
                )
            ),

            'messages' => array
            (
                'deleteField' => $this->translate('_delete_field'),
                'removeFieldQuestion' => $this->translate('_remove_field_question')
            ),

            'selectorValueTypes' => SpecField::getSelectorValueTypes(),
            'doNotTranslateTheseValueTypes' => array(2),
            'countNewValues' => 0
        );
    }

    public function index()
    {
        $response = new ActionResponse();

        $categoryID = (int)$this->request->getValue('id');
        $category = Category::getInstanceByID($categoryID);
        
        $response->setValue('specFieldsWithGroups', $category->getSpecificationFieldArray(true, true, true));

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
            'categoryID' => $categoryID
        );

        $response->setValue('categoryID', $categoryID);
        $response->setValue('configuration', $this->specFieldConfig);
        $response->setValue('specFieldsList', $defaultSpecFieldValues);
        $response->setValue('defaultLangCode', $this->store->getDefaultLanguageCode());

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
		$specFieldList = SpecField::getInstanceByID($this->request->getValue('id'), true, true)->toArray(false, false);
		
		foreach(SpecFieldValue::getRecordSetArray($specFieldList['ID']) as $value)
		{
		   $specFieldList['values'][$value['ID']] = $value['value'];
		}
		
		$specFieldList['categoryID'] = $specFieldList['Category']['ID'];
		unset($specFieldList['Category']);
				
		return new JSONResponse($specFieldList);
    }

    /**
     * Get group data
     */
    public function group()
    {
        return new JSONResponse(SpecFieldGroup::getInstanceByID((int)$this->request->getValue('id'), true)->toArray(false, false));
    }
    
    /**
     * Save group data to the database
     */
    public function saveGroup()
    {        
        $name = $this->request->getValue('name');
        
        $specFieldGroup = SpecFieldGroup::getInstanceByID($this->request->getValue('id'));
        $specFieldGroup->setLanguageField('name', @array_map($this->htmlspecialcharsUtf_8, $name), array_keys($this->specFieldConfig['languages']));
        $specFieldGroup->save();

        return new JSONResponse(array('status' => 'success', 'id' => $specFieldGroup->getID()));
    }
    
    /**
     * Creates a new or modifies an exisitng specification field (according to a passed parameters)
     *
     * @return JSONResponse Returns success status or failure status with array of erros
     */
    public function save()
    {
        if(preg_match('/new$/', $this->request->getValue('ID')))
        {
            $specField = SpecField::getNewInstance(Category::getInstanceByID($this->request->getValue('categoryID', false)));
            $specField->setFieldValue('position', 100000);
        }
        else
        {
            if(SpecField::exists((int)$this->request->getValue('ID')))
            {
                $specField = SpecField::getInstanceByID((int)$this->request->getValue('ID'));
            }
            else
            {
                return new JSONResponse(array('errors' => array('ID' => $this->translate('_error_record_id_is_not_valid')), 'status' => 'failure'));
            }
        }

        if(count($errors = $this->validateSpecField($this->request->getValueArray(array('handle', 'values', 'name', 'type', 'dataType', 'categoryID', 'ID')))) == 0)
        {
            $dataType = (int)$this->request->getValue('dataType');
            $type = (int)$this->request->getValue('type');
            $categoryID = (int)$this->request->getValue('categoryID');

            $description = $this->request->getValue('description');
            $name = $this->request->getValue('name');
            $handle = $this->request->getValue('handle');
            $values = $this->request->getValue('values');
            $isMultiValue = $this->request->getValue('multipleSelector') == 1 ? 1 : 0;
            $isRequired = $this->request->getValue('isRequired') == 1 ? 1 : 0;

            

            $specField->setFieldValue('dataType',       $dataType);
            $specField->setFieldValue('type',           $type);
            $specField->setFieldValue('handle',         $handle);
            $specField->setFieldValue('isMultiValue',   $isMultiValue);
            $specField->setFieldValue('isRequired',     $isRequired);
            $specField->setLanguageField('description', @array_map($this->htmlspecialcharsUtf_8, $description), array_keys($this->specFieldConfig['languages']));
            $specField->setLanguageField('name',        @array_map($this->htmlspecialcharsUtf_8, $name),        array_keys($this->specFieldConfig['languages']));

            $specField->save();           
            if(!empty($values)) $specField->saveValues($values, $type, $this->specFieldConfig['languages']);

            return new JSONResponse(array('status' => 'success', 'id' => $specField->getID()));
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
    private function validateSpecField($values = array())
    {
        $errors = array();

        $languageCodes = array_keys($this->specFieldConfig['languages']);

        if(!isset($values['name']) || $values['name'][$languageCodes[0]] == '')
        {
            $errors['name'] = $this->translate('_error_name_empty');
        }

        if(!isset($values['handle']) || $values['handle'] == '' || preg_match('/[^\w\d_.]/', $values['handle']))
        {
            $errors['handle'] = $this->translate('_error_handle_invalid');
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
                $errors['handle'] =  $this->translate('_error_handle_exists');
            }
        }

        if(!isset($values['handle']) || $values['handle'] == '')
        {
            $errors['handle'] = $this->translate('_error_handle_empty');
        }

        if(in_array($values['type'], $this->specFieldConfig['selectorValueTypes']) && isset($values['values']) && is_array($values['values']))
        {
            foreach ($values['values'] as $key => $v)
            {
                if(empty($v[$languageCodes[0]]))
                {
                    $errors['values'][$key] = $this->translate('_error_value_empty');
                }

                if($values['dataType'] == 2 && !is_numeric($v[$languageCodes[0]]))
                {
                    $errors['values'][$key] = $this->translate('_error_value_is_not_a_number');
                }
            }
        }


        return $errors;
    }

    public function delete()
    {
        if($id = $this->request->getValue("id", false))
        {
            SpecField::deleteById($id);
            return new JSONResponse(array('status' => 'success'));
        }
        else
        {
            return new JSONResponse(array('status' => 'failure'));
        }
    }

    public function sort()
    {
        $target = $this->request->getValue('target');
        preg_match('/_(\d+)$/', $target, $match); // Get group. 
        
        foreach($this->request->getValue($target, array()) as $position => $key)
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

        return new JSONResponse(array('status' => 'success'));
    }

    public function deleteValue()
    {
        if($id = $this->request->getValue("id", false))
        {
            SpecFieldValue::deleteById($id);
            return new JSONResponse(array('status' => 'success'));
        }
        else
        {
            return new JSONResponse(array('status' => 'failure'));
        }
    }

    public function sortValues()
    {
        foreach($this->request->getValue($this->request->getValue('target'), array()) as $position => $key)
        {
            // Except new fields, because they are not yet in database
            if(!empty($key) && !preg_match('/^new/', $key))
            {
                $specField = SpecFieldValue::getInstanceByID((int)$key);
                $specField->setFieldValue('position', (int)$position);
                $specField->save();
            }
        }

        return new JSONResponse(array('status' => 'success'));
    }
    
    public function sortGroups()
    {
        foreach($this->request->getValue($this->request->getValue('target'), array()) as $position => $key)
        {
            // Except new fields, because they are not yet in database
            $group = SpecFieldGroup::getInstanceByID((int)$key);
            $group->setFieldValue('position', (int)$position);
            $group->save();
        }

        return new JSONResponse(array('status' => 'success'));
    }

}
