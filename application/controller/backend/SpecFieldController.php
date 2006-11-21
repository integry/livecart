<?php
ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
//ClassLoader::import("application.model.product.SpecField");

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

        ClassLoader::import("application.model.category.*");
        $category = Category::getInstanceByID(8);
        $response->setValue('specFields', $category->getSpecFieldList());



        $configuration = array(
            'languages' => array (
                'en' => 'English',
                'lt' => 'Lithuanian',
                'de' => 'German'
            ),

            'types' => array
            (
                'numbers' => array
                (
                    'selector' => 'Selector',
                    'numbers' => 'Numbers'
                ),
                'text' => array
                (
                    'text' => 'Text',
                    'wordProcesser' => 'Word processer',
                    'selector' => 'selector',
                    'date' => 'Date'
                )
            ),

            'messages' => array
            (
                'deleteField' => 'delete field'
            ),

            'selectorValueTypes' => array ('_selector', 'selector'),
            'doNotTranslateTheseValueTypes' => array('numbers'),
            'countNewValues' => 0
        );
//
//
//
//        $specFieldsList[96] = array
//        (
//            'id'                => 96,
//            'rootId'         => 'specField_items_list_96',
//            'handle'            => 'field1',
//
//            'type'              => '_selector',
//            'valueType'         => 'text',
//            'multipleSelector'  => true,
//
//            'translations'      => array
//            (
//                'en' => array('title' => 'WiFi', 'description' => 'Wireless internet'),
//                'lt' => array('title' => 'WiFi', 'description' => 'Bevivielis internetas'),
//                'de' => array('title' => 'WiFi', 'description' => 'Wirelichtinterneten')
//            ),
//
//            'values' => array
//            (
//                '1' => array('en' => 'Yes', 'lt' => 'Yra',  'de' => 'Ya'),
//                '2' => array('en' => 'No',  'lt' => 'Nera', 'de' => 'Nicht')
//            )
//        );
//
//    	$specFieldsList[95] = array
//    	(
//    		'id'              => 95,
//            'rootId'       => 'specField_items_list_95',
//    		'handle'          =>'manufacter',
//
//    		'valueType'       => 'text',
//    		'type'            => 'text',
//
//    		'translations' => array(
//    			'en' => array('title' => 'Manufacter',		'description' => 'Apple, Assus, Lenovo etc'),
//    			'lt' => array('title' => 'Gamyntojas',		'description' => 'Apple, Assus, Lenovo ir kiti'),
//    			'de' => array('title' => 'Machtengiher',		'description' => 'Apple, Assus, Lenovo und fuhr')
//    		)
//    	);
//
//    	$specFieldsList[100] = array
//    	(
//    		'id'              => 100,
//            'rootId'       => 'specField_items_list_100',
//    		'handle'          =>'field1',
//
//    		'type'            =>'text',
//    		'valueType'       =>'text',
//
//    		'translations' => array(
//    			'en' => array('title' => 'Other features',		'description' => 'Other features'),
//    			'lt' => array('title' => 'Kiti navorotai',		'description' => 'Kiti navorotai'),
//    			'de' => array('title' => 'Blachen fileich',		'description' => 'Blachen fileich')
//    		)
//    	);
//
//    	$specFieldsList[102] = array
//    	(
//            'id'            => 102,
//            'rootId'     => 'specField_items_list_102',
//            'handle'        => 'field1',
//
//            'type'          => '_selector',
//            'valueType'     => 'text',
//
//            'translations'  => array(
//            	'en' => array('title' => 'Pressent',               'description' => 'You will get a pressent when you buy this product'),
//            	'lt' => array('title' => 'Dovana',                 'description' => 'Gausite dovana perkant si produkta'),
//            	'de' => array('title' => 'Preshentwirdshihtceit',  'description' => 'Present mit bhot das kein!')
//            ),
//
//            'values' => array(
//            	'45' => array('en' => 'TV tunner',         'lt' => 'TV tuneris',       'de' => 'TV thuner'),
//            	'46' => array('en' => 'Ultraslim',         'lt' => 'Super plonas',     'de' => 'Shicht'),
//            	'47' => array('en' => 'Life time waranty', 'lt' => 'Amzina garantiha', 'de' => 'Das gluklich garantee')
//            )
//    	);
//
//
//
//
//    	foreach ($specFieldsList as $f)
//    	{
//    	    foreach(array('en', 'lt', 'de') as $ln)
//    	    {
//    	        $name[$ln] = $f['translations'][$ln]['title'];
//    	        $description[$ln] = $f['translations'][$ln]['description'];
//    	    }
//
////    	    echo "INSERT INTO SpecField (`id`, `handle`, `type`, `dataType`, `name`, `description`) VALUES({$f['id']}, '{$f['handle']}', '{$f['type']}', '{$f['valueType']}', '".addslashes(serialize($name))."', '".addslashes(serialize($description))."');<br />";
//
//            if(isset($f['values']))
//            {
//        	    foreach($f['values'] as $id => $v)
//        	    {
//        	        $values = array();
//            	    foreach(array('en', 'lt', 'de') as $ln)
//            	    {
//            	        $values[$ln] = $v[$ln];
//            	    }
//
////        	        echo "INSERT INTO SpecFieldValue (id, specFieldID, `value`) VALUES($id, {$f['id']}, '".addslashes(serialize($values))."');<br />";
//        	    }
//            }
//    	}


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
       echo "<pre>".print_r(SpecField::getInstanceByID($this->request->getValue('id'), true)->toArray(), true)."</pre>";
       $response->setValue('specFieldsList', SpecField::getInstanceByID($this->request->getValue('id'))->toArray());
       return $response;

