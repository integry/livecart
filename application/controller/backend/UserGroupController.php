<?php

ClassLoader::import('application.controller.backend.abstract.ActiveGridController');
ClassLoader::import("application.controller.backend.*");
ClassLoader::import("application.model.user.*");

/**
 * @package application.controller.backend
 * @author Integry Systems
 * @role userGroup
 */
class UserGroupController extends ActiveGridController
{
	/**
	 * Action shows filters and datagrid.
	 * @return ActionResponse
	 */
	public function index()
	{
		$userGroups = array();
		$userGroups[] = array('ID' => -2, 'name' => $this->translate('_all_users'), 'rootID' => 0);
		$userGroups[] = array('ID' => -1, 'name' => $this->translate('_default_user_group'), 'rootID' => -2);
		foreach(UserGroup::getRecordSet(new ARSelectFilter())->toArray() as $group)
		{
			$userGroups[] = array('ID' => $group['ID'], 'name' => $group['name'], 'rootID' => -2);
		}
		$userGroups[] = array('ID' => -3, 'name' => $this->translate('_online_users'), 'rootID' => 0);

		return new ActionResponse('userGroups', $userGroups);
	}

	public function edit()
	{
		$group = UserGroup::getInstanceByID((int)$this->request->get('id'), true);
		$form = $this->createUserGroupForm($group);

		$response = new ActionResponse();
		$response->set('userGroup', $group->toArray());
		$response->set('userGroupForm', $form);

		$group->getSpecification()->setFormResponse($response, $form);

		return $response;
	}

	public function changeColumns()
	{
		parent::changeColumns();

		return new ActionRedirectResponse('backend.userGroup', 'users', array('id' => $this->request->get('id')));
	}

	public function users()
	{
		$id = (int)$this->request->get("id");

		$response = new ActionResponse();

		$availableUserGroups = array('' => $this->translate('_default_user_group'));
		foreach(UserGroup::getRecordSet(new ARSelectFilter()) as $group)
		{
			$availableUserGroups[$group->getID()] = $group->name->get();
		}

		$form = UserController::createUserForm($this, null, $response);

		$form->setData(array_merge($form->getData(), array('UserGroup' => $id, 'ID' => 0, 'isEnabled' => 1)));

		$response->set('newUser', array('UserGroup' => array('ID' => $id), 'ID' => 0, 'isEnabled' => 1));
		$response->set('availableUserGroups', $availableUserGroups);
		$response->set('form', $form);
		$response->set('countries', array_merge(array('' => ''), $this->application->getEnabledCountries()));

		$response->set("userGroupID", $id);

		$this->setGridResponse($response);

		return $response;
	}

	/**
	 * @role update
	 */
	public function save()
	{
		if($id = $this->request->get('id'))
		{
			$group = UserGroup::getInstanceByID($id);
		}
		else
		{
			$group = UserGroup::getNewInstance($name, $description);
		}

		$validator = $this->createUserGroupFormValidator($group);
		if($validator->isValid())
		{
			$group->loadRequestData($this->request);
			$group->save();

			return new JSONResponse(array('group' => $group->toArray()), 'success', $this->translate('_user_group_successfully_saved'));
		}
		else
		{
			return new JSONResponse(array('errors' => $validator->getErrorList()), 'failure', $this->translate('_could_not_save_user_group'));
		}
	}

	/**
	 * @role create
	 */
	public function create()
	{
		$userGroup = UserGroup::getNewInstance($this->translate('_new_user_group'));
		$userGroup->save();

		return new JSONResponse($userGroup->toArray(), 'success', $this->translate('_new_user_group_successfully_created'));
	}

	/**
	 * @role remove
	 */
	public function remove()
	{
		$userGroup = UserGroup::getInstanceByID((int)$this->request->get("id"), true);
		$userGroupArray = $userGroup->toArray();
		$userGroup->delete();

		return new JSONResponse(array('userGroup' => $userGroupArray), 'success', $this->translate('_user_group_was_successfully_removed'));
	}

	/**
	 * @return Form
	 */
	private function createUserGroupForm(UserGroup $group)
	{
		$form = new Form($this->createUserGroupFormValidator($group));
		$form->setData($group->toArray());

		return $form;
	}

	/**
	 * @return RequestValidator
	 */
	private function createUserGroupFormValidator(UserGroup $group)
	{
		$validator = $this->getValidator("userGroupForm_" . $group->isExistingRecord() ? $group->getID() : '', $this->request);
		$validator->addCheck("name", new IsNotEmptyCheck($this->translate("_error_name_should_not_be_empty")));
		$group->getSpecification()->setValidation($validator);
		return $validator;
	}

