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
		parent::__construct($application, 'Category');
	}
	
	
	public function filter()
	{
		$root = Category::getRootNode();
		$f = new ARSelectFilter(new MoreThanCond(new ARFieldHandle('Category', $root->getProductCountField()), 0));
		$f->mergeCondition(new NotEqualsCond(new ARFieldHandle('Category', 'ID'), $root->getID()));
		$f->setOrder(MultiLingualObject::getLangOrderHandle(new ARFieldHandle('Category', 'name')));

		// return new ActionResponse('categories', );
		$categories = ActiveRecordModel::getRecordSetArray('Category', $f);
		$response = new SimpleXMLElement('<response datetime="'.date('c').'"></response>');
		while($category = array_shift($categories))
		{
			$xmlCategory = $response->addChild('category');
			foreach($category as $k => $v)
			{
				if(substr($k, 0, 2) != '__' && is_string($v)) // show every string whoes key does not start with __ (like __class__)
				{
					// todo: how to escape in simplexml, cdata? create cdata or what is was
					//       need manual :)
					$xmlCategory->addChild($k, htmlentities($v,'utf-8'));
				}
			}
		}
		return new SimpleXMLResponse($response);
	}
}
?>
