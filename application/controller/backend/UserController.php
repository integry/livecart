<?php

ClassLoader::import('application.controller.backend.abstract.StoreManagementController');
ClassLoader::import('application.controller.backend.*');
ClassLoader::import('application.model.user.*');
ClassLoader::import('application.helper.massAction.MassActionInterface');

/**
 *
 * @package application.controller.backend
 * @author Integry Systems
 * @role user
 */
class UserController extends StoreManagementController
{
	public function info()
	{
		$user = User::getInstanceById((int)$this->request->get('id'), ActiveRecord::LOAD_DATA, array('UserGroup'));

		$availableUserGroups = array('' => $this->translate('_default_user_group'));
		foreach(UserGroup::getRecordSet(new ARSelectFilter()) as $group)
		{
			$availableUserGroups[$group->getID()] = $group->name->get();
		}

		$response = new ActionResponse();
		$response->set('countries', array_merge(array('' => ''), $this->application->getEnabledCountries()));
		$form = self::createUserForm($this, $user, $response);
		$response->set('form', $form);
		$response->set('shippingAddressStates', State::getStatesByCountry($form->get('shippingAddress_countryID')));
		$response->set('billingAddressStates', State::getStatesByCountry($form->get('billingAddress_countryID')));
		$user->loadAddresses();
		$response->set('someUser', $user->toArray());
		$response->set('availableUserGroups', $availableUserGroups);

		return $response;
	}

	/**
	 * @role create
	 */
	public function create()
	{
		return $this->save(null);
	}

	/**
	 * @role update
	 */
	public function update()
	{
		$user = User::getInstanceByID((int)$this->request->get('id'), true);

		$user->loadAddresses();

		return $this->save($user);
	}

	/**
	 * @return RequestValidator
	 */
	public static function createUserFormValidator(StoreManagementController $controller, $user = false, $quickEditValidation=false)
	{
		$inst = new UserController(ActiveRecordModel::getApplication());
		$validator = $inst->getValidator("UserForm", $controller->getRequest());

		$validator->addCheck('email', new IsNotEmptyCheck($controller->translate('_err_email_empty')));
		$validator->addCheck('email', new IsValidEmailCheck($controller->translate('_err_invalid_email')));
		$validator->addCheck('firstName', new IsNotEmptyCheck($controller->translate('_err_first_name_empty')));
		$validator->addCheck('lastName', new IsNotEmptyCheck($controller->translate('_err_last_name_empty')));

		$passwordLengthStart = 6;
		$passwordLengthEnd = 30;
		$allowEmpty = $user;

		$validator->addCheck('password',
			new IsLengthBetweenCheck(
				sprintf($controller->translate('_err_password_lenght_should_be_in_interval'), $passwordLengthStart, $passwordLengthEnd),
				$passwordLengthStart, $passwordLengthEnd, $allowEmpty
			));

		$validator->addCheck('userGroupID', new IsNumericCheck($controller->translate('_err_invalid_group')));

		if (!$user)
		{
			$user = ActiveRecordModel::getNewInstance('User');
		}

		if($quickEditValidation !== true)
		{
			$user->getSpecification()->setValidation($validator);
		}

		return $validator;
	}

	public function generatePassword()
	{
		ClassLoader::import("library.text.Password");
		return new RawResponse(Password::create(10, Password::MIX));
	}

	/**
	 * @return Form
	 */
	public static function createUserForm(StoreManagementController $controller, User $user = null, ActionResponse $response)
	{
		$form = new Form(self::createUserFormValidator($controller, $user));

		$userArray = array();
		if($user)
		{
			$userArray = array_merge($userArray, $user->toFlatArray());

			$user->loadAddresses();

			foreach (array('defaultShippingAddress' => 'shippingAddress_', 'defaultBillingAddress' => 'billingAddress_') as $field => $prefix)
			{
				if ($user->$field->get())
				{
					$user->$field->get()->load(array('UserAddress'));
					$address = $user->$field->get()->userAddress->get();
					$addressArray = $address->toFlatArray();
					$addresses[] = $addressArray;
					foreach($addressArray as $property => $value)
					{
						if ($property == 'State')
						{
							$property = 'stateID';
						}

						$userArray[$prefix . $property] = $value;
					}
				}
				else
				{
					$addresses[] = array();
					$address = UserAddress::getNewInstance();
				}

				$address->getSpecification()->setFormResponse($response, $form, $prefix);
			}

			if(!$user->defaultBillingAddress->get() ||
			!$user->defaultBillingAddress->get() ||
			(array_diff_key($addresses[0], array('ID' => 0)) == array_diff_key($addresses[1], array('ID' => 0))))
			{
				$userArray['sameAddresses'] = 1;
			}
		}
		else
		{
			foreach (array('shippingAddress_', 'billingAddress_') as $prefix)
			{
				UserAddress::getNewInstance()->getSpecification()->setFormResponse($response, $form, $prefix);
			}

			$userArray['sameAddresses'] = 1;
		}

		$form->setData($userArray);

		if (!$user)
		{
			$user = ActiveRecordModel::getNewInstance('User');
		}
		$user->getSpecification()->setFormResponse($response, $form);

		return $form;
	}

