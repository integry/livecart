<?php


/**
 * Web service access layer for Category model
 *
 * @package application/model/datasync
 * @author Integry Systems <http://integry.com>
 * 
 */
class CategoryApi extends ModelApi
{
	private $root = null;

	public static function canParse(Request $request)
	{
		return parent::canParse($request, array('XmlCategoryApiReader'));
	}

	public function __construct(LiveCart $application)
	{
		$this->root = Category::getRootNode();
		parent::__construct(
			$application,
			'Category',
			array() // fields to ignore in Category model
		);
	}
	
	// ------
	public function get()
	{
		$parser = $this->getParser();
		$id = $this->getRequestID();
		$categories = ActiveRecordModel::getRecordSetArray('Category',
			select(eq(f('Category.ID'), $id))
		);
		if(count($categories) == 0)
		{
			throw new Exception('Category not found');
		}
		$apiFieldNames = $parser->getApiFieldNames();

		// --
		$response = new LiveCartSimpleXMLElement('<response datetime="'.date('c').'"></response>');
		$responseCategory = $response->addChild('category');
		while($category = array_shift($categories))
		{
			foreach($category as $k => $v)
			{
				if(in_array($k, $apiFieldNames))
				{
					$responseCategory->addChild($k, $v);
				}
			}
		}
		return new SimpleXMLResponse($response);
	}
	
	public function create()
	{
		$parser = $this->getParser();
		$request = $this->application->getRequest();
		$parser->loadDataInRequest($request);

		$parentId = $request->get(Category::PARENT_NODE_FIELD_NAME);
		$parentCategory = $parentId > 0
			? $this->_getCategoryById($parentId, true)
			: $this->root;
		$category = Category::getNewInstance($parentCategory);
		$category->loadRequestData($request);
		$category->save();

		return $this->statusResponse($category->getID(), 'created');
	}

	public function update()
	{
		$parser = $this->getParser();
		$request = $this->application->getRequest();
		$parser->loadDataInRequest($request);
		$category = $this->_getCategoryById($request->get('ID'));
		$category->loadRequestData($request);
		$this->_attachCategoryToParentNode($category)->save();

		return $this->statusResponse($category->getID(), 'updated');
	}


	public function delete()
	{
		$id = $this->getRequestID();
		$this->_getCategoryById($id)->delete();
		return $this->statusResponse($id, 'deleted');
	}


	public function filter($emptyListIsException = false)
	{
		$request = $this->application->getRequest();
		$parser = $this->getParser();
		$apiFieldNames = $parser->getApiFieldNames();
		$parser->loadDataInRequest($request);
		$f = new ARSelectFilter();
		$id = $request->get('ID');
		if(intval($id) > 0) // get action
		{
			$f->mergeCondition(new EqualsCond(new ARFieldHandle('Category', 'ID'), $id));
		}
		$f->setOrder(MultiLingualObject::getLangOrderHandle(new ARFieldHandle('Category', 'name')));
		$categories = ActiveRecordModel::getRecordSetArray('Category', $f);
		$response = new LiveCartSimpleXMLElement('<response datetime="'.date('c').'"></response>');
		if($emptyListIsException && count($categories) == 0)
		{
			throw new Exception('Category not found');
		}
		while($category = array_shift($categories))
		{
			$xmlCategory = $response->addChild('category');
			foreach($category as $k => $v)
			{
				if(in_array($k, $apiFieldNames))                 // those who are allowed fields ($this->apiFieldNames) ?
				{
					$xmlCategory->addChild($k, htmlentities($v));
				}
			}
		}
		return new SimpleXMLResponse($response);
	}

	// ------ 

	private function _attachCategoryToParentNode($category)
	{
		$request =  $this->application->getRequest();
		$parentId = $request->get(Category::PARENT_NODE_FIELD_NAME);
		$id = $category->getID();
		if(intval($parentId) > 0) {
			$parentCategory = $this->_getCategoryById($parentId, /* don't throw exception, i promise, i will not change root node, honestly! */ true);
			if($id > 0)
			{
				if($id == $parentId)
				{
					throw new Exception('Parent node ID cannot be equal to node ID');
				}
				$category->moveTo($parentCategory);
			} else {
				$category->setParentNode($parentCategory);
			}
			// $request->remove(Category::PARENT_NODE_FIELD_NAME);
		}
		return $category;
	}

	private function _getCategoryById($id, $includeRootNode = false)
	{
		if($includeRootNode == false && $id == $this->root->getID())
		{
			throw new Exception('Cannot change root level category.');
		}
		
		$category = Category::getInstanceByID($id, true);
		// if id is not integer getInstanceByID() will not throw exception?

		// could remove next 3 lines, if all ID come from ModelApi::getRequestID(), but they not (parent node id, for example)
		if(false == ($category->getID() > 0))
		{
			throw new Exception('Bad ID field value.');
		}
		return $category;
	}
}

?>