<?php

ClassLoader::import("application.controller.backend.abstract.SiteManagementController");
ClassLoader::import("application.model.*");
ClassLoader::import("application.model.Locale.*");
/**
 * 
 * @package application.controller.backend
 */
class LanguageController extends SiteManagementController {
		
	protected function languageSectionBlock() {
		
		$list =	Language::getLanguages(1);
		
		$response =	new BlockResponse();
		$response->SetValue("languagesList", $list->toArray());				

		$response->SetValue("language", $this->request->getValue("language"));
		$response->SetValue("controller", $this->request->getValue("controller"));
		$response->SetValue("action", $this->request->getValue("action"));
		$response->SetValue("id", $this->request->getValue("id"));	
		
		return $response;
	}	
	
	public function init() {
		
		parent::init();
		$this->addBlock("NAV", "languageSection");		
	}
	
	/**
	 * Gets definitions from project files and updates them in database. 
	 * @return ActionResponse
	 */
	public function update() {
		 		 
		$path = ClassLoader::getRealPath("application.language_definitions")."\\";

	  	$setup = new LanguageSetup($path);
	    $setup->updateFromFiles();
	    	    
	    $response = new ActionResponse();
		return $response;
	}
	
	/**
	 * Displays definitions editing page.
	 */
	public function edit() {

	  	// Reading
	  	$en_locale = Locale::getInstance("en");	
	  	
		$edit_locale_name = $this->request->getValue("id");	
	  	$edit_locale = Locale::getInstance($edit_locale_name);	 
	  	
		$files = $edit_locale->getDefinitionFiles(".lng");							

		$selected_all = '';
		$selected_defined = '';
		$selected_not_defined = '';
		if ($this->request->isValueSet("file")) {
		  
		 	$file = $this->request->getValue("file");		 	
		 	switch ($this->request->getValue("show")) {
			   
				case 'all':
				
					$selected_all = 'checked';
				break;
				
				case 'defined':
				
					$selected_defined = 'checked';
				break;
				
				case 'not_defined':
				
					$selected_not_defined = 'checked';
				break;
			}
		} else {
		  
		  	$file = key($files);
		  	$selected_all = 'checked';	
		}
		
		$definitions = $edit_locale->getDefinitionsFromFile($file);				
		$definitions_shown = array();
		foreach ($definitions as $key => $value) {
		  
		  	if ($selected_all ||
		  		$selected_defined && !empty($value) ||
		  		$selected_not_defined && empty($value[$key])) {
		  				  		
			 	$definitions_shown[$key] = $value;
			}
		}				
		
		//  Saving
		if ($this->request->isValueSet("event") && $this->request->getValue("event") == "save") {
			
			$full = &$edit_locale->getFullDefinitionsArray();
					  	
			foreach ($definitions_shown as $key => $value) {
				
				//magic_quotes_gpc = On				
				$full[$key][Locale::value] = stripslashes($this->request->getValue("lang_".$key)); 
				//magic_quotes_gpc = Off				
				//$full[$key][Locale::value] = ($this->request->getValue("lang_".$key)); 
			}	
			
			/*$lang = interfaceTranslation::getInstanceByCode($edit_locale_name);			
			$lang->defs->Set(addslashes(serialize($full)));						
			$lang->Save();	*/
			$data = interfaceTranslation::getInstanceById("interfaceTranslation", array("ID" => $edit_locale_name));
			$data->interfaceData->set(addslashes(serialize($full)));
			$data->save();
									
			$definitions = $edit_locale->getDefinitionsFromFile($file);	
			$definitions_shown = array();
			foreach ($definitions as $key => $value) {
		  
			  	if ($selected_all ||
			  		$selected_defined && !empty($value) ||
			  		$selected_not_defined && empty($value[$key])) {
			  				  		
				 	$definitions_shown[$key] = $value;
				}
			}					
		}					
		///		
			  		  		  	
	  	$response = new ActionResponse();	  	  		  		
		
		$response->SetValue("language", $this->request->getValue("language"));
		$response->setValue("id", $edit_locale_name);	
		
	//	$response->setValue("edit_language", $this->locale->getLanguage($edit_locale_name));
		$response->setValue("files", $files);		
		$response->setValue("file", $file);		
				
		$response->setValue("en_definitions", $en_locale->getDefinitionsFromFile($file));					
		$response->setValue("definitions", $definitions_shown);		
		
		//	echo $selected_all;
		$response->setValue("selected_all", $selected_all);		
		$response->setValue("selected_defined", $selected_defined);
		$response->setValue("selected_not_defined", $selected_not_defined);	
	  	
	  	return $response;
	}
	
