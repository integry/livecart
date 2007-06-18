<?php

ClassLoader::import("application.model.order.Shipment");
ClassLoader::import("application.model.tax.TaxRate");

/**
 * Tax amount for a particular shipment. One shipment can have multiple taxes, depending on
 * how they are set up for a particular system.
 *
 * @package application.model.order
 * @author Integry Systems <http://integry.com>   
 */
class ShipmentTax extends ActiveRecordModel
{
	public static function defineSchema($className = __CLASS__)
	{
		$schema = self::getSchemaInstance($className);
		$schema->setName(__class__);
		
		$schema->registerField(new ARPrimaryKeyField("ID", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("taxRateID", "TaxRate", "ID", "TaxRate", ARInteger::instance()));
		$schema->registerField(new ARForeignKeyField("shipmentID", "Shipment", "ID", "Shipment", ARInteger::instance()));
		$schema->registerField(new ARField("amount", ARFloat::instance()));
	}
	
	/**
	 * Create a new instance
	 * 
     * @return ShipmentTax
	 */
	public static function getNewInstance(TaxRate $taxRate, Shipment $shipment)
	{
	  	$instance = ActiveRecordModel::getNewInstance(__CLASS__);	  	
	  	$instance->taxRate->set($taxRate);
	  	$instance->shipment->set($shipment);
	  	$instance->recalculateAmount();
	  	return $instance;
	}
	
	/**
	 * Recalculate tax amount
	 */
    public function recalculateAmount()
    {
        $this->shipment->get()->recalculateAmounts();
        $totalAmount = $this->shipment->get()->amount->get();
        if ($this->shipment->get()->isLoaded())
        {
            $totalAmount += $this->shipment->get()->shippingAmount->get();
        } 
        
        $taxAmount = $totalAmount * ($this->taxRate->get()->rate->get() / 100);
        $this->amount->set(round($taxAmount, 2));
    }     
    
    public function getAmountByCurrency(Currency $currency)
    {
        $amountCurrency = $this->shipment->get()->amountCurrency->get();
        return $currency->convertAmount($amountCurrency, $this->amount->get());                
    }
    
    public function toArray()
    {
        $array = parent::toArray();
        $array['formattedAmount'] = array();
        
        $amountCurrency = $this->shipment->get()->amountCurrency->get();
        $currencies = Store::getInstance()->getCurrencySet();

        // get and format prices
        foreach ($currencies as $id => $currency)
        {
            $array['formattedAmount'][$id] = $currency->getFormattedPrice($this->getAmountByCurrency($currency));
        }
        
        return $array;
    }   
}