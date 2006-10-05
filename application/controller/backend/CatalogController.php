<?php

ClassLoader::import("application.controller.backend.abstract.StoreManagementController");
ClassLoader::import("application.controller.backend.*");
ClassLoader::import("application.model.product.*");
ClassLoader::import("library.json.*");
ClassLoader::import("library.AJAX_TreeMenu.*");
ClassLoader::import("application.model.locale.Language");

/**
 * Controller for catalog (product category) related actions
 * 
 * @package application.controller.backend
 * @author Denis Slaveckij <denis@integry.net>
 * 
 */
class CatalogController extends StoreManagementController {
	
	private $multi_language;
	
	
	/**
	 */
	public function init() {
	  	  	
	  	parent::init();

	  	$this->addBlock("NAV", "treeSection");	  		
		  		
		@session_start();
	  	$this->multi_language = isSet($_SESSION['CURRENT_MULTI_LANGUAGE']) ? $_SESSION['CURRENT_MULTI_LANGUAGE'] : 'en';	  	
	  	
	  	//js sources
		$app = Application::getInstance();								
	}
	
	/**
	 * Block of group tree.
	 */
	protected function treeSectionBlock() {
		
		//response
		$response = new BlockResponse();
		$response->setValue("title", $this->locale->translate("_catalogTree"));
		$response->setValue("group", $this->createTreeHTML("formatTreeMenu", "objTreeMenuAjax_catalog", "objTreeMenuAjax_catalog")->toHtml());
		$response->setValue("id", $this->request->getValue("id"));
		
		//js sources
		$app = Application::getInstance();						
		$app->getRenderer()->appendValue("JAVASCRIPT", Router::getInstance()->getBaseDir()."/public/javascript/document.js");
		//ajaxtreemenu js sources
		$app->getRenderer()->appendValue("JAVASCRIPT", Router::getInstance()->getBaseDir()."/public/javascript/ajax.js");
		$app->getRenderer()->appendValue("JAVASCRIPT", Router::getInstance()->getBaseDir()."/public/javascript/TreeMenuAjax.js");
		
		return $response;		
	}
	
	/**
	 * Form of movieng node to another.
	 * @return ActionResponse
	 */
	public function moveForm() {
	  
	  	if (!$this->request->isValueSet("id")) {
	  		
	  		exit();
	  	}
	  
	  	//resposne
	  	$response = new ActionResponse();	  		  	
	  	$response->setValue("group", $this->createTreeHTML("formatTreeMenuForMove", "objTreeMenuAjax_catalogMove", "objTreeMenuAjax_catalogMove")->toHtml("objTreeMenuAjax_catalogMove"));	
		$response->setValue("id", $this->request->getValue("id"));  	  
	  	return $response;  		
	}
	
	/**
	 * Create html treemenu of catalogs.
	 * @param string $js_object_name
	 * @param string $method
	 * @return AJAX_TreeMenu_DHTML
	 */
	private function createTreeHTML($format_method, $js_object_name, $method) {

 		$tree = TreeCatalog::getAllTree("TreeCatalog", true);	
		
		//format TreeMenu
		$treemenu =	new AJAX_TreeMenu();		
		$this->$format_method($treemenu, $tree, $js_object_name, $method);
				
		//html
		return new AJAX_TreeMenu_DHTML("", $js_object_name, $treemenu, array('images' => Router::getInstance()->getBaseDir().'/library/AJAX_TreeMenu/imagesAlt2', 'defaultClass' => 'treeMenuDefault'));			
	}
	
	/**
	 * Formats AJAX_TreeMenu object $treemenu.
	 * @param Ajax_TreeMenu $treemenu Formated object.
	 * @param Tree $tree
	 * @param string $js_object_name
	 * @param string $method
	 */
	private function formatTreeMenu($treemenu, $tree, $js_object_name, $method) {

		$node  = &new AJAX_TreeNode($tree->getId(), $this->$method($tree, $js_object_name));
		$treemenu->addItem($node);	
	  	foreach ($tree as $child) {
						
			$this->formatTreeMenu($node, $child, $js_object_name, $method);														  		  				
		}
	}
	