	/**
	 * Displays main admin page.
	 */
	public function index() {
	  	
		$languages_select = $this->locale->getLanguages();
	  	
	  	$list =	Language::getLanguages()->toArray();							
	  	$count_active = 0;
		foreach ($list as $key => $value) {

			if ($value["isEnabled"] == 1) {
			  	
			  	$count_active ++;
			}  
			//unset($languages_select[$value['code']]);
		  	$list[$key]['name'] = $this->locale->getLanguage($value['ID']);		  	
		}		
		    
	  	$response = new ActionResponse();			  	
		
		$response->SetValue("language", $this->request->getValue("language"));		
		$response->SetValue("languagesList", $list);		
		$response->SetValue("languages_select", $languages_select);					
		$response->SetValue("count_all", count($list));		
		$response->SetValue("count_active", $count_active);		
		
		/*for ($i = 1; $i < 200; $i ++) {
			
			$number[] = $i;					
		}*/
				
		//$response->SetValue("number", $number);				
		return $response;
	}
	
	/**
	 * Displays variuos information.
	 * @return ActionResponse
	 */
	public function information() {
		
		$locale = Locale::GetInstance($this->localeName);		
		$response = new ActionResponse();				

		//$masyvas["I18Nv2::getInfo()"] = I18Nv2::getInfo();
		$masyvas["\$locale->GetCountries()"] = $locale->GetCountries();
		$masyvas["\$locale->GetLanguages()"] = $locale->GetLanguages();
		$masyvas["\$locale->GetCurrencies()"] = $locale->GetCurrencies();
			
		$response->SetValue("masyvas", $masyvas);								
		return $response;
	}
	
	/**
	 * @todo Perdaryti siuo metu neveikia
	 */
	public function add() {

		if ($this->request->isValueSet("new_language")) {	  	  
					
			Language::add($this->request->getValue("new_language"));		  					  	
		}
		
	  	return new ActionRedirectResponse($this->request->getControllerName(), "index", array());  
	}
	
	/**
	 * Sets if language is enabled
	 * @return ActionRedirectResponse
	 */
	public function setEnabled() {
	  
	  	if ($this->request->isValueSet("change_active")) {	  	  
	
			Language::setEnabled($this->request->getValue("change_active"), $this->request->getValue("change_to"));		  					  	
		}
		
		$array = array("id" => 0);
		if ($this->request->isValueSet("language")) {
		  
		  	$array["language"] = $this->request->getValue("language");		  
		}
	  	return new ActionRedirectResponse($this->request->getControllerName(), "index", $array); 	  	
	}
	
	/**
	 * Sets default language.
	 * @return ActionRedirectResponse
	 */
	public function setDefault() {
	  
	  	if ($this->request->isValueSet("change_to")) {	  	  
						
			Language::setDefault($this->request->getValue("change_to"));		  					  	
		}
		
		$array = array("id" => 0);
		if ($this->request->isValueSet("language")) {
		  
		  	$array["language"] = $this->request->getValue("language");		  
		}
	  	return new ActionRedirectResponse($this->request->getControllerName(), "index", $array); 
	}

}

?>