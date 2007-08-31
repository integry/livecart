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
 * @role filter
 */
class FilterController extends StoreManagementController
{
    /**
     * Delete filter from database
     * 
     * @role update
     * 
     * @return JSONResponse
     */
    public function delete()
    {
        if($id = $this->request->get("id", false))
        {
            Filter::deleteByID($id);
            return new JSONResponse(false, 'success');
        }
        else
        {
            return new JSONResponse(false, 'success', $this->translate('_could_not_remove_filter_group'));
        }
    }

    /**
     * Sort filters
     *
     * @role update
     * 
     * @return JSONResponse
     */
    public function sort()
    {
        foreach($this->request->get($this->request->get('target'), array()) as $position => $key)
        {
            // Except new fields, because they are not yet in database
            if(!empty($key) && !preg_match('/^new/', $key))
            {
                $filter = Filter::getInstanceByID((int)$key);
                $filter->setFieldValue('position', (int)$position);
                $filter->save();
            }
        }

        return new JSONResponse(false, 'success');
    }
    
    /**
     * Generate filters according to specification fields names and types
     *
     * @role update
     * 
     * @return JSONResponse
     */
    public function generate()
    {        
        $filters = array();   
        foreach(SpecField::getInstanceByID((int)$this->request->get('specFieldID'), true)->getValuesList() as $value) 
        {
            $filters[$value['ID']] = array('name' => $value['value'], 'specFieldValueID' => $value['ID']);
        }
        
        return new JSONResponse(array("filters" => $filters), 'success', $this->translate('_filters_were_successfully_generated'));
    }
}

?>