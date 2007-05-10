<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
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
		$userGroups[] = array('ID' => -1, 'name' => $this->translate('_default_user_group'));
		foreach(UserGroup::getRecordSet(new ARSelectFilter())->toArray() as $group) 
		{
		    $userGroups[] = array('ID' => $group['ID'], 'name' => $group['name']);
		}
		    
		$response = new ActionResponse();
		$response->setValue('userGroups', $userGroups);
		return $response;
	    
	    
//		//user count for paging
//		$schema = ActiveRecord::getSchemaInstance("User");
//		$db = ActiveRecord::GetDbConnection();
//		$res = $db->executeQuery("SELECT count(id) AS users_count FROM ".$schema->getName());
//		$res->next();
//		$count = (int)$res->getInt("users_count");
//
//		//filter
//		$filter = new DataGridFilter("User", $this->request->toArray());
//
//		$filter->selector()->addField("User.email", $this->locale->translate("_email"));
//		$filter->selector()->addField("User.nickName", $this->locale->translate("_nickName"));
//		$filter->selector()->addField("User.creationDate", $this->locale->translate("_creationDate"));
//
//		$filter->sorter()->addField("User.email", $this->locale->translate("_email"));
//		$filter->sorter()->addField("User.nickName", $this->locale->translate("_nickName"));
//		$filter->sorter()->addField("User.creationDate", $this->locale->translate("_creationDate"));
//		$filter->sorter()->addField("User.isActive", $this->locale->translate("_active"));
//
//		$filter->pager()->setOptions($count, 10);
//
//		$display = new DataGridFilterDisplayer($filter);
//
//		$record_set = ActiveRecord::getRecordSet("User", $filter->getArSelectFilter(), true);
//
//		//datagrid
//		$grid = new DataGridArSetDisplayer();
//		$grid->setDataSource($record_set);
//		$grid->setSortedFields($filter->sorter()->getFields());
//
//		$grid->addBulcColumn("User.ID");
//		$grid->addColumnComplex(array("User.nickName", "User.ID"), get_class($this), 'formatNickColUrl', $this->locale->translate("_user"));
//		$grid->addColumn("User.email", $this->locale->translate("_email"), "<a href='mailto:{0}'>{0}</a>");
//		$grid->addColumn("User.firstName", $this->locale->translate("_firstName"));
//		$grid->addColumn("User.lastName", $this->locale->translate("_lastName"));
//		$grid->addColumnComplex("User.creationDate", get_class($this), 'formatCreationDate', $this->locale->translate("_creationDate"));
//		$grid->addColumnComplex("User.isActive", get_class($this), 'formatActive', $this->locale->translate("_active"));
//
//		//response
//		$action_response = new ActionResponse();
//		//$action_response->setValue('action', 'index');
//		$action_response->setValue('tabclass_search', 'tabpageselected');
//		$action_response->setValue('tabclass_group', 'tabpage');
//		$action_response->setValue('filter', $display->display());
//		$action_response->setValue('grid', $grid->display());
//		$action_response->setValue('paging', $display->displayPagerAsSelect(""));
//		$action_response->setValue('group', $this->createRoleGroupView("objTreeMenuAjax_add", "objTreeMenuAjax_add"));
//		$action_response->setValue('hrefs_list', 'backend/user/group.hrefslist.tpl');
//
//		//application rendering
//		$this->ajaxJS();
//		$app = Application::getInstance();
//		$app->getRenderer()->appendValue("BODY_ONLOAD", $display->displayOnLoad(1, 1));
//		$app->getRenderer()->appendValue("JAVASCRIPT", Router::getInstance()->getBaseDir()."/public/javascript/DataGrid/datagrid.js");
//
//		return $action_response;
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
	 * Creates user form.
	 * @todo email check
	 * @param array $data Initial values
	 * @return Form
	 */
	private function createUserForm($data)
	{
		ClassLoader::import("library.formhandler.*");
		ClassLoader::import("library.formhandler.check.string.*");

		$form = new Form("userForm", $data);

		if ($this->request->isValueSet("id"))
		{
			$form->setAction(Router::getInstance()->createUrl(array('controller' => 'backend.user', 'action' => 'save', 'id' => $this->request->getValue("id"))));
		}
		else
		{
			$form->setAction(Router::getInstance()->createUrl(array('controller' => 'backend.user', 'action' => 'save')));
		}

		$field = new TextLineField("nickName", "Nick name");
		$field->addCheck(new MinLengthCheck("Nick name must be at least 2 chars length!", 2));
		$form->addField($field);

		$field = new TextLineField("email", "E-mail");
		//$field->addValidator(new FormValEmail("E-mail is not valid.", "asdf"));
		$form->addField($field);

		$field = new TextLineField("firstName", "First name");
		$field->addCheck(new RequiredValueCheck("First name required."));
		$form->addField($field);

		$field = new TextLineField("middleName", "Middle name");
		$form->addField($field);

		$field = new TextLineField("lastName", "Last name");
		$field->addCheck(new RequiredValueCheck("Last name required."));
		$form->addField($field);
		$form->addField(new SubmitField("submit", "Save"));

		return $form;
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