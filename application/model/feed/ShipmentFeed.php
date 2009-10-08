<?php

ClassLoader::import('library.activerecord.ARFeed');
ClassLoader::import('application.model.order.Shipment');

/**
 * Order data feed
 *
 * @author Integry Systems
 * @package application.controller
 */
class ShipmentFeed extends ARFeed
{
	protected $productFilter;

	public function __construct(ARSelectFilter $filter)
	{
		parent::__construct($filter, 'Shipment', array('CustomerOrder'));
	}

	protected function postProcessData()
	{
		$addresses = array();
		foreach ($this->data as $key => $shipment)
		{
			$id = !empty($shipment['shippingAddressID']) ? $shipment['shippingAddressID'] : $shipment['CustomerOrder']['shippingAddressID'];
			$addresses[$id] = $key;
		}

		foreach (ActiveRecordModel::getRecordSetArray('UserAddress', select(in('UserAddress.ID', array_keys($addresses)))) as $address)
		{
			$this->data[$addresses[$address['ID']]]['ShippingAddress'] = $address;
		}
	}
}

?>