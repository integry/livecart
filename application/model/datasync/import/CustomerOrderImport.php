<?php

ClassLoader::import('application.model.datasync.DataImport');
ClassLoader::import('application.model.order.CustomerOrder');
ClassLoader::import('application.model.product.Product');
ClassLoader::import('application.controller.backend.CustomerOrderController');

/**
 *
 *  @package application.model.datasync.import
 *  @author Integry Systems
 */
class CustomerOrderImport extends DataImport
{
	public function getFields()
	{
		$this->loadLanguageFile('backend/CustomerOrder');
		$this->loadLanguageFile('backend/Shipment');
		$this->loadLanguageFile('backend/Product');
		
		$controller = new CustomerOrderController($this->application);
		foreach ($controller->getAvailableColumns() as $key => $data)
		{
			$fields[$key] = $this->translate($data['name']);
		}

		unset($fields['CustomerOrder.taxAmount']);
		unset($fields['CustomerOrder.dateCreated']);
		unset($fields['User.fullName']);
		
		// Billing address
		foreach (array('BillingAddress.firstName', 'BillingAddress.lastName', 'BillingAddress.countryID', 'BillingAddress.stateName',
						'BillingAddress.city', 'BillingAddress.address1', 'BillingAddress.postalCode', 'BillingAddress.phone') as $field)
		{
			$fields[$field] = $this->translate('ShippingAddress.' . array_pop(explode('.', $field)));		
		}
		
		$fields['OrderedItem.sku'] = $this->translate('Product.sku');
		$fields['OrderedItem.count'] = $this->translate('OrderedItem.count');
		$fields['OrderedItem.price'] = $this->translate('OrderedItem.price');
		$fields['OrderedItem.shipment'] = $this->translate('OrderedItem.shipment');
		$fields['OrderedItem.products'] = $this->translate('OrderedItem.products');
		
		$groupedFields = array();
		foreach ($fields as $field => $fieldName)
		{
			list($class, $field) = explode('.', $field, 2);
			$groupedFields[$class][$class . '.' . $field] = $fieldName;
		}

		return $groupedFields;
	}

	public function isRootCategory()
	{
		return false;
	}

	protected function getInstance($record, CsvImportProfile $profile)
	{
		CustomerOrder::allowEmpty(true);
		
		$fields = $profile->getSortedFields();
		if (isset($fields['CustomerOrder']['ID']))
		{
			$id = $record[$fields['CustomerOrder']['ID']];
			$this->setLastImportedRecordName($id);
			$order = CustomerOrder::getInstanceByID($id, true);
		}
		else if (isset($fields['CustomerOrder']['invoiceNumber']))
		{
			$id = $record[$fields['CustomerOrder']['invoiceNumber']];
			if (!$id)
			{
				return null;
			}
			
			$this->setLastImportedRecordName($id);
			$order = CustomerOrder::getInstanceByInvoiceNumber($id);
			
			if (!$order)
			{
				$order = CustomerOrder::getNewInstance(User::getNewInstance('import@import.com'));
				$order->invoiceNumber->set($id);

				$order->save();
				$order->finalize();
			}
		}
		else
		{
			return null;
		}
		
		if ($order)
		{
			$order->loadAll();
			return $order;
		}
	}

	protected function import_User($instance, $record, CsvImportProfile $profile)
	{
		$user = $this->getImporterInstance('User')->getInstance($record, $profile);
		if (!$user->email->get())
		{
			return;
		}
		
		$id = $this->importRelatedRecord('User', $user, $record, $profile);
		$instance->user->set(User::getInstanceByID($id, true));
		$instance->save();
	}

	protected function import_ShippingAddress($instance, $record, CsvImportProfile $profile)
	{
		$this->importAddress($instance, $record, $profile, 'shippingAddress');
	}

	protected function import_BillingAddress($instance, $record, CsvImportProfile $profile)
	{
		$this->importAddress($instance, $record, $profile, 'billingAddress');
	}

	private function importAddress($instance, $record, CsvImportProfile $profile, $type)
	{
		$address = $instance->$type->get();
		if (!$address)
		{
			$address = UserAddress::getNewInstance();
		}
		else
		{
			$address->load();
		}
		
		$profile->renameType(ucfirst($type), 'UserAddress');
		$id = $this->importRelatedRecord('UserAddress', $address, $record, $profile);
		$address = ActiveRecordModel::getInstanceByID('UserAddress', $id, true);
		$address->save();
		
		$instance->$type->set($address);
		
		// if billing or shipping address is not provided, use the same address
		$otherType = 'billingAddress' == $type ? 'shippingAddress' : 'billingAddress';
		if (!$instance->$otherType->get())
		{
			$clone = clone $address;
			$clone->save();
			$instance->$otherType->set($clone);
		}
		
		$instance->save();
	}
	
