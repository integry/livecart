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

    public function index()
    {
        $response = new ActionResponse();
//		$this->setLayout("empty");


        $categoryID = 8;
        $category = Category::getInstanceByID($categoryID);
        $response->setValue('specFields', $category->getSpecFieldList());

        $defaultSpecFieldValues = array
        (
            'ID' => 'new',
            'name' => array(),
            'description' => array(),
            'handle' => '',
            'values' => Array(),
            'rootId' => 'specField_item_new',
            'type' => 3,
            'dataType' => 1,
            'categoryID' => $categoryID
        );
        $response->setValue('specFieldsList', $defaultSpecFieldValues);

        /**
         * Types:
         * 1 - numbers
         * 2 - text
         */
        $configuration = array(
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

       $response->setValue('configuration', $configuration);


        return $response;
    }

    public function add()
    {
//        $this->setLayout("categoryManager");
        $this->removeLayout();
//        $specField = array("name" => $this->request->getValue("name"), "description" => $this->request->getValue("description"));

        $response = new ActionResponse();
//        $response->setValue("specField", $specField);
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

       $specFieldList['rootId'] = "specField_items_list_".$specFieldList['ID'];

       $response->setValue('specFieldsList', $specFieldList);

       return $response;
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
                $specField->setFieldValue('categoryID', Category::getInstanceByID((int)$categoryID));
            }
        }
        else
        {
            $specField = SpecField::getInstanceByID((int)$this->request->getValue('ID'));
        }


        $dataType = (int)$this->request->getValue('dataType');
        $type = (int)$this->request->getValue('type');
        $categoryID = (int)$this->request->getValue('categoryID');

        $description = $this->request->getValue('description');
        $name = $this->request->getValue('name');
        $handle = $this->request->getValue('handle');
        $values = $this->request->getValue('values');

        if(count($errors = $this->validateSpecField($specField, $this->request->getValueArray(array('handle', 'values')))) == 0)
        {
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
                $position = 0;
                foreach ($values as $key => $value)
                {
                    if(preg_match('/^new_/', $key))
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
                        $specFieldValues->setLanguageField('value', @array_map($htmlspecialcharsUtf_8, $name), $this->specFieldLocalesArray);
                    }


//                    $specFieldValues->setFieldValue('position', $position);
                    $specFieldValues->setFieldValue('specFieldID', $specField[$this->specFieldLocalesArray[0]]);

                    $specFieldValues->save();
                    $position++;
                }
            }

//            return new RawResponse('1');
            return new RawResponse("<pre>".print_r($_POST, true)."</pre>");
        }
        else
        {
            return new JSONResponse($errors);
        }



    }



    private function validateSpecField($specField, $values = array())
    {
        $errors = array();

        if(preg_match('/[^\w\d_]/', $values['handle']))
        {
            $errors['handle'] = 'Handle contains invalid symbols';
        }

        if(!isset($values['values']) && !empty($values['values']))
        {
            foreach ($values['values'] as $key => $value)
            {
                if(!isset($value[$this->specFieldLocalesArray[0]]) || !is_numeric($value[$this->specFieldLocalesArray[0]]))
                {
                    $errors['values'][$key] = 'Field value should be a valid number';
                }
            }
        }
        return $errors;
    }


    /**
     * Removes a specification field and returns back to a field list
     *
     * @return ActionRedirectResponse
     */
    public function remove()
    {
        if ($this->request->isValueSet("id"))
        {
            SpecField::deleteByID($this->request->getValue("id"));
        }
        return new ActionRedirectResponse("specField", "index");
    }

    private function buildValidator()
    {
        ClassLoader::import("framework.request.validator.RequestValidator");
        $validator = new RequestValidator("specField", $this->request);

        $validator->addCheck("name", new IsNotEmptyCheck("You must enter your name"));
        $validator->addCheck("name", new MaxLengthCheck("Field name must not exceed 40 chars", 40));
        $validator->addCheck("type", new IsNotEmptyCheck("You must set a field type"));

        return $validator;
    }

    public function delete()
    {
        return new RawResponse('1');
    }
}



//        $validator = $this->buildValidator();
//        $validator->execute();
//        if ($validator->hasFailed())
//        {
//            $validator->saveState();
//            return new ActionRedirectResponse("backend.specField", "form");
//        }
//        else
//        {
//            if ($this->request->isValueSet("id"))
//            {
//                $specField = SpecField::getInstanceByID($this->request->getValue("id"));
//            }
//            else
//            {
//                $specField = SpecField::getNewInstance();
//            }
//
//            $langCode = $this->user->getActiveLang()->getID();
//            $category = Category::getInstanceByID($this->request->getValue("categoryID"));
//
//            $specField->lang($langCode)->name->set($form->getFieldValue('name'));
//            $specField->lang($langCode)->description->set($form->getFieldValue('description'));
//            $specField->category->set($category);
//            $specField->type->set($this->request->getValue("type"));
//            $specField->dataType->set($this->request->getValue("dataType"));
//            $specField->handle->set($this->request->getValue("handle"));
//            return new ActionRedirectResponse("backend.specField", "form", array("id" => $this->request->getValue('id')));
//        }

?>