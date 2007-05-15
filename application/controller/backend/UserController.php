<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("framework.request.validator.RequestValidator");
ClassLoader::import("framework.request.validator.Form");
ClassLoader::import("application.controller.backend.*");
ClassLoader::import("application.model.user.*");
ClassLoader::import("library.DataGrid.*");
ClassLoader::import("library.AJAX_TreeMenu.*");

/**
 *
 * @package application.controller.backend
 */
class UserController extends StoreManagementController
{
	/**
	 * Action shows filters and datagrid.
	 * @return ActionResponse
	 */
	public function index()
	{
		$userGroups = array();
		$userGroups[] = array('ID' => -2, 'name' => 'root', 'rootID' => 0);
		$userGroups[] = array('ID' => -1, 'name' => $this->translate('_default_user_group'), 'rootID' => -2);
		foreach(UserGroup::getRecordSet(new ARSelectFilter())->toArray() as $group) 
		{
		    $userGroups[] = array('ID' => $group['ID'], 'name' => $group['name'], 'rootID' => -2);
		}
		    
		$response = new ActionResponse();
		$response->setValue('userGroups', $userGroups);
		return $response;
	    
	}
	
	public function users()
	{
	    $id = (int)$this->request->getValue("id");
	    if($id > 0)
	    {
	        $showAllGroups = false;
	        $userGroup = UserGroup::getInstanceByID($id, ActiveRecord::LOAD_DATA);
	    }
	    else if($id == -1)
	    {
	        $showAllGroups = false;
	        $userGroup = null;
	    }
	    else if($id == -2)
	    {
	        $showAllGroups = true;
	        $userGroup = null;
	    }
	    else
	    {
	        return;
	    }
	        
		$availableColumns = $this->getAvailableColumns();
		$displayedColumns = $this->getDisplayedColumns();
		
		// sort available columns by display state (displayed columns first)
		$displayedAvailable = array_intersect_key($availableColumns, $displayedColumns);
		$notDisplayedAvailable = array_diff_key($availableColumns, $displayedColumns);		
		$availableColumns = array_merge($displayedAvailable, $notDisplayedAvailable);
			
		$response = new ActionResponse();
        $response->setValue("massForm", $this->getMassForm());
        $response->setValue("displayedColumns", $displayedColumns);
        $response->setValue("availableColumns", $availableColumns);
		$response->setValue("userGroupID", $id);
		$response->setValue("offset", $this->request->getValue('offset'));
		$response->setValue("totalCount", '0');
				
		return $response;
	}	
	
	public function changeColumns()
	{		
		$columns = array_keys($this->request->getValue('col', array()));
		$this->setSessionData('columns', $columns);
		return new ActionRedirectResponse('backend.user', 'users', array('id' => $this->request->getValue('group')));
	}

	public function lists()
	{
	    $id = (int)substr($this->request->getValue('id'), 6);
	    if($id > 0)
	    {
	        $showAllGroups = false;
	        $userGroup = UserGroup::getInstanceByID($id, ActiveRecord::LOAD_DATA);
	    }
	    else if($id == -1)
	    {
	        $showAllGroups = false;
	        $userGroup = null;
	    }
	    else if($id == -2)
	    {
	        $showAllGroups = true;
	        $userGroup = null;
	    }
	    else
	    {
	        return;
	    }

	    $filter = new ARSelectFilter();
	    new ActiveGrid($this->request, $filter);
	    if($showAllGroups)
	    {
	        $usersArray = User::getRecordSet($filter, array('UserGroup'))->toArray();
	    }
	    else
	    {
	        $usersArray = User::getRecordSetByGroup($userGroup, $filter, array('UserGroup'))->toArray();
	    }
	    
		$displayedColumns = $this->getDisplayedColumns($userGroup);

    	$data = array();
		foreach ($usersArray as $user)
    	{
            $record = array();
            foreach ($displayedColumns as $column => $type)
            {
                list($class, $field) = explode('.', $column, 2);
                
                if ('User' == $class)
                {
					$value = isset($user[$field]) ? $user[$field] : '';
                }
				
                if ('UserGroup' == $class)
                {
					$value = isset($user['UserGroup'][$field]) ? $user['UserGroup'][$field] : '';
                }
				
				if ('bool' == $type)
				{
					$value = $value ? $this->translate('_yes') : $this->translate('_no');
				}
				
				$record[] = $value;
            }
            
            $data[] = $record;
        }
    	
    	$return = array();
    	$return['columns'] = array_keys($displayedColumns);
    	$return['totalCount'] = count($usersArray);
    	$return['data'] = $data;
    	
    	return new JSONResponse($return);	  	  	
	}
	
