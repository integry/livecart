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
	    $product = Product::getInstanceByID((int)$this->request->get('productID'));
	    $relationshipGroup = ProductRelationshipGroup::getNewInstance($product);
	    
	    return $this->save($relationshipGroup);
    }
    
    /**
     * @role update
     */
    public function update()
    {
        $relationshipGroup = ProductRelationshipGroup::getInstanceByID((int)$this->request->get('ID'));
        
        return $this->save($relationshipGroup);
    }
    
    /**
     * @role update
     */
	public function delete()
	{
	    ProductRelationshipGroup::getInstanceByID((int)$this->request->get('id'))->delete();
	    
        return new JSONResponse(false, 'success');   
	}

    /**
     * @role update
     */
    public function sort()
    {
        foreach($this->request->get($this->request->get('target'), array()) as $position => $key)
        {
            if(empty($key)) continue;
            $relationship = ProductRelationshipGroup::getInstanceByID((int)$key); 
            $relationship->position->set((int)$position);
            $relationship->save();
        }
        
        return new JSONResponse(false, 'success', $this->translate('_relationship_groups_were_reordered'));   
    }

    public function edit()
    {
        $group = ProductRelationshipGroup::getInstanceByID((int)$this->request->get('id'), true);
        
        return new JSONResponse($group->toArray());
    }

    private function buildValidator()
    {
		ClassLoader::import("framework.request.validator.RequestValidator");
		$validator = new RequestValidator("productRelationshipGroupValidator", $this->request);

		$validator->addCheck('name_' . $this->application->getDefaultLanguageCode(), new IsNotEmptyCheck('_err_relationship_name_is_empty'));

		return $validator;
    }

    private function save(ProductRelationshipGroup $relationshipGroup)
    {
        $validator = $this->buildValidator();
		if ($validator->isValid())
		{
		    foreach ($this->application->getLanguageArray(true) as $lang)
    		{
    			if ($this->request->isValueSet('name_' . $lang))
    			{
    			    $relationshipGroup->setValueByLang('name', $lang, $this->request->get('name_' . $lang));
    			}
    		}
    		
    		$relationshipGroup->save();
    		
            return new JSONResponse(array('ID' => $relationshipGroup->getID()), 'success', $this->translate('_relationship_group_was_successfully_saved'));
		}
		else
		{
			return new JSONResponse(array('errors' => $validator->getErrorList()), 'failure', $this->translate('_could_not_save_relationship_group'));
		}
    }
}

?>