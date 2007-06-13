<?php

ClassLoader::import('library.shipping.ShippingRateSet');
ClassLoader::import('library.shipping.ShippingRateResult');
ClassLoader::import('application.model.delivery.ShippingService');

class ShipmentDeliveryRate extends ShippingRateResult
{
    public static function getNewInstance(ShippingService $service, $cost)
    {
        $inst = new ShipmentDeliveryRate();
        $inst->setServiceId($service->getID());
        $inst->setCost($cost, Store::getInstance()->getDefaultCurrencyCode());
        return $inst;
    }
    
    public function getAmountByCurrency(Currency $currency)
    {
        $amountCurrency = Currency::getInstanceById($this->getCostCurrency());
        $amount = $currency->convertAmount($amountCurrency, $this->getCostAmount());
        
        return round($amount, 2);
    }
    
    public function toArray()
    {
        $array = parent::toArray();
        
        $amountCurrency = Currency::getInstanceById($array['costCurrency']);
        $currencies = Store::getInstance()->getCurrencySet();

        // get and format prices
        $prices = array();
        $formattedPrices = array();
        foreach ($currencies as $id => $currency)
        {
            $prices[$id] = $currency->convertAmount($amountCurrency, $array['costAmount']);
            $formattedPrices[$id] = $currency->getFormattedPrice($prices[$id]);
        }

        $array['price'] = $prices;
        $array['formattedPrice'] = $formattedPrices;
        
        // shipping service name
        if ($id = $this->getServiceID())
        {
            $service = ShippingService::getInstanceById($id, ShippingService::LOAD_DATA);   
            $array['ShippingService'] = $service->toArray();
        }
        
        return $array;
    }
}

?>