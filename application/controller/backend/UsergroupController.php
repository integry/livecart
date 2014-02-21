<?php

require_once(dirname(__FILE__) . '/abstract/ActiveGridController.php');

use Phalcon\Validation\Validator;
use user\User;
use user\UserGroup;

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
		$userGroups[] = array('id' => -2, 'title' => $this->translate('_all_users'));
		$userGroups[] = array('id' => -1, 'title' => $this->translate('_default_user_group'));
		foreach(UserGroup::query()->execute() as $group)
		{
			$arr = $group->toArray();
			$arr['title'] = $arr['name'];
			$arr['id'] = $arr['ID'];
			$userGroups[] = $arr;
		}

		$this->set('userGroups', array('children' => $userGroups));
		//$this->view->pick('userGroup/index.tpl');
	}
	
	public function viewAction()
	{
	}
	
	public function listAction()
	{
	}

	public function editAction()
	{
		$group = UserGroup::getInstanceByID((int)$this->request->get('id'), true);
		$form = $this->createUserGroupForm($group);


		$this->set('userGroup', $group->toArray());
		$this->set('userGroupForm', $form);

		$group->getSpecification()->setFormResponse($response, $form);

	}

	public function usersAction()
	{
		$id = (int)$this->request->get("id");

		$availableUserGroups = array('' => $this->translate('_default_user_group'));
		foreach(UserGroup::getRecordSet(new ARSelectFilter()) as $group)
		{
			$availableUserGroups[$group->getID()] = $group->name;
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

	public function getAvailableColumns()
	{
		$availableColumns = parent::getAvailableColumns();
		$availableColumns['UserGroup.name'] = array('type' => 'text', 'name' => $this->translate('UserGroup.name'));
		//$availableColumns['isOnline'] = array('type' => 'bool', 'name' => $this->translate('User.isOnline'));

		//$addressFields = parent::getAvailableColumns('UserAddress');
		//$availableColumns = array_merge($availableColumns, $addressFields);
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
		unset($availableColumns['user\User.password']);
		unset($availableColumns['user\User.preferences']);
		unset($availableColumns['user\User.isAdmin']);
		unset($availableColumns['user\User.image']);
		return $availableColumns;
	}

	protected function getClassName()
	{
		return 'user\User';
	}

	protected function getCSVFileName()
	{
		return 'users.csv';
	}

	protected function getSelectFilter()
	{
		$filter = parent::getSelectFilter();

		$id = $this->dispatcher->getParam(0);

		if($id > 0)
		{
			$filter->andWhere('userGroupID = :userGroupID:', array('userGroupID' => $id));
		}
		else if($id == -1)
		{
			// without group
			$filter->andWhere('userGroupID IS NULL');
		}
		else if($id == -3)
		{
			// online
			//$filter->mergeHavingCondition(new EqualsOrMoreCond(f('isOnline'), 1));
		}

		//$filter->addField('(SELECT COUNT(*) > 0 From SessionData WHERE userID=User.ID)', '', 'isOnline');

		return $filter;
	}

	protected function getReferencedData()
	{
		//return array('UserGroup', 'BillingAddress', 'UserAddress');
	}

	protected function getDefaultColumns()
	{
		return array(
			 	'user\User.email',
				'user\UserGroup.name',
				'user\User.firstName',
				//'user\User.lastName',
				//'user\User.companyName',
				'user\User.dateCreated',
				'user\User.isEnabled'
			);
	}
}

?>
