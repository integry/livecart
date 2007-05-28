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
    /**
     * @role update
     */
    public function create()
    {
	    $product = Product::getInstanceByID((int)$this->request->getValue('productID'));
	    $relationshipGroup = ProductRelationshipGroup::getNewInstance($product);
	    
	    return $this->save($relationshipGroup);
    }
    
    /**
     * @role update
     */
    public function update()
    {
        $relationshipGroup = ProductRelationshipGroup::getInstanceByID((int)$this->request->getValue('ID'));
        
        return $this->save($relationshipGroup);
    }
    
    /**
     * @role update
     */
	public function delete()
	{
	    ProductRelationshipGroup::getInstanceByID((int)$this->request->getValue('id'))->delete();
	    return new JSONResponse(array('status' => 'success'));
	}

    /**
     * @role update
     */
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

    private function buildValidator()
    {
		ClassLoader::import("framework.request.validator.RequestValidator");
		$validator = new RequestValidator("productRelationshipGroupValidator", $this->request);

		$validator->addCheck('name_' . $this->store->getDefaultLanguageCode(), new IsNotEmptyCheck('_err_relationship_name_is_empty'));

		return $validator;
    }

    private function save(ProductRelationshipGroup $relationshipGroup)
    {
        $validator = $this->buildValidator();
		if ($validator->isValid())
		{
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
}

?>