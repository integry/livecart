<?php

ClassLoader::import("application.model.datasync.ModelApi");
ClassLoader::import("application.model.product.Product");
ClassLoader::import("application.model.category.Category");

class ProductApi extends ModelApi
{
	public static function canParse(Request $request)
	{
		return parent::canParse($request, array('XmlProductApiReader'));
	}

	public function __construct(LiveCart $application)
	{
		parent::__construct(
			$application,
			'Product',
			array_keys(Product::getNewInstance(Category::getRootNode())->getSchema()->getFieldList())
		);
	}



	public function get()
	{
		$request = $this->application->getRequest();
		$response = new SimpleXMLElement('<response datetime="'.date('c').'"></response>');
		$parser = $this->getParser();
		$parser->loadDataInRequest($request);
		$apiFieldNames = $parser->getApiFieldNames();
		$f = new ARSelectFilter();
		$f->mergeCondition(new EqualsCond(new ARFieldHandle('Product', 'sku'), $request->get('SKU')));
		$products = ActiveRecordModel::getRecordSetArray('Product', $f);
		$responseProduct = $response->addChild('product');
		if(count($products) == 0)
		{
			throw new Exception('Product not found');
		}
		while($product = array_shift($products))
		{
			foreach($product as $k => $v)
			{
				if(in_array($k, $apiFieldNames))
				{
					$responseProduct->addChild($k, $v);
				}
			}
		}
		return new SimpleXMLResponse($response);
	}

	public function __get_Object()
	{
		$request = $this->application->getRequest();
		$response = new SimpleXMLElement('<response datetime="'.date('c').'"></response>');
		$parser = $this->getParser();
		$parser->loadDataInRequest($request);
		$product = Product::getInstanceBySKU($request->get('SKU'));
		$apiFieldNames = $parser->getApiFieldNames();
		$responseProduct = $response->addChild('product');
		foreach($product as $k => $v)
		{
			if(in_array($k, $apiFieldNames))
			{
				// todo: how to escape in simplexml, cdata? create cdata or what?
				$v = $product->$k->get();
				if(is_string($v))
				{
					$responseProduct->addChild($k, $v);
				} else {
					// ..
				}
			}
		}
		return new SimpleXMLResponse($response);
	}
}
?>
