USPS Rates
<pre>
<?php
require("usps.php");

$usps = new USPS;
$usps->setServer("http://testing.shippingapis.com/ShippingAPITest.dll");
$usps->setUserName("GETYOUROWN");
$usps->setService("All");
$usps->setDestZip("20008");
$usps->setOrigZip("10022");
$usps->setWeight(10, 5);
$usps->setContainer("Flat Rate Box");
$usps->setCountry("USA");
$usps->setMachinable("true");
$usps->setSize("LARGE");
$price = $usps->getPrice(); 
print_r($price);
?>
</pre>
