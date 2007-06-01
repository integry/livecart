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
	public function index()
	{

	    $this->locale->translationManager()->loadFile('backend/Product');
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
    
	public static function addShippingValidator(RequestValidator $validator)
	{
		// shipping related numeric field validations
		$validator->addCheck('shippingSurchargeAmount', new IsNumericCheck('_err_surcharge_not_numeric'));
		$validator->addFilter('shippingSurchargeAmount', new NumericFilter());

		$validator->addCheck('minimumQuantity', new IsNumericCheck('_err_quantity_not_numeric'));
		$validator->addCheck('minimumQuantity', new MinValueCheck('_err_quantity_negative', 0));
		$validator->addFilter('minimumQuantity', new NumericFilter());

		$validator->addFilter('shippingHiUnit', new NumericFilter());
		$validator->addCheck('shippingHiUnit', new IsNumericCheck('_err_weight_not_numeric'));
		$validator->addCheck('shippingHiUnit', new MinValueCheck('_err_weight_negative', 0));

		$validator->addFilter('shippingLoUnit', new NumericFilter());
		$validator->addCheck('shippingLoUnit', new IsNumericCheck('_err_weight_not_numeric'));
		$validator->addCheck('shippingLoUnit', new MinValueCheck('_err_weight_negative', 0));

		return $validator;
	}

	public static function addPricesValidator(RequestValidator $validator)
	{
		// price in base currency
		$baseCurrency = Store::getInstance()->getDefaultCurrency()->getID();
		$validator->addCheck('price_' . $baseCurrency, new IsNotEmptyCheck('_err_price_empty'));

	    $currencies = Store::getInstance()->getCurrencyArray();
		foreach ($currencies as $currency)
		{
			$validator->addCheck('price_' . $currency, new IsNumericCheck('_err_price_invalid'));
			$validator->addCheck('price_' . $currency, new MinValueCheck('_err_price_negative', 0));
			$validator->addFilter('price_' . $currency, new NumericFilter());
		}

		return $validator;
	}    
    
	public function addInventoryValidator(RequestValidator $validator)
	{
		if (!$this->config->getValue('DISABLE_INVENTORY'))
		{    
			$validator->addCheck('stockCount', new IsNotEmptyCheck($this->translate('_err_stock_required')));  
			$validator->addCheck('stockCount', new IsNumericCheck($this->translate('_err_stock_not_numeric')));		  
			$validator->addCheck('stockCount', new MinValueCheck($this->translate('_err_stock_negative'), 0));	
	    }

		$validator->addFilter('stockCount', new NumericFilter());	
			
		return $validator;
	}
     
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

    private function buildPricingFormValidator()
    {
		ClassLoader::import("framework.request.validator.RequestValidator");
		$validator = new RequestValidator("pricingFormValidator", $this->request);

		self::addPricesValidator($validator);
		self::addShippingValidator($validator);
		self::addInventoryValidator($validator);
        		
		if (!$this->config->getValue('DISABLE_INVENTORY'))
		{
            $validator->addCheck('stockCount', new IsNotEmptyCheck($this->translate('_err_stock_required'))); 
        }

		return $validator;
    }
    
}
?>