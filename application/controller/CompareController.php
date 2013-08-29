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
					'return', $this->request->gget('return')
					);
	}

	public function addAction()
	{
		$this->setLayout('empty');
		$compare = new ProductCompare($this->application);
		$added = $compare->addProductById($this->request->gget('id'));

		if (!$this->request->gget('ajax'))
		{
			return new RedirectResponse($this->router->createUrlFromRoute($this->request->gget('return')));
		}
		else
		{
			return new ActionResponse(
					'products', $compare->getComparedProductInfo(),
					'added', $added,
					'return', $this->request->gget('return')
					);
		}
	}

	public function deleteAction()
	{
		$compare = new ProductCompare($this->application);
		$compare->removeProductById($this->request->gget('id'));

		if ($this->request->gget('ajax'))
		{

		}
		else
		{
			return new RedirectResponse($this->router->createUrlFromRoute($this->request->gget('return')));
		}
	}

	public function compareMenuBlockAction()
	{
		$compare = new ProductCompare($this->application);
		return new BlockResponse('products', $compare->getComparedProductInfo());
	}
}

?>