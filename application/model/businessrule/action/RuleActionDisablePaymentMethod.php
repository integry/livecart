<?php

ClassLoader::import('application.model.businessrule.RuleAction');
ClassLoader::import('application.model.tax.TaxClass');

/**
 *
 * @author Integry Systems
 * @package application.model.businessrule.action
 */
class RuleActionDisablePaymentMethod extends RuleAction implements RuleOrderAction
{
	public function applyToOrder(CustomerOrder $order)
	{
		$config = ActiveRecordModel::getApplication()->getConfig();
		$method = $this->getFieldValue('method', '');

		if ($method == $config->get('CC_HANDLER'))
		{
			$config->setRuntime('CC_ENABLE', false);
		}

		foreach (array('OFFLINE_HANDLERS', 'EXPRESS_HANDLERS', 'PAYMENT_HANDLERS') as $key)
		{
			$setting = $config->get($key);
			if (!$setting)
			{
				continue;
			}

			unset($setting[$method]);
			$config->setRuntime($key, $setting);
		}
	}

	public function getFields()
	{
		$app = ActiveRecordModel::getApplication();
		$app->loadLanguageFile('backend/Settings');
		$app->loadLanguageFiles();
		$config = $app->getConfig();

		$handlers = array();
		foreach (array_merge($app->getPaymentHandlerList(true), array($config->get('CC_HANDLER')), $app->getExpressPaymentHandlerList(true)) as $class)
		{
			$handlers[$class] = $app->translate($class);
		}

		foreach (OfflineTransactionHandler::getEnabledMethods() as $offline)
		{
			$handlers[$offline] = OfflineTransactionHandler::getMethodName($offline);
		}

		return array(array('type' => 'select', 'label' => '_payment_method', 'name' => 'method', 'options'=> $handlers));
	}
}

?>