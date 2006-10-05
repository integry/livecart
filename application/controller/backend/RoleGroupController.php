<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.controller.backend.*");
ClassLoader::import("application.model.user.*");
ClassLoader::import("library.json.*");
ClassLoader::import("library.AJAX_TreeMenu.*");

/**
 * 
 * @package application.controller.backend
 */
class RoleGroupController extends StoreManagementController {
	
	/**
	 */
	public function init() {
	  	  	
	  	parent::init();
		  	  	
	  	$this->addBlock("NAV", "rolegroupSection");
	  	
	  	$app = Application::getInstance();						
		$app->getRenderer()->appendValue("JAVASCRIPT", Router::getInstance()->getBaseDir()."/public/javascript/document.js");
		//ajaxtreemenu js sources
		$app->getRenderer()->appendValue("JAVASCRIPT", Router::getInstance()->getBaseDir()."/public/javascript/ajax.js");
		$app->getRenderer()->appendValue("JAVASCRIPT", Router::getInstance()->getBaseDir()."/public/javascript/TreeMenuAjax.js");		
	}
	
	/**
	 * Block of group tree.
	 */
	protected function rolegroupSectionBlock() {
		
		$id = $this->request->isValueSet("id") ? $this->request->getValue("id") : 0;	
			
		$app = Application::getInstance();						
		$app->getRenderer()->appendValue("BODY_ONLOAD", "eventGroup(".$id.", \"".Ajax_TreeMenu_DHTML::spanID("objTreeMenuAjax_view", $id)."\");");			
		
		//response
		$response = new BlockResponse();					
		$response->setValue("group", $this->createTreeHTML("objTreeMenuAjax_view", "objTreeMenuAjax_view")->toHtml());
		
		return $response;		
	}
	
	/**
	 * Initial action.
	 * @return ActionResponse
	 */
	public function index() {
	  		  	
		return new ActionResponse();
	}
	
	/**
	 * Shows form of selected group.
	 * @retuns ActionResponse
	 */
	public function viewForm() {
		
		if ($this->request->isValueSet("node_id")) {

			//layout
			$this->setLayout("empty");	
			
			//group information
			$groups = ActiveRecord::getInstanceById("RoleGroup", $this->request->getValue("node_id"), true);				
			
			//list of parents
			$trees = Tree::getParentsList("RoleGroup", $this->request->getValue("node_id"));			
			$i = 0;
			$list = array();	
			foreach ($trees as $value) {
			  
			  	$list[$i]['name'] = $value->name->get();	
			  	$i ++;
			}

			//response
			$response = new ActionResponse();	
			$response->setValue("title", "Selected group");				
			$response->setValue("id", $this->request->getValue("node_id"));	
			$response->setValue("name", $groups->name->get());
			$response->setValue("description", $groups->description->get());									
			$response->setValue("list", array_reverse($list));			
			return $response;			
		} else {
		  
		  	exit();
		}  	
	}
	
	/**
	 * Updates users group.
	 * @return ActionRedirectResponse
	 */
	public function update() {

		$groups = ActiveRecord::getInstanceById("RoleGroup", $this->request->getValue("id"), true);		
		$groups->name->set($this->request->getValue("name"));
		$groups->description->set($this->request->getValue("description"));		
		$groups->save();
		
		return new ActionRedirectResponse($this->request->getControllerName(), "index", array("id" => $this->request->getValue("id")));	  
	}
	
	/**
	 * Shows form of adding users group.
	 * @return ActionResponse
	 */
	public function addForm() {
	
		//layout
		$this->setLayout("empty");

		//response
		$response = new ActionResponse();	
		$response->setValue("title", "Add user group");				
		$response->setValue("id", $this->request->getValue("node_id"));	
		return $response;
	}
	
	/**
	 * Adds user group.
	 * @return ActionRedirectResponse
	 */
	public function add() {
		    
		$id = $this->request->isValueSet("id") ? (int)$this->request->getValue("id") : null;
	  
	  	//save
	  	$groups = Tree::getNewTreeInstance("RoleGroup", $id);	  	
	  	$groups->name->set($this->request->getValue("name"));
		$groups->description->set($this->request->getValue("description"));		
		$groups->save();	
		
		//response
		return new ActionRedirectResponse($this->request->getControllerName(), "index", array("id" => $id));		
	}
	
