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
	    $product->getPricesArray();
	    $pricing = new ProductPricing($product, $product->getPricesArray());
	    	    
	    $pricingForm = $this->buildPricingForm();
	    $pricingForm->setData($pricing->toArray());
	    
	    $response->setValue('product', $product->toArray());
	    $response->setValue('pricingForm', $pricingForm);
	    
	    return $response;
	}
	
    private function buildPricingForm()
    {
        ClassLoader::import("framework.request.validator.Form");
        
		$form = new Form($this->buildPricingFormValidator());
		return $form;
    }
    
    private function buildPricingFormValidator()
    {
		ClassLoader::import("framework.request.validator.RequestValidator");
		$validator = new RequestValidator("pricingFormValidator", $this->request);
		
		// price in base currency
		$baseCurrency = Store::getInstance()->getDefaultCurrency()->getID();
		$validator->addCheck('price_' . $baseCurrency, new IsNotEmptyCheck($this->translate('_err_price_empty')));
		
		return $validator;
    }
}
?>