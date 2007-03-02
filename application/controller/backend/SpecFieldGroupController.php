<?php
ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.model.category.*");
ClassLoader::import("library.*");

/**
 * Category specification group controller
 *
 * @package application.controller.backend
 * @author Sergej Andrejev <sergej@gmail.net>
 * @role admin.store.category
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
     * Create array of language codes
     *
     * @see self::$languageCodes
     */
    private function createLanguageCodes()
    {
        if(!empty($this->languageCodes)) return true;
        
        $this->languageCodes[] = $this->store->getDefaultLanguageCode();
        foreach ($this->store->getLanguageList()->toArray() as $lang)
        {
            if($lang['isDefault'] != 1) $this->languageCodes[] = $lang['ID'];
        }
    }
    
    /**
     * Get specification field group data
     * 
     * @return JSONResponse
     */
    public function item()
    {
        return new JSONResponse(SpecFieldGroup::getInstanceByID((int)$this->request->getValue('id'), true)->toArray(false, false));
    }
    
    /**
     * Save group data to the database
     * 
     * @return JSONResponse Returns status and errors if status is equal to failure
     */
    public function save()
    {   
        if($id = $this->request->getValue('id'))
        {
            $specFieldGroup = SpecFieldGroup::getInstanceByID($id);
        }
        else
        {
            $category = Category::getInstanceByID((int)$this->request->getValue('categoryID'));
            $specFieldGroup = SpecFieldGroup::getNewInstance();
            $specFieldGroup->setFieldValue('categoryID', $category);
            $specFieldGroup->setFieldValue('position', 100000);
        }
        
        $this->createLanguageCodes();
        if(count($errors = SpecFieldGroup::validate($this->request->getValueArray(array('name')), $this->languageCodes)) == 0)
        {
            $name = $this->request->getValue('name');
            $specFieldGroup->setLanguageField('name', $name, $this->languageCodes);
            $specFieldGroup->save();
            
            return new JSONResponse(array('status' => 'success', 'id' => $specFieldGroup->getID()));
        }
        else
        {
            return new JSONResponse(array('errors' => $this->translateArray($errors), 'status' => 'failure'));
        }
    }
    
    /**
     * Delete specification field group from database
     *
     * @return JSONResponse Status
     */
    public function delete()
    {
        if($id = $this->request->getValue("id", false))
        {
            SpecFieldGroup::deleteById($id);
            return new JSONResponse(array('status' => 'success'));
        }
        else
        {
            return new JSONResponse(array('status' => 'failure'));
        }
    }
    
    /**
     * Sort specification groups
     * 
     * @return JSONResponse Status
     */
    public function sort()
    {
        foreach($this->request->getValue($this->request->getValue('target'), array()) as $position => $key)
        {
            // Except new fields, because they are not yet in database
            $group = SpecFieldGroup::getInstanceByID((int)$key);
            $group->setFieldValue('position', (int)$position);
            $group->save();
        }

        return new JSONResponse(array('status' => 'success'));
    }
}
