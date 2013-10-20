<?php


/**
 *
 * @author Integry Systems
 * @package application/model/businessrule/condition
 */
class RuleConditionIsNewsletterSubscriber extends RuleCondition implements RuleOrderCondition
{
	public function isApplicable()
	{
		$user = $this->getContext()->getUser();

		if (!$user)
		{
			return;
		}
		
		if (is_null($user->_isSubscriber))
		{
			$user->load();
			$subscriber = NewsletterSubscriber::getInstanceByEmail($user->email);
			if ($subscriber && !$subscriber->isEnabled)
			{
				$user->_isSubscriber = false;
			}
			else
			{
				$user->_isSubscriber = true;
			}
		}
		
		return $user->_isSubscriber;
	}

	public static function getSortorderBy()
	{
		return 5;
	}
}

?>
