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
	    
		$response = new ActionResponse();

	    $response->setValue('categoryID', $this->request->getValue('categoryID'));
		$response->setValue('productID', $productID);
		$response->setValue('relationships', $product->getRelationships()->toArray());
		
		$response->setValue('relationshipsWithGroups', $product->getRelatedProductsWithGroupsArray());
		
	    return $response;
	}
	
	public function selectProduct()
	{
	    $response = new ActionResponse();	    
	    
		$categoryList = Category::getRootNode()->getDirectChildNodes();
		$categoryList->unshift(Category::getRootNode());
		$response->setValue("categoryList", $categoryList->toArray($this->store->getDefaultLanguageCode()));
		
		return $response;
	}
	
	public function addRelated()
	{
	    $productID = (int)$this->request->getValue('id');
	    $relatedProductID = (int)$this->request->getValue('relatedProductID');
	    
	    $relatedProduct = Product::getInstanceByID($relatedProductID);
	    $product = Product::getInstanceByID($productID);
	    
	    if(!$relatedProduct->isRelatedTo($product))
	    {
	        try
	        {
		        $product->addRelatedProduct($relatedProduct);
		        $product->save();
		        
			    $response = new ActionResponse();
			    $response->setValue('product', Product::getInstanceByID($relatedProductID, ActiveRecord::LOAD_DATA)->toArray());
			    return $response;
	        }
	        catch(ProductRelationshipException $e)
	        {
	            $error = '_trying_add_the_product_itself_to_the_related_products_list';
	        }
	    }
	    else
	    {
	        $error = '_product_cannot_have_more_than_one_relationship_with_same_product';
	    }
	    
        return new JSONResponse(array('error' => $this->translate($error)));
	}
	
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