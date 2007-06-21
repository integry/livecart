<?php

ClassLoader::import('library.shipping.ShippingRateSet');
ClassLoader::import('library.shipping.ShippingRateResult');
ClassLoader::import('application.model.delivery.ShippingService');

/**
 * Shipping cost calculation result for a particular Shipment. One Shipment can have several
 * ShipmentDeliveryRates - one for each available shipping service. Customer is able to choose between
 * the available rates. ShipmentDeliveryRate can be either a pre-defined rate or a real-time rate.
 *
 * @package application.model.delivery
 * @author Integry Systems <http://integry.com> 
 */
class ShipmentDeliveryRate extends ShippingRateResult
{
    protected $amountWithTax;
    
    public static function getNewInstance(ShippingService $service, $cost)
    {
        $inst = new ShipmentDeliveryRate();
        $inst->setServiceId($service->getID());
        $inst->setCost($cost, Store::getInstance()->getDefaultCurrencyCode());
        return $inst;
    }
    
    public static function getRealTimeRates(ShippingRateCalculator $handler, Shipment $shipment)
    {
        $handler->setWeight($shipment->getChargeableWeight());
        
        $address = $shipment->order->get()->shippingAddress->get();        
        $handler->setDestCountry($address->countryID->get()); 
        $handler->setDestZip($address->postalCode->get());

        $config = Config::getInstance();        
        $handler->setSourceCountry($config->getValue('STORE_COUNTRY'));
        $handler->setSourceZip($config->getValue('STORE_ZIP'));
        
        $rates = new ShippingRateSet();
        foreach ($handler->getAllRates() as $k => $rate)        
        {            
            $newRate = new ShipmentDeliveryRate();
            $newRate->setCost($rate->getCostAmount(), $rate->getCostCurrency()); 
            $newRate->setServiceName($rate->getServiceName());
            $newRate->setClassName($rate->getClassName());
            $newRate->setProviderName($rate->getProviderName());
            $newRate->setServiceId($rate->getClassName() . '_' . $k);
            $rates->add($newRate);
        }
        
        return $rates;
    }
    
    public function getAmountByCurrency(Currency $currency)
    {
        $amountCurrency = Currency::getInstanceById($this->getCostCurrency());
        $amount = $currency->convertAmount($amountCurrency, $this->getCostAmount());
        
        return round($amount, 2);
    }
    
    public function setAmountWithTax($amount)
    {
        $this->amountWithTax = $amount;
    }
    
    public function toArray()
    {
        $array = parent::toArray();
        
        $amountCurrency = Currency::getInstanceById($array['costCurrency']);
        $currencies = Store::getInstance()->getCurrencySet();

        // get and format prices
        $prices = $formattedPrices = $taxPrices = array();
        array();
        foreach ($currencies as $id => $currency)
        {
            $prices[$id] = $currency->convertAmount($amountCurrency, $array['costAmount']);
            $formattedPrices[$id] = $currency->getFormattedPrice($prices[$id]);
            $taxPrices[$id] = $currency->getFormattedPrice($currency->convertAmount($amountCurrency, $this->amountWithTax));
        }

        $array['price'] = $prices;
        $array['formattedPrice'] = $formattedPrices;
        $array['taxPrice'] = $taxPrices;
                
        // shipping service name
        $id = $this->getServiceID();
        if (is_numeric($id))
        {
            $service = ShippingService::getInstanceById($id, ShippingService::LOAD_DATA);   
            $array['ShippingService'] = $service->toArray();
        }
        else
        {
            $array['ShippingService'] = array('name_lang' => $this->getServiceName(), 'provider' => $this->getProviderName());
        }
        
        return $array;
    }
}
?>