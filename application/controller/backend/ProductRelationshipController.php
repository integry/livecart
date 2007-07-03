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
class ProductRelationshipController extends StoreManagementController 
{
	public function index()
	{		
	    $productID = (int)$this->request->getValue('id');
		$product = Product::getInstanceByID($productID, ActiveRecord::LOAD_DATA, array('Category'));
	    
		$languages = array();
		foreach($this->store->getLanguageList()->toArray() as $language) $languages[$language['ID']] = $language;
		
		$response = new ActionResponse();

	    $response->setValue('categoryID', $this->request->getValue('categoryID'));
		$response->setValue('languages', $languages);
		$response->setValue('productID', $productID);
		$response->setValue('relationships', $product->getRelationships()->toArray());
		$response->setValue('relationshipsWithGroups', $product->getRelatedProductsWithGroupsArray());
		
	    return $response;
	}
	
    /**
     * Products popup
     * 
     * @role update
     */
	public function selectProduct()
	{
	    $response = new ActionResponse();	    
	    
		$categoryList = Category::getRootNode()->getDirectChildNodes();
		$categoryList->unshift(Category::getRootNode());
		$response->setValue("categoryList", $categoryList->toArray($this->store->getDefaultLanguageCode()));
		
		return $response;
	}
	
    /**
     * Creates new relationship
     * 
     * @role update
     */
	public function addRelated()
	{
	    $productID = (int)$this->request->getValue('id');
	    $relatedProductID = (int)$this->request->getValue('relatedProductID');
	    
	    $relatedProduct = Product::getInstanceByID($relatedProductID, true, array('DefaultImage' => 'ProductImage'));
	    $product = Product::getInstanceByID($productID);
	    
	    if(!$relatedProduct->isRelatedTo($product))
	    {
	        try
	        {
		        $product->addRelatedProduct($relatedProduct);
		        $product->save();
		        
			    $response = new ActionResponse();
			    $response->setValue('product', $relatedProduct->toArray());
			    return $response;
	        }
	        catch(ProductRelationshipException $e)
	        {
	            $error = '_err_circular';
	        }
	    }
	    else
	    {
	        $error = '_err_multiple';
	    }
	    
        return new JSONResponse(array('error' => $this->translate($error)));
	}
	
    /**
     * @role update
     */
	public function delete()
	{
	    $productID = (int)$this->request->getValue('id');
	    $relatedProductID = (int)$this->request->getValue('relatedProductID');
	    
	    $relatedProduct = Product::getInstanceByID($relatedProductID);
	    $product = Product::getInstanceByID($productID);	    
	    
	    $product->removeFromRelatedProducts($relatedProduct);
	    $product->save();
	    
	    return new JSONResponse(array('status' => 'success'));
	}

    /**
     * @role update
     */
    public function sort()
    {
        $product = Product::getInstanceByID((int)$this->request->getValue('id'));    
        $target = $this->request->getValue('target');    
        preg_match('/_(\d+)$/', $target, $match); // Get group. 

        foreach($this->request->getValue($this->request->getValue('target'), array()) as $position => $key)
        {
            if(empty($key)) continue;
            
            $relationship = ProductRelationship::getInstance($product, Product::getInstanceByID((int)$key)); 
            $relationship->position->set((int)$position);
            
            if(isset($match[1])) $relationship->productRelationshipGroup->set(ProductRelationshipGroup::getInstanceByID((int)$match[1])); 
            else $relationship->productRelationshipGroup->setNull();
            
            $relationship->save();
        }
        
        return new JSONResponse(array('status' => 'success'));
    }
}

?>