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
        $product = Product::getInstanceByID($this->request->get('id'), ActiveRecord::LOAD_DATA, ActiveRecord::LOAD_REFERENCES);

	    $pricingForm = $this->buildPricingForm($product);

	    $response = new ActionResponse();
	    $response->set("product", $product->toFlatArray());
		$response->set("otherCurrencies", $this->application->getCurrencyArray(LiveCart::EXCLUDE_DEFAULT_CURRENCY));
		$response->set("baseCurrency", $this->application->getDefaultCurrency()->getID());
		$response->set("pricingForm", $pricingForm);

	    return $response;
	}

    /**
     * @role update
     */
    public function save()
    {
        $product = Product::getInstanceByID((int)$this->request->get('id'));

		$validator = $this->buildPricingFormValidator($product);
		if ($validator->isValid())
		{
		    // Save prices
    		foreach ($this->application->getCurrencyArray() as $currency)
    		{
    			if ($this->request->isValueSet('price_' . $currency))
    			{
    				$product->setPrice($currency, $this->request->get('price_' . $currency));
    			}
    		}

    		// Save shipping
    		$product->loadSpecification();
    		$product->loadPricing();
    		$product->setFieldValue('stockCount', (int)$this->request->get('stockCount'));
            $product->setFieldValue('shippingWeight', (float)$this->request->get('shippingWeight'));
            $product->setFieldValue('shippingSurchargeAmount', (float)$this->request->get('shippingSurchargeAmount'));
            $product->setFieldValue('minimumQuantity', (int)$this->request->get('minimumQuantity'));
            $product->setFieldValue('isSeparateShipment', $this->request->get('isSeparateShipment') ? 1 : 0);
            $product->setFieldValue('isFreeShipping', $this->request->get('isFreeShipping') ? 1 : 0);
            $product->setFieldValue('isBackOrderable', $this->request->get('isBackOrderable') ? 1 : 0);
            $product->save();

            return new JSONResponse(array('prices' => $product->getPricesFields()), 'success', $this->translate('_product_prices_were_successfully_updated'));
		}
		else
		{
			return new JSONResponse(array('errors' => $validator->getErrorList()), 'failure', $this->translate('_product_prices_could_not_be_updated'));
		}
    }
    
	public function addShippingValidator(RequestValidator $validator)
	{
		// shipping related numeric field validations
		$validator->addCheck('shippingSurchargeAmount', new IsNumericCheck($this->translate('_err_surcharge_not_numeric')));
		$validator->addFilter('shippingSurchargeAmount', new NumericFilter());

		$validator->addCheck('minimumQuantity', new IsNumericCheck($this->translate('_err_quantity_not_numeric')));
		$validator->addCheck('minimumQuantity', new MinValueCheck($this->translate('_err_quantity_negative'), 0));
		$validator->addFilter('minimumQuantity', new NumericFilter());

		$validator->addFilter('shippingHiUnit', new NumericFilter());
		$validator->addCheck('shippingHiUnit', new IsNumericCheck($this->translate('_err_weight_not_numeric')));
		$validator->addCheck('shippingHiUnit', new MinValueCheck($this->translate('_err_weight_negative'), 0));

		$validator->addFilter('shippingLoUnit', new NumericFilter());
		$validator->addCheck('shippingLoUnit', new IsNumericCheck($this->translate('_err_weight_not_numeric')));
		$validator->addCheck('shippingLoUnit', new MinValueCheck($this->translate('_err_weight_negative'), 0));

		return $validator;
	}

	public function addPricesValidator(RequestValidator $validator)
	{
		// price in base currency
		$baseCurrency = $this->application->getDefaultCurrency()->getID();
		$validator->addCheck('price_' . $baseCurrency, new IsNotEmptyCheck($this->translate('_err_price_empty')));

	    $currencies = $this->application->getCurrencyArray();
		foreach ($currencies as $currency)
		{
			$validator->addCheck('price_' . $currency, new IsNumericCheck($this->translate('_err_price_invalid')));
			$validator->addCheck('price_' . $currency, new MinValueCheck($this->translate('_err_price_negative'), 0));
			$validator->addFilter('price_' . $currency, new NumericFilter());
		}

		return $validator;
	}    
    
	public function addInventoryValidator(RequestValidator $validator)
	{
		if (!$this->config->get('DISABLE_INVENTORY'))
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
        		
		if (!$this->config->get('DISABLE_INVENTORY'))
		{
            $validator->addCheck('stockCount', new IsNotEmptyCheck($this->translate('_err_stock_required'))); 
        }

		return $validator;
    }
    
}
?>