	protected function set_OrderedItem_products($instance, $value, $record, CsvImportProfile $profile)
	{
		if (!$value)
		{
			return;
		}

		$productProfile = new CsvImportProfile('OrderedItem');
		$productProfile->setField(0, 'OrderedItem.sku');
		$productProfile->setField(1, 'OrderedItem.count');
		$productProfile->setField(2, 'OrderedItem.price');
		$productProfile->setField(3, 'OrderedItem.shipment');
		foreach (explode(';', $value) as $product)
		{
			$item = explode(':', $product);
			$this->set_OrderedItem_sku($instance, $item[0], $item, $productProfile);
		}
		
		ActiveRecordModel::clearPool();
		$instance = CustomerOrder::getInstanceByID($instance->getID());
		$instance->loadAll();
		$instance->isFinalized->set(false);
		$instance->finalize(array('customPrice' => true, 'allowRefinalize' => true));
		$instance->save();
	}
	
	/**
	 *  Import or update an ordered product
	 */
	protected function set_OrderedItem_sku($instance, $value, $record, CsvImportProfile $profile)
	{
		if (!$value)
		{
			return;
		}
		$product = Product::getInstanceBySKU($value);
		if (!$product)
		{
			return;
		}

		$items = $instance->getItemsByProduct($product);
		
		// create initial shipment
		if (!$instance->getShipments()->size())
		{
			$shipment = Shipment::getNewInstance($instance);
			$shipment->save();
		}

		// any particular shipment?
		$shipment = $item = null;
		if ($profile->isColumnSet('OrderedItem.shipment'))
		{
			// internal indexes are 0-based, but the import references are 1-based
			$shipmentNo = $this->getColumnValue($record, $profile, 'OrderedItem.shipment') - 1;
			
			if (is_numeric($this->getColumnValue($record, $profile, 'OrderedItem.shipment')))
			{
				foreach ($instance->getShipments() as $key => $shipment)
				{
					if ($key == $shipmentNo)
					{
						break;
					}
					
					$shipment = null;
				}
				
				// create a new shipment
				if (!$shipment)
				{
					$shipment = Shipment::getNewInstance($instance);
					$shipment->save();
				}
				
				foreach ($items as $item)
				{
					if ($item->shipment->get() == $shipment)
					{
						break;
					}
					
					unset($item);
				}
			}
		}
		
		if (!$item)
		{
			$item = array_shift($items);
		}

		if (!$item)
		{
			$count = $this->getColumnValue($record, $profile, 'OrderedItem.count');
			$item = OrderedItem::getNewInstance($instance, $product, max(1, $count));
			$instance->addItem($item);
		}
		
		if ($profile->isColumnSet('OrderedItem.count'))
		{
			$count = $this->getColumnValue($record, $profile, 'OrderedItem.count');
			$item->count->set(max(1, $count));
		}

		if ($profile->isColumnSet('OrderedItem.price'))
		{
			$item->price->set($this->getColumnValue($record, $profile, 'OrderedItem.price'));
		}

		if (!$shipment)
		{
			$shipment = $instance->getShipments()->get(0);
		}
		
		$item->shipment->set($shipment);
		$item->save();
		
		$instance->finalize(array('customPrice' => true, 'allowRefinalize' => true));
	}

	public function set_status($instance, $value)
	{
		if (!is_numeric($value))
		{
			switch (strtolower($value))
			{
				case 'new':
					$value = CustomerOrder::STATUS_NEW;
					break;
				case 'processing':
					$value = CustomerOrder::STATUS_PROCESSING;
					break;
				case 'awaiting':
				case 'awaiting shipment':
					$value = CustomerOrder::STATUS_AWAITING;
					break;
				case 'shipped':
					$value = CustomerOrder::STATUS_SHIPPED;
					break;
				case 'returned':
					$value = CustomerOrder::STATUS_RETURNED;
					break;
				default:
					$value = (int)$value;
			}
		}

		if (strlen($value))
		{
			$instance->setStatus($value);
		}
	}
	
	protected function getReferencedData()
	{
		return array('User', 'BillingAddress', 'ShippingAddress');
	}
}

?>
