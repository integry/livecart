<?php

ClassLoader::import('application.model.datasync.ModelApi');
ClassLoader::import('application.model.product.Product');
ClassLoader::import('application.model.datasync.api.reader.XmlStockApiReader');
ClassLoader::import('application.helper.LiveCartSimpleXMLElement');
	
/**
 * Web service access layer for Stock
 *
 * @package application.model.datasync
 * @author Integry Systems <http://integry.com>
 * 
 */

class StockApi extends ModelApi
{
	private $listFilterMapping = null;
	protected $application;

	public static function canParse(Request $request)
	{
		return parent::canParse($request, array('XmlStockApiReader'));
	}

	public function __construct(LiveCart $application)
	{
		parent::__construct(
			$application,
			null,
			array()
		);
		$this->removeSupportedApiActionName('create','update','list', 'filter', 'delete');
		$this->addSupportedApiActionName('set');
	}

	// ------ 
	public function set()
	{
		$request = $this->getApplication()->getRequest();
		$sku = $request->get('sku');


		return $this->statusResponse($sku, 'updated');
	}


	public function get()
	{
		$request = $this->getApplication()->getRequest();
		$product = Product::getInstanceBySku($request->get('SKU'));
		if($product == null)
		{
			throw new Exception('Product not found');
		}
		$response = new LiveCartSimpleXMLElement('<response datetime="'.date('c').'"></response>');		
		$product = $product->toArray();		
		if(!$product['stockCount'])
		{
			$product['stockCount'] = '0';
		}
		$this->fillSimpleXmlResponseItem($response, $product);
		return new SimpleXMLResponse($response);
	}
	
	public function fillSimpleXmlResponseItem($xml, $product)
	{
		//pp($product);
		$fieldNames = array('sku','stockCount');
		foreach($fieldNames as $fieldName)
		{
			$xml->addChild($fieldName, $product[$fieldName]);
		}
	}
}

?>