	public function getAvailableColumns()
	{
		$availableColumns = parent::getAvailableColumns();
		$availableColumns['UserGroup.name'] = array('type' => 'text', 'name' => $this->translate('UserGroup.name'));
		$availableColumns['isOnline'] = array('type' => 'bool', 'name' => $this->translate('User.isOnline'));

		$addressFields = parent::getAvailableColumns('UserAddress');
		$availableColumns = array_merge($availableColumns, $addressFields);
/*
		foreach (array('BillingAddress', 'ShippingAddress') as $type)
		{
			foreach ($addressFields as $field => $fieldData)
			{
				$fieldData['name'] = $this->translate($type) . ': ' . $fieldData['name'];
				$field = str_replace('UserAddress', $type, $field);
				$availableColumns[$field] = $fieldData;
			}
		}
*/
		unset($availableColumns['User.password']);
		unset($availableColumns['User.preferences']);
		return $availableColumns;
	}

	protected function getClassName()
	{
		return 'User';
	}

	protected function getCSVFileName()
	{
		return 'users.csv';
	}

	protected function getSelectFilter()
	{
		$filter = parent::getSelectFilter();

		$id = $this->request->get('id');

		if (!is_numeric($id))
		{
			$id = (int)substr($id, 6);
		}

		if($id > 0)
		{
			$filter->mergeCondition(new EqualsCond(new ARFieldHandle('User', 'userGroupID'), $id));
		}
		else if($id == -1)
		{
			// without group
			$filter->mergeCondition(new IsNullCond(new ARFieldHandle('User', 'userGroupID')));
		}
		else if($id == -3)
		{
			// online
			$filter->mergeHavingCondition(new EqualsOrMoreCond(f('isOnline'), 1));
		}

		$filter->addField('(SELECT COUNT(*) > 0 From SessionData WHERE userID=User.ID)', '', 'isOnline');

		return $filter;
	}

	protected function getReferencedData()
	{
		return array('UserGroup', 'BillingAddress', 'UserAddress');
	}

	protected function getDefaultColumns()
	{
		return array(
			 	'User.email',
				'UserGroup.name',
				'User.firstName',
				'User.lastName',
				'User.companyName',
				'User.dateCreated',
				'User.isEnabled'
			);
	}

	public function isQuickEdit()
	{
		return true;
	}

	public function quickEdit()
	{
		$request = $this->getRequest();
		$userID = $request->get('id');
		$user = User::getInstanceByID($userID, ActiveRecordModel::LOAD_DATA, array('UserGroup'));
		$lastOrder = $user->getLastOrder();
		
		$f = new ARSelectFilter(new EqualsCond(new ARFieldHandle('CustomerOrder', 'userID'), $user->getID()));
		// $f->mergeCondition(new EqualsCond(new ARFieldHandle('CustomerOrder', 'isFinalized'), true));
		$f->setOrder(new ARFieldHandle('CustomerOrder', 'dateCompleted'));
		$customerOrders = ActiveRecordModel::getRecordSet('CustomerOrder', $f, ActiveRecordModel::LOAD_REFERENCES);
		
		$response = new ActionResponse();
		$response->set('someUser', $user->toArray());
		if($lastOrder)
		{
			$response->set('lastOrder', $lastOrder->toArray());
		}
		if($customerOrders->size() > 0)
		{
			foreach($customerOrders as $order)
			{
				$order->loadAll();
				$order->getCoupons();
			}
			$response->set('orders', $customerOrders->toArray());
		}
		$form = UserController::createUserForm($this, $user,$response);
		$response->set('form',$form);
		$availableUserGroups = array('' => $this->translate('_default_user_group'));
		foreach(UserGroup::getRecordSet(new ARSelectFilter()) as $group)
		{
			$availableUserGroups[$group->getID()] = $group->name->get();
		}
		$response->set('availableUserGroups',$availableUserGroups);

		return $response;
	}

	public function saveQuickEdit()
	{
		$user = User::getInstanceByID((int)$this->request->get('id'), true);
		$validator = UserController::createUserFormValidator($this, $user, true);
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
			
			
			
		$displayedColumns = $this->getRequestColumns();
		$r = array(
			'data'=> $this->recordSetArrayToListData(array($user->toArray()), $displayedColumns),
			'columns'=>array_keys($displayedColumns)
		);
		return new JSONResponse($r, 'success');
		
		
			
			//return new JSONResponse(array('user' => $user->toFlatArray()), 'success', $this->translate('_user_details_were_successfully_saved'));
		}
		else
		{
			return new JSONResponse(array('errors' => $validator->getErrorList()), 'failure', $this->translate('_could_not_save_user_details'));
		}
	}
}

?>