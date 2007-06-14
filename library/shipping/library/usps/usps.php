<?php
require_once("xmlparser.php");

class USPSHandler {

    var $server = "";
    var $user = "";
    var $pass = "";
    var $service = "";
    var $dest_zip;
    var $orig_zip;
    var $pounds;
    var $ounces;
    var $container = "None";
    var $size = "REGULAR";
    var $machinable;
    var $country = "USA";
    
    function setServer($server) {
        $this->server = $server;
    }

    function setUserName($user) {
        $this->user = $user;
    }

    function setPass($pass) {
        $this->pass = $pass;
    }

    function setService($service) {
        /* Must be: Express, Priority, or Parcel */
        $this->service = $service;
    }
    
    function setDestZip($sending_zip) {
        /* Must be 5 digit zip (No extension) */
        $this->dest_zip = $sending_zip;
    }

    function setOrigZip($orig_zip) {
        $this->orig_zip = $orig_zip;
    }

    function setWeight($pounds, $ounces=0) {
        /* Must weight less than 70 lbs. */
        $this->pounds = $pounds;
        $this->ounces = $ounces;
    }

    function setContainer($cont) {
        $this->container = $cont;
    }

    function setSize($size) {
        $this->size = $size;
    }

    function setMachinable($mach) {
        /* Required for Parcel Post only, set to True or False */
        $this->machinable = $mach;
    }
    
    function setCountry($country) {
        $this->country = $country;
    }
    
