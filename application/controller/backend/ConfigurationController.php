<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.model.product.*");
ClassLoader::import("library.AJAX_TreeMenu.*");	
ClassLoader::import("application.helper.json.*");
ClassLoader::import("library.DataGrid.*");

/**
 * Controller for handling product based actions performed by store administrators
 *
 * @package application.controller.backend
 * @role admin.store.product
 */
class ConfigurationController extends StoreManagementController {

	/**
	 * @return ActionResponse
	 */    
    public function index() {
		
		$configData = Configuration::getInstance()->getData();		

		$configFormData = Configuration::getDataFromFile();	
		
		$form = $this->createConfigForm($configFormData, $configData, $configData);
		$form->setAction(Router::getInstance()->createUrl(array("controller" => $this->request->getControllerName(), "action" => "save")));

		$response = new ActionResponse();	
		$response->setValue("form", @$form->render());			
		
		return $response;
	}

	/**
	 * Gets configuration values from form and saves them.
	 * @return ActionRedirectResposnse	 
	 */
	public function save() {

		$config = Configuration::getInstance();
		$configData = $config->getData();	
			  
	  	$configFormData = Configuration::getDataFromFile();		  
	  	$form = $this->createConfigForm($configFormData, $configData, $this->request->toArray());
		  
		foreach ($configData as $key => $value) {
		  
		  	$config->setValue($key, $form->getField($key)->getValue());	
		}
		
		$config->save();
		
		return new ActionRedirectResponse($this->request->getControllerName(), "index");  
	}	
	
	/**
	 * Adds configuration key/value pairs from config file. Removes not needed.
	 * @return ActionRedirectResponse
	 */
	public function update() {
	  
	  	$config = Configuration::getInstance();
	  	$config->updateFromFile();
		return new ActionRedirectResponse($this->request->getControllerName(), "index");    
	}
	
	/**
	 * Create form.
	 * @return Form
	 */
	private function createConfigForm($configFormData, $configData, $initialData) {
	  
	  	ClassLoader::import("library.formhandler.*");	 
	  	ClassLoader::import("library.formhandler.filter.*"); 	  		 	
		$form = new Form("configForm", $initialData);					

		//print_R($configData);
		//print_R($configFormData);
		foreach ($configFormData as $key => $value) {

			if (!array_key_exists($value['key'], $configData)) {
				
				//echo $value['key'];
			  	continue;
			} 

		  	if (!isSet($sect) || $configFormData[$key]['category'] != $configFormData[$key - 1]['category']) {
						
				$sect = new Section($configFormData[$key]['category']);						
				$form->addSection($sect);				    
			}
			
			switch ($value['type']) {
			  	
			  	case 'string':
			
					$field = new TextLineField($value['key'], $this->locale->translate($value['key']));	
					$field->setAttribute("style", 'width: 400px');						
			  	break;
			  	
			  	case 'integer':
			  	
			  		$field = new TextLineField($value['key'], $this->locale->translate($value['key']));	
			  		$field->addCheck(new IsIntegerCheck($this->locale->translate("_checkInteger")));
					$field->addFilter(new TrimFilter());
					$field->setAttribute("style", 'width: 50px');									
			  	break;			  
			  	
			  	case 'numeric':
			  	
			  		$field = new TextLineField($value['key'], $this->locale->translate($value['key']));				  		
					$field->addCheck(new IsNumericCheck($this->locale->translate("_checkNumeric"), true));	
					$field->addFilter(new NumericFilter());
					$field->setAttribute("style", 'width: 50px');									
			  	break;
			  	
			  	case 'bool':

				  	$field = new CheckboxField($value['key'], $this->locale->translate($value['key']), "", 1);			  		
			  	break;
			  	
			  	case 'list':
			  		
			  		$field = new SelectField($value['key'], $this->locale->translate($value['key']));
					
					foreach ($value['items'] as $item) {

						$field->addValue($item, $this->locale->translate($item));
					}
					$field->setValue($value['value']);
			  	break;
			}
			
			$sect->addField($field);			  				
		}
		
		if (isSet($sect)) {
		
			$sect->addField(new SubmitField("submit", "Save"));	
		}
		
		return $form;
	}

}



?>