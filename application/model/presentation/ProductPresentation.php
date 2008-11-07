<?php

ClassLoader::import('application.model.presentation.AbstractPresentation');
ClassLoader::import('application.model.product.Product');

/**
 * Store entity presentation configuration (products, categories)
 *
 * @package application.model.product
 * @author Integry Systems <http://integry.com>
 */
class ProductPresentation extends AbstractPresentation
{
	public function getReferencedClass()
	{
		return 'Product';
	}

	public static function defineSchema($className = __CLASS__)
	{
		parent::defineSchema($className);
	}

	public static function getNewInstance(Product $product)
	{
		$instance = parent::getNewInstance(__CLASS__);
		$instance->product->set($product);
		return $instance;
	}

	public static function getThemeByProduct(Product $product)
	{
		// check if a theme is defined for this product particularly
		$set = $product->getRelatedRecordSet('ProductPresentation', new ARSelectFilter());
		if ($set->size())
		{
			return $set->get(0);
		}

		// otherwise use the category theme
		ClassLoader::import('application.model.presentation.CategoryPresentation');
		return CategoryPresentation::getThemeByCategory($product->getCategory());
	}
}

?>