<?php

use Phalcon\Validation\Validator;
use user\User;
use user\UserGroup;

/**
 *
 * @package application/controller/backend
 * @author Integry Systems
 * @role user
 */
class UserController extends ControllerBackend
{
	public function editAction()
	{
		$availableUserGroups = array('' => $this->translate('_default_user_group'));
		foreach(UserGroup::query()->execute() as $group)
		{
			$availableUserGroups[$group->getID()] = $group->name;
		}

		$this->set('availableUserGroups', $availableUserGroups);
		
		$this->setValidator($this->buildValidator());
/*
		$this->set('countries', array_merge(array('' => ''), $this->application->getEnabledCountries()));
		$form = self::createUserForm($this, $user, $response);
		$this->set('form', $form);
		$this->set('shippingAddressStates', State::getStatesByCountry($form->get('shippingAddress_countryID')));
		$this->set('billingAddressStates', State::getStatesByCountry($form->get('billingAddress_countryID')));
*/
	}

	public function getAction()
	{
		if ((int)$this->request->get('id'))
		{
			$user = User::getInstanceByID($this->request->get('id'));
			$user->loadSpecification();
			//$product->loadPricing();

			$arr = $user->toArray();
			$arr['password'] = '';
			$arr['UserGroup'] = $arr['userGroupID'];
		}
		else
		{
/*
			$cat = Category::getInstanceByID($this->request->get('categoryID'), true);
			$product = Product::getNewInstance($cat);
			$arr = $product->toArray();
*/
		}

		echo json_encode($arr);
	}
	
	public function eavAction()
	{
		$user = User::getInstanceByID($this->request->get('id'));
		$manager = new \eav\EavFieldManager(\eav\EavField::getClassID($user));
		$manager->loadFields();
		
		echo json_encode($manager->toArray());
	}

	/**
	 * @role create
	 */
	public function createAction()
	{
		return $this->save(null);
	}

	/**
	 * @role update
	 */
	public function updateAction()
	{
		$user = User::getInstanceByID((int)$this->request->getJson('ID'));

		//$user->loadAddresses();

		return $this->save($user);
	}

	/**
	 * @return \Phalcon\Validation
	 */
	protected function buildValidator()
	{
		$validator = $this->getValidator("UserForm");

		$validator->add('email', new Validator\PresenceOf(array('message' => $this->translate('_err_email_empty'))));
		$validator->add('email', new Validator\Email(array('message' => $this->translate('_err_invalid_email'))));
		$validator->setFilters('email', 'email');
		
		$validator->add('firstName', new Validator\PresenceOf(array('message' => $this->translate('_err_first_name_empty'))));
//		$validator->add('lastName', new Validator\PresenceOf(array('message' => $this->translate('_err_last_name_empty')));

		$passwordLengthStart = 6;
		$passwordLengthEnd = 30;

/*
		$validator->add('password', new Validator\StringLength(array(
			'min' => $passwordLengthStart,
			'messageMinimum' => sprintf($this->translate('_err_short_password'), $passwordLengthEnd)
			)));
		$validator->add('password', new Validator\PresenceOf(array('message' => $this->translate('_err_enter_password'))));
*/
		//$validator->add('userGroupID', new IsNumericCheck($controller->translate('_err_invalid_group')));

		return $validator;
	}

	public function generatePasswordAction()
	{
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
				if ($user->$field)
				{
					$user->$field->load(array('UserAddress'));
					$address = $user->$field->userAddress;
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

			if(!$user->defaultBillingAddress ||
			!$user->defaultBillingAddress ||
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
			$user = new User;
		}
		$user->getSpecification()->setFormResponse($response, $form);

		return $form;
	}

	/**
	 * @role mass
	 */
	public function processMassAction()
	{

		$filter = new ARSelectFilter();

		$id = (int)$this->request->get('id');
		if($id > 0)
		{
			$filter->setCondition('User.userGroupID = :User.userGroupID:', array('User.userGroupID' => $id));
		}
		else if($id == -1)
		{
			$filter->setCondition(new IsNullCond('User.userGroupID'));
		}
		else if($id != -2)
		{
			return;
		}

		$mass = new UserMassActionProcessor(new ActiveGrid($this->application, $filter, 'User'));
		$mass->setCompletionMessage($this->translate('_mass_action_succeed'));
		return $mass->process(User::LOAD_REFERENCES);
	}

	public function isMassCancelledAction()
	{

		return new JSONResponse(array('isCancelled' => UserMassActionProcessor::isCancelled($this->request->get('pid'))));
	}

	/**
	 *  Return a list of states for the selected country
	 *  @return JSONResponse
	 */
	public function statesAction()
	{
		$states = State::getStatesByCountry($this->request->get('country'));
		return new JSONResponse($states);
	}

	public function selectPopupAction()
	{
		$userGroups = array();
		$userGroups[] = array('ID' => -2, 'name' => $this->translate('_all_users'), 'rootID' => 0);
		$userGroups[] = array('ID' => -1, 'name' => $this->translate('_default_user_group'), 'rootID' => -2);

		foreach(UserGroup::getRecordSet(new ARSelectFilter())->toArray() as $group)
		{
			$userGroups[] = array('ID' => $group['ID'], 'name' => $group['name'], 'rootID' => -2);
		}

		$this->set('userGroups', $userGroups);
	}

	private function save(User $user = null)
	{
   		$validator = $this->buildValidator();
		if ($validator->validate($this->request))
		{
			$email = $this->request->getJson('email');
			$password = $this->request->getJson('password');

			if(($user && $email != $user->email && User::getInstanceByEmail($email)) ||
			   (!$user && User::getInstanceByEmail($email)))
			{
				return new JSONResponse(false, 'failure', $this->translate('_err_this_email_is_already_being_used_by_other_user'));
			}

			if($groupID = (int)$this->request->getJson('UserGroup'))
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

			$pass = $user->password;
			$user->loadRequestData($this->request);
			$user->password = $pass;

			$user->userGroupID = $group ? $group->getID() : null;

			if(!empty($password))
			{
				$user->setPassword($password);
			}

			$user->save();
			$user->getSpecification()->save();

			var_dump($user->password);

			//$this->saveAddresses($user);

			echo json_encode($user->toArray());
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
			$address = $user->$field ? $user->$field->userAddress : UserAddress::getNewInstance();
			$address->loadRequestData($this->request, $prefix . '_');

			// get address state
			if ($stateID = $this->request->get($prefix . '_stateID'))
			{
				$address->state->set(State::getInstanceByID($stateID, ActiveRecordModel::LOAD_DATA));
				$address->stateName = null;
			}
			else
			{
				$address->stateName->set($this->request->get($prefix . '_stateName'));
				$address->state = null;
			}

			$modified = false;
			foreach (ActiveRecordModel::getSchemaInstance('UserAddress')->getFieldList() as $f)
			{
				if ($address->readAttribute($f->getName()))
				{
					$modified = true;
				}
			}

			if ($modified)
			{
				$address->save();

				if(!$user->$field)
				{
					$addressType = call_user_func_array(array($prefix, 'getNewInstance'), array($user, $address));
					$addressType->save();
				}
			}
		}

		if($this->request->get('sameAddresses') && $user->defaultBillingAddress)
		{
			$shippingAddress = ShippingAddress::getNewInstance($user, clone $user->defaultBillingAddress->userAddress);
			$shippingAddress->save();
		}
	}
}
?>
