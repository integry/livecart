<?php


/**
 *  Handles user address import logic
 *
 *  @package application.model.datasync.import
 *  @author Integry Systems
 */
class UserAddressImport extends DataImport
{
	public function getFields()
	{
		$this->loadLanguageFile('backend/User');
		$this->loadLanguageFile('backend/UserGroup');

		foreach (ActiveGridController::getSchemaColumns('UserAddress', $this->application) as $key => $data)
		{
			$fields[$key] = $this->translate($data['name']);
		}

		$groupedFields = $this->getGroupedFields($fields);

		$identify = array();
		foreach (array('ID', 'email', 'isDefault', 'isShipping') as $field)
		{
			$field = 'AddressUser.' . $field;
			$identify[$field] = $this->application->translate($field);
		}

		$groupedFields['AddressUser'] = $identify;

		return $groupedFields;
	}

	public function isRootCategory()
	{
		return false;
	}

	//
	// todo: fix updating, importing customer billing/shipping addresses without this
	//       
	public function getClassName($classNameToCompare=null, $instanceClassName=null)
	{
		if($instanceClassName == 'UserAddress')
		{
			// pretend that 'ShippingAddress' or 'BillingAddress' are equal to 'UserAddress'
			return in_array($classNameToCompare, array('ShippingAddress', 'BillingAddress')) ? $classNameToCompare : $instanceClassName;
		}
		return parent::getClassName();
	}

	protected function getInstance($record, CsvImportProfile $profile)
	{
		
		pp('User address import get instance');
		
		
		$fields = $profile->getSortedFields();
		if (isset($fields['UserAddress']['ID']))
		{
			$instance = ActiveRecordModel::getInstanceByID('UserAddress', $record[$fields['UserAddress']['ID']], true);
		}
		else if (isset($fields['AddressUser']['ID']))
		{
			$owner = User::getInstanceByID($record[$fields['AddressUser']['ID']], true);
		}
		else if (isset($fields['AddressUser']['email']))
		{
			$owner = User::getInstanceByEmail($record[$fields['AddressUser']['email']]);
		}

		if (isset($owner))
		{
			if ($profile->isColumnSet('AddressUser.isShipping'))
			{
				$type = $this->evalBool(strtolower($record[$profile->getColumnIndex('AddressUser.isShipping')])) ? 'ShippingAddress' : 'BillingAddress';
			}
			else
			{
				$type = 'BillingAddress';
			}

			$owner->loadAddresses();
		}

		if (empty($instance))
		{
			if (empty($owner))
			{
				return;
			}

			$isDefault = $profile->isColumnSet('AddressUser.isDefault') && $this->evalBool(strtolower($record[$profile->getColumnIndex('AddressUser.isDefault')]));
			if ($isDefault)
			{
				$field = 'default' . $type;
				$addressType = $owner->$field->get();
				$instance = $addressType->userAddress->get();
			}

			if (empty($addressType))
			{
				$instance = UserAddress::getNewInstance();
				$addressType = call_user_func_array(array($type, 'getNewInstance'), array($owner, $instance));

				if ($isDefault)
				{
					$owner->$field->set($addressType);
				}
			}

			$addressType->userAddress->set($instance);
			$instance->addressType = $addressType;
		}

		return $instance;
	}

	protected function afterSave(UserAddress $instance, $record)
	{
				
		if ($instance->addressType)
		{
			$instance->addressType->save();
		}
	}
}

?>