	protected function getDisplayedColumns()
	{	
		// get displayed columns
		$displayedColumns = $this->getSessionData('columns');		

		if (!$displayedColumns)
		{
			$displayedColumns = array(
				'User.ID', 
			 	'User.email',
				'UserGroup.name',
				'User.firstName', 
				'User.lastName', 
				'User.companyName', 
				'User.dateCreated', 
				'User.isEnabled'
			);				
		}
		
		$availableColumns = $this->getAvailableColumns();
		$displayedColumns = array_intersect_key(array_flip($displayedColumns), $availableColumns);	

		// User ID is always passed as the first column
		$displayedColumns = array_merge(array('User.ID' => 'numeric'), $displayedColumns);
				
		// set field type as value
		foreach ($displayedColumns as $column => $foo)
		{
			if (is_numeric($displayedColumns[$column]))
			{
				$displayedColumns[$column] = $availableColumns[$column]['type'];					
			}
		}

		return $displayedColumns;		
	}
	
	protected function getAvailableColumns()
	{
		// get available columns
		$availableColumns = array();
		foreach (ActiveRecordModel::getSchemaInstance('User')->getFieldList() as $field)
		{
			$fieldType = $field->getDataType();
			
			if($field->getName() == 'password')
			{
			    continue;
			}
			if ($field instanceof ARForeignKeyField)
			{
			  	continue;
			}		            
			if ($field instanceof ARPrimaryKeyField)
			{
			  	continue;
			}		            
			elseif ($fieldType instanceof ARBool)
			{
			  	$type = 'bool';
			}	  
			elseif ($fieldType instanceof ARNumeric)
			{
				$type = 'numeric';	  	
			}			
			else
			{
			  	$type = 'text';
			}
			
			$availableColumns['User.' . $field->getName()] = $type;
		}		
		
		$availableColumns['UserGroup.name'] = 'text';

		foreach ($availableColumns as $column => $type)
		{
			$availableColumns[$column] = array(
				'name' => $this->translate($column), 
				'type' => $type
			);	
		}


		return $availableColumns;
	}
	
    protected function getMassForm()
    {
		$validator = new RequestValidator("UsersFilterFormValidator", $this->request);		
		
        return new Form($validator);                
    }
    
    public function processMass()
    {        
		$filter = new ARSelectFilter();
		
		$filters = (array)json_decode($this->request->getValue('filters'));
		$this->request->setValue('filters', $filters);
		
        $grid = new ActiveGrid($this->request, $filter, 'User');
        $filter->setLimit(0);
        					
		$users = ActiveRecordModel::getRecordSet('User', $filter, User::LOAD_REFERENCES);
		
        $act = $this->request->getValue('act');
		$field = array_pop(explode('_', $act, 2));           

        foreach ($users as $user)
		{
            if (substr($act, 0, 7) == 'enable_')
            {
                $user->setFieldValue($field, 1);    
            }        
            else if (substr($act, 0, 8) == 'disable_')
            {
                $user->setFieldValue($field, 0);                 
            } 
            else if ('delete' == $act)
            {
				$user->delete();
			}         
            
			$user->save();
        }		
		
		return new JSONResponse($this->request->getValue('act'));	
    } 
    
	public function info()
	{
	    $user = User::getInstanceById((int)$this->request->getValue('id'), ActiveRecord::LOAD_DATA, array('UserGroup'));
		
        $availableUserGroups = array('' => '');
        foreach(UserGroup::getRecordSet(new ARSelectFilter()) as $group)
        {
            $availableUserGroups[$group->getID()] = $group->name->get();
        }
        
	    $response = new ActionResponse();	    
	    $response->setValue('user', $user->toFlatArray());
	    $response->setValue('availableUserGroups', $availableUserGroups);
	    $response->setValue('form', $this->createUserForm($user));
		
		return $response;
	}
	
