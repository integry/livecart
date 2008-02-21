<?php

ClassLoader::import('application.model.presentation.AbstractPresentation');
ClassLoader::import('application.model.category.Category');

/**
 * Store entity presentation configuration (products, categories)
 *
 * @package application.model.product
 * @author Integry Systems <http://integry.com>
 */
class CategoryPresentation extends AbstractPresentation
{
	public function getReferencedClass()
	{
		return 'Category';
	}

	public static function defineSchema($className = __CLASS__)
	{
		$schema = parent::defineSchema($className);
		$schema->registerField(new ARField("isSubcategories", ARBool::instance()));
	}

	public static function getNewInstance(Category $category)
	{
		$instance = parent::getNewInstance(__CLASS__);
		$instance->category->set($category);
		return $instance;
	}

	public static function getThemeByCategory(Category $category)
	{
		$own = new EqualsCond(new ARFieldHandle('CategoryPresentation', 'ID'), $category->getID());

		$parent = new EqualsOrLessCond(new ARFieldHandle('Category', 'lft'), $category->lft->get());
		$parent->addAND(new EqualsOrMoreCond(new ARFieldHandle('Category', 'rgt'), $category->rgt->get()));
		$parent->addAND(new EqualsCond(new ARFieldHandle('CategoryPresentation', 'isSubcategories'), true));

		$own->addOR($parent);

		$f = new ARSelectFilter($own);
		$f->setOrder(new ARExpressionHandle('CategoryPresentation.ID=' . $category->getID()), 'DESC');
		$f->setOrder(new ARFieldHandle('Category', 'lft'), 'DESC');
		$f->setLimit(1);

		$set = ActiveRecordModel::getRecordSet('CategoryPresentation', $f, array('Category'));
		if ($set->size())
		{
			return $set->get(0);
		}
	}
}

?>