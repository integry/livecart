<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.model.category.Category");
ClassLoader::import("application.model.product.Product");

/**
 * Controller for handling product based actions performed by store administrators
 *
 * @package application.controller.backend
 * @role product
 */
class ProductRelationshipGroupController extends StoreManagementController 
{
    private function buildValidator()
    {
		ClassLoader::import("framework.request.validator.RequestValidator");
		$validator = new RequestValidator("productRelationshipGroupValidator", $this->request);

		$validator->addCheck('name_' . $this->store->getDefaultLanguageCode(), new IsNotEmptyCheck('_err_relationship_name_is_empty'));

		return $validator;
    }

    public function save()
    {
        $validator = $this->buildValidator();
		if ($validator->isValid())
		{
    		$ID = $this->request->getValue('ID');
		    if(!empty($ID))
    		{
    		    $relationshipGroup = ProductRelationshipGroup::getInstanceByID((int)$this->request->getValue('ID'));
    		}
    		else
    		{
    		    $product = Product::getInstanceByID((int)$this->request->getValue('productID'));
    		    $relationshipGroup = ProductRelationshipGroup::getNewInstance($product);
    		}
    		
		    foreach ($this->store->getLanguageArray(true) as $lang)
    		{
    			if ($this->request->isValueSet('name_' . $lang))
    			{
    			    $relationshipGroup->setValueByLang('name', $lang, $this->request->getValue('name_' . $lang));
    			}
    		}
    		
    		$relationshipGroup->save();
    		
            return new JSONResponse(array('status' => "success", 'ID' => $relationshipGroup->getID()));
		}
		else
		{
			return new JSONResponse(array('status' => "failure", 'errors' => $validator->getErrorList()));
		}
    }
    
	public function delete()
	{
	    ProductRelationshipGroup::getInstanceByID((int)$this->request->getValue('id'))->delete();
	    return new JSONResponse(array('status' => 'success'));
	}

    public function sort()
    {
        foreach($this->request->getValue($this->request->getValue('target'), array()) as $position => $key)
        {
            if(empty($key)) continue;
            $relationship = ProductRelationshipGroup::getInstanceByID((int)$key); 
            $relationship->position->set((int)$position);
            $relationship->save();
        }
        
        return new JSONResponse(array('status' => 'success'));
    }

    public function edit()
    {
        $group = ProductRelationshipGroup::getInstanceByID((int)$this->request->getValue('id'), true);
        
        return new JSONResponse($group->toArray());
    }
}

?>