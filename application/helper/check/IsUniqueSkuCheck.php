<?php


/**
 * Checks if product SKU is unique
 *
 * @package application.helper.check
 * @author Integry Systems 
 */
class IsUniqueSkuCheck extends Check
{
	var $product;
	
	public function __construct($errorMessage, Product $product)
	{
		parent::__construct($errorMessage);
		$this->product = $product;  
	}
	
	public function isValid($value)
	{
		$filter = new ARSelectFilter();
		$cond = new EqualsCond(new ARFieldHandle('Product', 'sku'), $value);
		if ($this->product->getID())
		{
		  	$cond->addAND(new NotEqualsCond(new ARFieldHandle('Product', 'ID'), $this->product->getID()));
		}
		$filter->setCondition($cond);	
		
		return (ActiveRecordModel::getRecordCount('Product', $filter) == 0);
	}
}

?>