    function getPrice() {
        if($this->country=="USA"){
            // may need to urlencode xml portion
            $str = $this->server. "?API=RateV3&XML=<RateV3Request%20USERID=\"";
            $str .= $this->user . "\"%20PASSWORD=\"" . $this->pass . "\"><Package%20ID=\"0\"><Service>";
            $str .= $this->service . "</Service><ZipOrigination>" . $this->orig_zip . "</ZipOrigination>";
            $str .= "<ZipDestination>" . $this->dest_zip . "</ZipDestination>";
            $str .= "<Pounds>" . $this->pounds . "</Pounds><Ounces>" . $this->ounces . "</Ounces>";
            $str .= "<Container>" . urlencode($this->container) . "</Container><Size>" . $this->size . "</Size>";
            $str .= "<Machinable>" . $this->machinable . "</Machinable>";
            
            if (strtolower($this->service) == 'first class')
            {
                $str .= "<FirstClassMailType>PARCEL</FirstClassMailType>";                
            }
            
            $str .= "</Package></RateV3Request>";
        }
        else {
            $str = $this->server. "?API=IntlRate&XML=<IntlRateRequest%20USERID=\"";
            $str .= $this->user . "\"%20PASSWORD=\"" . $this->pass . "\"><Package%20ID=\"0\">";
            $str .= "<Pounds>" . $this->pounds . "</Pounds><Ounces>" . $this->ounces . "</Ounces>";
            $str .= "<MailType>Package</MailType><Country>".urlencode($this->country)."</Country></Package></IntlRateRequest>";
        }

        $ch = curl_init();
        // set URL and other appropriate options
        curl_setopt($ch, CURLOPT_URL, $str);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // grab URL and pass it to the browser
        $ats = curl_exec($ch);

        // close curl resource, and free up system resources
        curl_close($ch);
        $xmlParser = new xmlparser();
        $array = $xmlParser->GetXMLTree($ats);

        $e = error_reporting();
        error_reporting(E_WARNING);

        //$xmlParser->printa($array);
        if(isset($array['ERROR'])) { // If it is error
            $error = new UspsError();
            $error->number = $array['ERROR'][0]['NUMBER'][0]['VALUE'];
            $error->source = $array['ERROR'][0]['SOURCE'][0]['VALUE'];
            $error->description = $array['ERROR'][0]['DESCRIPTION'][0]['VALUE'];
            $error->helpcontext = $array['ERROR'][0]['HELPCONTEXT'][0]['VALUE'];
            $error->helpfile = $array['ERROR'][0]['HELPFILE'][0]['VALUE'];
            $this->error = $error;
        } else if(isset($array['RATEV3RESPONSE'][0]['PACKAGE'][0]['ERROR'])) {
            $error = new UspsError();
            $error->number = $array['RATEV3RESPONSE'][0]['PACKAGE'][0]['ERROR'][0]['NUMBER'][0]['VALUE'];
            $error->source = $array['RATEV3RESPONSE'][0]['PACKAGE'][0]['ERROR'][0]['SOURCE'][0]['VALUE'];
            $error->description = $array['RATEV3RESPONSE'][0]['PACKAGE'][0]['ERROR'][0]['DESCRIPTION'][0]['VALUE'];
            $error->helpcontext = $array['RATEV3RESPONSE'][0]['PACKAGE'][0]['ERROR'][0]['HELPCONTEXT'][0]['VALUE'];
            $error->helpfile = $array['RATEV3RESPONSE'][0]['PACKAGE'][0]['ERROR'][0]['HELPFILE'][0]['VALUE'];
            $this->error = $error;        
        } else if(isset($array['INTLRATERESPONSE'][0]['PACKAGE'][0]['ERROR'])){ //if it is international shipping error
            $error = new UspsError($array['INTLRATERESPONSE'][0]['PACKAGE'][0]['ERROR']);
            $error->number = $array['INTLRATERESPONSE'][0]['PACKAGE'][0]['ERROR'][0]['NUMBER'][0]['VALUE'];
            $error->source = $array['INTLRATERESPONSE'][0]['PACKAGE'][0]['ERROR'][0]['SOURCE'][0]['VALUE'];
            $error->description = $array['INTLRATERESPONSE'][0]['PACKAGE'][0]['ERROR'][0]['DESCRIPTION'][0]['VALUE'];
            $error->helpcontext = $array['INTLRATERESPONSE'][0]['PACKAGE'][0]['ERROR'][0]['HELPCONTEXT'][0]['VALUE'];
            $error->helpfile = $array['INTLRATERESPONSE'][0]['PACKAGE'][0]['ERROR'][0]['HELPFILE'][0]['VALUE'];
            $this->error = $error;
        } else if(isset($array['RATEV3RESPONSE'])){ // if everything OK
            //print_r($array['RATEV2RESPONSE']);
            $this->zone = $array['RATEV3RESPONSE'][0]['PACKAGE'][0]['ZONE'][0]['VALUE'];
            foreach ($array['RATEV3RESPONSE'][0]['PACKAGE'][0]['POSTAGE'] as $value){
                $price = new UspsPrice();
                $price->mailservice = $value['MAILSERVICE'][0]['VALUE'];
                $price->rate = $value['RATE'][0]['VALUE'];
                $this->list[] = $price;
            }
        } else if (isset($array['INTLRATERESPONSE'][0]['PACKAGE'][0]['SERVICE'])) { // if it is international shipping and it is OK
            foreach($array['INTLRATERESPONSE'][0]['PACKAGE'][0]['SERVICE'] as $value) {
                $price = new UspsIntPrice();
                $price->id = $value['ATTRIBUTES']['ID'];
                $price->pounds = $value['POUNDS'][0]['VALUE'];
                $price->ounces = $value['OUNCES'][0]['VALUE'];
                $price->mailtype = $value['MAILTYPE'][0]['VALUE'];
                $price->country = $value['COUNTRY'][0]['VALUE'];
                $price->rate = $value['POSTAGE'][0]['VALUE'];
                $price->svccommitments = $value['SVCCOMMITMENTS'][0]['VALUE'];
                $price->svcdescription = $value['SVCDESCRIPTION'][0]['VALUE'];
                $price->maxdimensions = $value['MAXDIMENSIONS'][0]['VALUE'];
                $price->maxweight = $value['MAXWEIGHT'][0]['VALUE'];
                $this->list[] = $price;
            }
        
        }
        
        //if (isset($error)) { echo '<pre>'; print_r($array); echo '</pre>';  }
        
        error_reporting($e);        
        
        return $this;
    }
}
class UspsError
{
    var $number;
    var $source;
    var $description;
    var $helpcontext;
    var $helpfile;
}
class UspsPrice
{
    var $mailservice;
    var $rate;
}
class UspsIntPrice
{
    var $id;
    var $rate;
}
?> 
