<?php
ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.model.category.*");
ClassLoader::import("application.model.filter.*");
ClassLoader::import("library.*");

/**
 * ...
 *
 * @package application.controller.backend
 * @author Saulius Rupainis <saulius@integry.net>
 *
 * @role admin.store.catalog
 */
class FilterController extends StoreManagementController
{
    /**
     * Delete filter from database
     * 
     * @return JSONResponse
     */
    public function delete()
    {
        if($id = $this->request->getValue("id", false))
        {
            Filter::deleteByID($id);
            return new JSONResponse(array('status' => 'success'));
        }
        else
        {
            return new JSONResponse(array('status' => 'failure'));
        }
    }

    /**
     * Sort filters
     *
     * @return JSONResponse
     */
    public function sort()
    {
        foreach($this->request->getValue($this->request->getValue('target'), array()) as $position => $key)
        {
            // Except new fields, because they are not yet in database
            if(!empty($key) && !preg_match('/^new/', $key))
            {
                $filter = Filter::getInstanceByID((int)$key);
                $filter->setFieldValue('position', (int)$position);
                $filter->save();
            }
        }

        return new JSONResponse(array('status' => 'success'));
    }
    
    /**
     * Generate filters according to specification fields names and types
     *
     * @return JSONResponse
     */
    public function generate()
    {        
        $filters = array();   
        foreach(SpecField::getInstanceByID((int)$this->request->getValue('specFieldID'), true)->getValuesList() as $value) 
        {
            $filters[$value['ID']] = array('name' => $value['value'], 'specFieldValueID' => $value['ID']);
        }
        
        return new JSONResponse(array("status" => "success", "filters" => $filters));
    }
}

?>