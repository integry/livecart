<?php
ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.model.category.*");
ClassLoader::import("library.*");

/**
 * Category specification field ("extra field") controller
 *
 * @package application.controller.backend
 * @author Saulius Rupainis <saulius@integry.net>
 * @author Sergej Andrejev <sandrejev@gmail.com>
 * @role category
 */
class SpecFieldController extends StoreManagementController
{
    /**
     * Configuration data
     * 
     * @see self::getSpecFieldConfig
     * @var array
     */
    protected $specFieldConfig = array();

    /**
     * Specification field index page
     * 
     * @return ActionResponse
     */
    public function index()
    {
        $response = new ActionResponse();

        $categoryID = (int)$this->request->getValue('id');
        $category = Category::getInstanceByID($categoryID);

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
        $response->setValue('configuration', $this->getSpecFieldConfig());
        $response->setValue('specFieldsList', $defaultSpecFieldValues);
        $response->setValue('defaultLangCode', $this->store->getDefaultLanguageCode());
        $response->setValue('specFieldsWithGroups', $category->getSpecFieldsWithGroupsArray());

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
		   $specFieldList['values'][$value['ID']] = $value;
		}
		
		$specFieldList['categoryID'] = $specFieldList['Category']['ID'];
		unset($specFieldList['Category']);
        
		return new JSONResponse($specFieldList);
    }
    
    /**
     * @role update
     */
    public function update()
    {
        if(SpecField::exists((int)$this->request->getValue('ID')))
        {
            $specField = SpecField::getInstanceByID((int)$this->request->getValue('ID'));
        }
        else
        {
            return new JSONResponse(array('errors' => array('ID' => $this->translate('_error_record_id_is_not_valid')), 'status' => 'failure', 'ID' => (int)$this->request->getValue('ID')));
        }
        
        return $this->save($specField);
    }
    
    /**
     * @role update
     */
    public function create()
    {
        $specField = SpecField::getNewInstance(Category::getInstanceByID($this->request->getValue('categoryID', false)));
        
        return $this->save($specField);
    }
    
    /**
     * Creates a new or modifies an exisitng specification field (according to a passed parameters)
     *
     * @return JSONResponse Returns success status or failure status with array of erros
     */
    private function save(SpecField $specField)
    {
        $this->getSpecFieldConfig();
		$errors = SpecField::validate($this->request->getValueArray(array('handle', 'values', 'name_' . $this->specFieldConfig['languageCodes'][0], 'type', 'dataType', 'categoryID', 'ID')), $this->specFieldConfig['languageCodes']);
		
        if(!$errors)
        {
            $type = $this->request->getValue('advancedText') ? SpecField::TYPE_TEXT_ADVANCED : (int)$this->request->getValue('type');
            $dataType = SpecField::getDataTypeFromType($type);
            $categoryID = (int)$this->request->getValue('categoryID');

            $handle = $this->request->getValue('handle');
            $values = $this->request->getValue('values');
            
            $isMultiValue = $this->request->getValue('multipleSelector') == 1 ? 1 : 0;
            $isRequired = $this->request->getValue('isRequired') == 1 ? 1 : 0;
            $isDisplayed = $this->request->getValue('isDisplayed') == 1 ? 1 : 0;
            $isDisplayedInList = $this->request->getValue('isDisplayedInList') == 1 ? 1 : 0;

            $specField->setFieldValue('dataType',          $dataType);
            $specField->setFieldValue('type',              $type);
            $specField->setFieldValue('handle',            $handle);
            
            $specField->setFieldValue('isMultiValue',      $isMultiValue);
            $specField->setFieldValue('isRequired',        $isRequired);
            $specField->setFieldValue('isDisplayed',       $isDisplayed);
            $specField->setFieldValue('isDisplayedInList', $isDisplayedInList);
            
			foreach($this->store->getLanguageArray(true) as $langCode) 
			{
			    $specField->setValueByLang('name', $langCode, $this->request->getValue('name_' . $langCode));
			    $specField->setValueByLang('valueSuffix', $langCode, $this->request->getValue('valueSuffix_' . $langCode));
			    $specField->setValueByLang('valuePrefix', $langCode, $this->request->getValue('valuePrefix_' . $langCode));
			    $specField->setValueByLang('description', $langCode, $this->request->getValue('description_' . $langCode));
			}
            
            $specField->save();  
                     
            // save specification field values in database
            $newIDs = array();
			if($specField->isSelector() && is_array($values)) 
        	{
		        $position = 1;
		        $countValues = count($values);
		        $i = 0;
		        foreach ($values as $key => $value)
		        {
		            $i++;

		            // If last new is empty miss it
	                if($countValues == $i && preg_match('/new/', $key) && empty($value[$this->specFieldConfig['languageCodes'][0]]))
	                {
	                    continue;
	                }

		            if(preg_match('/^new/', $key))
		            {
		                $specFieldValues = SpecFieldValue::getNewInstance($specField);
		            }
		            else
		            {
		               $specFieldValues = SpecFieldValue::getInstanceByID((int)$key);
		            }
		
		            if(SpecField::TYPE_NUMBERS_SELECTOR == $type)
		            {
		                $specFieldValues->setFieldValue('value', $value);
		            }
		            else
		            {
		                $specFieldValues->setLanguageField('value', $value, $this->specFieldConfig['languageCodes']);
		            }
		
		            $specFieldValues->setFieldValue('position', $position++);
		            $specFieldValues->save();
		            
       	            if(preg_match('/^new/', $key))
		            {
		                $newIDs[$specFieldValues->getID()] = $key;
		            }
		        }        
			}
            
			
			
            return new JSONResponse(array('status' => 'success', 'id' => $specField->getID(), 'newIDs' => $newIDs));
        }
        else
        {
            return new JSONResponse(array('errors' => $this->translateArray($errors), 'status' => 'failure'));
        }
    }
    
    /**
     * Delete specification field from database
     * 
     * @role update
     * @return JSONResponse
     */
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

    /**
     * Sort specification fields
     * 
     * @role update
     * @return JSONResponse
     */
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
    

    /**
     * Create and return configurational data. If configurational data is already created just return the array
     * 
     * @see self::$specFieldConfig
     * @return array
     */
    private function getSpecFieldConfig()
    {
        if(!empty($this->specFieldConfig)) return $this->specFieldConfig;
        
        $languages[$this->store->getDefaultLanguageCode()] =  $this->locale->info()->getOriginalLanguageName($this->store->getDefaultLanguageCode());
        foreach ($this->store->getLanguageList()->toArray() as $lang)
        {
            if($lang['isDefault'] != 1)
            {
                $languages[$lang['ID']] = $this->locale->info()->getOriginalLanguageName($lang['ID']);
            }
        }

        $this->specFieldConfig = array(
            'languages' => $languages,
            'languageCodes' => array_keys($languages),
            'messages' => array
            (
                'deleteField' => $this->translate('_delete_field'),
                'removeFieldQuestion' => $this->translate('_remove_field_question')
            ),

            'selectorValueTypes' => SpecField::getSelectorValueTypes(),
            'doNotTranslateTheseValueTypes' => array(2),
            'countNewValues' => 0
        );
        
        return $this->specFieldConfig;
    }
    
}
