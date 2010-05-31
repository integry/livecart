<?php

ClassLoader::import('application.model.datasync.ModelApi');
ClassLoader::import('application.model.product.ProductPrice');
ClassLoader::import('application.model.datasync.api.reader.XmlPriceApiReader');
ClassLoader::import('application.helper.LiveCartSimpleXMLElement');
	
/**
 * Web service access layer for ProductPrice
 *
 * @package application.model.datasync
 * @author Integry Systems <http://integry.com>
 * 
 */

class ProductPriceApi extends ModelApi
{
	private $listFilterMapping = null;
	protected $application;

	public static function canParse(Request $request)
	{
		return parent::canParse($request, array('XmlProductPriceApiReader'));
	}

	public function __construct(LiveCart $application)
	{
		parent::__construct(
			$application,
			'ProductPrice',
			array('eavObjectID', 'preferences') // fields to ignore in User model
		);
		$this->removeSupportedApiActionName('create', 'filter', 'delete');
	}

	// ------ 
	public function update()
	{

	}

	public function get()
	{
		$request = $this->getApplication()->getRequest();
		echo $request->get('SKU');
	}
}

?>