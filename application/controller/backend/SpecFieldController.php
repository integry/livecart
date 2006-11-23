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
    public function index()
    {
        $response = new ActionResponse();
//		$this->setLayout("empty");

        $defaultLanguage = $this->locale->getCurrentLocale();



        $category = Category::getInstanceByID(8);
        $response->setValue('specFields', $category->getSpecFieldList());

        $defaultSpecFieldValues = array
        (
            'ID' => 'new',
            'name' => array(),
            'description' => array(),
            'handle' => '',
            'values' => Array(),
            'rootId' => 'specField_item_new',
            'type' => 5,
            'dataType' => 2
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
       $specFieldList = SpecField::getInstanceByID($this->request->getValue('id'), true, true)->toArray();

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
        $defaultLocale = 'en';

        if($this->request->getValue('ID') == 'new')
        {
            $specField = SpecField::getNewInstance();
        }
        else
        {
            $specField = SpecField::getInstanceByID((int)$this->request->getValue('ID'));
        }


/*        $specField->getSchemaInstance('SpecField');*/

        $dataType = $this->request->getValue('dataType');
        $type = $this->request->getValue('type');
        $description = $this->request->getValue('description');
        $name = $this->request->getValue('name');
        $handle = $this->request->getValue('handle');


        if(count($errors = array('handle' => 'blablabla') /*$this->validateSpecField($specField, $dataType, $type, $description, $name, $handle) */) == 0)
        {
            $htmlspecialcharsUtf_8 = create_function('$val', 'return htmlspecialchars($val, null, "UTF-8");');

            $specField->setFieldValue('dataType', (int)$this->request->getValue('dataType'));
            $specField->setFieldValue('type', (int)$this->request->getValue('type'));
            $specField->setFieldValue('handle', preg_replace('[^\w\d_]', '_', $this->request->getValue('handle')));
            $specField->setLanguageField('description', @array_map($htmlspecialcharsUtf_8, $description), array('en', 'lt', 'de'));
            $specField->setLanguageField('name',        @array_map($htmlspecialcharsUtf_8, $name),        array('en', 'lt', 'de'));

            $specField->save();

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
        $schema = $specField->getSchemaInstance('SpecField');
        $validTypes = array(
                1 => array(3, 4, 5, 6),
                2 => array(1, 2)
        );


        if(!isset($values['dataType']) || !isset($validTypes[$values['dataType']]) || !in_array($values['type'], $validTypes[$values['dataType']]))
        {
            $errors['type'] = 'invalid data type';
        }

        if(!isset($values['handle']) || strlen($values['handle']) > $schema->getField('handle')->getDataType()->getLength())
        {
             $errors['handle'] = 'Handle is too long';
        }

        if(!isset($values['name']) || strlen($values['name']) > $schema->getField('name')->getDataType()->getLength())
        {
             $errors['name'] = 'Name is too long';
        }

        if(!isset($values['description']) || strlen($values['description']) > $schema->getField('description')->getDataType()->getLength())
        {
             $errors['description'] = 'Description is too long';
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