<?php

ClassLoader::import('application.model.datasync.DataImport');
ClassLoader::import('application.model.order.CustomerOrder');
ClassLoader::import('application.model.product.Product');

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

		$fields['CustomerOrder.ID'] = $this->translate('CustomerOrder.ID');
		$fields['CustomerOrder.status'] = $this->translate('CustomerOrder.status');

		unset($fields['Product.reviewCount']);
		unset($fields['hiddenType']);
		unset($fields['ProductImage.url']);

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
		$fields = $profile->getSortedFields();
		if (isset($fields['CustomerOrder']['ID']))
		{
			$id = $record[$fields['CustomerOrder']['ID']];
			$this->setLastImportedRecordName($id);
			return CustomerOrder::getInstanceByID($id, true);
		}
		else
		{
			return null;
		}
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
					$value = null;
			}
		}

		if (strlen($value))
		{
			$instance->setStatus($value);
		}
	}
}

?>