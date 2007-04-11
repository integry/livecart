<?php

include_once('unittest/UTStandalone.php');

include_once('ShippingTest.php');
include_once('../method/usps/UspsRateCalculator.php');

class TestUsps extends ShippingTest
{
    function testUsZip()
    {
        $usps = new UspsRateCalculator();
        $usps->setSourceCountry('US');
        $usps->setSourceZip('10022');
        $usps->setDestCountry('US');
        $usps->setDestZip('20008');
        $usps->setWeight(5000);
        
        $rates = $usps->getRates();
        
        echo '<pre>';
        print_r($rates);
        echo '</pre>';
        
    }   
}

?>