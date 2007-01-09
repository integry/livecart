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
                    1 => 'Selector',
                    2 => 'Numbers'
                ),
                1 => array
                (
                    3 => 'Text',
                    4 => 'Word processer',
                    5 => 'selector',
                    6 => 'Date'
                )
            ),

            'messages' => array
            (
                'deleteField' => 'delete field'
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
        $response->setValue('specFields', $category->getSpecificationFieldArray());

        $defaultSpecFieldValues = array
        (
            'ID' => 'new',
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
        $response->setValue('specFieldsList', $defaultSpecFieldValues);
        $response->setValue('configuration', $this->specFieldConfig);
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
       $specFieldList = SpecField::getInstanceByID($this->request->getValue('id'), true, true)->toArray(false);

       foreach(SpecFieldValue::getRecordSetArray($specFieldList['ID']) as $value)
       {
           $specFieldList['values'][$value['ID']] = $value['value'];
       }

       $specFieldList['rootId'] = "specField_items_list_".$specFieldList['categoryID']."_".$specFieldList['ID'];

       return new JSONResponse($specFieldList);
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
            $specField = SpecField::getNewInstance();
            $specField->setFieldValue('position', 100000);

            if($categoryID = $this->request->getValue('categoryID', false))
            {
                $specField->setFieldValue('categoryID', (int)$categoryID);
            }
        }
        else
        {

            if(SpecField::exists((int)$this->request->getValue('ID')))
            {
                $specField = SpecField::getInstanceByID((int)$this->request->getValue('ID'));
            }
            else
            {
                return new JSONResponse(array('errors' => array('ID' => 'Record with such id does not exist'), 'status' => 'failure'));
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

            $htmlspecialcharsUtf_8 = create_function('$val', 'return htmlspecialchars($val, null, "UTF-8");');

            $specField->setFieldValue('dataType',       $dataType);
            $specField->setFieldValue('type',           $type);
            $specField->setFieldValue('handle',         $handle);
            $specField->setLanguageField('description', @array_map($htmlspecialcharsUtf_8, $description), array_keys($this->specFieldConfig['languages']));
            $specField->setLanguageField('name',        @array_map($htmlspecialcharsUtf_8, $name),        array_keys($this->specFieldConfig['languages']));

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

        if(!isset($values['handle']) || preg_match('/[^\w\d_]/', $values['handle']))
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
            SpecField::delete($id);
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
                $specField = SpecField::getInstanceByID((int)$key);
                $specField->setFieldValue('position', (int)$position);
                $specField->save();
            }
        }

        return new JSONResponse(array('status' => 'success'));
    }

    public function deleteValue()
    {
        if($id = $this->request->getValue("id", false))
        {
            SpecFieldValue::delete($id);
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

}