	/**
	 * @return RequestValidator
	 */
    private function createUserFormValidator()
    {
		$validator = new RequestValidator("UserForm", $this->request);		            
		
		$validator->addCheck('email', new IsNotEmptyCheck($this->translate('_err_email_empty')));		
		$validator->addCheck('email', new IsValidEmailCheck($this->translate('_err_invalid_email')));  
		$validator->addCheck('firstName', new IsNotEmptyCheck($this->translate('_err_first_name_empty')));		
		$validator->addCheck('lastName', new IsNotEmptyCheck($this->translate('_err_last_name_empty')));
		$validator->addCheck('password1', new PasswordEqualityCheck(
						                        $this->translate('_err_passwords_are_not_the_same'), 
						                        $this->request->getValue('password2'), 
												'password2'
					                        ));
		$validator->addCheck('password2', new PasswordEqualityCheck(
		                                        $this->translate('_err_passwords_are_not_the_same'), 
		                                        $this->request->getValue('password1'), 
												'password1'
	                                        ));

		$validator->addCheck('userGroupID', new IsNumericCheck($this->translate('_err_invalid_group')));
		
		return $validator;
    }

    /**
     * @return Form
     */
	private function createUserForm(User $user = null)
	{
		$form = new Form($this->createUserFormValidator());
		
		if($user)
		{
		    $form->setData($user->toFlatArray());
		}

		return $form;
	}
	
	public function saveInfo()
	{
	  	if ($id = (int)$this->request->getValue('id'))
	  	{
		  	$user = User::getInstanceByID((int)$id);
	  	}
	  	else
	  	{
	  	    $user = null;
	  	}
	    
   		$validator = $this->createUserFormValidator($user);
		if ($validator->isValid())
		{
		    $email = $this->request->getValue('email');
		    $password = $this->request->getValue('password');
		    $firstName = $this->request->getValue('firstName');
		    $lastName = $this->request->getValue('lastName');
		    $companyName = $this->request->getValue('companyName');
		    
		    if($groupID = (int)$this->request->getValue('UserGroup'))
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
			
			$user->lastName->set($lastName);
			$user->firstName->set($firstName);
			$user->setPassword($password);
			$user->companyName->set($companyName);
			$user->email->set($email);
			$user->userGroup->set($group);
			
			$user->save();
			
			return new JSONResponse(array('status' => 'success', 'user' => $user->toArray()));
		}
		else
		{
		    return new JSONResponse(array('status' => 'failure', 'errors' => $validator->getErrorList()));
		}
	}
    
	
	
	
	
	private function ajaxJS()
	{
		$app = Application::getInstance();
		$app->getRenderer()->appendValue("JAVASCRIPT", Router::getInstance()->getBaseDir()."/public/javascript/document.js");
		$app->getRenderer()->appendValue("JAVASCRIPT", Router::getInstance()->getBaseDir()."/public/javascript/AJAX_TreeMenu/TreeMenuAjax.js");
		$app->getRenderer()->appendValue("JAVASCRIPT", Router::getInstance()->getBaseDir()."/public/javascript/ajax.js");
	}
	
	
	
	/**
	 * Search by group.
	 * @return ActionResponse
	 */
	public function group()
	{
		//performing search and format grid
		if ($this->request->isValueSet("group_search_id"))
		{
			$filter = new ARSelectFilter();
			$filter->setCondition(new EqualsCond(new ArFieldHandle("UserGroup", "roleGroupID"), $this->request->getValue("group_search_id")));

			$record_set = ActiveRecord::getRecordSet("UserGroup", $filter, true);
			$grid = new DataGridArSetDisplayer();
			$grid->setDataSource($record_set);

			$grid->addBulcColumn("User.ID");
			$grid->addColumnComplex(array("User.nickName", "User.ID"), get_class($this), 'formatNickColUrl', $this->locale->translate("_user"));
			$grid->addColumn("User.email", $this->locale->translate("_email"), "<a href='mailto:{0}'>{0}</a>");
			$grid->addColumn("User.firstName", $this->locale->translate("_firstName"));
			$grid->addColumn("User.lastName", $this->locale->translate("_lastNname"));
			$grid->addColumnComplex("User.creationDate", get_class($this), 'formatCreationDate', $this->locale->translate("_creationDate"));
			$grid->addColumnComplex("User.isActive", get_class($this), 'formatActive', $this->locale->translate("_active"));

			$filter = new DataGridFilter("User", $this->request->toArray());
		}

		//rendering div content
		$app = Application::getInstance();
		$app->getRenderer()->setValue('group_search', $this->createRoleGroupView("objTreeMenuAjax_search", "objTreeMenuAjax_search"));
		$divpart = $app->getRenderer()->render("backend/user/group.divpart.tpl");

		//response
		$action_response = new ActionResponse();
		$action_response->setValue('tabclass_search', 'tabpage');
		$action_response->setValue('tabclass_group', 'tabpageselected');
		$action_response->setValue('group', $this->createRoleGroupView("objTreeMenuAjax_add", "objTreeMenuAjax_add"));
		$action_response->setValue('filter', $divpart);
		$action_response->setValue('hrefs_list', 'backend/user/group.hrefslist.tpl');

		if (!empty($grid))
		{
			$action_response->setValue('grid', $grid->display());
		}
		//application responsobilities rendering
		$this->ajaxJS();
		return $action_response;
	}

