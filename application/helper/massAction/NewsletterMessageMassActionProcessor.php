<?php

include_once dirname(__file__) . '/MassActionProcessor.php';

/**
 * @package application.helper.massAction
 * @author Integry Systems
 */
class NewsletterMessageMassActionProcessor extends MassActionProcessor
{
	protected function processRecord(NewsletterMessage $product)
	{
		$act = $this->getAction();
		$field = $this->getField();
	}
}

?>