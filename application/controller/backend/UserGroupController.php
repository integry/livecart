<?php

ClassLoader::import('application.controller.backend.abstract.ActiveGridController');
ClassLoader::import("application.controller.backend.*");
ClassLoader::import("application.model.user.*");
ClassLoader::import("framework.request.validator.Form");
ClassLoader::import("framework.request.validator.RequestValidator");

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

		$form = UserController::createUserForm($this, null);

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
		$validator = new RequestValidator("userGroupForm_" . $group->isExistingRecord() ? $group->getID() : '', $this->request);
		$validator->addCheck("name", new IsNotEmptyCheck($this->translate("_error_name_should_not_be_empty")));
		$group->getSpecification()->setValidation($validator);
		return $validator;
	}

	public function getAvailableColumns()
	{
		$availableColumns = parent::getAvailableColumns();
		$availableColumns['UserGroup.name'] = array('type' => 'text', 'name' => $this->translate('UserGroup.name'));
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
		$filter = new ARSelectFilter();

		$id = (int)substr($this->request->get('id'), 6);
		if($id > 0)
		{
			$filter->mergeCondition(new EqualsCond(new ARFieldHandle('User', 'userGroupID'), $id));
		}
		else if($id == -1)
		{
			// without group
			$filter->mergeCondition(new IsNullCond(new ARFieldHandle('User', 'userGroupID')));
		}

		//$id = is_numeric($id) ? $id : substr($this->request->get("id"), 9);

		return $filter;
	}

	protected function getReferencedData()
	{
		return array('UserGroup');
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