	/**
	 * Formats nick format for grid column.
	 * @return string
	 */
	public static function formatNickColUrl($params)
	{
		return "<a href=\"".Router::getInstance()->createUrl(array('controller' => 'backend.user', 'action' => 'view', 'id' => $params["User.ID"]))."\">".$params["User.nickName"]."</a>";
	}

	/**
	 * Formats creation date format for grid column.
	 * @return string
	 */
	public static function formatCreationDate($params)
	{
		return substr($params["User.creationDate"], 0, 10);
	}

	/**
	 * Formats "active" format for grid column.
	 * @return string
	 */
	public static function formatActive($params)
	{
		return !empty($params["User.isActive"]) ? "true" : "";
	}

	/**
	 * Changes User's activeness.
	 * @param $mode int Possible values: 0, 1
	 */
	private function queryActivate($mode)
	{
		$activate = DataGridDisplayer::getBulcArray($this->request->toArray());
		if (count($activate) > 0)
		{
			$update = new ARUpdateFilter();
			$update->addModifier("User.isActive", $mode);

			foreach($activate as $value)
			{
				if (empty($cond))
				{
					$cond = new EqualsCond(new ArFieldHandle("User", "ID"), $value);
				}
				else
				{
					$cond->addOr(new EqualsCond(new ArFieldHandle("User", "ID"), $value));
				}
			}

			$update->setCondition($cond);
			ActiveRecord::updateRecordSet("User", $update);
		}
	}

	/**
	 * Activates user.
	 */
	public function deactivate()
	{
		$this->queryActivate(0);
		return new ActionRedirectResponse("backend.user", "index");
	}

	/**
	 * Deactivates user.
	 */
	public function activate()
	{
		$this->queryActivate(1);
		return new ActionRedirectResponse("backend.user", "index");
	}

	/**
	 * Action of assigning users to group.
	 */
	public function assign()
	{
		$assign = DataGridDisplayer::getBulcArray($this->request->toArray());
		$group = ActiveRecord::getInstanceById("RoleGroup", $this->request->getValue("id"));

		foreach($assign as $value)
		{
			$user = ActiveRecord::getInstanceById("User", (int)$value);

			$rel = ActiveRecord::getNewInstance("UserGroup");
			$rel->user->set($user);
			$rel->roleGroup->set($group);
			$rel->save();
		}
	}

	/**
	 * Action shows user adding or editing form
	 */
	public function view()
	{
		if ($this->request->isValueSet("id"))
		{
			$user = ActiveRecord::getInstanceById("User", $this->request->getValue("id"), true);
			$form = $this->createUserForm($user->toArray());
		}
		else
		{
			$form = $this->createUserForm(array());
		}

		if ($form->validationFailed())
		{
			$form->restore();
		}

		$form->getField("nickName")->setAttribute("maxlength", 20);
		$form->getField("email")->setAttribute("maxlength", 60);
		$form->getField("firstName")->setAttribute("maxlength", 20);
		$form->getField("middleName")->setAttribute("maxlength", 20);
		$form->getField("lastName")->setAttribute("maxlength", 20);

		$app = Application::getInstance();
		$app->getRenderer()->appendValue("JAVASCRIPT", "validate.js");

		$action_response = new ActionResponse();
		$action_response->setValue("form", @$form->render());
		return $action_response;
	}

