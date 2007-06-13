Algirdas Varnagiris
algirdas@varnagiris.net
www.varnagiris.net

The USPS Web Tools allow developers of web-based and shrink-wrapped applications
access to the on-line services of the United States Postal Service (USPS).  They
provide easy access to shipping information and services for your customers.
Your customers can utilize the functions provided by the USPS without ever
leaving your web site.  Once the Web Tools are integrated, your server
communicates through the USPS Web Tools server over HTTP using XML (eXtensible
Markup Language).

 Rates Calculators
 
 Everything in details can be found here:
 http://www.usps.com/webtools/htm/Rates-Calculatorsv1-0.htm
 
 First steps:
 1. Register online (You will get your own user name)
 2. Test this script (You can use only test data)
 3. Contact ICCC and go live.
    When you have completed your testing, email the USPS Internet Customer Care
    Center (ICCC).  They will switch your profile to allow you access to the 
    production server and will provide you with the production URL.
    
    Domestic and International Rates
    
    Domestic rates
    
    Test 1:
    
    $usps = new USPS;
    $usps->setServer("http://testing.shippingapis.com/ShippingAPITest.dll");
    $usps->setUserName("?????????");
    $usps->setService("PRIORITY");
    $usps->setDestZip("20008");
    $usps->setOrigZip("10022");
    $usps->setWeight(10, 5);
    $usps->setContainer("Flat Rate Box");
    $usps->setCountry("USA");
    $price = $usps->getPrice();
    
    ----------------------------------------------------------------------------
    
    Response 1:
    
    usps Object
    (
        [server] => http://testing.shippingapis.com/ShippingAPITest.dll
        [user] => ???????????
        [pass] => 
        [service] => PRIORITY
        [dest_zip] => 20008
        [orig_zip] => 10022
        [pounds] => 10
        [ounces] => 5
        [container] => Flat Rate Box
        [size] => REGULAR
        [machinable] => 
        [country] => USA
        [zone] => 3
        [list] => Array
            (
                [0] => price Object
                    (
                        [mailservice] => Priority Mail Flat Rate Box (11.25" x 8.75" x 6")
                        [rate] => 7.70
                    )
    
                [1] => price Object
                    (
                        [mailservice] => Priority Mail Flat Rate Box (14" x 12" x 3.5")
                        [rate] => 7.70
                    )
    
            )
    
    )
    
    ============================================================================
    
    Test 2:
    
    $usps = new USPS;
    $usps->setServer("http://testing.shippingapis.com/ShippingAPITest.dll");
    $usps->setUserName("?????????");
    $usps->setService("All");
    $usps->setDestZip("20008");
    $usps->setOrigZip("10022");
    $usps->setWeight(10, 5);
    $usps->setContainer("Flat Rate Box");
    $usps->setCountry("USA");
    $usps->setMachinable("true");
    $usps->setSize("LARGE");
    $price = $usps->getPrice();
    
    ----------------------------------------------------------------------------
    
    Response 2:
    
    usps Object
    (
        [server] => http://testing.shippingapis.com/ShippingAPITest.dll
        [user] => ???????????
        [pass] => 
        [service] => All
        [dest_zip] => 20008
        [orig_zip] => 10022
        [pounds] => 10
        [ounces] => 5
        [container] => Flat Rate Box
        [size] => LARGE
        [machinable] => true
        [country] => USA
        [zone] => 3
        [list] => Array
            (
                [0] => price Object
                    (
                        [mailservice] => Express Mail to PO Addressee
                        [rate] => 39.20
                    )
    
                [1] => price Object
                    (
                        [mailservice] => Priority Mail
                        [rate] => 8.95
                    )
    
                [2] => price Object
                    (
                        [mailservice] => Parcel Post
                        [rate] => 7.80
                    )
    
                [3] => price Object
                    (
                        [mailservice] => Bound Printed Matter
                        [rate] => 3.53
                    )
    
                [4] => price Object
                    (
                        [mailservice] => Media Mail
                        [rate] => 5.14
                    )
    
                [5] => price Object
                    (
                        [mailservice] => Library Mail
                        [rate] => 4.91
                    )
    
            )
    
    )
    ============================================================================
    
    International rates:
    
    Test 1:
    
    $usps = new USPS;
    $usps->setServer("http://testing.shippingapis.com/ShippingAPITest.dll");
    $usps->setUserName("??????????");
    $usps->setWeight(2, 0);
    $usps->setCountry("Albania");
    $price = $usps->getPrice(); 
    
    ----------------------------------------------------------------------------
    
    Response 1:
    
    usps Object
    (
        [server] => http://testing.shippingapis.com/ShippingAPITest.dll
        [user] => ?????????
        [pass] => 
        [service] => 
        [dest_zip] => 
        [orig_zip] => 
        [pounds] => 2
        [ounces] => 0
        [container] => None
        [size] => REGULAR
        [machinable] => 
        [country] => Albania
        [list] => Array
            (
                [0] => intprice Object
                    (
                        [id] => 0
                        [rate] => 87
                        [pounds] => 2
                        [ounces] => 0
                        [mailtype] => Package
                        [country] => ALBANIA
                        [svccommitments] => See Service Guide
                        [svcdescription] => Global Express Guaranteed (GXG) Document Service
                        [maxdimensions] => Max. length 46", depth 35", height 46" and max. girth 108"
                        [maxweight] => 22
                    )
    
                [1] => intprice Object
                    (
                        [id] => 1
                        [rate] => 96
                        [pounds] => 2
                        [ounces] => 0
                        [mailtype] => Package
                        [country] => ALBANIA 
                        [svccommitments] => See Service Guide
                        [svcdescription] => Global Express Guaranteed (GXG) Non-Document Service
                        [maxdimensions] => Max. length 46", depth 35", height 46" and max. girth 108"
                        [maxweight] => 22
                    )
    
            )
    
    )
    
    ============================================================================
    
    Possible errors:
    
    usps Object
    (
        [server] => http://testing.shippingapis.com/ShippingAPITest.dll
        [user] => ???????????
        [pass] => 
        [service] => Alll
        [dest_zip] => 20008
        [orig_zip] => 10022
        [pounds] => 10
        [ounces] => 5
        [container] => Flat Rate Box
        [size] => LARGE
        [machinable] => true
        [country] => USA
        [error] => error Object
            (
                [number] => -2147219487
                [source] => Rate_Respond;SOLServerRatesTest.RateV2_Respond
                [description] => Invalid value for package size.
                [helpcontext] => 1000440
                [helpfile] => 
            )
    
    )
