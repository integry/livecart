<?php

ClassLoader::import('library.shipping.ShippingRateSet');
ClassLoader::import('library.shipping.ShippingRateResult');

class ShipmentDeliveryRate extends ShippingRateResult
{
    function getAmountByCurrency(Currency $currency)
    {
        $amountCurrency = Currency::getInstanceById($this->getCostCurrency());
        return $currency->convertAmount($amountCurrency, $this->getCostAmount());
    }
    
    function toArray()
    {
        $array = parent::toArray();
        
        $amountCurrency = Currency::getInstanceById($array['costCurrency']);
        $currencies = Store::getInstance()->getCurrencySet();

        $prices = array();
        $formattedPrices = array();
        foreach ($currencies as $id => $currency)
        {
            $prices[$id] = $currency->convertAmount($amountCurrency, $array['costAmount']);
            $formattedPrices[$id] = $currency->getFormattedPrice($prices[$id]);
        }
    
        $array['price'] = $prices;
        $array['formattedPrice'] = $formattedPrices;
        
        return $array;
    }
}

?>