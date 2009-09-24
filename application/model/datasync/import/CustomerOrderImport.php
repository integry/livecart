<?php

ClassLoader::import('application.model.datasync.DataImport');
ClassLoader::import('application.model.order.CustomerOrder');
ClassLoader::import('application.model.product.Product');

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

	public function importInstance($record, CsvImportProfile $profile)
	{
		$impReq = new Request();
		$defLang = $this->application->getDefaultLanguageCode();
		$references = array('DefaultImage' => 'ProductImage', 'Manufacturer');
		$fields = $profile->getSortedFields();

		if (isset($fields['CustomerOrder']['ID']))
		{
			$id = $record[$fields['CustomerOrder']['ID']];
			$instance = CustomerOrder::getInstanceByID($id, true);
		}
		else
		{
			die('wat');
			return;
		}

		foreach ($profile->getFields() as $csvIndex => $field)
		{
			$column = $field['name'];
			$params = $field['params'];

			if (!isset($record[$csvIndex]) || empty($column))
			{
				continue;
			}

			$value = $record[$csvIndex];

			list($className, $field) = explode('.', $column, 2);

			if ('status' == $field)
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

		$instance->save();

		$instance->__destruct();
		$instance->destruct(true);

		ActiveRecord::clearPool();

		return true;
	}
}

?>