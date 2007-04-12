<?php

include_once('../ShippingRateCalculator.php');

class UspsRateCalculator extends ShippingRateCalculator
{
    private $userId = '';
    
    //private $server = 'http://testing.shippingapis.com/ShippingAPITest.dll';
    
    private $server = 'http://production.shippingapis.com/ShippingAPI.dll';
    
    private $isMachinable = 'True';
    
    private $service;
    
    private $container;
    
    private $size;
    
    public function getRates()
    {
        include_once('lib/usps.php');   
        include('Countries.php'); 
        
        $usps = new USPSHandler();
        $usps->setServer($this->server);
        $usps->setUserName($this->userId);
        
        $usps->setOrigZip($this->sourceZip);
        $usps->setDestZip($this->destZip);

        $country = isset($countries[$this->destCountry]) ? $countries[$this->destCountry] : 'USA';
        $usps->setCountry($country);
        
        // get weight in pounds/ounces
        $pounds = floor($this->weight / 453.59237);
        $ounces = ceil(($this->weight % 453.59237) / 28.3495231);
        $usps->setWeight($pounds, $ounces);     

        $usps->setMachinable($this->isMachinable);                     
        $usps->setService($this->service);
        $usps->setSize($this->size);
        
        if ($this->container)
        {
            $usps->setContainer($this->container);            
        }
                    
        $price = $usps->getPrice();
        
        // success
        if (isset($price->list))
        {
            $result = new ShippingRateSet();
            foreach ($price->list as $rate)
            {
                $r = new ShippingRateResult();
                $r->setServiceName($rate->mailservice);
                $r->setCost($rate->rate, 'USD');                
                $result->add($r);
            }
        }        
        // error
        else
        {
            $errorMsg = isset($price->error) ? $price->error->description : '';
            $result = new ShippingRateError($errorMsg);  
        }
        
        $result->setRawResponse($price);
        
        return $result;                
    }
    
    public function setMachinable($isMachinable = true)
    {
        $this->machinable = $isMachinable ? 'TRUE' : 'FALSE';
    }

    public function setService($service)
    {
        $this->service = $service;
        
        if ('Express' == $service)
        {
            $this->setContainer('Flat Rate Envelope');    
        }        
    }
    
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }
    
    public function setContainer($container)
    {
        $this->container = $container;
    }

    public function setSize($size)
    {
        $this->size = $size;
    }
}

?>