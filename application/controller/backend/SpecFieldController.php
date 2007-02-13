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
 * @role admin.store.category
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

        $response->setValue('specFieldsWithGroups', $category->getSpecificationFieldArray(false, true, true));

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
                return new JSONResponse(array('errors' => array('ID' => $this->translate('_error_record_id_is_not_valid')), 'status' => 'failure', 'ID' => (int)$this->request->getValue('ID')));
            }
        }

        $this->getSpecFieldConfig();
        if(count($errors = SpecField::validate($this->request->getValueArray(array('handle', 'values', 'name', 'type', 'dataType', 'categoryID', 'ID')), $this->specFieldConfig['languageCodes'])) == 0)
        {
            $type = $this->request->getValue('advancedText') ? SpecField::TYPE_TEXT_ADVANCED : (int)$this->request->getValue('type');
            $dataType = SpecField::getDataTypeFromType($type);
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
            $specField->setLanguageField('description', $description, $this->specFieldConfig['languageCodes']);
            $specField->setLanguageField('name',        $name,        $this->specFieldConfig['languageCodes']);
            $specField->save();  
                     
            try
            {
                if(is_array($values)) $specField->saveValues($values, $type, $this->specFieldConfig['languageCodes']);
            }
            catch(Exception $e)
            { 
            }
            
            return new JSONResponse(array('status' => 'success', 'id' => $specField->getID()));
        }
        else
        {
            return new JSONResponse(array('errors' => $this->translateArray($errors), 'status' => 'failure'));
        }
    }
    
    /**
     * Delete specification field from database
     * 
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
}
