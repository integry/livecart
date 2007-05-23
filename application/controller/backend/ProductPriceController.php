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
class ProductPriceController extends StoreManagementController
{
    /**
     * @role update
     */
	public function index()
	{

	    $product = Product::getInstanceByID($this->request->getValue('id'), ActiveRecord::LOAD_DATA, ActiveRecord::LOAD_REFERENCES);

	    $pricingForm = $this->buildPricingForm($product);

	    $response = new ActionResponse();
	    $response->setValue("product", $product->toFlatArray());
		$response->setValue("otherCurrencies", Store::getInstance()->getCurrencyArray(Store::EXCLUDE_DEFAULT_CURRENCY));
		$response->setValue("baseCurrency", Store::getInstance()->getDefaultCurrency()->getID());
		$response->setValue("pricingForm", $pricingForm);

	    return $response;
	}

    /**
     * @role update
     */
    public function save()
    {
        $product = Product::getInstanceByID((int)$this->request->getValue('id'));

		$validator = $this->buildPricingFormValidator($product);
		if ($validator->isValid())
		{
		    // Save prices
    		foreach (Store::getInstance()->getCurrencyArray() as $currency)
    		{
    			if ($this->request->isValueSet('price_' . $currency))
    			{
    				$product->setPrice($currency, $this->request->getValue('price_' . $currency));
    			}
    		}

    		// Save shipping
    		$product->loadSpecification();
    		$product->loadPricing();
    		$product->setFieldValue('stockCount', (int)$this->request->getValue('stockCount'));
            $product->setFieldValue('shippingWeight', (float)$this->request->getValue('shippingWeight'));
            $product->setFieldValue('shippingSurchargeAmount', (float)$this->request->getValue('shippingSurchargeAmount'));
            $product->setFieldValue('minimumQuantity', (int)$this->request->getValue('minimumQuantity'));
            $product->setFieldValue('isSeparateShipment', $this->request->getValue('isSeparateShipment') ? 1 : 0);
            $product->setFieldValue('isFreeShipping', $this->request->getValue('isFreeShipping') ? 1 : 0);
            $product->setFieldValue('isBackOrderable', $this->request->getValue('isBackOrderable') ? 1 : 0);
            $product->save();

            return new JSONResponse(array('status' => "success", 'prices' => $product->getPricesFields()));
		}
		else
		{
			return new JSONResponse(array('status' => "failure", 'errors' => $validator->getErrorList()));
		}
    }
    

    /**
     * @role update
     */
    private function buildPricingForm(Product $product)
    {
        ClassLoader::import("framework.request.validator.Form");
		if(!$product->isLoaded()) $product->load(ActiveRecord::LOAD_REFERENCES);
		
		$product->loadPricing();
        $pricing = $product->getPricingHandler();
		$form = new Form($this->buildPricingFormValidator());
		
		$pricesData = $product->toArray();
		$pricesData['shippingHiUnit'] = (int)$pricesData['shippingWeight'];
		$pricesData['shippingLoUnit'] = ($pricesData['shippingWeight'] - $pricesData['shippingHiUnit']) * 1000;
		$pricesData = array_merge($pricesData, $product->getPricesFields());

	    $form->setData($pricesData);

		return $form;
    }

    /**
     * @role update
     */
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