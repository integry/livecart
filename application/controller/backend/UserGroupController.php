<?php


/**
 * @package application/controller/backend
 * @author Integry Systems
 * @role userGroup
 */
class UserGroupController extends ActiveGridController
{
	/**
	 * Action shows filters and datagrid.
	 */
	public function indexAction()
	{
		$userGroups = array();
		$userGroups[] = array('ID' => -2, 'name' => $this->translate('_all_users'), 'rootID' => 0);
		$userGroups[] = array('ID' => -1, 'name' => $this->translate('_default_user_group'), 'rootID' => -2);
		foreach(UserGroup::getRecordSet(new ARSelectFilter())->toArray() as $group)
		{
			$userGroups[] = array('ID' => $group['ID'], 'name' => $group['name'], 'rootID' => -2);
		}
		$userGroups[] = array('ID' => -3, 'name' => $this->translate('_online_users'), 'rootID' => 0);

		$this->set('userGroups', $userGroups);
	}

	public function editAction()
	{
		$group = UserGroup::getInstanceByID((int)$this->request->get('id'), true);
		$form = $this->createUserGroupForm($group);


		$this->set('userGroup', $group->toArray());
		$this->set('userGroupForm', $form);

		$group->getSpecification()->setFormResponse($response, $form);

	}

	public function changeColumnsAction()
	{
		parent::changeColumns();

		return new ActionRedirectResponse('backend.userGroup', 'users', array('id' => $this->request->get('id')));
	}

	public function usersAction()
	{
		$id = (int)$this->request->get("id");



		$availableUserGroups = array('' => $this->translate('_default_user_group'));
		foreach(UserGroup::getRecordSet(new ARSelectFilter()) as $group)
		{
			$availableUserGroups[$group->getID()] = $group->name->get();
		}

		$form = UserController::createUserForm($this, null, $response);

		$form->setData(array_merge($form->getData(), array('UserGroup' => $id, 'ID' => 0, 'isEnabled' => 1)));

		$this->set('newUser', array('UserGroup' => array('ID' => $id), 'ID' => 0, 'isEnabled' => 1));
		$this->set('availableUserGroups', $availableUserGroups);
		$this->set('form', $form);
		$this->set('countries', array_merge(array('' => ''), $this->application->getEnabledCountries()));

		$this->set("userGroupID", $id);

		$this->setGridResponse($response);

	}

	/**
	 * @role update
	 */
	public function saveAction()
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
	public function createAction()
	{
		$userGroup = UserGroup::getNewInstance($this->translate('_new_user_group'));
		$userGroup->save();

		return new JSONResponse($userGroup->toArray(), 'success', $this->translate('_new_user_group_successfully_created'));
	}

	/**
	 * @role remove
	 */
	public function removeAction()
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
	 * @return \Phalcon\Validation
	 */
	private function createUserGroupFormValidator(UserGroup $group)
	{
		$validator = $this->getValidator("userGroupForm_" . $group->isExistingRecord() ? $group->getID() : '', $this->request);
		$validator->add("name", new Validator\PresenceOf(array('message' => $this->translate("_error_name_should_not_be_empty"))));
		$group->getSpecification()->setValidation($validator);
		return $validator;
	}

	public function getAvailableColumnsAction()
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
}

?>