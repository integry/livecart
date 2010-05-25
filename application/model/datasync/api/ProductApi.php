<?php

ClassLoader::import('application.model.datasync.ModelApi');
ClassLoader::import('application.model.product.Product');
ClassLoader::import('application.model.category.Category');

/**
 * Web service access layer for Product model
 *
 * @package application.model.datasync
 * @author Integry Systems <http://integry.com>
 * 
 */

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

		$parser = $this->getParser();
		$parser->loadDataInRequest($request);
		$products = ActiveRecordModel::getRecordSetArray('Product',
			select(eq(f('Product.sku'), $request->get('SKU')))
		);
		if(count($products) == 0)
		{
			throw new Exception('Product not found');
		}

		// --
		$response = new SimpleXMLElement('<response datetime="'.date('c').'"></response>');
		$responseProduct = $response->addChild('product');
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
	
}
?>
