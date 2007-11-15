<?php
ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.model.category.*");
ClassLoader::import("library.*");

/**
 * Category specification group controller
 *
 * @package application.controller.backend
 * @author	Integry Systems
 * @role category
 */
class SpecFieldGroupController extends StoreManagementController
{
	/**
	 * Array of available language codes. First language code is default language
	 * 
	 * @var array
	 */
	protected $languageCodes = array();

	/**
	 * Get specification field group data
	 * 
	 * @return JSONResponse
	 */
	public function item()
	{
		return new JSONResponse(SpecFieldGroup::getInstanceByID((int)$this->request->get('id'), true)->toArray(false));
	}
	
	/**
	 * @role update
	 */
	public function update()
	{
		$specFieldGroup = SpecFieldGroup::getInstanceByID((int)$this->request->get('id'));
		
		return $this->save($specFieldGroup);
	}
	
	/**
	 * @role update
	 */
	public function create()
	{
		$category = Category::getInstanceByID((int)$this->request->get('categoryID'));
		$specFieldGroup = SpecFieldGroup::getNewInstance($category);
		$specFieldGroup->setFieldValue('position', 100000);
		
		return $this->save($specFieldGroup);
	}
	
	/**
	 * Delete specification field group from database
	 *
	 * @role update
	 * 
	 * @return JSONResponse Status
	 */
	public function delete()
	{
		if($id = $this->request->get("id", false))
		{
			SpecFieldGroup::deleteById($id);
			return new JSONResponse(false, 'success');
		}
		else
		{
			return new JSONResponse(false, 'failure', $this->translate('_could_not_remove_specfield_group'));
		}
	}
   
	/**
	 * Sort specification groups
	 * 
	 * @role update
	 * 
	 * @return JSONResponse Status
	 */
	public function sort()
	{
		foreach($this->request->get($this->request->get('target'), array()) as $position => $key)
		{
			// Except new fields, because they are not yet in database
			$group = SpecFieldGroup::getInstanceByID((int)$key);
			$group->setFieldValue('position', (int)$position);
			$group->save();
		}

		return new JSONResponse(false, 'success');
	}
	
	/**
	 * Save group data to the database
	 * 
	 * @return JSONResponse Returns status and errors if status is equal to failure
	 */
	private function save(SpecFieldGroup $specFieldGroup)
	{		  
		$this->createLanguageCodes();
		if(count($errors = $this->validate($this->request->getValueArray(array("name_{$this->languageCodes[0]}")), $this->languageCodes)) == 0)
		{
			foreach($this->application->getLanguageArray(true) as $langCode) 
			{
				$specFieldGroup->setValueByLang('name', $langCode, $this->request->get('name_' . $langCode));
			}
			
			$specFieldGroup->save();
			
			return new JSONResponse(array('id' => $specFieldGroup->getID()), 'success');
		}
		else
		{
			return new JSONResponse(array('errors' => $this->translateArray($errors)), 'failure', $this->translate('_could_not_save_specification_group'));
		}
	}

	/**
	 * Create array of language codes
	 *
	 * @see self::$languageCodes
	 */
	private function createLanguageCodes()
	{
		if(!empty($this->languageCodes)) return true;
		
		$this->languageCodes[] = $this->application->getDefaultLanguageCode();
		foreach ($this->application->getLanguageList()->toArray() as $lang)
		{
			if($lang['isDefault'] != 1) $this->languageCodes[] = $lang['ID'];
		}
	}
		
	/**
	 * Validate submitted specification group
	 *
	 * @param unknown_type $values
	 * @param unknown_type $config
	 * @return unknown
	 */
	private function validate($values = array(), $languageCodes)
	{
		$errors = array();
		
		if(!isset($values["name_{$languageCodes[0]}"]) || $values["name_{$languageCodes[0]}"] == '')
		{
			$errors["name_{$languageCodes[0]}"] = '_error_you_should_provide_default_group_name';
		}
		
		return $errors;
	}	
}