<?php

ClassLoader::import('application.model.businessrule.RuleAction');
ClassLoader::import('application.model.tax.TaxClass');

/**
 *
 * @author Integry Systems
 * @package application.model.businessrule.action
 */
class RuleActionChangeShippingTaxClass extends RuleAction implements RuleOrderAction
{
	public function applyToOrder(CustomerOrder $order)
	{
		$taxClassID = $this->getFieldValue('taxClassID', -1);
		if($taxClassID > 0)
		{
			ActiveRecordModel::getApplication()->getConfig()->setRuntime('DELIVERY_TAX_CLASS', $taxClassID);
		}
		else
		{
			ActiveRecordModel::getApplication()->getConfig()->resetRuntime('DELIVERY_TAX_CLASS');
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