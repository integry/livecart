<?php

include_once('unittest/UTStandalone.php');

include_once('ShippingTest.php');
include_once('../method/usps/UspsRateCalculator.php');

class TestUsps extends ShippingTest
{
    function testDomesticRates()
    {
        $usps = new UspsRateCalculator();
        $usps->setUserId('550INTEG8147');
        $usps->setSourceCountry('US');
        $usps->setSourceZip('90210');
        $usps->setDestCountry('US');
        $usps->setDestZip('20008');
        $usps->setDestZip('20008');
        $usps->setSize('REGULAR');
        $usps->setMachinable(true);
        $usps->setWeight(15000);        
                                        
        // priority
        $usps->setService('Priority');
        $usps->setContainer('Flat Rate Box');
        $rates = $usps->getRates();     
        $this->assertTrue($rates instanceof ShippingRateSet);

        $usps->setContainer('Flat Rate Envelope');
        $rates = $usps->getRates();     
        $this->assertTrue($rates instanceof ShippingRateSet);

        // express
        $usps->setService('Express');        
        $rates = $usps->getRates();     
        $this->assertTrue($rates instanceof ShippingRateSet);
        
        // parcel post
        $usps->setService('Parcel');        
        $rates = $usps->getRates();     
        $this->assertTrue($rates instanceof ShippingRateSet);

        // parcel post
        $usps->setService('Parcel');        
        $rates = $usps->getRates();     
        $this->assertTrue($rates instanceof ShippingRateSet);

        // library
        $usps->setService('Library'); 
        $rates = $usps->getRates();     
        $this->assertTrue($rates instanceof ShippingRateSet);

        // Bound Printed Matter
        $usps->setWeight(3000); 
        $usps->setService('BPM'); 
        $rates = $usps->getRates();     
        $this->assertTrue($rates instanceof ShippingRateSet);

        // Media
        $usps->setWeight(3000); 
        $usps->setService('Media'); 
        $rates = $usps->getRates();     
        $this->assertTrue($rates instanceof ShippingRateSet);       

        // overweight package
        $usps->setWeight(50000); 
        $usps->setService('Media'); 
        $rates = $usps->getRates();     
        $this->assertTrue($rates instanceof ShippingRateError);
    }   
    
    public function testInternational()
    {
        $usps = new UspsRateCalculator();
        $usps->setUserId('550INTEG8147');
        $usps->setSourceCountry('US');
        $usps->setSourceZip('90210');
        $usps->setDestCountry('LT');
        $usps->setSize('REGULAR');
        $usps->setMachinable(true);
        $usps->setWeight(15000);        
        $usps->setService('Package');
                
        $rates = $usps->getRates();     
        $this->assertTrue($rates instanceof ShippingRateSet);

    }
}

?>