	/**
	 * Saves user information.
	 * @todo ActiveRecord instance should be created after check of validity
	 */
	public function save()
	{
		$params = array();
		if ($this->request->isValueSet("id"))
		{
			$params['id'] = $this->request->getValue("id");
			$user = ActiveRecord::getInstanceById("User", $this->request->getvalue("id"));
		}
		else
		{
			$user = ActiveRecord::getNewInstance("User");
		}

		$form = $this->createUserForm($this->request->toArray());

		if ($form->isValid())
		{
			$nick_cond = new EqualsCond(new ArFieldHandle("User", "nickName"), $form->getField("nickName")->getValue());
			$email_cond = new EqualsCond(new ArFieldHandle("User", "email"), $form->getField("email")->getValue());

			if ($this->request->isValueSet("id"))
			{
				$nick_cond->addAND(new OperatorCond(new ArFieldHandle("User", "ID"), $this->request->getValue("id"), "<>"));
				$email_cond->addAND(new OperatorCond(new ArFieldHandle("User", "ID"), $this->request->getValue("id"), "<>"));
			}

			//checking unique nick name
			$filter = new ARSelectFilter();
			$filter->setCondition($nick_cond);
			$arset = ActiveRecord::getRecordSet("User", $filter);
			if ($arset->size() > 0)
			{
				$form->setFieldError("nickName", "Such nick name exists.");
				$not_unique = true;
			}

			//checking unique email
			$filter = new ARSelectFilter();
			$filter->setCondition($email_cond);
			$arset = ActiveRecord::getRecordSet("User", $filter);
			if ($arset->size() > 0)
			{
				$form->setFieldError("email", "Such e-mail exists.");
				$not_unique = true;
			}

			//if not unique redirect
			if (!empty($not_unique))
			{
				$form->saveState();
				return new ActionRedirectResponse("backend.user", "view", $params);
			}

			//saving data
			$user->nickName->set($form->getField("nickName")->GetValue());
			$user->email->set($form->getField("email")->GetValue());
			$user->firstName->set($form->getField("firstName")->GetValue());
			$user->middleName->set($form->getField("middleName")->GetValue());
			$user->lastName->set($form->getField("lastName")->GetValue());
			$user->creationDate->set(date("Y-m-d"));
			$user->save();

			//response
			return new ActionRedirectResponse("backend.user", "index");
		}
		else
		{
			$form->saveState();
			return new ActionRedirectResponse("backend.user", "view", $params);
		}
	}



	/**
	 */
	private function createRoleGroupView($js_object_name, $method)
	{
		$groups = Tree::getAllTree("RoleGroup");
		//$groups = Tree::getTreeInstanceById("RoleGroup", 1);

		$treemenu = new AJAX_TreeMenu();
		$this->formatTreeMenu($treemenu, $groups, $js_object_name, $method);

		$treemenuDHTML = &new AJAX_TreeMenu_DHTML("", $js_object_name, $treemenu, array('images' => Router::getInstance()->getBaseDir().'/library/AJAX_TreeMenu/imagesAlt2', 'defaultClass' => 'treeMenuDefault'));

		return $treemenuDHTML->toHtml();
	}

	/**
	 */
	private function formatTreeMenu($treemenu, $tree, $js_object_name, $method)
	{
		$parent = 0;

		foreach($tree->getChildren()as $key => $child)
		{
			$array = $this->$method($child);

			$node = &new AJAX_TreeNode($child->getId(), $array, array());
			if ($child->getChildrenCount() > 0)
			{
				$this->formatTreeMenu($node, $child, $js_object_name, $method);
			}
			$treemenu->addItem($node);
		}
	}

	/**
	 */
	private function objTreeMenuAjax_add($child)
	{
		$array = array();
		$array['text'] = $child->name->get();
		$array['link'] = 'javascript: bulc_assign('.$child->getId().');';
		$array['cssClass'] = 'treeMenuNode';
		return $array;
	}

	/**
	 */
	private function objTreeMenuAjax_search($child)
	{
		$array = array();
		$array['text'] = $child->name->get();
		$array['link'] = 'javascript: group_search('.$child->getId().');';

		if ($this->request->isValueSet("group_search_id") && $this->request->getValue("group_search_id") == $child->getID())
		{
			$array['cssClass'] = 'treeMenuNodeSelected';
		}
		else
		{
			$array['cssClass'] = 'treeMenuNode';
		}
		return $array;
	}
}
?>