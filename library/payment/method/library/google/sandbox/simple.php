<?php

	/**
	 * Simple Cart Example using phpGCheckout
	 * 
	 * @author  Ron Howard
	 * 
	 */
	
	$OUTPUT_XML = false;	/* Show XML or HTML Form */
	
	/**
	 * Include phpGCheckout configuration file
	 *
	 */
	require_once('../config.php');
	
	
	/**
	 * Your Merchant IDs and Keys here
	 */
	$merchant_id = '';
	$mercant_key = '';
	
	
	/**
	 * Create a new Cart
	 */
	$GCheckout = new gCart($merchant_id, $mercant_key);
	
	/**
	 * Create Item
	 */
	$MegaSoundPlayer = new gItem('HelloWorld 2GB MP3 Player', 'HelloWorld, the simple MP3 player', 1, 159.99);
	
	
	/**
	 * Add Item to Cart
	 */
	$GCheckout->addItems(array($MegaSoundPlayer));
	
	
	if($OUTPUT_XML){
		header('Content-type: text/xml');
		echo $GCheckout->getCart();
	}
	else {
?>
		<form action="https://sandbox.google.com/cws/v2/Merchant/<?php echo $merchant_id; ?>/checkout" method="post">
		<input type="hidden" name="cart" value="<?php echo base64_encode($GCheckout->getCart());?>">
		<input type="hidden" name="signature" value="<?php echo base64_encode($GCheckout->getSignature($GCheckout->getCart()));?>">
		<input type="image" name="Google Checkout" alt="Fast checkout through Google"   src="http://checkout.google.com/buttons/checkout.gif?merchant_id=<?php echo $merchant_id;?>&w=180&h=46
		&style=white&variant=text&loc=en_US" height="46" width="180">
		</form>
<?php
	}
?>