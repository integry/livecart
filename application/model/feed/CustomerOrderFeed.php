<?php

ClassLoader::import('library.activerecord.ARFeed');
ClassLoader::import('application.model.order.CustomerOrder');

/**
 * Order data feed
 *
 * @author Integry Systems
 * @package application.controller
 */
class CustomerOrderFeed extends ARFeed
{
	protected $productFilter;

	public function __construct(ARSelectFilter $filter)
	{
		parent::__construct($filter, 'CustomerOrder', array('User'));
	}

	protected function postProcessData()
	{
	}
}

?>