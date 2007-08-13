<?php

include_once(dirname(__file__) . '/../abstract/ExternalPayment.php');

class PaypalWebsitePaymentsStandard extends ExternalPayment
{
    public function getUrl()
    {
        $url = 'https://www.' . ($this->getConfigValue('SANDBOX') ? 'sandbox.' : '') . 'paypal.com/cgi-bin/webscr';
        
        $params = array();
        $params['cmd'] = '_xclick';
        $params['business'] = $this->getConfigValue('EMAIL');
        $params['item_name'] = $this->getConfigValue('ITEM_NAME');
        $params['amount'] = $this->details->amount->get();
        $params['mc_currency'] = $this->details->currency->get();
        $params['custom'] = $this->details->invoiceID->get();
        $params['return'] = $this->getConfigValue('RETURN_URL');
        $params['notify_url'] = $this->getConfigValue('NOTIFY_URL');
        
        $pairs = array();
        foreach ($params as $key => $value)
        {
            $pairs[] = $key . '=' . urlencode($value);
        }
        
        return $url . '?' . implode('&', $pairs);
    }
    
    public function notify($requestArray)
    {
        // assign posted variables to local variables
        $paymentStatus = $requestArray['payment_status'];
        $paymentAmount = $requestArray['mc_gross'];
        $paymentCurrency = $requestArray['mc_currency'];
        $txn_id = $requestArray['txn_id'];
        $receiverEmail = $requestArray['receiver_email'];
        $payerEmail = $requestArray['payer_email'];

        // read the post from PayPal system and add 'cmd'
        $req = 'cmd=_notify-validate';

        foreach ($requestArray as $key => $value) 
        {
            $value = urlencode(stripslashes($value));
            $req .= "&".$key."=".$value;
        }

        // check that receiver_email is your Primary PayPal email
        if ($receiverEmail != $this->getConfigValue('EMAIL'))
        {
            throw new PaymentException('Invalid PayPal receiver e-mail');
        }

        // check that payment_amount/payment_currency are correct
        if ($paymentCurrency != $this->details->currency->get())
        {
            throw new PaymentException('Payment currency does not match order currency');
        }

        // post back to PayPal system to validate        
        $header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
        $fp = fsockopen ('www.' . ($this->getConfigValue('SANDBOX') ? 'sandbox.' : '') . 'paypal.com', 80, $errno, $errstr, 30);

        if (!$fp) 
        {
            throw new PaymentException('Could not connect to PayPal server');
        } 
        else 
        {
            fputs ($fp, $header . $req);

            while (!feof($fp)) 
            {
                $res = fgets ($fp, 1024);
                if (strcmp ($res, "VERIFIED") == 0) 
                {
                    if ($paymentStatus != 'Completed')
                    {
                        throw new PaymentException('Payment is not completed');
                    }
                }
                else if (strcmp ($res, "INVALID") == 0) 
                {
                    throw new PaymentException('Invalid response from PayPal');                  
                }
            }
            
            fclose ($fp);
        }
        
        $result = new TransactionResult();
		$result->gatewayTransactionID->set($requestArray['txn_id']);
		$result->amount->set($requestArray['mc_gross']);
		$result->currency->set($requestArray['mc_currency']);						
		$result->rawResponse->set($requestArray);        
        
        if ('Completed' == $requestArray['payment_status'])
        {
            $result->setTransactionType(TransactionResult::TYPE_SALE);
        }
        else
        {
            $result->setTransactionType(TransactionResult::TYPE_AUTH);
        }
        
        return $result;
    }
    
    public static function isVoidable()
    {
        return false;
    }
    
    public function void()
    {
        return false;
    }
}

?>