	/**
	 * Formats AJAX_TreeMenu object of $treemenu (form move tree).
	 * @param Ajax_TreeMenu $treemenu Formated object.
	 * @param Tree $tree
	 * @param string $js_object_name
	 * @param string $method
	 */
	private function formatTreeMenuForMove($treemenu, $tree, $js_object_name, $method) {

		$node  = &new AJAX_TreeNode($tree->getId(), $this->$method($tree, $js_object_name));
	  	$treemenu->addItem($node);
		if ($tree->getId() != $this->request->getValue("id")) {

			foreach ($tree as $child) {			
			  
				$this->formatTreeMenuForMove($node, $child, $js_object_name, $method);				
			}										  		  				
		}
	}			
	
	/** 
	 */
	private function objTreeMenuAjax_catalog($tree, $js_object_name) {
	  	
	  	$array = array();	  			
		$array['text'] = $tree->hasId() ? $tree->lang("en")->name->get() : "ALL";					
		$array['link'] = Router::getInstance()->createUrl(array("controller" => "backend.catalog", "action" => "index", "id" => $tree->getId()));		
		$array['cssClass'] = (!$tree->hasId() && !$this->request->isValueSet("id")) ||
							 ($tree->hasId() && $tree->getId() == $this->request->getValue("id")) ? 'treeMenuNodeSelected' : 'treeMenuNode';				
		return $array;
	}
	
	/** 
	 */
	private function objTreeMenuAjax_catalogMove($tree, $js_object_name) {
	  	
	  	$array = array();	  			
		$array['text'] = $tree->hasId() ? $tree->lang("en")->name->get() : "ALL";				
		if ($tree->getId() != $this->request->getValue("id") && $tree->parent->get() != $this->request->getValue("id")) {

			$array['link'] = "javascript: makeMove(".$tree->getId().");";		
		}
		$array['cssClass'] = 'treeMenuNode';				
		return $array;
	}	

	/**
	 * Moves category to another parent.
	 * @return ActionRedirectResponse
	 */	
	public function move() {

	  	Tree::modifyTreeParent("Catalog", $this->request->getValue("id"), $this->request->getValue("moveto"));
		return new ActionRedirectResponse($this->request->getControllerName(), "index", array("id" => $this->request->getValue("moveto")));	
	}
	
	/**
	 * Main action.
	 */	
	public function index() {
	  		  	
	  	if ($this->request->isValueSet("id")) {
			
			//languages
			$langs = Language::getLanguages(1)->toArray();									
			
			//form handling
			$form = $this->createCatalogForm(array());
			$form->setAction(Router::getInstance()->createUrl(array('controller' => 'backend.catalog', 'action' => 'save', 'id' => $this->request->getValue("id"))));
			
			if ($form->validationFailed()) {
	
				$form->restore();
			} else {
			  
				$catalog = Catalog::getInstanceById($this->request->getValue("id"), true);		
				$data = $catalog->toArray();	 			
				$form->setData($data['lang'][$this->multi_language]);
			}	
					
			//response
		
			$response = new ActionResponse();		 
			$response->setValue("action", "index");						
			$response->setValue("current_lang", $this->multi_language);			
			$response->setValue("langs", $langs);
			$response->setValue("id", $this->request->getValue("id"));

			if ($this->multi_language != "en") {
			  
			 	$response->setValue("english_name", $data['lang']['en']['name']); 	
			 	$response->setValue("english_description", $data['lang']['en']['description']); 	
			}			
			
			$response->setValue("file_content", "backend/catalog/index.content.tpl");
			//rendering "backend/catalog/index.content.tpl"
			$response->setValue("content", "<br>".@$form->render()); 	

			return $response; 		
		} else {
		  
		  	return new ActionResponse();
		}  		 	
	}
	
