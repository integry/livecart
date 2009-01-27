<?php

ClassLoader::import('application.controller.FrontendController');
ClassLoader::import('application.model.category.Category');
ClassLoader::import('application.model.product.Product');
ClassLoader::import('application.model.product.ProductCompare');

/**
 * Compare products
 *
 * @author Integry Systems
 * @package application.controller
 */
class CompareController extends FrontendController
{
	public function index()
	{
		$compare = new ProductCompare($this->application);
		return new ActionResponse(
					'products', $compare->getCompareData(),
					'return', $this->request->get('return')
					);
	}

	public function add()
	{
		$this->setLayout('empty');
		$compare = new ProductCompare($this->application);
		$added = $compare->addProductById($this->request->get('id'));

		if (!$this->request->get('ajax'))
		{
			return new RedirectResponse($this->router->createUrlFromRoute($this->request->get('return')));
		}
		else
		{
			return new ActionResponse(
					'products', $compare->getComparedProductInfo(),
					'added', $added
					);
		}
	}

	public function delete()
	{
		$compare = new ProductCompare($this->application);
		$compare->removeProductById($this->request->get('id'));

		if ($this->request->get('ajax'))
		{

		}
		else
		{
			return new RedirectResponse($this->router->createUrlFromRoute($this->request->get('return')));
		}
	}

	public function compareMenuBlock()
	{
		$compare = new ProductCompare($this->application);
		return new BlockResponse('products', $compare->getComparedProductInfo());
	}
}

?>