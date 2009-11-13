<?php

ClassLoader::import('application.model.datasync.DataImport');
ClassLoader::import('application.model.user.User');
ClassLoader::import('application.controller.backend.UserGroupController');
ClassLoader::import('application.model.datasync.import.UserAddressImport');

/**
 *  Handles user data import logic
 *
 *  @package application.model.datasync.import
 *  @author Integry Systems
 */
class UserImport extends DataImport
{
	public function getFields()
	{
		$this->loadLanguageFile('backend/User');
		$this->loadLanguageFile('backend/UserGroup');

		$controller = new UserGroupController($this->application);
		foreach ($controller->getAvailableColumns() as $key => $data)
		{
			$fields[$key] = $this->translate($data['name']);
		}

		unset($fields['User.isOnline']);
		$fields['User.password'] = $this->translate('_password');
		$fields['User.group'] = $this->translate('_user_group');

		$groupedFields = $this->getGroupedFields($fields);

		$address = $groupedFields['UserAddress'];
		unset($groupedFields['UserAddress']);
		foreach (array('ShippingAddress', 'BillingAddress') as $type)
		{
			$typeAddress = array();
			foreach ($address as $field => $name)
			{
				list($foo, $field) = explode('.', $field);
				$typeAddress[$type . '.' . $field] = $name;
			}
			$groupedFields[$type] = $typeAddress;
		}

		unset($groupedFields['UserGroup']);

		return $groupedFields;
	}

	public function isRootCategory()
	{
		return false;
	}

	protected function getInstance($record, CsvImportProfile $profile)
	{
		$fields = $profile->getSortedFields();

		if (!empty($fields['User']['ID']))
		{
			$instance = User::getInstanceByID($record[$fields['User']['ID']], true);
		}
		else if (!empty($fields['User']['email']))
		{
			$instance = User::getInstanceByEmail($record[$fields['User']['email']]);
		}

		if (empty($instance))
		{
			$instance = User::getNewInstance('');
		}

		$this->setLastImportedRecordName($instance->email->get());
		return $instance;
	}

	protected function set_password($instance, $value)
	{
		$instance->setPassword($value);
	}

	protected function set_group($instance, $value)
	{
		$group = ActiveRecordModel::getRecordSet('UserGroup', select(eq('UserGroup.name', $value)))->shift();
		$instance->userGroup->set($group);
	}

	protected function getReferencedData()
	{
		return array('BillingAddress', 'ShippingAddress');
	}

	protected function import_ShippingAddress($instance, $record, CsvImportProfile $profile)
	{
		$this->importAddress($instance, $record, $profile, 'ShippingAddress');
	}

	protected function import_BillingAddress($instance, $record, CsvImportProfile $profile)
	{
		$this->importAddress($instance, $record, $profile, 'BillingAddress');
	}

	private function importAddress($instance, $record, CsvImportProfile $profile, $type)
	{
		$field = 'default' . $type;
		$instance->loadAddresses();
		if (!$instance->$field->get())
		{
			$address = UserAddress::getNewInstance();
			$address->firstName->setAsModified();
			$address->save();
			$shippingAddress = call_user_func(array($type, 'getNewInstance'), $instance, $address);
			$shippingAddress->save();
		}
		else
		{
			$address = $instance->$field->get()->userAddress->get();
		}

		$id = $this->importRelatedRecord('UserAddress', $address, $record, $profile);
		$related = ActiveRecordModel::getInstanceByID('UserAddress', $id, true);
		foreach (array('firstName', 'lastName', 'companyName') as $field)
		{
			if (!$related->$field->get() && $instance->$field->get())
			{
				$related->$field->set($instance->$field->get());
			}
		}
		$related->save();
	}
}

?>