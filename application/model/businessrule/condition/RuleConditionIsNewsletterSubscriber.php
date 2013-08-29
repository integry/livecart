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
			$subscriber = NewsletterSubscriber::getInstanceByEmail($user->email->get());
			if ($subscriber && !$subscriber->isEnabled->get())
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

	public static function getSortOrder()
	{
		return 5;
	}
}

?>
