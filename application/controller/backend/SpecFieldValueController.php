<?php
ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.model.category.*");
ClassLoader::import("library.*");

/**
 * Category specification field value controller
 *
 * @package application.controller.backend
 * @author Sergej Andrejev <sandrejev@gmail.com>
 * @role admin.store.category
 */
class SpecFieldValueController extends StoreManagementController
{  
    /**
     * Delete specification field value from database
     *
     * @return JSONResponse Indicates status
     */
    public function delete()
    {
        if($id = $this->request->getValue("id", false))
        {
            SpecFieldValue::deleteById($id);
            return new JSONResponse(array('status' => 'success'));
        }
        else
        {
            return new JSONResponse(array('status' => 'failure'));
        }
    }

    /**
     * Sort specification field values
     * 
     * return JSONResponse Indicates status
     */
    public function sort()
    {
        foreach($this->request->getValue($this->request->getValue('target'), array()) as $position => $key)
        {
            // Except new fields, because they are not yet in database
            if(!empty($key) && !preg_match('/^new/', $key))
            {
                $specField = SpecFieldValue::getInstanceByID((int)$key);
                $specField->setFieldValue('position', (int)$position);
                $specField->save();
            }
        }

        return new JSONResponse(array('status' => 'success'));
    }

    public function mergeValues()
    {
        $mergedIntoValue = SpecFieldValue::getInstanceByID((int)$this->request->getValue('mergeIntoValue'), true);
        
        foreach($this->request->getValue('mergedValues') as $mergedValueId)
        {
            $mergedValue = SpecFieldValue::getInstanceByID((int)$mergedValueId, true);
            $mergedIntoValue->mergeWith($mergedValue);
        }

        $mergedIntoValue->save();
        return new JSONResponse(array('status' => 'success'));
    }
}
