<?php


/**
 *
 * @author Integry Systems
 * @package application/model/businessrule/action
 */
class RuleActionChangeTaxClass extends RuleAction implements RuleItemAction
{
	public function applyToItem(BusinessRuleProductInterface $item)
	{
		$taxClassID = $this->readAttribute('taxClassID', -1);
		if($taxClassID > 0)
		{
			$product = $item->getProduct();
			if($product instanceof Product)
			{
				// ff('setting custom tax class ID: '.$taxClassID);
				$product->setTemporaryTaxClass(TaxClass::getInstanceByID($taxClassID));
			}
		}
	}

	public function getFields()
	{
		$allTaxClassesArray = array();
		foreach(TaxClass::getAllClasses()->toArray() as $instanceArray)
		{
			$allTaxClassesArray[$instanceArray['ID']] = $instanceArray['name_lang'];
		}
		return array(array('type' => 'select', 'label' => '_tax_class', 'name' => 'taxClassID', 'options'=>$allTaxClassesArray));
	}
}

?>