	/** 
	 * Deletes user group
	 * @return ActionRedirectResponse
	 */
	public function delete() {
	  
	  	if ($this->request->isValueSet("del")) {
		    		    
		    Tree::delete("RoleGroup", $this->request->getValue("del"));
		}
		
		return new ActionRedirectResponse($this->request->getControllerName(), "index");	  	
	}
		
	/**
	 * Shows layer for moving tree node.
	 */
	public function moveForm() {
	  
	  	if ($this->request->isValueSet("node_id")) {

			$this->setLayout("empty");	  	  
			
	  	  	$groups = Tree::getAllTree("RoleGroup");		
			$group = ActiveRecord::getInstanceById("RoleGroup", $this->request->getValue("node_id"), true);
			
			$javascript = $this->createTreeHTML("objTreeMenuAjax_move", "objTreeMenuAjax_move")->toLayer("move_div");
			
		
			//response
			$response = new ActionResponse();				
			$response->setValue("id", $this->request->getValue("node_id"));			
			
			$app = Application::getInstance();
			$output = $app->render("backend/rolegroup", "moveForm", $response);
			
			require_once("JSON.php");  	
			$json = new Services_JSON(); 		
			echo $json->encode(array('output' => $output, 'javascript' => $javascript));			
			exit();					
	  	} else {
		    
		    exit();
		}	
	}
	
	/**
	 * Move group to another parent group.
	 */
	public function move() {
	  
	  	Tree::modifyTreeParent("RoleGroup", $this->request->getValue("id"), $this->request->getValue("moveto"));
		return new ActionRedirectResponse($this->request->getControllerName(), "index", array("id" => $this->request->getValue("moveto")));	
	}
	
	/**	 
	 * @params $js_object_name string Name of js ajax_tremenu object
	 * @params $js_object_name string Name of method for creating aray of node params
	 */
	private function createTreeHTML($js_object_name, $method) {
	  
 		$groups = Tree::getAllTree("RoleGroup");	
		$treemenu =	new AJAX_TreeMenu(); 	
							  		  	
		$node = &new AJAX_TreeNode(0, $this->$method(false, $js_object_name));			
		
		$treemenu->addItem($node);					
		$this->formatTreeMenu($node, $groups, $js_object_name, $method);
				
		$treemenuDHTML = &new AJAX_TreeMenu_DHTML("", $js_object_name, $treemenu, array('images' => Router::getInstance()->getBaseDir().'/library/AJAX_TreeMenu/imagesAlt2', 'defaultClass' => 'treeMenuDefault'));			

		return $treemenuDHTML;
	}
	
	/**
	 */
	private function formatTreeMenu($treemenu, $tree, $js_object_name, $method) {
							
		$parent = 0;		
		
	  	foreach ($tree->getChildren() as $key => $child) {

			$array = $this->$method($child, $js_object_name);
		
			$node  = &new AJAX_TreeNode($child->getId(), $array);
			if ($child->getChildrenCount() > 0 
				//this is done just in move tree
				//check if new parent usergroup, is not the same, is not the child of group, which is moved
				&& $child->getId() != $this->request->getValue("node_id")) {
			 
			   	$this->formatTreeMenu($node, $child, $js_object_name, $method);			
			}				
		
			$treemenu->addItem($node);						  		  				
		}
	}	
	
	/** 
	 */
	private function objTreeMenuAjax_view($child = false, $js_object_name) {
	  	
	  	$array = array();
	  	
		if (empty($child)) {  	 
			
			$array['expanded'] = true;
		}
		$id = !empty($child) ? $child->getID() : 0;		
		
		$array['text'] = !empty($child) ? $child->name->get() : 'All' ;					
		$array['link'] = "javascript: eventGroup(".$id.", \'".$js_object_name."_span_".$id."\')";				
		$array['cssClass'] = 'treeMenuNode';				
		return $array;
	}
	
	/** 
	 */
	private function objTreeMenuAjax_move($child = false, $js_object_name) {
	  	
	  	$array = array();
	  	
		if (empty($child)) {  	 
			
			$array['expanded'] = true;
		}
		$id = !empty($child) ? $child->getID() : 0;		
		
		$array['text'] = !empty($child) ? $child->name->get() : 'All' ;							
		if ($id != $this->request->getValue("node_id")) {							
		
			$array['link'] = "javascript: makeMove(".$id.")";		
		}				
		$array['cssClass'] = 'treeMenuNode';				
		
		return $array;
	}
}
























?>