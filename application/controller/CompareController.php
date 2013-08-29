<?php


/**
 * Compare products
 *
 * @author Integry Systems
 * @package application/controller
 */
class CompareController extends FrontendController
{
	public function indexAction()
	{
		$compare = new ProductCompare($this->application);
		return new ActionResponse(
					'products', $compare->getCompareData(),
					'return', $this->request->get('return')
					);
	}

	public function addAction()
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
					'added', $added,
					'return', $this->request->get('return')
					);
		}
	}

	public function deleteAction()
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

	public function compareMenuBlockAction()
	{
		$compare = new ProductCompare($this->application);
		return new BlockResponse('products', $compare->getComparedProductInfo());
	}
}

?>