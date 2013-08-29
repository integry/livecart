<?php

include_once dirname(__file__) . '/MassActionProcessor.php';

/**
 * @package application/helper/massAction
 * @author Integry Systems
 */
class ProductMassActionProcessor extends MassActionProcessor
{
	protected function processSet(ARSet $set)
	{
		if (isset($this->params['price']))
		{
			ProductPrice::loadPricesForRecordSet($set);
		}

		return parent::processSet($set);
	}

	protected function processRecord(Product $product)
	{
		$act = $this->getAction();
		$field = $this->getField();

		if ('manufacturer' == $act)
		{
			$product->manufacturer->set($this->params['manufacturer']);
		}
		else if ('price' == $act)
		{
			$product->setPrice($this->params['baseCurrency'], $this->params['price']);
		}
		else if (in_array($act, array('inc_price', 'multi_price', 'div_price')))
		{
			$actions = array('inc_price' => 'increasePriceByPercent', 'multi_price' => 'multiplyPrice', 'div_price' => 'dividePrice');
			$action = $actions[$act];
			$pricing = $product->getPricingHandler();
			foreach ($this->params['currencies'] as $currency)
			{
				if ($pricing->isPriceSet($currency))
				{
					$p = $pricing->getPrice($currency);
					$p->$action($this->params['inc_price_value'], $this->params['inc_quant_price']);
					$p->save();
				}
			}
		}
		else if ('inc_stock' == $act)
		{
			$product->stockCount->set($product->stockCount + $this->request->gget($act));
		}
		else if ('addRelated' == $act)
		{
			$product->addRelatedProduct($this->params['relatedProduct']);
		}
		else if ('copy' == $act)
		{
			$cloned = clone $product;
			$cloned->category->set($this->params['category']);
			$cloned->save();
		}
		else if ('addCat' == $act)
		{
			// check if the product is not assigned to this category already
			$relation = ActiveRecordModel::getInstanceByIdIfExists('ProductCategory', array('productID' => $product->getID(), 'categoryID' => $this->params['category']->getID()));
			if (!$relation->isExistingRecord() && ($product->category !== $category))
			{
				$relation->save();
			}
		}
		else if ('theme' == $act)
		{
			$instance = CategoryPresentation::getInstance($product);
			$instance->theme->set($this->params['theme']);
			$instance->save();
		}
		else if ('shippingClass' == $act)
		{
			$product->shippingClass->set(ActiveRecordModel::getInstanceByIDIfExists('ShippingClass', $this->params['shippingClass'], false));
		}
		else if ('taxClass' == $act)
		{
			$product->taxClass->set(ActiveRecordModel::getInstanceByIDIfExists('TaxClass', $this->params['taxClass'], false));
		}
		else if (substr($act, 0, 13) == 'set_specField')
		{
			$this->params['request']->remove('manufacturer');
			$product->loadRequestData($this->params['request']);
		}
		else if (substr($act, 0, 16) == 'remove_specField')
		{
			$this->params['request']->remove('manufacturer');
			if ($this->params['field']->isMultiValue)
			{
				// remove only selected multi-select options
				$product->loadRequestData($this->params['request']);
			}
			else
			{
				$product->removeAttribute($this->params['field']);
			}
		}
		else
		{
			parent::processRecord($product);
		}
	}
}

?>
