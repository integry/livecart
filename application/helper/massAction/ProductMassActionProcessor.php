<?php

include_once dirname(__file__) . '/MassActionProcessor.php';

/**
 * @package application.helper.massAction
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
		else if ('inc_price' == $act)
		{
			$pricing = $product->getPricingHandler();
			foreach ($this->params['currencies'] as $currency)
			{
				if ($pricing->isPriceSet($currency))
				{
					$p = $pricing->getPrice($currency);
					$p->increasePriceByPercent($this->params['price']);
				}
			}
		}
		else if ('inc_stock' == $act)
		{
			$product->stockCount->set($product->stockCount->get() + $this->request->get($act));
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
			if (!$relation->isExistingRecord() && ($product->category->get() !== $category))
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
		else
		{
			parent::processRecord($product);
		}
	}
}

?>