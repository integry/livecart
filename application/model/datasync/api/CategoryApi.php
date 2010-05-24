<?php

ClassLoader::import('application.model.datasync.ModelApi');
ClassLoader::import('application.model.datasync.api.reader.XmlCategoryApiReader');
ClassLoader::import('application.model.category.Category');

/**
 * Web service access layer for Category model
 *
 * @package application.model.datasync
 * @author Integry Systems <http://integry.com>
 * 
 */
class CategoryApi extends ModelApi
{
	private $root = null;

	public static function canParse(Request $request)
	{
		if(XmlCategoryApiReader::canParse($request))
		{
			return true;
		}
		return false;
	}

	public function __construct(LiveCart $application)
	{
		$this->root = Category::getRootNode();
		parent::__construct(
			$application,
			'Category',
			array_keys($this->root->getSchema()->getFieldList()) // field names, (this line means all that are in Category model).
		);
	}
	
	
	public function update()
	{
		$parser = $this->getParser();
		$request = $this->application->getRequest();
		$parser->loadDataInRequest($request);
		$category = Category::getInstanceByID($request->get('ID'));
		$category->loadRequestData($request);
		$category->save();
	}
	
	
	public function create()
	{
		$parser = $this->getParser();
		$category=Category::getNewInstance($this->root);		
		$category->loadRequestData($parser->loadDataInRequest(
			$this->application->getRequest()
		));	
		$category->save();
	}
	
	public function filter()
	{
		$root = Category::getRootNode();
		
		$f = new ARSelectFilter();
		$f->mergeCondition(new NotEqualsCond(new ARFieldHandle('Category', 'ID'), $root->getID()));
		$f->setOrder(MultiLingualObject::getLangOrderHandle(new ARFieldHandle('Category', 'name')));

		// return new ActionResponse('categories', );
		
		$apiFieldNames = $this->getParser()->getApiFieldNames();
		$categories = ActiveRecordModel::getRecordSetArray('Category', $f);
		$response = new SimpleXMLElement('<response datetime="'.date('c').'"></response>');
		while($category = array_shift($categories))
		{
			$xmlCategory = $response->addChild('category');
			foreach($category as $k => $v)
			{
				// if(substr($k, 0, 2) != '__' && is_string($v)) // show every string whoes key does not start with __ (like __class__)
				                                                 // or maybe
				if(in_array($k, $apiFieldNames))                 // those who are allowed fields ($this->apiFieldNames) ?
				{
					// todo: how to escape in simplexml, cdata? create cdata or what?
					$xmlCategory->addChild($k, htmlentities($v));
				}
			}
		}
		return new SimpleXMLResponse($response);
	}
}
?>
