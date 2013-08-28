<?php


/**
 * Manage newsletters subscribers
 *
 * @package application.controller.backend
 * @author Integry Systems
 * @role newsletter
 */
class NewsletterSubscriberController extends ActiveGridController
{
	public function indexAction()
	{
		return $this->setGridResponse(new ActionResponse());
	}

	protected function getClassName()
	{
		return 'NewsletterSubscriber';
	}

	protected function getDefaultColumns()
	{
		return array('NewsletterSubscriber.ID', 'NewsletterSubscriber.email', 'NewsletterSubscriber.isEnabled');
	}
}

?>