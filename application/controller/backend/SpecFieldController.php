<?php
ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.model.category.*");

/**
 * Category specification field ("extra field") controller
 *
 * @package application.controller.backend
 * @author Saulius Rupainis <saulius@integry.net>
 * @role admin.store.category
 */
class SpecFieldController extends StoreManagementController
{
    private $specFieldLocalesArray = array('en', 'lt', 'de');

    /**
     * Types:
     * 1 - numbers
     * 2 - text
     */
    private function getSpecFieldConfig()
    {
        return array(
            'languages' => array (
                'en' => 'English',
                'lt' => 'Lithuanian',
                'de' => 'German'
            ),

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

            'selectorValueTypes' => array (1, 5),
            'doNotTranslateTheseValueTypes' => array(2),
            'countNewValues' => 0
        );
    }

    public function index()
    {
//        $this->setLayout("mainLayout");

        $response = new ActionResponse();
//		$this->setLayout("empty");


        $categoryID = (int)$this->request->getValue('id');
        $category = Category::getInstanceByID($categoryID);
        $response->setValue('specFields', $category->getSpecFieldList());

        $defaultSpecFieldValues = array
        (
            'ID' => 'new',
            'name' => array(),
            'description' => array(),
            'handle' => '',
            'values' => Array(),
            'rootId' => 'specField_item_new_'.$categoryID.'_form',
            'type' => 3,
            'dataType' => 1,
            'categoryID' => $categoryID
        );

        $response->setValue('categoryID', $categoryID);
        $response->setValue('specFieldsList', $defaultSpecFieldValues);
        $response->setValue('configuration', $this->getSpecFieldConfig());

        return $response;
    }

    /**
     * Displays form for creating a new or editing existing one product group specification field
     *
     * @return ActionResponse
     */
    public function item()
    {
        ClassLoader::import("application.model.category.*");

        $this->setLayout("empty");

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

        if(count($errors = $this->validateSpecField($this->request->getValueArray(array('handle', 'values', 'name', 'type', 'dataType')))) == 0)
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
            $specField->setLanguageField('description', @array_map($htmlspecialcharsUtf_8, $description), $this->specFieldLocalesArray);
            $specField->setLanguageField('name',        @array_map($htmlspecialcharsUtf_8, $name),        $this->specFieldLocalesArray);

            $specField->save();

            $specFieldID = $specField->getID();

            if(!empty($values))
            {
                $position = 1;
                foreach ($values as $key => $value)
                {
                    if(preg_match('/^new/', $key))
                    {
                        $specFieldValues = SpecFieldValue::getNewInstance();
                    }
                    else
                    {
                       $specFieldValues = SpecFieldValue::getInstanceByID((int)$key);
                    }

                    if($type == 1)
                    {
                        $specFieldValues->setFieldValue('value', $value);
                    }
                    else
                    {
                        $specFieldValues->setLanguageField('value', @array_map($htmlspecialcharsUtf_8, $value), $this->specFieldLocalesArray);
                    }


                    $specFieldValues->setFieldValue('specFieldID', $specField);
                    $specFieldValues->setFieldValue('position', $position);

                    $specFieldValues->save();

                    $position++;
                }
            }

            return new JSONResponse(array('status' => 'success', 'id' => $specFieldID));
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
        $config = $this->getSpecFieldConfig();
        $errors = array();

        if(!isset($values['name']) || empty($values['name'][$this->specFieldLocalesArray[0]]))
        {
            $errors['name'] = 'Name empty';
        }

        if(!isset($values['handle']) || preg_match('/[^\w\d_]/', $values['handle']))
        {
            $errors['handle'] = 'Handle contains invalid symbols';
        }

        if(!isset($values['handle']) || empty($values['handle']))
        {
            $errors['handle'] = 'Handle empty';
        }

        if(in_array($values['type'], $config['selectorValueTypes']) && isset($values['values']) && is_array($values['values']))
        {
            foreach ($values['values'] as $key => $v)
            {
                if(empty($v[$this->specFieldLocalesArray[0]]))
                {
                    $errors['values'][$key] = 'You are required to fill this field';
                }

                if($values['dataType'] == 2 && !is_numeric($v[$this->specFieldLocalesArray[0]]))
                {
                    $errors['values'][$key] = 'Field value should be a valid number';
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
        foreach($this->request->getValue('specField_items_list') as $position => $key)
        {
            if(!empty($key))
            {
                $specField = SpecField::getInstanceByID((int)$key);
                $specField->setFieldValue('position', (int)$position);
//                $specField->save();
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
//                $specField->save();
            }
        }

        return new JSONResponse(array('status' => 'success'));
    }

}