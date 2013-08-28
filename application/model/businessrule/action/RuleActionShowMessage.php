<?php


/**
 *
 * @author Integry Systems
 * @package application.model.businessrule.action
 */
class RuleActionShowMessage extends RuleAction implements RuleItemAction
{
	public function applyToItem(BusinessRuleProductInterface $item)
	{
		$session = new Session();
		$session->set('controllerMessage', $this->getFieldValue('message'));
	}

	public function getFields()
	{
		return array(array('type' => 'text', 'label' => '_message', 'name' => 'message'));
	}
}

?>