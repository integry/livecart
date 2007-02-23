<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.model.category.Category");
ClassLoader::import("application.model.product.Product");

/**
 * Controller for handling product based actions performed by store administrators
 *
 * @package application.controller.backend
 * @role admin.store.product
 */
class ProductPriceController extends StoreManagementController 
{
	public function index()
	{
	    $response = new ActionResponse();
	    
	    $product = Product::getInstanceByID($this->request->getValue('id'), ActiveRecord::LOAD_DATA, ActiveRecord::LOAD_REFERENCES);
	    
	    $pricingForm = $this->buildPricingForm($product);

	    $response->setValue("product", $product->toArray());
		$response->setValue("otherCurrencies", Store::getInstance()->getCurrencyArray(Store::EXCLUDE_DEFAULT_CURRENCY));
		$response->setValue("baseCurrency", Store::getInstance()->getDefaultCurrency()->getID());
		$response->setValue("pricingForm", $pricingForm);
		
	    return $response;
	}
	
    private function buildPricingForm(Product $product)
    {
        ClassLoader::import("framework.request.validator.Form");
        
        $pricing = new ProductPricing($product, $product->getPricesArray());        
        
		$form = new Form($this->buildPricingFormValidator());
	    $form->setData($product->toArray());
		
		return $form;
    }
    
    private function buildPricingFormValidator()
    {
		ClassLoader::import("framework.request.validator.RequestValidator");
		$validator = new RequestValidator("pricingFormValidator", $this->request);
		
		ProductPricing::addPricesValidator($validator);
		ProductPricing::addShippingValidator($validator);
		
		return $validator;
    }
}
?>