	/**
	 * @role mass
	 */
	public function processMass()
	{
		ClassLoader::import('application.helper.massAction.UserMassActionProcessor');

		$filter = new ARSelectFilter();

		$id = (int)$this->request->get('id');
		if($id > 0)
		{
			$filter->setCondition(new EqualsCond(new ARFieldHandle('User', 'userGroupID'), $id));
		}
		else if($id == -1)
		{
			$filter->setCondition(new IsNullCond(new ARFieldHandle('User', 'userGroupID')));
		}
		else if($id != -2)
		{
			return;
		}

		$mass = new UserMassActionProcessor(new ActiveGrid($this->application, $filter, 'User'));
		$mass->setCompletionMessage($this->translate('_mass_action_succeed'));
		return $mass->process(User::LOAD_REFERENCES);
	}

	public function isMassCancelled()
	{
		ClassLoader::import('application.helper.massAction.UserMassActionProcessor');

		return new JSONResponse(array('isCancelled' => UserMassActionProcessor::isCancelled($this->request->get('pid'))));
	}

	/**
	 *  Return a list of states for the selected country
	 *  @return JSONResponse
	 */
	public function states()
	{
		$states = State::getStatesByCountry($this->request->get('country'));
		return new JSONResponse($states);
	}

	public function selectPopup()
	{
		$userGroups = array();
		$userGroups[] = array('ID' => -2, 'name' => $this->translate('_all_users'), 'rootID' => 0);
		$userGroups[] = array('ID' => -1, 'name' => $this->translate('_default_user_group'), 'rootID' => -2);

		foreach(UserGroup::getRecordSet(new ARSelectFilter())->toArray() as $group)
		{
			$userGroups[] = array('ID' => $group['ID'], 'name' => $group['name'], 'rootID' => -2);
		}

		return new ActionResponse('userGroups', $userGroups);
	}

	private function save(User $user = null)
	{
   		$validator = self::createUserFormValidator($this, $user);
		if ($validator->isValid())
		{
			$email = $this->request->get('email');
			$password = $this->request->get('password');

			if(($user && $email != $user->email->get() && User::getInstanceByEmail($email)) ||
			   (!$user && User::getInstanceByEmail($email)))
			{
				return new JSONResponse(false, 'failure', $this->translate('_err_this_email_is_already_being_used_by_other_user'));
			}

			if($groupID = (int)$this->request->get('UserGroup'))
			{
				$group = UserGroup::getInstanceByID((int)$groupID);
			}
			else
			{
				$group = null;
			}

		  	if (!$user)
			{
				$user = User::getNewInstance($email, $password, $group);
			}

			$user->loadRequestData($this->request);

			$user->userGroup->set($group);

			if(!empty($password))
			{
				$user->setPassword($password);
			}

			$user->save();

			$this->saveAddresses($user);

			return new JSONResponse(array('user' => $user->toFlatArray()), 'success', $this->translate('_user_details_were_successfully_saved'));
		}
		else
		{
			return new JSONResponse(array('errors' => $validator->getErrorList()), 'failure', $this->translate('_could_not_save_user_details'));
		}
	}

	private function saveAddresses(User $user = null)
	{
		$user->loadAddresses();

		foreach (array('defaultBillingAddress' => 'billingAddress', 'defaultShippingAddress' => 'shippingAddress') as $field => $prefix)
		{
			$address = $user->$field->get() ? $user->$field->get()->userAddress->get() : UserAddress::getNewInstance();
			$address->loadRequestData($this->request, $prefix . '_');

			// get address state
			if ($stateID = $this->request->get($prefix . '_stateID'))
			{
				$address->state->set(ActiveRecordModel::getInstanceByID('State', $stateID, ActiveRecordModel::LOAD_DATA));
				$address->stateName->setNull();
			}
			else
			{
				$address->stateName->set($this->request->get($prefix . '_stateName'));
				$address->state->setNull();
			}

			$modified = false;
			foreach (ActiveRecordModel::getSchemaInstance('UserAddress')->getFieldList() as $f)
			{
				if ($address->getFieldValue($f->getName()))
				{
					$modified = true;
				}
			}

			if ($modified)
			{
				$address->save();

				if(!$user->$field->get())
				{
					$addressType = call_user_func_array(array($prefix, 'getNewInstance'), array($user, $address));
					$addressType->save();
				}
			}
		}

		if($this->request->get('sameAddresses') && $user->defaultBillingAddress->get())
		{
			$shippingAddress = ShippingAddress::getNewInstance($user, clone $user->defaultBillingAddress->get()->userAddress->get());
			$shippingAddress->save();
		}
	}
}
?>