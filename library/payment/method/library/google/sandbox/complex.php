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
	$xml_merchant_private_data = "<item-note>Popular item: Check inventory and order more if needed</item-note>";
	$GCheckout = new gCart($merchant_id, $mercant_key, '2006-12-31T23:59:59', $xml_merchant_private_data);
	
	/**
	 * Add Merchant Checkout Flow Support
	 */
	$GCheckout->setMerchantCheckoutFlowSupport("http://www.example.com/edit", "http://www.example.com/shopping");
	
	/**
	 * Create Item
	 */
	$AARechargeableBatteryPack = new gItem('Dry Food Pack AA1453', 
										   'A pack of highly nutritious dried food for emergency - store in your garage for up to one year!!', 1, 35.00);
	
    $AARechargeableBatteryPack_SKU = "<item-sku>SK00000001</item-sku>";
    $AARechargeableBatteryPack->setPrivateItemData($AARechargeableBatteryPack_SKU);
	$AARechargeableBatteryPack->setTaxTableSelector("food");

	
	$MegaSoundPlayer = new gItem('HelloWorld 2GB MP3 Player', 'HelloWorld, the simple MP3 player', 1, 178.99);
	
	
	/**
	 * Add Item to Cart
	 */
	$GCheckout->addItems(array($AARechargeableBatteryPack, $MegaSoundPlayer));
	
	
	/**
	 * Create Default Tax Table
	 */
	$default_tax_rule = new gTaxRule(0.0775, COUNTRY_AREA_FULL_50);
	$default_tax_rule->setShippingTaxed(GCHECKOUT_TRUE);
	$default_tax_table = new gTaxTable("Default", array($default_tax_rule), TAX_TABLE_DEFAULT);
	$GCheckout->setDefaultTaxTable($default_tax_table);
	
	
	/**
	 * Alternate Food Tax Tables
	 */
	$tax_food_rule_CA = new gTaxRule(0.0225,null,array("CA"));
	$tax_food_rule_NY = new gTaxRule(0.0200, null, array("NY"));
	$food_tax_table   = new gTaxTable("food", array($tax_food_rule_CA, $tax_food_rule_NY));
	
	/**
	 * Alternate Drug Tax Tables
	 */
	$tax_drug_rule_ALL_States = new gTaxRule(0.0500, COUNTRY_AREA_ALL);
	$tax_drug_table			  = new gTaxTable("drugs", array($tax_drug_rule_ALL_States), TAX_TABLE_ALTERNATE, TAX_TABLE_STANDALONE );
	
	/**
	 * Set Alternate Tax Tables
	 */
	$GCheckout->setAlternateTaxTables(array($food_tax_table, $tax_drug_table));
	
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