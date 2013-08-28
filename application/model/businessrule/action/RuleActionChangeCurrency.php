<?php


/**
 *
 * @author Integry Systems
 * @package application.model.businessrule.action
 */
class RuleActionChangeCurrency extends RuleAction implements RuleOrderAction
{
	public function applyToOrder(CustomerOrder $order)
	{
		$currencyID = $this->getFieldValue('currency', '');
		if($currencyID)
		{
			$currency = Currency::getInstanceById($currencyID);
			$order->changeCurrency($currency);
			ActiveRecordModel::getApplication()->getRequest()->set('currency', $currencyID);
		}
	}

	public function getFields()
	{
		$currencies = ActiveRecordModel::getApplication()->getCurrencyArray();
		return array(array('type' => 'select', 'label' => '_currency', 'name' => 'currency', 'options'=> array_combine($currencies, $currencies)));
	}
}

?>