<?php

include_once('../ShippingRateCalculator.php');

class UspsRateCalculator extends ShippingRateCalculator
{
    private $userId = '550INTEG8147';
    
    private $server = 'http://testing.shippingapis.com/ShippingAPITest.dll';
    
    public function getRates()
    {
        include_once('lib/usps.php');   
        include('countries.php'); 
        
        $usps = new USPS();
        $usps->setServer($this->server);
        $usps->setUserName($this->userId);
        
        $usps->setOrigZip($this->sourceZip);
        $usps->setDestZip($this->destZip);

        $country = isset($countries[$this->destCountry]) ? $countries[$this->destCountry] : 'USA';
        $usps->setCountry($country);
        
        // get weight in pounds/ounces
        $pounds = floor($this->weight / 453);
        $ounces = ceil(($this->weight % 453) / 27);
        $usps->setWeight(3, 2);     
        
        $usps->setService('PRIORITY');
        $usps->setContainer("Flat Rate Box");
            
        $price = $usps->getPrice();
        return $price;    
                
    }
}

?>