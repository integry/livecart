<?php

ClassLoader::import('application.model.datasync.ModelApi');
ClassLoader::import('application.model.product.Product');
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

		$product = Product::getInstanceBySku($sku, array('ProductPrice'));
		if($product == null)
		{
			throw new Exception('Product not found');
		}

		$currency = $request->get('currency');
		
		$price=$request->get('definedPrice');
		if(is_numeric($price))
		{
			$product->setPrice($currency, $price, false);
		}
		
		$price=$request->get('definedListPrice');
		if(is_numeric($price))
		{
			$product->setPrice($currency, $price, true);
		}
	
		$quantityPrices = $request->get('quantityPrices');
		$groupedQuantityPrices = array();
		
		foreach($quantityPrices as $item)
		{
			if($item['currency'] == '')
			{
				$item['currency'] = $currency;
			}
			
			if($item['group'] == '')
			{
				$item['group'] = 0;
			}
			$groupedQuantityPrices[$item['currency']][$item['quantity']][$item['group']] = $item['price'];
		}

		foreach ($product->getRelatedRecordSet('ProductPrice', new ARSelectFilter()) as $productPrice)
		{
			$currencyID = $productPrice->currency->get()->getID();
			if(array_key_exists($currencyID, $groupedQuantityPrices))
			{
				// todo: append or rewrite here?
				$rules = unserialize($productPrice->serializedRules->get());
				if(!is_array($rules))
				{
					$rules = array();
				}
				foreach($groupedQuantityPrices[$currencyID] as $quanty => $qItem)
				{
					foreach($qItem as $group => $price)
					{
						$rules[$quanty][$group] = $price;
					}
				}
				$productPrice->serializedRules->set(serialize($rules));
				$productPrice->save();
				unset($groupedQuantityPrices[$currencyID]); // for this currency saved
			}
		}

		if(count($groupedQuantityPrices) > 0)
		{
			// there is missing ProductPrice for some currencies,
			// will try to save as  new ProductPrice items
			
			foreach($groupedQuantityPrices as $currency => $rules)
			{
				$productPrice = ProductPrice::getNewInstance($product, Currency::getInstanceById($currency));
				$productPrice->serializedRules->set(serialize($rules));
				$productPrice->save();
			}
		}
		
		$product->save();

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
		$products = array($product->toArray());
		ProductPrice::loadPricesForRecordSetArray($products);

		$response = new LiveCartSimpleXMLElement('<response datetime="'.date('c').'"></response>');
		foreach($products as $product)
		{
			$this->fillSimpleXmlResponseItem($response, $product);
		}
		
		return new SimpleXMLResponse($response);
	}
	
	public function fillSimpleXmlResponseItem($xml, $product)
	{
		// product info
		$fieldNames = array('sku');
		foreach($fieldNames as $fieldName)
		{
			$xml->addChild($fieldName, $product[$fieldName]);
		}
		
		// pricing info
	
		foreach(array('definedPrices', 'definedListPrices') as $key)
		{
			if(array_key_exists($key, $product))
			{
				$xmlGrop = $xml->AddChild($key);
				foreach($product[$key] as $currency => $value)
				{
					$xmlGrop->addChild($currency, $value);
				}
			}
		}

		$xmlGrop = $xml->addChild('quantityPrices');
		
		if(array_key_exists('prices', $product) && is_array($product['prices']))
		{
			foreach($product['prices'] as $currency => $pricingDetails)
			{
				if(array_key_exists('quantityPrices', $pricingDetails))
				{
					foreach($pricingDetails['quantityPrices'] as $quantityPrice)
					{
						foreach($quantityPrice as $quantity=>$item)
						{
							$xmlQp = $xmlGrop->addChild('quantityPrice');
							$xmlQp->addChild('currency', $currency);
							foreach(array(/*'originalPrice',*/'price','from','to') as $itemFieldName)
							{
								$xmlQp->addChild($itemFieldName, $item[$itemFieldName]);
							}
						}
					}
				}
			}
		}

		// pp($product);
	}
}

?>