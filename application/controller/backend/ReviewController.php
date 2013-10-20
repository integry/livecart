<?php


/**
 *
 * @package application/controller/backend
 * @author Integry Systems
 * @role product
 */
class ReviewController extends ActiveGridController
{
	public function indexAction()
	{
		$response = $this->getGridResponse();
		$this->set('id', ($this->isCategory() ? 'c' : '') . $this->getID());
		$this->set('container', $this->request->get('category') ? 'tabReviews' : 'tabProductReviews');
	}

	public function editAction()
	{
		$review = ProductReview::getInstanceByID($this->request->get('id'), ProductReview::LOAD_DATA, array('Product'));
		//$manufacturer->getSpecification();

		$this->set('review', $review->toArray());
		$form = $this->buildForm($review);
		$form->setData($review->toArray());

		// get ratings
		foreach ($review->getRelatedRecordSetArray('ProductRating', new ARSelectFilter()) as $rating)
		{
			$form->set('rating_' . $rating['ratingTypeID'], $rating['rating']);
		}

		$form->set('rating_', $review->rating);

		//$manufacturer->getSpecification()->setFormResponse($response, $form);
		$this->set('form', $form);
		$this->set('ratingTypes', ProductRatingType::getProductRatingTypes($review->product)->toArray());

		$options = range(1, $this->config->get('RATING_SCALE'));
		$this->set('ratingOptions', array_combine($options, $options));

	}

	public function updateAction()
	{
		$review = ProductReview::getInstanceByID($this->request->get('id'), ProductReview::LOAD_DATA, array('Product'));
		$validator = $this->buildValidator($review);

		if ($validator->isValid())
		{
			$review->loadRequestData($this->request);
			$review->save();

			// set ratings
			foreach ($review->getRelatedRecordSet('ProductRating', new ARSelectFilter()) as $rating)
			{
				$typeId = $rating->ratingType ? $rating->ratingType->getID() : '';
				$rating->rating->set($this->request->get('rating_' . $typeId));
				$rating->save();
			}

			return new JSONResponse(array('review' => $review->toFlatArray()), 'success', $this->translate('_review_was_successfully_saved'));
		}
		else
		{
			return new JSONResponse(array('errors' => $validator->getErrorList()), 'failure');
		}
	}

	public function changeColumnsAction()
	{
		parent::changeColumns();
		return $this->getGridResponse();
	}

	private function getGridResponse()
	{
		$this->loadLanguageFile('backend/Category');


		$this->setGridResponse($response);
	}

	protected function getClassName()
	{
		return 'ProductReview';
	}

	protected function getCSVFileName()
	{
		return 'reviews.csv';
	}

	protected function getDefaultColumns()
	{
		return array('ProductReview.ID', 'ProductReview.title', 'Product.name', 'ProductReview.nickname', 'ProductReview.dateCreated', 'ProductReview.isEnabled');
	}

	protected function getDisplayedColumns()
	{
		return parent::getDisplayedColumns(null, array('Product.ID' => 'numeric'));
	}

	public function getAvailableColumnsAction()
	{
		$availableColumns = parent::getAvailableColumns();

		unset($availableColumns['ProductReview.ratingSum']);
		unset($availableColumns['ProductReview.ratingCount']);
		unset($availableColumns['ProductReview.ip']);
		unset($availableColumns['Product.ID']);

		return $availableColumns;
	}

	protected function getCustomColumns()
	{
		if ($this->isCategory())
		{
			$availableColumns['Product.name'] = 'text';
			$availableColumns['Product.ID'] = 'numeric';

			return $availableColumns;
		}

		return array();
	}

	protected function setDefaultSortorderBy(ARSelectFilter $filter)
	{
		$filter->orderBy(new ARFieldHandle($this->getClassName(), 'ID'), 'DESC');
	}

	protected function getSelectFilter()
	{
		$id = $this->getID();

		if ($this->isCategory())
		{
			$owner = Category::getInstanceByID($id, Category::LOAD_DATA);

			$cond = new EqualsOrMoreCond(new ARFieldHandle('Category', 'lft'), $owner->lft);
			$cond->addAND(new EqualsOrLessCond(new ARFieldHandle('Category', 'rgt'), $owner->rgt));
		}
		else
		{
			$cond = new EqualsCond(new ARFieldHandle('ProductReview', 'productID'), $id);
		}

		return new ARSelectFilter($cond);
	}

	private function isCategory()
	{
		$id = array_pop(explode('_', $this->request->get('id')));
		return (substr($id, 0, 1) == 'c') || $this->request->get('category');
	}

	private function getID()
	{
		$id = array_pop(explode('_', $this->request->get('id')));

		if ($this->isCategory() && (substr($id, 0, 1) == 'c'))
		{
			$id = substr($id, 1);
		}

		return $id;
	}

	protected function getReferencedData()
	{
		return array('Product', 'Category');
	}

	private function buildValidator(ProductReview $review)
	{
		$validator = $this->getValidator("productRating", $this->getRequest());

		// option validation
		foreach (ProductRatingType::getProductRatingTypes($review->product)->toArray() as $type)
		{
			$validator->add('rating_' . $type['ID'], new Validator\PresenceOf(array('message' => $this->translate('_err_no_rating_selected'))));
		}

		$validator->add('nickname', new Validator\PresenceOf(array('message' => $this->translate('_err_no_review_nickname'))));
		$validator->add('title', new Validator\PresenceOf(array('message' => $this->translate('_err_no_review_summary'))));
		$validator->add('text', new Validator\PresenceOf(array('message' => $this->translate('_err_no_review_text'))));

		return $validator;
	}

	/**
	 * Builds a category form instance
	 *
	 * @return Form
	 */
	private function buildForm(ProductReview $review)
	{
		return new Form($this->buildValidator($review));
	}
}

?>