//        ClassLoader::import("framework.request.validator.Form");
//        $systemLangList = array("lt" => "Lietuvių", "de" => "Deutch");
//        $specFieldTypeList = array("1" => "Text Field", "2" => "Checkbox", "3" => "Select field");
//        $form = new Form($this->buildValidator());
//
//        if ($this->request->isValueSet("id"))
//        {
//            ClassLoader::import("application.model.product.SpecField");
//            $specField = SpecField::getInstanceByID($this->request->getValue("id"), SpecField::LOAD_DATA);
//            $form->setData($specField->toArray());
//        }
//
//        $specFieldList = array(array("name" => "test", "description" => "test"), array("name" => "another item", "description" => "one more..."));
//
//        $response = new ActionResponse();
//
//        $response->setValue("specFieldList", $specFieldList);
//
//        $response->setValue("specFieldForm", $form);
//        $response->setValue("systemLangList", $systemLangList);
//        $response->setValue("typeList", $specFieldTypeList);
//        return $response;
    }

    /**
     * Creates a new or modifies an exisitng specification field (according to a passed parameters)
     *
     * @return ActionRedirectResponse Redirects back to a form if validation fails or to a field list
     */
    public function save()
    {
        $validator = $this->buildValidator();
        $validator->execute();
        if ($validator->hasFailed())
        {
            $validator->saveState();
            return new ActionRedirectResponse("backend.specField", "form");
        }
        else
        {
            if ($this->request->isValueSet("id"))
            {
                $specField = SpecField::getInstanceByID($this->request->getValue("id"));
            }
            else
            {
                $specField = SpecField::getNewInstance();
            }

            $langCode = $this->user->getActiveLang()->getID();
            $category = Category::getInstanceByID($this->request->getValue("categoryID"));

            $specField->lang($langCode)->name->set($form->getFieldValue('name'));
            $specField->lang($langCode)->description->set($form->getFieldValue('description'));
            $specField->category->set($category);
            $specField->type->set($this->request->getValue("type"));
            $specField->dataType->set($this->request->getValue("dataType"));
            $specField->handle->set($this->request->getValue("handle"));
            return new ActionRedirectResponse("backend.specField", "form", array("id" => $this->request->getValue('id')));
        }
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

?>