	/**
	 * Subgroup adding form.
	 * @return ActionResponse
	 */
	public function addForm() {
	
		//form
		$form = $this->createCatalogForm(array());	 
		$form->setAction(Router::getInstance()->createUrl(array('controller' => 'backend.catalog', 'action' => 'add', 'id' => $this->request->getValue("id"))));
		 
	  	if ($form->validationFailed()) {
		    	
			$form->restore();
		}			
				
		//response  		  
	  	$response = new ActionResponse();	  
		$response->setValue("form", @$form->render());	
	  	return $response;	
	}
	
	/**
	 *
	 * @param array $data Initial values
	 * @return Form
	 * @todo For saving part for displaying form is not neeted go //See 1
	 */
	public function createCatalogForm($data) {
	  
	  	ClassLoader::import("library.formhandler.*");
	  	ClassLoader::import("library.formhandler.check.string.*");
	  		  	
		$form = new Form("catalogForm", $data);				
		
		$field = new TextLineField("name", $this->locale->translate("_name"));	
		$field->addCheck(new MinLengthCheck($this->locale->translate("_nameMustBeAtLeast2CharsLength"), 2));
		$form->addField($field);		
		
		$field = new TextareaField("description", $this->locale->translate("_description"));;	
		$form->addField($field);		
		
		$form->addField(new SubmitField("submit", $this->locale->translate("_save")));	
		
		// This is done just for displaying form //See 1
		$form->getField("name")->setAttribute("maxlength", 100);
				
		return $form;		
	}
	
	/**
	 * Saves main details of current catalog
	 * @return ActionRedirectResponse
	 */
	public function save() {
	  
	  	if ($this->request->isValueSet("id")) {
	  		  	 
		   	$form = $this->createCatalogForm($this->request->toArray());	  	
			if ($form->isValid()) {	 
			  	
			  	$catalog = Catalog::getInstanceById($this->request->getValue("id"));
			  	$catalog->lang($this->multi_language)->name->set($this->request->getValue("name"));
			  	$catalog->lang($this->multi_language)->description->set($this->request->getValue("description"));
			  	$catalog->save();			  	
			} else {
		  		  	
		  		$form->saveState();		  				
			}
			
			return new ActionRedirectResponse("backend.catalog", "index", array("id" => $this->request->getValue("id")));
	  	}
	}
	
	/**
	 * Adds catalog subgroup.
	 * @return ActionRedirectResponse
	 */
	public function add() {
	  
	  	$form = $this->createCatalogForm($this->request->toArray());
	  	
		if ($form->isValid()) {	
		  			  	
		  	$catalog = TreeCatalog::getNewTreeInstance($this->request->isValueSet("id") ? $this->request->getValue("id") : false);			
			
			$catalog->lang("en")->name->Set($this->request->getValue("name"));
			$catalog->lang("en")->description->set($this->request->getValue("description"));	
			$catalog->Save();
		  	
		  	return new ActionRedirectResponse("backend.catalog", "AddForm", array("id" => $this->request->getValue("id")));				
		} else {
		  
		  	//redirect response if form not valid
		  	$form->saveState();	
		  	return new ActionRedirectResponse("backend.catalog", "AddForm", array("id" => $this->request->getValue("id")));	
		}	
	}
	
	/**
	 * Deletes catalog group.
	 * @return ActionRecirectResponse
	 */	 
	public function delete() {
	  
	  	if ($this->request->isValueSet("id")) {
		    		    
		    Tree::delete("Catalog", $this->request->getValue("id"));
		}
		
		return new ActionRedirectResponse($this->request->getControllerName(), "index");
	}
	
	/**
	 * Changes current multi language.
	 * @return ActionRedirectResponse
	 */	
	public function change() {
	  
	  	@session_start();
	  	$_SESSION['CURRENT_MULTI_LANGUAGE'] = $this->request->getValue("multi_language");
	  	
	  	return new ActionRedirectResponse("backend.catalog", "index", array("id" => $this->request->getValue("id")));
	}
		
	public function fields() {
	  	
	 	$response = new ActionResponse();		 
		$response->setValue("action", "fields");	
		return $response; 	
	}
	
	public function filters() {
	  	
	 	$response = new ActionResponse();		 
		$response->setValue("action", "filters");	
		return $response; 	
	}
	
	
	
	
}

?>